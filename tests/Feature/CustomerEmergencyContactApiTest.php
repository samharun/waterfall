<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerEmergencyContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_get_zone_delivery_manager_as_emergency_contact(): void
    {
        $deliveryManager = User::factory()->create([
            'name' => 'Zone Manager',
            'email' => 'manager@example.com',
            'mobile' => '01700000061',
            'role' => 'delivery_manager',
        ]);

        $zone = Zone::create([
            'name' => 'Dhaka North',
            'code' => 'DN-01',
            'delivery_manager_id' => $deliveryManager->id,
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'user_id' => null,
            'name' => 'Emergency Contact Customer',
            'mobile' => '01700000041',
            'email' => 'customer-emergency@example.com',
            'address' => 'Mirpur, Dhaka',
            'zone_id' => $zone->id,
            'customer_type' => 'residential',
            'approval_status' => 'approved',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/customer/emergency-contact');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.emergency_contact.name', 'Zone Manager')
            ->assertJsonPath('data.emergency_contact.email', 'manager@example.com')
            ->assertJsonPath('data.emergency_contact.phone', '01700000061')
            ->assertJsonPath('data.emergency_contact.mobile', '01700000061')
            ->assertJsonPath('data.emergency_contact.designation', 'Delivery Manager')
            ->assertJsonPath('data.emergency_contact.zone.id', $zone->id)
            ->assertJsonPath('data.emergency_contact.zone.name', 'Dhaka North')
            ->assertJsonPath('data.emergency_contact.zone.code', 'DN-01');
    }

    public function test_dashboard_includes_emergency_contact_data(): void
    {
        $deliveryManager = User::factory()->create([
            'name' => 'Dashboard Manager',
            'email' => 'dashboard-manager@example.com',
            'mobile' => '01700000062',
            'role' => 'delivery_manager',
        ]);

        $zone = Zone::create([
            'name' => 'Dhaka South',
            'code' => 'DS-01',
            'delivery_manager_id' => $deliveryManager->id,
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'user_id' => null,
            'name' => 'Dashboard Customer',
            'mobile' => '01700000042',
            'email' => 'dashboard-customer@example.com',
            'address' => 'Dhanmondi, Dhaka',
            'zone_id' => $zone->id,
            'customer_type' => 'residential',
            'approval_status' => 'approved',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/customer/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.emergency_contact.name', 'Dashboard Manager')
            ->assertJsonPath('data.emergency_contact.email', 'dashboard-manager@example.com')
            ->assertJsonPath('data.emergency_contact.phone', '01700000062')
            ->assertJsonPath('data.emergency_contact.mobile', '01700000062')
            ->assertJsonPath('data.emergency_contact.zone.name', 'Dhaka South');
    }
}
