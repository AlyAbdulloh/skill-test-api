<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\HouseResident;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseResidentApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving a list of house-resident associations.
     */
    public function test_can_list_house_residents(): void
    {
        HouseResident::factory()->count(3)->create();

        $response = $this->getJson('/api/house-residents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'house_id',
                            'resident_id',
                            'start_date',
                            'end_date',
                            'is_active',
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test listing house-resident associations with search and filter.
     */
    public function test_can_filter_and_search_house_residents(): void
    {
        $house1 = House::create(['house_number' => 'A-10', 'address' => 'Sudirman', 'occupancy_status' => 'occupied']);
        $house2 = House::create(['house_number' => 'B-20', 'address' => 'Thamrin', 'occupancy_status' => 'unoccupied']);

        $resident1 = Resident::create([
            'full_name' => 'John Doe',
            'id_card_photo' => 'photo1.jpg',
            'resident_status' => 'permanent',
            'phone_number' => '081234567890',
            'is_married' => true
        ]);
        $resident2 = Resident::create([
            'full_name' => 'Jane Smith',
            'id_card_photo' => 'photo2.jpg',
            'resident_status' => 'contract',
            'phone_number' => '081234567891',
            'is_married' => false
        ]);

        HouseResident::create([
            'house_id' => $house1->id,
            'resident_id' => $resident1->id,
            'start_date' => '2026-01-01',
            'end_date' => null,
            'is_active' => true
        ]);

        HouseResident::create([
            'house_id' => $house2->id,
            'resident_id' => $resident2->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-01-01',
            'is_active' => false
        ]);

        // Search by house number
        $response = $this->getJson('/api/house-residents?search=A-10');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.house_id', $house1->id);

        // Search by resident name
        $response = $this->getJson('/api/house-residents?search=Jane');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.resident_id', $resident2->id);

        // Filter by active status
        $response = $this->getJson('/api/house-residents?is_active=1');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.house_id', $house1->id);
    }

    /**
     * Test creating association for permanent resident (end_date not required).
     */
    public function test_can_create_association_for_permanent_resident(): void
    {
        $house = House::factory()->create();
        $resident = Resident::create([
            'full_name' => 'Permanent Resident',
            'id_card_photo' => 'photo.jpg',
            'resident_status' => 'permanent',
            'phone_number' => '081234567890',
            'is_married' => true
        ]);

        $payload = [
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'start_date' => '2026-06-01',
            'is_active' => true,
        ];

        // Should succeed without end_date
        $response = $this->postJson('/api/house-residents', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.end_date', null);

        $this->assertDatabaseHas('tr_house_residents', [
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'end_date' => null,
        ]);
    }

    /**
     * Test creating association for contract resident (end_date required).
     */
    public function test_create_association_for_contract_resident_requires_end_date(): void
    {
        $house = House::factory()->create();
        $resident = Resident::create([
            'full_name' => 'Contract Resident',
            'id_card_photo' => 'photo.jpg',
            'resident_status' => 'contract',
            'phone_number' => '081234567890',
            'is_married' => true
        ]);

        $payload = [
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'start_date' => '2026-06-01',
            'is_active' => true,
        ];

        // Missing end_date should fail validation
        $response = $this->postJson('/api/house-residents', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);

        // Providing end_date should succeed
        $payload['end_date'] = '2027-06-01';
        $response = $this->postJson('/api/house-residents', $payload);
        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.end_date', '2027-06-01');

        $this->assertDatabaseHas('tr_house_residents', [
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $association = HouseResident::where('house_id', $house->id)->where('resident_id', $resident->id)->first();
        $this->assertEquals('2027-06-01', $association->end_date?->format('Y-m-d'));
    }

    /**
     * Test showing a specific association.
     */
    public function test_can_show_house_resident_association(): void
    {
        $association = HouseResident::factory()->create();

        $response = $this->getJson("/api/house-residents/{$association->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'house_id',
                    'house' => [
                        'id',
                        'house_number',
                    ],
                    'resident_id',
                    'resident' => [
                        'id',
                        'full_name',
                    ],
                    'start_date',
                    'end_date',
                    'is_active',
                ]
            ]);
    }

    /**
     * Test updating a house resident association.
     */
    public function test_can_update_house_resident_association(): void
    {
        $association = HouseResident::factory()->create();

        $payload = [
            'is_active' => false,
        ];

        $response = $this->putJson("/api/house-residents/{$association->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_active', false);

        $association->refresh();
        $this->assertFalse($association->is_active);
    }

    /**
     * Test deleting a house resident association.
     */
    public function test_can_delete_house_resident_association(): void
    {
        $association = HouseResident::factory()->create();

        $response = $this->deleteJson("/api/house-residents/{$association->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tr_house_residents', ['id' => $association->id]);
    }
}
