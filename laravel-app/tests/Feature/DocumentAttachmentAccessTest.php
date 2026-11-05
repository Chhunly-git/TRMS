<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\DocumentWorkflowConfig;
use App\Models\InboundDocument;
use App\Models\InboundDocumentResponse;
use App\Models\Position;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentAttachmentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(StorageService::diskName());
    }

    public function test_guest_cannot_access_outbound_or_inbound_files(): void
    {
        $this->getJson('/api/outbound-documents/1/download-file')->assertStatus(401);
        $this->getJson('/api/outbound-documents/1/view-file')->assertStatus(401);
        $this->getJson('/api/inbound-documents/responses/1/download')->assertStatus(401);
        $this->getJson('/api/inbound-documents/responses/1/view')->assertStatus(401);
        $this->getJson('/api/inbound-documents/1/download-original')->assertStatus(401);
        $this->getJson('/api/inbound-documents/1/view-original')->assertStatus(401);
    }

    public function test_unrelated_user_cannot_download_or_view_response_attachment_idor(): void
    {
        $dept1 = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $dept2 = Department::create(['code' => 'D2', 'name_kh' => 'នាយកដ្ឋាន ២', 'name_en' => 'Dept 2']);
        $posOfficer = Position::create(['title_kh' => 'មន្ត្រី', 'title_en' => 'Officer', 'level' => 9]);

        $drafter = User::factory()->create([
            'department_id' => $dept1->id,
            'position_id' => $posOfficer->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $unrelatedUser = User::factory()->create([
            'department_id' => $dept2->id,
            'position_id' => $posOfficer->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        // Restrict unrelatedUser's workflow config to SELF_ONLY
        DocumentWorkflowConfig::create([
            'user_id' => $unrelatedUser->id,
            'view_type' => 'SELF_ONLY',
        ]);

        $filePath = 'documents/responses/secret_attachment.pdf';
        Storage::disk(StorageService::diskName())->put($filePath, 'secret-pdf-content');

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Secret Report',
            'drafted_by' => $drafter->id,
            'department_id' => $dept1->id,
            'file_path' => $filePath,
            'file_name' => 'secret_attachment.pdf',
            'status' => 'DRAFT',
        ]);

        // Unrelated user tries to access via Outbound endpoints
        Sanctum::actingAs($unrelatedUser);
        $this->getJson("/api/outbound-documents/{$responseDoc->id}/download-file")->assertStatus(403);
        $this->getJson("/api/outbound-documents/{$responseDoc->id}/view-file")->assertStatus(403);

        // Unrelated user tries to access via Inbound endpoints
        $this->getJson("/api/inbound-documents/responses/{$responseDoc->id}/download")->assertStatus(403);
        $this->getJson("/api/inbound-documents/responses/{$responseDoc->id}/view")->assertStatus(403);
    }

    public function test_authorized_drafter_can_download_and_view_response_attachment(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $posOfficer = Position::create(['title_kh' => 'មន្ត្រី', 'title_en' => 'Officer', 'level' => 9]);

        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'position_id' => $posOfficer->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $filePath = 'documents/responses/my_report.pdf';
        Storage::disk(StorageService::diskName())->put($filePath, 'report-pdf-content');

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'My Report',
            'drafted_by' => $drafter->id,
            'department_id' => $dept->id,
            'file_path' => $filePath,
            'file_name' => 'my_report.pdf',
            'status' => 'DRAFT',
        ]);

        Sanctum::actingAs($drafter);

        // Outbound download & view
        $downloadRes = $this->get("/api/outbound-documents/{$responseDoc->id}/download-file");
        $downloadRes->assertStatus(200);

        $viewRes = $this->get("/api/outbound-documents/{$responseDoc->id}/view-file");
        $viewRes->assertStatus(200);

        // Inbound download & view
        $inboundDownload = $this->get("/api/inbound-documents/responses/{$responseDoc->id}/download");
        $inboundDownload->assertStatus(200);

        $inboundView = $this->get("/api/inbound-documents/responses/{$responseDoc->id}/view");
        $inboundView->assertStatus(200);
    }

    public function test_inbound_original_document_idor_protection(): void
    {
        $dept1 = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $dept2 = Department::create(['code' => 'D2', 'name_kh' => 'នាយកដ្ឋាន ២', 'name_en' => 'Dept 2']);
        $posOfficer = Position::create(['title_kh' => 'មន្ត្រី', 'title_en' => 'Officer', 'level' => 9]);

        $recipient = User::factory()->create([
            'department_id' => $dept1->id,
            'position_id' => $posOfficer->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $unrelatedUser = User::factory()->create([
            'department_id' => $dept2->id,
            'position_id' => $posOfficer->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        DocumentWorkflowConfig::create([
            'user_id' => $unrelatedUser->id,
            'view_type' => 'SELF_ONLY',
        ]);

        $filePath = 'documents/inbound/confidential_inbound.pdf';
        Storage::disk(StorageService::diskName())->put($filePath, 'inbound-pdf-content');

        $inboundDoc = InboundDocument::create([
            'title' => 'Confidential Inbound',
            'sender_organization' => 'Ministry of Economy and Finance',
            'received_date' => now()->toDateString(),
            'received_time' => now()->toTimeString(),
            'deliverer_name' => 'Courier',
            'original_file_path' => $filePath,
            'original_file_name' => 'confidential_inbound.pdf',
            'registered_by' => $recipient->id,
            'target_user_id' => $recipient->id,
            'status' => 'DISPATCHED',
        ]);

        // Unrelated user
        Sanctum::actingAs($unrelatedUser);
        $this->getJson("/api/inbound-documents/{$inboundDoc->id}/download-original")->assertStatus(403);
        $this->getJson("/api/inbound-documents/{$inboundDoc->id}/view-original")->assertStatus(403);

        // Recipient user
        Sanctum::actingAs($recipient);
        $this->get("/api/inbound-documents/{$inboundDoc->id}/download-original")->assertStatus(200);
        $this->get("/api/inbound-documents/{$inboundDoc->id}/view-original")->assertStatus(200);
    }
}
