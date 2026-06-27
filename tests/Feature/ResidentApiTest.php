<?php

namespace Tests\Feature;

use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResidentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test retrieving a list of residents.
     */
    public function test_can_list_residents(): void
    {
        Resident::factory()->count(3)->create();

        $response = $this->getJson('/api/residents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'full_name',
                            'id_card_photo',
                            'id_card_photo_url',
                            'resident_status',
                            'phone_number',
                            'is_married',
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test creating a resident with validation.
     */
    public function test_can_create_resident(): void
    {
        $photo = UploadedFile::fake()->image('id_card.jpg');

        $payload = [
            'full_name' => 'John Doe',
            'id_card_photo' => $photo,
            'resident_status' => 'permanent',
            'phone_number' => '081234567890',
            'is_married' => true,
        ];

        $response = $this->postJson('/api/residents', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'John Doe');

        $resident = Resident::first();
        $this->assertNotNull($resident);
        $this->assertEquals('permanent', $resident->resident_status);
        
        // Verify file was stored on public disk
        Storage::disk('public')->assertExists($resident->id_card_photo);
    }

    /**
     * Test creating resident validation errors.
     */
    public function test_create_resident_validation_fails(): void
    {
        $response = $this->postJson('/api/residents', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['full_name', 'id_card_photo', 'resident_status', 'phone_number', 'is_married']);
    }

    /**
     * Test showing a specific resident.
     */
    public function test_can_show_resident(): void
    {
        $resident = Resident::create([
            'full_name' => 'Jane Doe',
            'id_card_photo' => 'residents/id_cards/fake.jpg',
            'resident_status' => 'contract',
            'phone_number' => '089876543210',
            'is_married' => false,
        ]);

        $response = $this->getJson("/api/residents/{$resident->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'Jane Doe');
    }

    /**
     * Test updating a resident details and photo.
     */
    public function test_can_update_resident(): void
    {
        $resident = Resident::create([
            'full_name' => 'Old Name',
            'id_card_photo' => 'residents/id_cards/old.jpg',
            'resident_status' => 'contract',
            'phone_number' => '089876543210',
            'is_married' => false,
        ]);

        // Mock old file exists
        Storage::disk('public')->put('residents/id_cards/old.jpg', 'content');

        $newPhoto = UploadedFile::fake()->image('new_id.jpg');

        $payload = [
            'full_name' => 'New Name',
            'id_card_photo' => $newPhoto,
            'resident_status' => 'permanent',
        ];

        $response = $this->putJson("/api/residents/{$resident->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'New Name');

        $resident->refresh();
        $this->assertEquals('New Name', $resident->full_name);
        $this->assertEquals('permanent', $resident->resident_status);

        // Verify old photo was deleted and new photo exists
        Storage::disk('public')->assertMissing('residents/id_cards/old.jpg');
        Storage::disk('public')->assertExists($resident->id_card_photo);
    }

    /**
     * Test deleting a resident and its photo.
     */
    public function test_can_delete_resident(): void
    {
        $resident = Resident::create([
            'full_name' => 'To Be Deleted',
            'id_card_photo' => 'residents/id_cards/delete.jpg',
            'resident_status' => 'contract',
            'phone_number' => '089876543210',
            'is_married' => false,
        ]);

        Storage::disk('public')->put('residents/id_cards/delete.jpg', 'content');

        $response = $this->deleteJson("/api/residents/{$resident->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('mst_residents', ['id' => $resident->id]);
        Storage::disk('public')->assertMissing('residents/id_cards/delete.jpg');
    }
}
