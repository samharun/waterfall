<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryStaffLocationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_staff_can_update_current_location(): void
    {
        $user = User::factory()->create([
            'role' => 'delivery_staff',
            'mobile' => '01700000071',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/delivery/location', [
            'latitude' => 23.810331,
            'longitude' => 90.412521,
            'accuracy' => 12.5,
            'speed' => 3.2,
            'heading' => 180,
            'battery_level' => 87,
            'tracked_at' => '2026-05-03T10:15:00+06:00',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Location updated successfully.')
            ->assertJsonPath('data.location.latitude', 23.810331)
            ->assertJsonPath('data.location.longitude', 90.412521);

        $this->assertDatabaseHas('delivery_staff_locations', [
            'user_id' => $user->id,
            'latitude' => 23.810331,
            'longitude' => 90.412521,
            'battery_level' => 87,
        ]);
    }

    public function test_non_delivery_user_cannot_update_delivery_location(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'mobile' => '01700000072',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/delivery/location', [
            'latitude' => 23.810331,
            'longitude' => 90.412521,
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }
}
