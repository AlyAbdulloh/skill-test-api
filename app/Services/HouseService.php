<?php

namespace App\Services;

use App\Models\House;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HouseService
{
    /**
     * Get all houses with optional filtering and search.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getHouses(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = House::query();

        // Search by house number or address
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('house_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by occupancy status
        if (!empty($filters['occupancy_status'])) {
            $query->where('occupancy_status', $filters['occupancy_status']);
        }

        // Order by latest created
        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new house record.
     *
     * @param array $data
     * @return House
     */
    public function createHouse(array $data): House
    {
        return House::create($data);
    }

    /**
     * Find a house by ID or throw exception.
     *
     * @param int $id
     * @return House
     */
    public function findHouseById(int $id): House
    {
        return House::findOrFail($id);
    }

    /**
     * Update a house record.
     *
     * @param House $house
     * @param array $data
     * @return House
     */
    public function updateHouse(House $house, array $data): House
    {
        $house->update($data);

        return $house;
    }

    /**
     * Delete a house record.
     *
     * @param House $house
     * @return bool|null
     */
    public function deleteHouse(House $house): ?bool
    {
        return $house->delete();
    }

    /**
     * Get the resident history for a specific house.
     *
     * @param House $house
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getResidentHistory(House $house, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $house->houseResidents()
            ->with('resident')
            ->latest('start_date')
            ->paginate($perPage);
    }
}
