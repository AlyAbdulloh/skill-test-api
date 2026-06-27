<?php

namespace Tests\Feature;

use App\Models\FeeType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeTypeApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving a list of fee types.
     */
    public function test_can_list_fee_types(): void
    {
        FeeType::factory()->count(3)->create();

        $response = $this->getJson('/api/fee-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'amount',
                            'is_active',
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test listing fee types with search and filter.
     */
    public function test_can_filter_and_search_fee_types(): void
    {
        FeeType::create([
            'name' => 'Monthly Trash Fee',
            'amount' => 20000,
            'is_active' => true,
        ]);

        FeeType::create([
            'name' => 'Yearly Security Fee',
            'amount' => 150000,
            'is_active' => false,
        ]);

        // Search by name
        $response = $this->getJson('/api/fee-types?search=Trash');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Monthly Trash Fee');

        // Filter by active status
        $response = $this->getJson('/api/fee-types?is_active=1');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Monthly Trash Fee');
    }

    /**
     * Test creating a fee type with validation.
     */
    public function test_can_create_fee_type(): void
    {
        $payload = [
            'name' => 'Maintenance Fee',
            'amount' => 50000,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/fee-types', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Maintenance Fee');

        $this->assertDatabaseHas('mst_fee_types', [
            'name' => 'Maintenance Fee',
            'amount' => 50000.00,
            'is_active' => 1,
        ]);
    }

    /**
     * Test creating fee type validation errors.
     */
    public function test_create_fee_type_validation_fails(): void
    {
        $response = $this->postJson('/api/fee-types', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'amount', 'is_active']);
    }

    /**
     * Test creating duplicate name validation fails.
     */
    public function test_create_fee_type_with_duplicate_name_fails(): void
    {
        FeeType::create([
            'name' => 'Internet Fee',
            'amount' => 200000,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Internet Fee',
            'amount' => 300000,
            'is_active' => false,
        ];

        $response = $this->postJson('/api/fee-types', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test showing a specific fee type.
     */
    public function test_can_show_fee_type(): void
    {
        $feeType = FeeType::create([
            'name' => 'Specific Fee',
            'amount' => 12345.67,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/fee-types/{$feeType->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Specific Fee');
    }

    /**
     * Test updating a fee type.
     */
    public function test_can_update_fee_type(): void
    {
        $feeType = FeeType::create([
            'name' => 'Old Fee',
            'amount' => 1000,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Updated Fee',
            'amount' => 2000,
        ];

        $response = $this->putJson("/api/fee-types/{$feeType->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Fee');

        $feeType->refresh();
        $this->assertEquals('Updated Fee', $feeType->name);
        $this->assertEquals(2000, $feeType->amount);
    }

    /**
     * Test deleting a fee type.
     */
    public function test_can_delete_fee_type(): void
    {
        $feeType = FeeType::create([
            'name' => 'Delete Fee',
            'amount' => 100,
            'is_active' => false,
        ]);

        $response = $this->deleteJson("/api/fee-types/{$feeType->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('mst_fee_types', ['id' => $feeType->id]);
    }
}
