<?php

namespace Tests\Feature;

use App\Models\House;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving a list of houses.
     */
    public function test_can_list_houses(): void
    {
        House::factory()->count(3)->create();

        $response = $this->getJson('/api/houses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'house_number',
                            'address',
                            'occupancy_status',
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test listing houses with search and filter.
     */
    public function test_can_filter_and_search_houses(): void
    {
        House::create([
            'house_number' => 'A-1',
            'address' => 'Sudirman Street',
            'occupancy_status' => 'occupied',
        ]);

        House::create([
            'house_number' => 'B-2',
            'address' => 'Thamrin Road',
            'occupancy_status' => 'unoccupied',
        ]);

        // Search by house number
        $response = $this->getJson('/api/houses?search=A-1');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.house_number', 'A-1');

        // Search by address
        $response = $this->getJson('/api/houses?search=Thamrin');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.house_number', 'B-2');

        // Filter by occupancy status
        $response = $this->getJson('/api/houses?occupancy_status=occupied');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.house_number', 'A-1');
    }

    /**
     * Test creating a house with validation.
     */
    public function test_can_create_house(): void
    {
        $payload = [
            'house_number' => 'C-3',
            'address' => 'Gatot Subroto Street',
            'occupancy_status' => 'unoccupied',
        ];

        $response = $this->postJson('/api/houses', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.house_number', 'C-3');

        $this->assertDatabaseHas('mst_houses', [
            'house_number' => 'C-3',
            'occupancy_status' => 'unoccupied',
        ]);
    }

    /**
     * Test creating house validation errors.
     */
    public function test_create_house_validation_fails(): void
    {
        $response = $this->postJson('/api/houses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['house_number', 'address', 'occupancy_status']);
    }

    /**
     * Test creating house with duplicate house number fails.
     */
    public function test_create_house_with_duplicate_house_number_fails(): void
    {
        House::create([
            'house_number' => 'D-4',
            'address' => 'Kuningan Street',
            'occupancy_status' => 'occupied',
        ]);

        $payload = [
            'house_number' => 'D-4',
            'address' => 'Another street',
            'occupancy_status' => 'unoccupied',
        ];

        $response = $this->postJson('/api/houses', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['house_number']);
    }

    /**
     * Test showing a specific house.
     */
    public function test_can_show_house(): void
    {
        $house = House::create([
            'house_number' => 'E-5',
            'address' => 'Palmerah Road',
            'occupancy_status' => 'occupied',
        ]);

        $response = $this->getJson("/api/houses/{$house->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.house_number', 'E-5');
    }

    /**
     * Test updating a house.
     */
    public function test_can_update_house(): void
    {
        $house = House::create([
            'house_number' => 'F-6',
            'address' => 'Slipi Street',
            'occupancy_status' => 'unoccupied',
        ]);

        $payload = [
            'house_number' => 'F-6-UPDATED',
            'occupancy_status' => 'occupied',
        ];

        $response = $this->putJson("/api/houses/{$house->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.house_number', 'F-6-UPDATED');

        $house->refresh();
        $this->assertEquals('F-6-UPDATED', $house->house_number);
        $this->assertEquals('occupied', $house->occupancy_status);
        $this->assertEquals('Slipi Street', $house->address); // Unchanged field remains
    }

    /**
     * Test deleting a house.
     */
    public function test_can_delete_house(): void
    {
        $house = House::create([
            'house_number' => 'G-7',
            'address' => 'Tomang Road',
            'occupancy_status' => 'unoccupied',
        ]);

        $response = $this->deleteJson("/api/houses/{$house->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('mst_houses', ['id' => $house->id]);
    }

    /**
     * Test retrieving the resident history for a specific house.
     */
    public function test_can_get_house_resident_history(): void
    {
        $house = House::factory()->create();
        $resident1 = \App\Models\Resident::factory()->create(['full_name' => 'Resident One']);
        $resident2 = \App\Models\Resident::factory()->create(['full_name' => 'Resident Two']);

        \App\Models\HouseResident::create([
            'house_id' => $house->id,
            'resident_id' => $resident1->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_active' => false
        ]);

        \App\Models\HouseResident::create([
            'house_id' => $house->id,
            'resident_id' => $resident2->id,
            'start_date' => '2026-01-01',
            'end_date' => null,
            'is_active' => true
        ]);

        $response = $this->getJson("/api/houses/{$house->id}/residents");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.data')
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'house_id',
                            'resident_id',
                            'resident' => [
                                'id',
                                'full_name',
                            ],
                            'start_date',
                            'end_date',
                            'is_active',
                        ]
                    ]
                ]
            ]);
    }
}
