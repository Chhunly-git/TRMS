<?php

namespace Tests\Feature;

use App\Models\InboundDocumentResponse;
use App\Models\InboundDocumentResponseRevision;
use App\Models\User;
use App\Services\FileUrlService;
use App\Services\StorageService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FileAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(StorageService::diskName());
    }

    public function test_signed_url_works_without_a_token(): void
    {
        $filePath = 'users/profile-images/avatar_test.jpg';
        Storage::disk(StorageService::diskName())->put($filePath, 'fake-image-binary-data');

        $signedUrl = FileUrlService::signed($filePath, 30);

        // Make raw HTTP GET without any Authorization header or session
        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Cache-Control', 'max-age=1800, private');
        $this->assertEquals('fake-image-binary-data', $response->streamedContent());
    }

    public function test_tampered_path_returns_403(): void
    {
        $filePath = 'users/profile-images/avatar_test.jpg';
        Storage::disk(StorageService::diskName())->put($filePath, 'data1');
        Storage::disk(StorageService::diskName())->put('users/profile-images/avatar_other.jpg', 'data2');

        $signedUrl = FileUrlService::signed($filePath, 30);

        // Tamper with query parameter path
        $tamperedUrl = str_replace('avatar_test.jpg', 'avatar_other.jpg', $signedUrl);

        $response = $this->get($tamperedUrl);
        $response->assertStatus(403);
    }

    public function test_expired_signed_url_returns_403(): void
    {
        $filePath = 'users/profile-images/avatar_test.jpg';
        Storage::disk(StorageService::diskName())->put($filePath, 'data');

        $signedUrl = FileUrlService::signed($filePath, 30);

        // Travel 65 minutes into the future to cross the rounded 30-min window
        Carbon::setTestNow(now()->addMinutes(65));

        $response = $this->get($signedUrl);
        $response->assertStatus(403);

        Carbon::setTestNow(); // Reset time
    }

    public function test_path_outside_whitelist_returns_403(): void
    {
        // Even if signed with app key, controller blocks paths outside whitelist
        $invalidPath = 'secrets/passwords.txt';
        Storage::disk(StorageService::diskName())->put($invalidPath, 'secret-data');

        $signedUrl = URL::temporarySignedRoute('files.show', now()->addMinutes(30), ['path' => $invalidPath]);

        $response = $this->get($signedUrl);
        $response->assertStatus(403);
    }

    public function test_path_with_directory_traversal_returns_403(): void
    {
        $traversalPath = 'users/profile-images/../../etc/passwd';

        $signedUrl = URL::temporarySignedRoute('files.show', now()->addMinutes(30), ['path' => $traversalPath]);

        $response = $this->get($signedUrl);
        $response->assertStatus(403);
    }

    public function test_unrelated_user_gets_no_signature_url_in_api_responses(): void
    {
        Storage::disk(StorageService::diskName())->put('users/signatures/sig_user2.png', 'sig-bytes');

        $viewer = User::factory()->create(['level' => 'OFFICER', 'status' => 'ACTIVE']);
        $otherUser = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/sig_user2.png',
        ]);

        Sanctum::actingAs($viewer);

        // $otherUser has no shared documents with $viewer
        $this->assertNull($otherUser->signature_image);
    }

    public function test_legacy_document_content_returns_rewritten_signed_urls_while_db_remains_unchanged(): void
    {
        $sigFile = 'users/signatures/sig_8243af03-093e-496d-9e95-eb831300a666.png';
        Storage::disk(StorageService::diskName())->put($sigFile, 'sig-png-content');

        $legacyHtml = '<p>អត្ថបទ</p><img src="http://localhost:9000/trms-bucket/' . $sigFile . '" alt="ហត្ថលេខា" />';

        $author = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => $sigFile,
        ]);

        $docId = DB::table('inbound_document_responses')->insertGetId([
            'title' => 'លិខិតចាស់',
            'content' => $legacyHtml,
            'drafted_by' => $author->id,
            'status' => 'DRAFT',
            'current_stage' => 'OFFICE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $doc = InboundDocumentResponse::find($docId);

        // 1. Raw DB content MUST remain completely unchanged
        $rawContent = DB::table('inbound_document_responses')->where('id', $docId)->value('content');
        $this->assertEquals($legacyHtml, $rawContent);

        // 2. Accessor content MUST return rewritten signed URL
        $renderedContent = $doc->content;
        $this->assertStringNotContainsString('http://localhost:9000/trms-bucket/', $renderedContent);
        $this->assertStringContainsString('/api/files?', $renderedContent);
        $this->assertStringContainsString('path=users%2Fsignatures%2F', $renderedContent);
        $this->assertStringContainsString('signature=', $renderedContent);

        // 3. Extract the generated signed URL and test fetching it
        preg_match('/src="([^"]+)"/', $renderedContent, $matches);
        $this->assertNotEmpty($matches[1]);

        $fileResponse = $this->get($matches[1]);
        $fileResponse->assertStatus(200);
        $this->assertEquals('sig-png-content', $fileResponse->streamedContent());
    }
}
