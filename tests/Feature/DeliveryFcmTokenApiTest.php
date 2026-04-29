<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFcmToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryFcmTokenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_user_can_save_and_delete_fcm_token(): void
    {
        $user = User::factory()->create([
            'role' => 'delivery_staff',
            'mobile' => '01700000061',
        ]);

        Sanctum::actingAs($user);

        $saveResponse = $this->postJson('/api/delivery/save-fcm-token', [
            'fcm_token' => 'delivery-fcm-token-abc',
            'platform' => 'android',
            'device_name' => 'Pixel 8',
        ]);

        $saveResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'FCM token saved successfully.');

        $this->assertDatabaseHas('user_fcm_tokens', [
            'user_id' => $user->id,
            'token_hash' => UserFcmToken::hashToken('delivery-fcm-token-abc'),
            'platform' => 'android',
            'device_name' => 'Pixel 8',
        ]);

        $deleteResponse = $this->postJson('/api/delivery/delete-fcm-token', [
            'fcm_token' => 'delivery-fcm-token-abc',
        ]);

        $deleteResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'FCM token deleted successfully.');

        $this->assertDatabaseMissing('user_fcm_tokens', [
            'user_id' => $user->id,
            'token_hash' => UserFcmToken::hashToken('delivery-fcm-token-abc'),
        ]);
    }

    public function test_non_delivery_user_cannot_save_delivery_fcm_token(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'mobile' => '01700000062',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/delivery/save-fcm-token', [
            'fcm_token' => 'customer-token-not-allowed',
            'platform' => 'android',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }
}
