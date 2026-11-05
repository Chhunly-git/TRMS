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

class ReviewActionLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_action_fails_if_document_already_forwarded_or_approved(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);

        $directorPos = Position::create(['title_kh' => 'ប្រធាននាយកដ្ឋាន', 'title_en' => 'Director', 'level' => 3]);
        $dgPos = Position::create(['title_kh' => 'អគ្គនាយក', 'title_en' => 'DG', 'level' => 1]);

        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);
        $director = User::factory()->create([
            'department_id' => $dept->id,
            'position_id' => $directorPos->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
        ]);
        $dg = User::factory()->create([
            'department_id' => $dept->id,
            'position_id' => $dgPos->id,
            'level' => 'DG',
            'status' => 'ACTIVE',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Report Under Review',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $director->id,
            'department_id' => $dept->id,
            'content' => '<p>Report content</p>',
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($director);

        // First forward succeeds
        $res1 = $this->postJson("/api/outbound-documents/{$responseDoc->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $dg->id,
            'comment' => 'Forward to DG',
        ]);
        $res1->assertStatus(200);

        // Ensure document current_approver changed to DG
        $freshDoc = InboundDocumentResponse::find($responseDoc->id);
        $this->assertEquals($dg->id, $freshDoc->current_approver_id);

        // Immediate second review call from director (e.g. double click or concurrent race) fails with 403
        $res2 = $this->postJson("/api/outbound-documents/{$responseDoc->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $dg->id,
            'comment' => 'Duplicate forward',
        ]);
        $res2->assertStatus(403);

        // Verify only 1 FORWARD approval record exists
        $this->assertEquals(1, InboundDocumentResponseApproval::where('response_id', $responseDoc->id)->count());
    }

    public function test_review_action_fails_when_status_is_not_under_review(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $directorPos = Position::create(['title_kh' => 'ប្រធាននាយកដ្ឋាន', 'title_en' => 'Director', 'level' => 3]);

        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);
        $director = User::factory()->create([
            'department_id' => $dept->id,
            'position_id' => $directorPos->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Draft Report',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $director->id,
            'department_id' => $dept->id,
            'content' => '<p>Draft content</p>',
            'status' => 'DRAFT', // Not UNDER_REVIEW
        ]);

        Sanctum::actingAs($director);

        $res = $this->postJson("/api/outbound-documents/{$responseDoc->id}/review-action", [
            'action' => 'FORWARD',
            'forwarded_to_id' => $director->id,
            'comment' => 'Attempt review on draft',
        ]);
        $res->assertStatus(400);
        $res->assertJsonFragment(['message' => 'លិខិតនេះមិនស្ថិតក្នុងការពិនិត្យឆ្លងឡើយ!']);
    }
}
