<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\InboundDocument;
use App\Models\InboundDocumentResponse;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RestrictOnDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_user_deactivates_and_revokes_tokens_instead_of_hard_delete(): void
    {
        $admin = User::factory()->create(['level' => 'ADMIN', 'status' => 'ACTIVE']);
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $officer = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $officer->createToken('test-token');
        $this->assertGreaterThan(0, $officer->tokens()->count());

        $doc = InboundDocumentResponse::create([
            'title' => 'លិខិតផ្លូវការ',
            'content' => '<p>ខ្លឹមសារ</p>',
            'drafted_by' => $officer->id,
            'department_id' => $dept->id,
            'status' => 'DRAFT',
        ]);

        Sanctum::actingAs($admin);

        $res = $this->deleteJson("/api/manage/users/delete/{$officer->id}");
        $res->assertStatus(200);
        $res->assertJsonFragment([
            'success' => true,
            'message' => 'គណនីអ្នកប្រើប្រាស់ត្រូវបានផ្អាកដំណើរការ (Deactivated) ដោយជោគជ័យ ដើម្បីរក្សាប្រវត្តិឯកសារ។',
        ]);

        // User row is preserved in DB with status INACTIVE
        $freshOfficer = User::find($officer->id);
        $this->assertNotNull($freshOfficer);
        $this->assertEquals('INACTIVE', $freshOfficer->status);

        // Tokens revoked
        $this->assertEquals(0, $freshOfficer->tokens()->count());

        // Document preserved and still points to officer
        $freshDoc = InboundDocumentResponse::find($doc->id);
        $this->assertNotNull($freshDoc);
        $this->assertEquals($officer->id, $freshDoc->drafted_by);
    }

    public function test_deleting_inbound_document_with_responses_returns_409(): void
    {
        $admin = User::factory()->create(['level' => 'ADMIN', 'status' => 'ACTIVE']);
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);

        $inboundDoc = InboundDocument::create([
            'document_number' => 'IN-001/26',
            'title' => 'លិខិតចូល',
            'received_date' => now()->toDateString(),
            'received_time' => now()->toTimeString(),
            'deliverer_name' => 'ឈ្មោះអ្នកដឹក',
            'sender_organization' => 'ក្រសួង',
            'registered_by' => $admin->id,
            'status' => 'COMPLETED',
        ]);

        InboundDocumentResponse::create([
            'inbound_document_id' => $inboundDoc->id,
            'title' => 'លិខិតឆ្លើយតប',
            'content' => '<p>ឆ្លើយតប</p>',
            'drafted_by' => $admin->id,
            'department_id' => $dept->id,
            'status' => 'DRAFT',
        ]);

        Sanctum::actingAs($admin);

        $res = $this->deleteJson("/api/inbound-documents/{$inboundDoc->id}");
        $res->assertStatus(409);
        $res->assertJsonFragment([
            'success' => false,
            'message' => 'មិនអាចលុបឯកសារចូលនេះបានទេ ដោយសារមានលិខិតឆ្លើយតបរួចហើយ!',
        ]);

        // Inbound document remains in database
        $this->assertDatabaseHas('inbound_documents', ['id' => $inboundDoc->id]);
    }

    public function test_deleting_department_with_offices_or_users_returns_409(): void
    {
        $admin = User::factory()->create(['level' => 'ADMIN', 'status' => 'ACTIVE']);
        $dept = Department::create(['code' => 'D2', 'name_kh' => 'នាយកដ្ឋាន ២', 'name_en' => 'Dept 2']);
        Office::create([
            'department_id' => $dept->id,
            'code' => 'OFF-1',
            'name_kh' => 'ការិយាល័យ ១',
            'name_en' => 'Office 1',
        ]);

        Sanctum::actingAs($admin);

        $res = $this->deleteJson("/api/manage/departments/delete/{$dept->id}");
        $res->assertStatus(409);
        $res->assertJsonFragment([
            'success' => false,
            'message' => 'មិនអាចលុបនាយកដ្ឋាននេះបានទេ ដោយសារមានការិយាល័យ បុគ្គលិក ឬឯកសារកំពុងប្រើប្រាស់!',
        ]);

        // Department remains in database
        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
    }

    public function test_database_level_foreign_key_restricts_direct_deletion_of_referenced_user(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Database level foreign key alterations are MySQL-specific and skipped on SQLite in-memory test runner.');
        }

        $dept = Department::create(['code' => 'D3', 'name_kh' => 'នាយកដ្ឋាន ៣', 'name_en' => 'Dept 3']);
        $user = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        InboundDocumentResponse::create([
            'title' => 'លិខិតផ្លូវការ',
            'content' => '<p>ខ្លឹមសារ</p>',
            'drafted_by' => $user->id,
            'department_id' => $dept->id,
            'status' => 'DRAFT',
        ]);

        // Direct database level delete attempt MUST throw QueryException due to restrictOnDelete
        $this->expectException(QueryException::class);
        DB::table('users')->where('id', $user->id)->delete();
    }
}
