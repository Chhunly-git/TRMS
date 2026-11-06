<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\DocumentNumberCounter;
use App\Models\DocumentWorkflowConfig;
use App\Models\InboundDocumentResponse;
use App\Models\InboundDocumentResponseApproval;
use App\Models\Position;
use App\Models\User;
use App\Models\WorkingGroup;
use App\Services\DocumentNumberService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentNumberingTest extends TestCase
{
    use RefreshDatabase;

    protected Department $dept1;
    protected Department $dept2;
    protected Position $dgPos;
    protected Position $directorPos;
    protected Position $deputyDirectorPos;
    protected Position $officerPos;

    protected User $dg;
    protected User $director1;
    protected User $deputyDirector1;
    protected User $officer1;
    protected User $director2;
    protected User $admin;

    protected WorkingGroup $wg;
    protected User $wgLeader;
    protected User $wgDeputyLeader;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Departments with Abbreviations
        $this->dept1 = Department::create([
            'code' => 'GAD',
            'name_kh' => 'នាយកដ្ឋានកិច្ចការទូទៅ',
            'abbreviation_kh' => 'ន.ក.ទ.',
        ]);

        $this->dept2 = Department::create([
            'code' => 'TRC',
            'name_kh' => 'នាយកដ្ឋានចុះបញ្ជីបរធនបាលកិច្ច',
            'abbreviation_kh' => 'ច.ប.ច.',
        ]);

        // Setup Positions
        $this->dgPos = Position::create(['title_kh' => 'អគ្គនាយក', 'title_en' => 'Director General', 'level' => 1]);
        $this->directorPos = Position::create(['title_kh' => 'ប្រធាននាយកដ្ឋាន', 'title_en' => 'Department Director', 'level' => 3]);
        $this->deputyDirectorPos = Position::create(['title_kh' => 'អនុប្រធាននាយកដ្ឋាន', 'title_en' => 'Deputy Director', 'level' => 4]);
        $this->officerPos = Position::create(['title_kh' => 'មន្ត្រី', 'title_en' => 'Officer', 'level' => 9]);

        // Setup Users
        $this->dg = User::factory()->create([
            'name_kh' => 'ឯកឧត្តម អគ្គនាយក',
            'department_id' => $this->dept1->id,
            'position_id' => $this->dgPos->id,
            'level' => 'DG',
            'status' => 'ACTIVE',
        ]);

        $this->director1 = User::factory()->create([
            'name_kh' => 'លោក ប្រធាននាយកដ្ឋានទី១',
            'department_id' => $this->dept1->id,
            'position_id' => $this->directorPos->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
        ]);

        $this->deputyDirector1 = User::factory()->create([
            'name_kh' => 'លោក អនុប្រធាននាយកដ្ឋានទី១',
            'department_id' => $this->dept1->id,
            'position_id' => $this->deputyDirectorPos->id,
            'level' => 'DEPUTY_DIRECTOR',
            'status' => 'ACTIVE',
        ]);

        $this->officer1 = User::factory()->create([
            'name_kh' => 'មន្ត្រី នាយកដ្ឋានទី១',
            'department_id' => $this->dept1->id,
            'position_id' => $this->officerPos->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $this->director2 = User::factory()->create([
            'name_kh' => 'លោក ប្រធាននាយកដ្ឋានទី២',
            'department_id' => $this->dept2->id,
            'position_id' => $this->directorPos->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
        ]);

        $this->admin = User::factory()->create([
            'name_kh' => 'Admin User',
            'department_id' => $this->dept1->id,
            'position_id' => $this->officerPos->id,
            'level' => 'ADMIN',
            'status' => 'ACTIVE',
        ]);

        // Setup Working Group
        $this->wg = WorkingGroup::create([
            'name_kh' => 'ក្រុមការងារបច្ចេកវិទ្យា',
            'code' => 'WG-TECH',
            'abbreviation_kh' => 'ក.ប.ច.',
            'status' => 'ACTIVE',
        ]);

        $this->wgLeader = User::factory()->create([
            'name_kh' => 'ប្រធានក្រុមការងារ',
            'department_id' => $this->dept1->id,
            'position_id' => $this->deputyDirectorPos->id, // DDG or Deputy Director leading WG
            'level' => 'DEPUTY_DIRECTOR',
            'status' => 'ACTIVE',
        ]);

        $this->wgDeputyLeader = User::factory()->create([
            'name_kh' => 'អនុប្រធានក្រុមការងារ',
            'department_id' => $this->dept1->id,
            'position_id' => $this->officerPos->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        DB::table('working_group_members')->insert([
            [
                'working_group_id' => $this->wg->id,
                'user_id' => $this->wgLeader->id,
                'role' => 'LEADER',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'working_group_id' => $this->wg->id,
                'user_id' => $this->wgDeputyLeader->id,
                'role' => 'DEPUTY_LEADER',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Test 1: Department sequence strictly continuous (001, 002) formatted in Khmer digits
     */
    public function test_department_numbering_sequence_and_khmer_format(): void
    {
        $currentYear = Carbon::now('Asia/Phnom_Penh')->year;
        $yyKh = DocumentNumberService::toKhmerDigits(substr((string) $currentYear, -2));

        // Create doc 1 drafted by officer, currently under review by director 1
        $doc1 = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'First Report',
            'drafted_by' => $this->officer1->id,
            'department_id' => $this->dept1->id,
            'issuing_unit_type' => 'DEPARTMENT',
            'issuing_unit_id' => $this->dept1->id,
            'current_approver_id' => $this->director1->id,
            'status' => 'UNDER_REVIEW',
            'content' => '<p>Report body <span class="doc-number-input">...</span> ន.ប.ធ.</p>',
        ]);

        Sanctum::actingAs($this->director1);

        // Director forwards outside to DG -> numbers doc 1
        $res1 = $this->postJson("/api/outbound-documents/{$doc1->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $this->dg->id,
            'comment' => 'Submit to DG',
        ]);

        $res1->assertStatus(200);
        $doc1->refresh();

        $this->assertEquals(1, $doc1->number_seq);
        $this->assertEquals($currentYear, $doc1->number_year);
        $this->assertEquals('ន.ក.ទ.', $doc1->number_suffix);
        $expectedNumber1 = "០០១/{$yyKh} ន.ប.ធ./ន.ក.ទ.";
        $this->assertEquals($expectedNumber1, $doc1->response_number);
        $this->assertStringContainsString('០០១', $doc1->content);

        // Create doc 2
        $doc2 = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Second Report',
            'drafted_by' => $this->officer1->id,
            'department_id' => $this->dept1->id,
            'issuing_unit_type' => 'DEPARTMENT',
            'issuing_unit_id' => $this->dept1->id,
            'current_approver_id' => $this->director1->id,
            'status' => 'UNDER_REVIEW',
        ]);

        // Director forwards outside to DG -> numbers doc 2
        $res2 = $this->postJson("/api/outbound-documents/{$doc2->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $this->dg->id,
            'comment' => 'Submit second to DG',
        ]);

        $res2->assertStatus(200);
        $doc2->refresh();

        $this->assertEquals(2, $doc2->number_seq);
        $expectedNumber2 = "០០២/{$yyKh} ន.ប.ធ./ន.ក.ទ.";
        $this->assertEquals($expectedNumber2, $doc2->response_number);

        // Verify Counter Table
        $counter = DB::table('document_number_counters')
            ->where('issuing_unit_type', 'DEPARTMENT')
            ->where('issuing_unit_id', $this->dept1->id)
            ->where('year', $currentYear)
            ->first();

        $this->assertNotNull($counter);
        $this->assertEquals(2, $counter->last_number);
    }

    /**
     * Test 2: Internal forward within department does NOT assign a number
     */
    public function test_internal_forward_within_department_does_not_assign_number(): void
    {
        // Officer forwards to Deputy Director within Dept 1
        $doc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Internal Review Report',
            'drafted_by' => $this->officer1->id,
            'department_id' => $this->dept1->id,
            'issuing_unit_type' => 'DEPARTMENT',
            'issuing_unit_id' => $this->dept1->id,
            'current_approver_id' => $this->deputyDirector1->id,
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($this->deputyDirector1);

        // Deputy Director forwards to Director (SAME department)
        $res = $this->postJson("/api/outbound-documents/{$doc->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $this->director1->id,
            'comment' => 'Forward to Director for approval',
        ]);

        $res->assertStatus(200);
        $doc->refresh();

        $this->assertNull($doc->number_seq);
        $this->assertNull($doc->response_number);
    }

    /**
     * Test 3: Deputy Director signing assigns the number when Director is absent (Owner Decision Q3)
     */
    public function test_deputy_director_signature_assigns_department_number(): void
    {
        $currentYear = Carbon::now('Asia/Phnom_Penh')->year;
        $yyKh = DocumentNumberService::toKhmerDigits(substr((string) $currentYear, -2));

        $doc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Report signed by Deputy Director',
            'drafted_by' => $this->officer1->id,
            'department_id' => $this->dept1->id,
            'issuing_unit_type' => 'DEPARTMENT',
            'issuing_unit_id' => $this->dept1->id,
            'current_approver_id' => $this->deputyDirector1->id,
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($this->deputyDirector1);

        // Deputy Director forwards outside to DG
        $res = $this->postJson("/api/outbound-documents/{$doc->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $this->dg->id,
            'comment' => 'Acting Deputy Director submits to DG',
        ]);

        $res->assertStatus(200);
        $doc->refresh();

        $this->assertEquals(1, $doc->number_seq);
        $this->assertEquals("០០១/{$yyKh} ន.ប.ធ./ន.ក.ទ.", $doc->response_number);
    }

    /**
     * Test 4: Working Group sequence and abbreviation (Owner Decision Q1 & Q2)
     */
    public function test_working_group_has_independent_sequence_and_abbreviation(): void
    {
        $currentYear = Carbon::now('Asia/Phnom_Penh')->year;
        $yyKh = DocumentNumberService::toKhmerDigits(substr((string) $currentYear, -2));

        $wgDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Working Group Tech Report',
            'drafted_by' => $this->wgLeader->id,
            'department_id' => $this->dept1->id,
            'issuing_unit_type' => 'WORKING_GROUP',
            'issuing_unit_id' => $this->wg->id,
            'current_approver_id' => $this->wgLeader->id,
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($this->wgLeader);

        // WG Leader forwards to DG -> gets WG sequence and WG abbreviation
        $res = $this->postJson("/api/outbound-documents/{$wgDoc->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $this->dg->id,
            'comment' => 'WG Leader submits to DG',
        ]);

        $res->assertStatus(200);
        $wgDoc->refresh();

        $this->assertEquals(1, $wgDoc->number_seq);
        $this->assertEquals('ក.ប.ច.', $wgDoc->number_suffix);
        $this->assertEquals("០០១/{$yyKh} ន.ប.ធ./ក.ប.ច.", $wgDoc->response_number);

        // Verify WG Counter exists and Department counter is unaffected
        $wgCounter = DB::table('document_number_counters')
            ->where('issuing_unit_type', 'WORKING_GROUP')
            ->where('issuing_unit_id', $this->wg->id)
            ->first();
        $this->assertEquals(1, $wgCounter->last_number);

        $deptCounter = DB::table('document_number_counters')
            ->where('issuing_unit_type', 'DEPARTMENT')
            ->where('issuing_unit_id', $this->dept1->id)
            ->first();
        $this->assertNull($deptCounter);
    }

    /**
     * Test 5: Forwarding to another department for consultation DOES assign a number (Owner Decision Q5)
     * And returning from consultation keeps the number unchanged!
     */
    public function test_consultation_forwarding_assigns_number_and_return_preserves_it(): void
    {
        // Configure Director 1 workflow to permit sending to Department 2 for consultation
        DocumentWorkflowConfig::create([
            'user_id' => $this->director1->id,
            'send_type' => 'CUSTOM',
            'send_dept_ids' => [$this->dept2->id],
        ]);

        $doc = InboundDocumentResponse::create([
            'response_type' => 'LETTER',
            'title' => 'Consultation Request Letter',
            'drafted_by' => $this->officer1->id,
            'department_id' => $this->dept1->id,
            'issuing_unit_type' => 'DEPARTMENT',
            'issuing_unit_id' => $this->dept1->id,
            'current_approver_id' => $this->director1->id,
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($this->director1);

        // Director 1 forwards to Director 2 (another department) for consultation
        $res = $this->postJson("/api/outbound-documents/{$doc->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $this->director2->id,
            'comment' => 'Forward to Dept 2 for consultation',
        ]);

        $res->assertStatus(200);
        $doc->refresh();

        $this->assertEquals(1, $doc->number_seq);
        $this->assertEquals('ន.ក.ទ.', $doc->number_suffix);
        $assignedNumber = $doc->response_number;
        $this->assertNotNull($assignedNumber);

        // Director 2 returns the document back to Director 1 with opinion
        Sanctum::actingAs($this->director2);
        $resReturn = $this->postJson("/api/outbound-documents/{$doc->id}/review-action", [
            'action' => 'RETURN',
            'comment' => 'Consultation feedback provided',
        ]);

        $resReturn->assertStatus(200);
        $doc->refresh();

        // Status is RETURNED but number remains permanent and untouched!
        $this->assertEquals('RETURNED', $doc->status);
        $this->assertEquals(1, $doc->number_seq);
        $this->assertEquals($assignedNumber, $doc->response_number);
    }

    /**
     * Test 6: Hard guard - Officer cannot skip Director / forward un-numbered doc outside unit (Owner Decision Q4)
     */
    public function test_officer_cannot_forward_un_numbered_document_outside_unit(): void
    {
        $doc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Officer Bypass Attempt',
            'drafted_by' => $this->officer1->id,
            'department_id' => $this->dept1->id,
            'issuing_unit_type' => 'DEPARTMENT',
            'issuing_unit_id' => $this->dept1->id,
            'current_approver_id' => $this->officer1->id,
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($this->officer1);

        // Officer tries to forward directly to DG (level 1)
        $res = $this->postJson("/api/outbound-documents/{$doc->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $this->dg->id,
            'comment' => 'Bypassing Director',
        ]);

        $res->assertStatus(422);
        $this->assertStringContainsString('មិនទាន់ត្រូវបានចុះហត្ថលេខាដោយប្រធានអង្គភាព', $res->json('message'));
        $doc->refresh();
        $this->assertNull($doc->number_seq);
    }

    /**
     * Test 7: Safeguards - Numbered document cannot be recalled to DRAFT or deleted
     */
    public function test_numbered_document_cannot_be_recalled_to_draft_or_deleted(): void
    {
        $doc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Numbered Permanent Document',
            'drafted_by' => $this->officer1->id,
            'department_id' => $this->dept1->id,
            'issuing_unit_type' => 'DEPARTMENT',
            'issuing_unit_id' => $this->dept1->id,
            'number_seq' => 1,
            'number_year' => 2026,
            'number_suffix' => 'ន.ក.ទ.',
            'response_number' => '០០១/២៦ ន.ប.ធ./ន.ក.ទ.',
            'current_approver_id' => $this->dg->id,
            'status' => 'UNDER_REVIEW',
        ]);

        // Drafter tries to recall back to DRAFT
        Sanctum::actingAs($this->officer1);
        $resRecall = $this->postJson("/api/outbound-documents/{$doc->id}/recall");
        $resRecall->assertStatus(409);
        $this->assertStringContainsString('មិនអាចទាញយកឯកសារដែលបានចុះលេខផ្លូវការរួចហើយ', $resRecall->json('message'));

        // Admin tries to delete numbered document
        Sanctum::actingAs($this->admin);
        $resDelete = $this->deleteJson("/api/outbound-documents/{$doc->id}");
        $resDelete->assertStatus(409);
        $this->assertStringContainsString('មិនអាចលុបឯកសារដែលបានចុះលេខផ្លូវការរួចហើយ', $resDelete->json('message'));

        $this->assertDatabaseHas('inbound_document_responses', ['id' => $doc->id]);
    }

    /**
     * Test 8: Admin can CANCEL a numbered document with audit reason while preserving sequence
     */
    public function test_admin_can_cancel_numbered_document_preserving_sequence(): void
    {
        $doc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Document to Cancel',
            'drafted_by' => $this->officer1->id,
            'department_id' => $this->dept1->id,
            'issuing_unit_type' => 'DEPARTMENT',
            'issuing_unit_id' => $this->dept1->id,
            'number_seq' => 5,
            'number_year' => 2026,
            'number_suffix' => 'ន.ក.ទ.',
            'response_number' => '០០៥/២៦ ន.ប.ធ./ន.ក.ទ.',
            'current_approver_id' => $this->dg->id,
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($this->admin);

        $resCancel = $this->postJson("/api/outbound-documents/{$doc->id}/cancel", [
            'reason' => 'លុបចោលដោយសារខុសកម្មវត្ថុរដ្ឋបាល',
        ]);

        $resCancel->assertStatus(200);
        $doc->refresh();

        $this->assertEquals('CANCELLED', $doc->status);
        $this->assertEquals(5, $doc->number_seq);
        $this->assertEquals('០០៥/២៦ ន.ប.ធ./ន.ក.ទ.', $doc->response_number);

        // Verify audit log in approvals table
        $approval = InboundDocumentResponseApproval::where('response_id', $doc->id)->where('action', 'CANCEL')->first();
        $this->assertNotNull($approval);
        $this->assertStringContainsString('លុបចោលដោយសារខុសកម្មវត្ថុរដ្ឋបាល', $approval->comment);
    }
}
