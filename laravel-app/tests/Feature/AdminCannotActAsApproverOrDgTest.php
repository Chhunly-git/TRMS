<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\InboundDocumentResponse;
use App\Models\InboundDocumentResponseApproval;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCannotActAsApproverOrDgTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_dg_approve_or_act_as_another_approver(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $posOfficer = Position::create(['title_kh' => 'មន្ត្រី', 'title_en' => 'Officer', 'level' => 9]);
        $posDirector = Position::create(['title_kh' => 'ប្រធាននាយកដ្ឋាន', 'title_en' => 'Director', 'level' => 3]);
        $posDg = Position::create(['title_kh' => 'ប្រតិភូរាជរដ្ឋាភិបាលទទួលបន្ទុកជាអគ្គនាយក', 'title_en' => 'Director General', 'level' => 1]);

        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'position_id' => $posOfficer->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $director = User::factory()->create([
            'department_id' => $dept->id,
            'position_id' => $posDirector->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
        ]);

        $dg = User::factory()->create([
            'department_id' => $dept->id,
            'position_id' => $posDg->id,
            'level' => 'DG',
            'status' => 'ACTIVE',
        ]);

        $admin = User::factory()->create([
            'level' => 'ADMIN',
            'status' => 'ACTIVE',
            'position_id' => $posOfficer->id, // Admin has officer position, not DG
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Important Report',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $director->id,
            'department_id' => $dept->id,
            'status' => 'UNDER_REVIEW',
        ]);

        // 1. Admin tries to act as Director in reviewAction -> 403 Forbidden
        Sanctum::actingAs($admin);
        $resActAsApprover = $this->postJson("/api/outbound-documents/{$responseDoc->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $dg->id,
            'comment' => 'Admin trying to forward on behalf of director',
        ]);
        $resActAsApprover->assertStatus(403);

        // 2. Document is now with DG, Admin tries to DG_APPROVE -> 403 Forbidden
        $responseDoc->update(['current_approver_id' => $admin->id]); // Even if assigned to admin, admin cannot DG_APPROVE
        $resAdminDgApprove = $this->postJson("/api/outbound-documents/{$responseDoc->id}/review-action", [
            'action' => 'DG_APPROVE',
            'comment' => 'Admin trying to approve as DG',
        ]);
        $resAdminDgApprove->assertStatus(403);

        // 3. Real DG can DG_APPROVE
        $responseDoc->update(['current_approver_id' => $dg->id]);
        Sanctum::actingAs($dg);
        $resRealDgApprove = $this->postJson("/api/outbound-documents/{$responseDoc->id}/review-action", [
            'action' => 'DG_APPROVE',
            'comment' => 'DG approval confirmed',
        ]);
        $resRealDgApprove->assertStatus(200);
        $this->assertEquals('APPROVED_BY_DG', $responseDoc->fresh()->status);
    }

    public function test_admin_recall_requires_reason_and_records_audit(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create(['department_id' => $dept->id, 'level' => 'OFFICER', 'status' => 'ACTIVE']);
        $director = User::factory()->create(['department_id' => $dept->id, 'level' => 'DIRECTOR', 'status' => 'ACTIVE']);
        $admin = User::factory()->create(['level' => 'ADMIN', 'status' => 'ACTIVE']);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Report Under Review',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $director->id,
            'department_id' => $dept->id,
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($admin);

        // Missing reason -> 422
        $resFail = $this->postJson("/api/outbound-documents/{$responseDoc->id}/recall", []);
        $resFail->assertStatus(422);

        // With valid audit reason -> 200
        $resSuccess = $this->postJson("/api/outbound-documents/{$responseDoc->id}/recall", [
            'reason' => 'Drafter requested emergency corrections via phone',
        ]);
        $resSuccess->assertStatus(200);

        $fresh = $responseDoc->fresh();
        $this->assertEquals('DRAFT', $fresh->status);
        $this->assertEquals($drafter->id, $fresh->current_approver_id);

        $this->assertDatabaseHas('inbound_document_response_approvals', [
            'response_id' => $responseDoc->id,
            'user_id' => $admin->id,
            'action' => 'RECALL',
            'forwarded_to_id' => $drafter->id,
        ]);
    }

    public function test_admin_reassign_requires_reason_and_records_audit(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create(['department_id' => $dept->id, 'level' => 'OFFICER', 'status' => 'ACTIVE']);
        $director1 = User::factory()->create(['department_id' => $dept->id, 'level' => 'DIRECTOR', 'status' => 'ACTIVE']);
        $director2 = User::factory()->create(['department_id' => $dept->id, 'level' => 'DIRECTOR', 'status' => 'ACTIVE']);
        $officer = User::factory()->create(['level' => 'OFFICER', 'status' => 'ACTIVE']);
        $admin = User::factory()->create(['level' => 'ADMIN', 'status' => 'ACTIVE']);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Report Under Review',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $director1->id,
            'department_id' => $dept->id,
            'status' => 'UNDER_REVIEW',
        ]);

        // Non-admin cannot reassign
        Sanctum::actingAs($officer);
        $this->postJson("/api/outbound-documents/{$responseDoc->id}/reassign", [
            'target_user_id' => $director2->id,
            'reason' => 'Director 1 is on mission',
        ])->assertStatus(403);

        // Admin without reason -> 422
        Sanctum::actingAs($admin);
        $this->postJson("/api/outbound-documents/{$responseDoc->id}/reassign", [
            'target_user_id' => $director2->id,
        ])->assertStatus(422);

        // Admin with reason -> 200
        $res = $this->postJson("/api/outbound-documents/{$responseDoc->id}/reassign", [
            'target_user_id' => $director2->id,
            'reason' => 'Director 1 is on overseas mission',
        ]);
        $res->assertStatus(200);

        $this->assertEquals($director2->id, $responseDoc->fresh()->current_approver_id);
        $this->assertDatabaseHas('inbound_document_response_approvals', [
            'response_id' => $responseDoc->id,
            'user_id' => $admin->id,
            'forwarded_to_id' => $director2->id,
        ]);
    }
}
