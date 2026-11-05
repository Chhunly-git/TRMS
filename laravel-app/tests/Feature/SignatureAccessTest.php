<?php

namespace Tests\Feature;

use App\Models\InboundDocumentResponse;
use App\Models\InboundDocumentResponseApproval;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SignatureAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_signature_image(): void
    {
        $response = $this->getJson('/api/users/1/signature-image');
        $response->assertStatus(401);
    }

    public function test_user_can_access_own_signature_image(): void
    {
        Storage::fake(StorageService::diskName());
        Storage::disk(StorageService::diskName())->put('users/signatures/my_sig.png', 'fake-image-bytes');

        $user = User::factory()->create([
            'signature_image' => 'users/signatures/my_sig.png',
            'status' => 'ACTIVE',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/users/{$user->id}/signature-image");
        $response->assertRedirect();
        $this->assertStringContainsString('/api/files?', $response->headers->get('Location'));
        $this->get($response->headers->get('Location'))->assertStatus(200);

        $myResponse = $this->getJson('/api/my-signature');
        $myResponse->assertRedirect();
        $this->assertStringContainsString('/api/files?', $myResponse->headers->get('Location'));
        $this->get($myResponse->headers->get('Location'))->assertStatus(200);
    }

    public function test_unrelated_user_cannot_access_another_users_signature_image(): void
    {
        Storage::fake(StorageService::diskName());
        Storage::disk(StorageService::diskName())->put('users/signatures/sig_user2.png', 'fake-image-bytes');

        $user1 = User::factory()->create(['level' => 'OFFICER', 'status' => 'ACTIVE']);
        $user2 = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/sig_user2.png',
        ]);

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/users/{$user2->id}/signature-image");
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_signature_of_user_without_accessible_documents(): void
    {
        Storage::fake(StorageService::diskName());
        Storage::disk(StorageService::diskName())->put('users/signatures/sig_isolated.png', 'fake-image-bytes');

        $admin = User::factory()->create(['level' => 'ADMIN', 'status' => 'ACTIVE']);
        $isolatedUser = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/sig_isolated.png',
        ]);

        Sanctum::actingAs($admin);

        // Blanket ADMIN bypass was removed: since isolatedUser has no responses or approvals in the system, admin gets 403
        $response = $this->getJson("/api/users/{$isolatedUser->id}/signature-image");
        $response->assertStatus(403);
    }

    public function test_viewer_can_access_signature_of_drafter_on_accessible_document(): void
    {
        Storage::fake(StorageService::diskName());
        Storage::disk(StorageService::diskName())->put('users/signatures/sig_drafter.png', 'fake-image-bytes');

        $drafter = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/sig_drafter.png',
        ]);

        $viewer = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        // Create document drafted by $drafter and currently assigned to / approved by $viewer
        InboundDocumentResponse::create([
            'title' => 'សេចក្តីរាយការណ៍សាកល្បង',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $viewer->id,
            'status' => 'UNDER_REVIEW',
            'current_stage' => 'OFFICE',
        ]);

        Sanctum::actingAs($viewer);

        $response = $this->getJson("/api/users/{$drafter->id}/signature-image");
        $response->assertRedirect();
        $this->assertStringContainsString('/api/files?', $response->headers->get('Location'));
        $this->get($response->headers->get('Location'))->assertStatus(200);
    }

    public function test_viewer_can_access_signature_of_approver_on_accessible_document(): void
    {
        Storage::fake(StorageService::diskName());
        Storage::disk(StorageService::diskName())->put('users/signatures/sig_approver.png', 'fake-image-bytes');

        $drafter = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $approver = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/sig_approver.png',
        ]);

        $viewer = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        // Create document where $viewer is the current approver
        $doc = InboundDocumentResponse::create([
            'title' => 'របាយការណ៍រួម',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $viewer->id,
            'status' => 'UNDER_REVIEW',
            'current_stage' => 'DEPARTMENT',
        ]);

        // Add approval record signed by $approver
        InboundDocumentResponseApproval::create([
            'response_id' => $doc->id,
            'user_id' => $approver->id,
            'action' => 'FORWARD',
            'stage' => 'OFFICE',
            'comment' => 'ឯកភាព',
        ]);

        Sanctum::actingAs($viewer);

        $response = $this->getJson("/api/users/{$approver->id}/signature-image");
        $response->assertRedirect();
        $this->assertStringContainsString('/api/files?', $response->headers->get('Location'));
        $this->get($response->headers->get('Location'))->assertStatus(200);
    }
}
