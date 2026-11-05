<?php

namespace Tests\Feature;

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

    public function test_user_cannot_access_another_users_signature_image(): void
    {
        $user1 = User::factory()->make(['id' => 101, 'level' => 'OFFICER', 'status' => 'ACTIVE']);
        $user2 = User::factory()->make(['id' => 102, 'level' => 'OFFICER', 'status' => 'ACTIVE']);

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/users/{$user2->id}/signature-image");
        $response->assertStatus(403);
    }

    public function test_user_can_access_own_signature_image(): void
    {
        Storage::fake(StorageService::diskName());
        Storage::disk(StorageService::diskName())->put('users/signatures/sig_test.png', 'fake-image-bytes');

        $user = User::factory()->create([
            'signature_image' => 'users/signatures/sig_test.png',
            'status' => 'ACTIVE',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/users/{$user->id}/signature-image");
        $response->assertStatus(200);

        $myResponse = $this->getJson('/api/my-signature');
        $myResponse->assertStatus(200);
    }

    public function test_admin_can_access_any_users_signature_image(): void
    {
        Storage::fake(StorageService::diskName());
        Storage::disk(StorageService::diskName())->put('users/signatures/sig_user.png', 'fake-image-bytes');

        $admin = User::factory()->create(['level' => 'ADMIN', 'status' => 'ACTIVE']);
        $officer = User::factory()->create([
            'signature_image' => 'users/signatures/sig_user.png',
            'status' => 'ACTIVE',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/users/{$officer->id}/signature-image");
        $response->assertStatus(200);
    }
}
