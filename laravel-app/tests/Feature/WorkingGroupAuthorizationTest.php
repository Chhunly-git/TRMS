<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkingGroup;
use App\Models\WorkingGroupMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkingGroupAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_working_group_write_endpoints(): void
    {
        $this->postJson('/api/working-groups', ['name_kh' => 'Test WG'])->assertStatus(401);
        $this->putJson('/api/working-groups/1', ['name_kh' => 'Test WG'])->assertStatus(401);
        $this->deleteJson('/api/working-groups/1')->assertStatus(401);
        $this->postJson('/api/working-groups/1/members', ['user_id' => 1])->assertStatus(401);
        $this->deleteJson('/api/working-groups/1/members/1')->assertStatus(401);
    }

    public function test_non_admin_officer_cannot_write_working_groups(): void
    {
        $officer = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $group = WorkingGroup::create([
            'name_kh' => 'ក្រុមការងារសាកល្បង',
            'code' => 'WG-001',
            'status' => 'ACTIVE',
        ]);

        Sanctum::actingAs($officer);

        // Store
        $this->postJson('/api/working-groups', [
            'name_kh' => 'ក្រុមការងារថ្មី',
            'code' => 'WG-002',
            'status' => 'ACTIVE',
        ])->assertStatus(403);

        // Update
        $this->putJson("/api/working-groups/{$group->id}", [
            'name_kh' => 'ឈ្មោះកែប្រែ',
        ])->assertStatus(403);

        // Add Member
        $this->postJson("/api/working-groups/{$group->id}/members", [
            'user_id' => $officer->id,
            'role' => 'LEADER',
        ])->assertStatus(403);

        // Remove Member
        $member = WorkingGroupMember::create([
            'working_group_id' => $group->id,
            'user_id' => $officer->id,
            'role' => 'MEMBER',
        ]);
        $this->deleteJson("/api/working-groups/{$group->id}/members/{$member->id}")->assertStatus(403);

        // Destroy
        $this->deleteJson("/api/working-groups/{$group->id}")->assertStatus(403);
    }

    public function test_admin_can_manage_working_groups(): void
    {
        $admin = User::factory()->create([
            'level' => 'ADMIN',
            'status' => 'ACTIVE',
        ]);

        $memberUser = User::factory()->create([
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        Sanctum::actingAs($admin);

        // 1. Create
        $createRes = $this->postJson('/api/working-groups', [
            'name_kh' => 'ក្រុមការងារបច្ចេកវិទ្យា',
            'code' => 'WG-TECH',
            'status' => 'ACTIVE',
        ]);
        $createRes->assertStatus(201);
        $groupId = $createRes->json('data.id');

        // 2. Update
        $updateRes = $this->putJson("/api/working-groups/{$groupId}", [
            'name_kh' => 'ក្រុមការងារបច្ចេកវិទ្យាកែប្រែ',
            'code' => 'WG-TECH',
            'status' => 'ACTIVE',
        ]);
        $updateRes->assertStatus(200);

        // 3. Add Member
        $addMemberRes = $this->postJson("/api/working-groups/{$groupId}/members", [
            'user_id' => $memberUser->id,
            'role' => 'LEADER',
        ]);
        $addMemberRes->assertStatus(200);
        $memberId = $addMemberRes->json('data.id');

        // 4. Remove Member
        $removeMemberRes = $this->deleteJson("/api/working-groups/{$groupId}/members/{$memberId}");
        $removeMemberRes->assertStatus(200);

        // 5. Delete Group
        $deleteRes = $this->deleteJson("/api/working-groups/{$groupId}");
        $deleteRes->assertStatus(200);
    }
}
