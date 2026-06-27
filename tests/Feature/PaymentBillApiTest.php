<?php

namespace Tests\Feature;

use App\Models\FeeType;
use App\Models\HouseResident;
use App\Models\PaymentBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentBillApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving a list of payment bills.
     */
    public function test_can_list_payment_bills(): void
    {
        PaymentBill::factory()->count(3)->create();

        $response = $this->getJson('/api/payment-bills');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'house_resident_id',
                            'fee_type_id',
                            'billing_month',
                            'amount',
                            'status',
                            'paid_at',
                            'payment_group_id',
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test bulk store payment bill for single month.
     */
    public function test_can_create_payment_bill_single_month(): void
    {
        $association = HouseResident::factory()->create();
        $feeType = FeeType::factory()->create();

        $payload = [
            'months' => ['2026-06'],
            'house_resident_id' => $association->id,
            'fee_type_id' => $feeType->id,
            'amount_per_month' => 50000,
            'paid_at' => '2026-06-25',
        ];

        $response = $this->postJson('/api/payment-bills', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_months', 1)
            ->assertJsonPath('data.total_amount', 50000)
            ->assertJsonPath('data.payment_group_id', null);

        $this->assertDatabaseHas('tr_payment_bills', [
            'house_resident_id' => $association->id,
            'fee_type_id' => $feeType->id,
            'billing_month' => '2026-06-01',
            'amount' => 50000,
            'status' => 'paid',
            'paid_at' => '2026-06-25',
            'payment_group_id' => null,
        ]);
    }

    /**
     * Test bulk store payment bills for multiple months.
     */
    public function test_can_create_payment_bills_multiple_months(): void
    {
        $association = HouseResident::factory()->create();
        $feeType = FeeType::factory()->create();

        $payload = [
            'months' => ['2026-06', '2026-07', '2026-08'],
            'house_resident_id' => $association->id,
            'fee_type_id' => $feeType->id,
            'amount_per_month' => 50000,
            'paid_at' => '2026-06-25',
        ];

        $response = $this->postJson('/api/payment-bills', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_months', 3)
            ->assertJsonPath('data.total_amount', 150000);

        $groupId = $response->json('data.payment_group_id');
        $this->assertNotNull($groupId);
        $this->assertIsString($groupId);

        $this->assertDatabaseCount('tr_payment_bills', 3);
        $this->assertDatabaseHas('tr_payment_bills', [
            'house_resident_id' => $association->id,
            'fee_type_id' => $feeType->id,
            'billing_month' => '2026-07-01',
            'amount' => 50000,
            'status' => 'paid',
            'paid_at' => '2026-06-25',
            'payment_group_id' => $groupId,
        ]);
    }

    /**
     * Test validation failure on creation.
     */
    public function test_create_payment_bill_validation_fails(): void
    {
        $response = $this->postJson('/api/payment-bills', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['months', 'house_resident_id', 'fee_type_id', 'amount_per_month', 'paid_at']);
    }

    /**
     * Test showing a specific bill.
     */
    public function test_can_show_payment_bill(): void
    {
        $bill = PaymentBill::factory()->create();

        $response = $this->getJson("/api/payment-bills/{$bill->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $bill->id);
    }

    /**
     * Test updating a bill.
     */
    public function test_can_update_payment_bill(): void
    {
        $bill = PaymentBill::factory()->create(['status' => 'unpaid', 'amount' => 30000]);

        $payload = [
            'status' => 'paid',
            'amount' => 40000,
            'paid_at' => '2026-06-25',
        ];

        $response = $this->putJson("/api/payment-bills/{$bill->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.amount', 40000);

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertEquals(40000, $bill->amount);
    }

    /**
     * Test deleting a bill.
     */
    public function test_can_delete_payment_bill(): void
    {
        $bill = PaymentBill::factory()->create();

        $response = $this->deleteJson("/api/payment-bills/{$bill->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tr_payment_bills', ['id' => $bill->id]);
    }
}
