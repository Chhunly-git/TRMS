<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\InboundDocumentResponse;
use App\Models\InboundDocumentResponseApproval;
use App\Models\InboundDocumentResponseRevision;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateContentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_content(): void
    {
        $this->patchJson('/api/outbound-documents/1/content', [
            'content' => '<p>New content</p>',
        ])->assertStatus(401);
    }

    public function test_drafter_can_update_content_in_draft_or_returned_and_creates_revision(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Initial Report',
            'drafted_by' => $drafter->id,
            'department_id' => $dept->id,
            'content' => '<p>Original content</p>',
            'status' => 'DRAFT',
        ]);

        Sanctum::actingAs($drafter);

        // Update in DRAFT status
        $res = $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => '<p>Updated content by drafter</p>',
        ]);
        $res->assertStatus(200);

        // Verify revision was created
        $this->assertDatabaseHas('inbound_document_response_revisions', [
            'response_id' => $responseDoc->id,
            'user_id' => $drafter->id,
            'content' => '<p>Updated content by drafter</p>',
        ]);

        // Change status to RETURNED
        $responseDoc->update(['status' => 'RETURNED']);
        $res2 = $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => '<p>Second update after return</p>',
        ]);
        $res2->assertStatus(200);
    }

    public function test_drafter_cannot_update_content_while_under_review(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);
        $approver = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Initial Report',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $approver->id,
            'department_id' => $dept->id,
            'content' => '<p>Original content</p>',
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($drafter);

        $res = $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => '<p>Drafter trying to edit while under review</p>',
        ]);
        $res->assertStatus(403);
    }

    public function test_current_approver_can_update_content_while_under_review_and_creates_revision(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);
        $approver = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Initial Report',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $approver->id,
            'department_id' => $dept->id,
            'content' => '<p>Original content</p>',
            'status' => 'UNDER_REVIEW',
        ]);

        Sanctum::actingAs($approver);

        $res = $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => '<p>Approver edit with opinion</p>',
        ]);
        $res->assertStatus(200);

        $this->assertDatabaseHas('inbound_document_response_revisions', [
            'response_id' => $responseDoc->id,
            'user_id' => $approver->id,
            'content' => '<p>Approver edit with opinion</p>',
        ]);
    }

    public function test_past_approver_cannot_update_content_after_forwarding(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);
        $pastApprover = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'DIRECTOR',
            'status' => 'ACTIVE',
        ]);
        $nextApprover = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'DG',
            'status' => 'ACTIVE',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Initial Report',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $nextApprover->id,
            'department_id' => $dept->id,
            'content' => '<p>Content</p>',
            'status' => 'UNDER_REVIEW',
        ]);

        InboundDocumentResponseApproval::create([
            'response_id' => $responseDoc->id,
            'user_id' => $pastApprover->id,
            'stage' => 'DEPARTMENT',
            'action' => 'FORWARD',
        ]);

        Sanctum::actingAs($pastApprover);

        $res = $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => '<p>Past approver attempting overwrite</p>',
        ]);
        $res->assertStatus(403);
    }

    public function test_cannot_update_content_when_document_is_locked(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);
        $dg = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'DG',
            'status' => 'ACTIVE',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Completed Report',
            'drafted_by' => $drafter->id,
            'current_approver_id' => $dg->id,
            'department_id' => $dept->id,
            'content' => '<p>Completed content</p>',
            'status' => 'APPROVED_BY_DG',
        ]);

        // Neither drafter nor DG can edit completed/locked doc
        Sanctum::actingAs($drafter);
        $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => '<p>Tampering</p>',
        ])->assertStatus(403);

        Sanctum::actingAs($dg);
        $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => '<p>Tampering</p>',
        ])->assertStatus(403);
    }
}
