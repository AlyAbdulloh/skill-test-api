<?php

namespace App\Services;

use App\Models\HouseResident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HouseResidentService
{
    /**
     * Get all house-resident associations with optional filtering and search.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getHouseResidents(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = HouseResident::query()->with(['house', 'resident']);

        // Search by resident name or house number
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('resident', function ($qr) use ($search) {
                    $qr->where('full_name', 'like', "%{$search}%");
                })->orWhereHas('house', function ($qh) use ($search) {
                    $qh->where('house_number', 'like', "%{$search}%");
                });
            });
        }

        // Filter by active status
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Filter by house_id
        if (!empty($filters['house_id'])) {
            $query->where('house_id', $filters['house_id']);
        }

        // Filter by resident_id
        if (!empty($filters['resident_id'])) {
            $query->where('resident_id', $filters['resident_id']);
        }

        // Order by latest created
        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new house-resident association.
     *
     * @param array $data
     * @return HouseResident
     */
    public function createHouseResident(array $data): HouseResident
    {
        // Enforce null end_date for permanent residents
        if (isset($data['resident_id'])) {
            $resident = \App\Models\Resident::find($data['resident_id']);
            if ($resident && $resident->resident_status === 'permanent') {
                $data['end_date'] = null;
            }
        }

        $association = HouseResident::create($data);
        return $association->load(['house', 'resident']);
    }

    /**
     * Find a house-resident association by ID or throw exception.
     *
     * @param int $id
     * @return HouseResident
     */
    public function findHouseResidentById(int $id): HouseResident
    {
        return HouseResident::with(['house', 'resident'])->findOrFail($id);
    }

    /**
     * Update a house-resident association.
     *
     * @param HouseResident $association
     * @param array $data
     * @return HouseResident
     */
    public function updateHouseResident(HouseResident $association, array $data): HouseResident
    {
        // Enforce null end_date if updating to a permanent resident
        $residentId = $data['resident_id'] ?? $association->resident_id;
        $resident = \App\Models\Resident::find($residentId);
        if ($resident && $resident->resident_status === 'permanent') {
            $data['end_date'] = null;
        }

        $association->update($data);
        return $association->load(['house', 'resident']);
    }

    /**
     * Delete a house-resident association.
     *
     * @param HouseResident $association
     * @return bool|null
     */
    public function deleteHouseResident(HouseResident $association): ?bool
    {
        return $association->delete();
    }
}
