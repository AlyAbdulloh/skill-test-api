<?php

namespace App\Services;

use App\Models\Resident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ResidentService
{
    /**
     * Get all residents with optional filtering and search.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getResidents(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Resident::query();

        // Search by full name or phone number
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Filter by resident status
        if (!empty($filters['resident_status'])) {
            $query->where('resident_status', $filters['resident_status']);
        }

        // Filter by marital status
        if (isset($filters['is_married']) && $filters['is_married'] !== '') {
            $query->where('is_married', (bool) $filters['is_married']);
        }

        // Order by latest created
        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new resident record.
     *
     * @param array $data
     * @return Resident
     */
    public function createResident(array $data): Resident
    {
        if (isset($data['id_card_photo']) && $data['id_card_photo'] instanceof UploadedFile) {
            $data['id_card_photo'] = $this->uploadPhoto($data['id_card_photo']);
        }

        return Resident::create($data);
    }

    /**
     * Find a resident by ID or throw exception.
     *
     * @param int $id
     * @return Resident
     */
    public function findResidentById(int $id): Resident
    {
        return Resident::findOrFail($id);
    }

    /**
     * Update a resident record.
     *
     * @param Resident $resident
     * @param array $data
     * @return Resident
     */
    public function updateResident(Resident $resident, array $data): Resident
    {
        if (isset($data['id_card_photo']) && $data['id_card_photo'] instanceof UploadedFile) {
            // Delete old photo if it exists
            $this->deletePhoto($resident->id_card_photo);
            
            // Upload new photo
            $data['id_card_photo'] = $this->uploadPhoto($data['id_card_photo']);
        }

        $resident->update($data);

        return $resident;
    }

    /**
     * Delete a resident record and its associated photo.
     *
     * @param Resident $resident
     * @return bool|null
     */
    public function deleteResident(Resident $resident): ?bool
    {
        $this->deletePhoto($resident->id_card_photo);
        return $resident->delete();
    }

    /**
     * Upload resident ID card photo.
     *
     * @param UploadedFile $file
     * @return string
     */
    private function uploadPhoto(UploadedFile $file): string
    {
        return $file->store('residents/id_cards', 'public');
    }

    /**
     * Delete resident ID card photo.
     *
     * @param string|null $path
     * @return void
     */
    private function deletePhoto(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
