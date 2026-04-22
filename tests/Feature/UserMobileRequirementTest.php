<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UserMobileRequirementTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_is_fillable_for_users(): void
    {
        $user = User::factory()->create([
            'role' => 'delivery_manager',
            'mobile' => '01700000051',
        ]);

        $this->assertSame('01700000051', $user->mobile);
    }

    public function test_mobile_is_required_for_delivery_manager_and_delivery_staff_validation_rule(): void
    {
        $deliveryManagerValidator = Validator::make(
            ['role' => 'delivery_manager', 'mobile' => null],
            ['mobile' => ['required_if:role,delivery_manager,delivery_staff']]
        );

        $deliveryStaffValidator = Validator::make(
            ['role' => 'delivery_staff', 'mobile' => null],
            ['mobile' => ['required_if:role,delivery_manager,delivery_staff']]
        );

        $adminValidator = Validator::make(
            ['role' => 'admin', 'mobile' => null],
            ['mobile' => ['required_if:role,delivery_manager,delivery_staff']]
        );

        $this->assertTrue($deliveryManagerValidator->fails());
        $this->assertTrue($deliveryStaffValidator->fails());
        $this->assertFalse($adminValidator->fails());
    }
}
