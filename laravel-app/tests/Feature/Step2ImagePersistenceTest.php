<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\InboundDocumentResponse;
use App\Models\InboundDocumentResponseApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Step2ImagePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_document_normalizes_signed_and_legacy_urls_to_data_file_path_without_signature_param(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/drafter_sig.png',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Report with Signatures',
            'drafted_by' => $drafter->id,
            'department_id' => $dept->id,
            'content' => '<p>Initial</p>',
            'status' => 'DRAFT',
        ]);

        Sanctum::actingAs($drafter);

        $htmlWithSignedUrlAndOpinionBox = '
            <div class="draggable-opinion-box" data-draggable="true" data-owner-id="99" data-signer-name="ឯកឧត្តម">
                <span class="box-handle handle-br" data-handle="br"></span>
                <div class="opinion-box-comment">ឯកភាពតាមការស្នើសុំ</div>
                <img src="http://localhost:8000/api/files?path=users%2Fsignatures%2Fleader_sig.png&expires=1799999999&signature=abcdef123456" alt="ហត្ថលេខា">
            </div>
            <table class="drafter-signature-table">
                <tr>
                    <td class="opinion-left-col"></td>
                    <td>
                        <img src="http://minio-service:9000/trms-bucket/users/signatures/drafter_sig.png" alt="ហត្ថលេខា">
                    </td>
                </tr>
            </table>
        ';

        $res = $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => $htmlWithSignedUrlAndOpinionBox,
        ]);
        $res->assertStatus(200);

        // Fetch raw DB value directly to ensure NO signed URL params or MinIO bucket URLs are stored
        $rawStored = DB::table('inbound_document_responses')->where('id', $responseDoc->id)->value('content');

        // Verify data-file-path attributes were added
        $this->assertStringContainsString('data-file-path="users/signatures/leader_sig.png"', $rawStored);
        $this->assertStringContainsString('data-file-path="users/signatures/drafter_sig.png"', $rawStored);

        // Verify expiring query parameters and bucket hostnames are NOT in database
        $this->assertStringNotContainsString('signature=', $rawStored);
        $this->assertStringNotContainsString('expires=', $rawStored);
        $this->assertStringNotContainsString('trms-bucket', $rawStored);

        // Verify opinion box attributes were preserved by HTMLPurifier
        $this->assertStringContainsString('data-draggable="true"', $rawStored);
        $this->assertStringContainsString('data-owner-id="99"', $rawStored);
        $this->assertStringContainsString('data-signer-name="ឯកឧត្តម"', $rawStored);
        $this->assertStringContainsString('data-handle="br"', $rawStored);
        $this->assertStringContainsString('opinion-box-comment', $rawStored);
        $this->assertStringContainsString('drafter-signature-table', $rawStored);
    }

    public function test_fetching_document_expands_authorized_signatures_to_fresh_signed_urls(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/drafter_valid.png',
        ]);
        $approver = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/approver_valid.png',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Report with Signatures',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $approver->id,
            'department_id' => $dept->id,
            'content' => '<p>Report</p><img data-file-path="users/signatures/drafter_valid.png" src="/img/signature-placeholder.png"><img data-file-path="users/signatures/approver_valid.png" src="/img/signature-placeholder.png">',
            'status' => 'UNDER_REVIEW',
        ]);

        InboundDocumentResponseApproval::create([
            'response_id' => $responseDoc->id,
            'user_id' => $approver->id,
            'stage' => 'DEPARTMENT',
            'action' => 'FORWARD',
        ]);

        Sanctum::actingAs($drafter);

        $res = $this->getJson("/api/outbound-documents/{$responseDoc->id}");
        $res->assertStatus(200);

        $renderedContent = $res->json('data.content');

        // Drafter signature should have a valid fresh signed route
        $this->assertStringContainsString('/api/files?', $renderedContent);
        $this->assertStringContainsString('path=users%2Fsignatures%2Fdrafter_valid.png', $renderedContent);
        $this->assertStringContainsString('signature=', $renderedContent);

        // Approver signature should have a valid fresh signed route
        $this->assertStringContainsString('path=users%2Fsignatures%2Fapprover_valid.png', $renderedContent);
    }

    public function test_security_blocks_and_strips_unauthorized_non_signature_paths(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/drafter.png',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Attack attempt report',
            'drafted_by' => $drafter->id,
            'department_id' => $dept->id,
            'content' => '<p>Initial</p>',
            'status' => 'DRAFT',
        ]);

        Sanctum::actingAs($drafter);

        // Drafter attempts to embed national ID card scan and path traversal
        $maliciousPayload = '
            <p>Text</p>
            <img data-file-path="users/national-ids/secret_id.jpg" src="/api/files?path=users%2Fnational-ids%2Fsecret_id.jpg">
            <img data-file-path="users/passports/passport.pdf" src="/api/files?path=users%2Fpassports%2Fpassport.pdf">
            <img data-file-path="../.env" src="/api/files?path=..%2F.env">
        ';

        $res = $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => $maliciousPayload,
        ]);
        $res->assertStatus(200);

        // Check raw database storage: non-signature data-file-path must be stripped
        $rawStored = DB::table('inbound_document_responses')->where('id', $responseDoc->id)->value('content');
        $this->assertStringNotContainsString('users/national-ids', $rawStored);
        $this->assertStringNotContainsString('users/passports', $rawStored);
        $this->assertStringNotContainsString('.env', $rawStored);

        // Check viewer rendering: no signed URL generated for non-signature files
        $showRes = $this->getJson("/api/outbound-documents/{$responseDoc->id}");
        $showContent = $showRes->json('data.content');
        $this->assertStringNotContainsString('secret_id.jpg', $showContent);
        $this->assertStringNotContainsString('passport.pdf', $showContent);
        $this->assertStringNotContainsString('.env', $showContent);
    }

    public function test_security_blocks_signatures_of_unrelated_users_not_on_document(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/drafter.png',
        ]);
        $unrelatedUser = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/victim_signature.png',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Signature leak attempt',
            'drafted_by' => $drafter->id,
            'department_id' => $dept->id,
            'content' => '<p>Report</p><img data-file-path="users/signatures/victim_signature.png" src="/img/signature-placeholder.png">',
            'status' => 'DRAFT',
        ]);

        Sanctum::actingAs($drafter);

        // Fetching document: unrelatedUser has no approval or drafter relationship to this document
        $res = $this->getJson("/api/outbound-documents/{$responseDoc->id}");
        $renderedContent = $res->json('data.content');

        // Should NOT generate signed URL for victim's signature
        $this->assertStringNotContainsString('victim_signature.png', $renderedContent);
        $this->assertStringContainsString('/img/signature-placeholder.png', $renderedContent);
    }
}
