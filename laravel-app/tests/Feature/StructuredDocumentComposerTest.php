<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\InboundDocumentResponse;
use App\Models\InboundDocumentResponseApproval;
use App\Models\Office;
use App\Models\ResponseOpinionPlacement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StructuredDocumentComposerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_fetch_structured_document(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $office = Office::create(['code' => 'O1', 'department_id' => $dept->id, 'name_kh' => 'ការិយាល័យ ១', 'name_en' => 'Office 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'office_id' => $office->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
            'signature_image' => 'users/signatures/drafter.png',
        ]);

        Sanctum::actingAs($drafter);

        $payload = [
            'is_draft' => true,
            'format_version' => 2,
            'response_type' => 'REPORT',
            'recipient_type' => 'INTERNAL',
            'recipient_text' => 'ឯកឧត្តមអគ្គនាយក នៃនិយ័តករបរធនបាលកិច្ច',
            'title' => 'របាយការណ៍ស្តីពីលទ្ធផលការងារប្រចាំត្រីមាស',
            'references' => [
                'ច្បាប់ស្តីពីបរធនបាលកិច្ច',
                'អនុក្រឹត្យលេខ ០៤ អនក្រ.បក',
            ],
            'closing_phrase_1' => 'សេចក្តីដូចបានគោរពរាយការណ៍ជូនខាងលើ សូម ឯកឧត្តមអគ្គនាយក មេត្តាពិនិត្យ។',
            'closing_phrase_2' => 'សូម ឯកឧត្តមអគ្គនាយក មេត្តាទទួលនូវការគោរពដ៏ខ្ពង់ខ្ពស់។',
            'body_html' => '<p>ខ្លឹមសារកថាខណ្ឌទី១ នៃការអនុវត្តការងារ។</p><p>ខ្លឹមសារកថាខណ្ឌទី២ នៃវឌ្ឍនភាព។</p>',
        ];

        $res = $this->postJson('/api/outbound-documents', $payload);
        $res->assertStatus(200);

        $docId = $res->json('data.id');
        $this->assertNotNull($docId);

        // Fetch document detail
        $showRes = $this->getJson("/api/outbound-documents/{$docId}");
        $showRes->assertStatus(200);

        $data = $showRes->json('data');
        $this->assertEquals(2, $data['format_version']);
        $this->assertEquals('របាយការណ៍ស្តីពីលទ្ធផលការងារប្រចាំត្រីមាស', $data['title']);
        $this->assertCount(2, $data['references']);
        $this->assertNotNull($data['content_hash']);
        $this->assertNotEmpty($data['rendered_html']);

        // Verify rendered HTML has zones
        $html = $data['rendered_html'];
        $this->assertStringContainsString('ព្រះរាជាណាចក្រកម្ពុជា', $html);
        $this->assertStringContainsString('និយ័តករបរធនបាលកិច្ច', $html);
        $this->assertStringContainsString('កម្មវត្ថុ ៖', $html);
        $this->assertStringContainsString('យោង ៖', $html);
        $this->assertStringContainsString('ខ្លឹមសារកថាខណ្ឌទី១', $html);
        $this->assertStringContainsString('page-1-footer', $html);
    }

    public function test_can_update_structured_document_fields_and_computes_new_hash(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $doc = InboundDocumentResponse::create([
            'format_version' => 2,
            'response_type' => 'REPORT',
            'title' => 'ចំណងជើងចាស់',
            'drafted_by' => $drafter->id,
            'department_id' => $dept->id,
            'body_html' => '<p>ខ្លឹមសារដើម</p>',
            'status' => 'DRAFT',
        ]);

        $initialHash = $doc->computeContentHash();
        $doc->content_hash = $initialHash;
        $doc->save();

        Sanctum::actingAs($drafter);

        $updatePayload = [
            'title' => 'ចំណងជើងថ្មីដែលបានកែសម្រួល',
            'references' => ['លិខិតលេខ ០១/២៦'],
            'body_html' => '<p>ខ្លឹមសារថ្មីដែលបានកែលម្អ</p>',
        ];

        $res = $this->patchJson("/api/outbound-documents/{$doc->id}/content", $updatePayload);
        $res->assertStatus(200);

        $fresh = InboundDocumentResponse::find($doc->id);
        $this->assertEquals('ចំណងជើងថ្មីដែលបានកែសម្រួល', $fresh->title);
        $this->assertNotEquals($initialHash, $fresh->content_hash);
        $this->assertEquals($fresh->computeContentHash(), $fresh->content_hash);
    }

    public function test_opinion_placement_api_rules(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);
        $leader1 = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
            'name_kh' => 'ថ្នាក់ដឹកនាំ ១',
        ]);
        $leader2 = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
            'name_kh' => 'ថ្នាក់ដឹកនាំ ២',
        ]);

        $doc = InboundDocumentResponse::create([
            'format_version' => 2,
            'response_type' => 'REPORT',
            'title' => 'របាយការណ៍',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $leader1->id,
            'department_id' => $dept->id,
            'status' => 'UNDER_REVIEW',
            'body_html' => '<p>ខ្លឹមសារ</p>',
        ]);

        $appr1 = InboundDocumentResponseApproval::create([
            'response_id' => $doc->id,
            'user_id' => $leader1->id,
            'action' => 'FORWARD',
            'stage' => 'DEPARTMENT',
            'comment' => 'បានពិនិត្យ និងយល់ព្រម',
        ]);

        $appr2 = InboundDocumentResponseApproval::create([
            'response_id' => $doc->id,
            'user_id' => $leader2->id,
            'action' => 'FORWARD',
            'stage' => 'DEPARTMENT',
            'comment' => 'ឯកភាព',
        ]);

        // Leader 1 checks opinion slots
        Sanctum::actingAs($leader1);
        $slotsRes = $this->getJson("/api/outbound-documents/{$doc->id}/opinion-slots");
        $slotsRes->assertStatus(200);
        $slotsRes->assertJsonStructure([
            'drafter_block' => ['user_id', 'is_locked', 'slot', 'description'],
            'opinions',
            'vacant_slots',
        ]);

        // Rule 1: Leader 1 moves own box to OPINION_LEFT_1 -> succeeds
        $moveRes = $this->patchJson("/api/outbound-documents/{$doc->id}/opinion-placement", [
            'approval_id' => $appr1->id,
            'target_slot' => 'OPINION_LEFT_1',
        ]);
        $moveRes->assertStatus(200);
        $this->assertDatabaseHas('response_opinion_placements', [
            'response_id' => $doc->id,
            'approval_id' => $appr1->id,
            'slot' => 'OPINION_LEFT_1',
        ]);

        // Rule 2: Leader 1 tries to move box to DRAFTER_AREA -> rejected with 422
        $drafterTargetRes = $this->patchJson("/api/outbound-documents/{$doc->id}/opinion-placement", [
            'approval_id' => $appr1->id,
            'target_slot' => 'DRAFTER_AREA',
        ]);
        $drafterTargetRes->assertStatus(422);

        // Rule 3: Leader 1 tries to move Leader 2's box -> 403 Forbidden
        $otherMoveRes = $this->patchJson("/api/outbound-documents/{$doc->id}/opinion-placement", [
            'approval_id' => $appr2->id,
            'target_slot' => 'OPINION_LEFT_3',
        ]);
        $otherMoveRes->assertStatus(403);

        // Rule 4: Leader 2 moves own box to already occupied slot OPINION_LEFT_1 -> 409 Conflict
        Sanctum::actingAs($leader2);
        $conflictRes = $this->patchJson("/api/outbound-documents/{$doc->id}/opinion-placement", [
            'approval_id' => $appr2->id,
            'target_slot' => 'OPINION_LEFT_1',
        ]);
        $conflictRes->assertStatus(409);

        // Rule 5: Cannot move on completed document
        $doc->update(['status' => 'COMPLETED']);
        $completedRes = $this->patchJson("/api/outbound-documents/{$doc->id}/opinion-placement", [
            'approval_id' => $appr2->id,
            'target_slot' => 'OPINION_LEFT_2',
        ]);
        $completedRes->assertStatus(422);
    }
}
