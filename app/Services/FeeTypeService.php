<?php

namespace App\Services;

use App\Models\FeeType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FeeTypeService
{
    /**
     * Get all fee types with optional filtering and search.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFeeTypes(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = FeeType::query();

        // Search by fee type name
        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        // Filter by active status
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Order by latest created
        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new fee type record.
     *
     * @param array $data
     * @return FeeType
     */
    public function createFeeType(array $data): FeeType
    {
        return FeeType::create($data);
    }

    /**
     * Find a fee type by ID or throw exception.
     *
     * @param int $id
     * @return FeeType
     */
    public function findFeeTypeById(int $id): FeeType
    {
        return FeeType::findOrFail($id);
    }

    /**
     * Update a fee type record.
     *
     * @param FeeType $feeType
     * @param array $data
     * @return FeeType
     */
    public function updateFeeType(FeeType $feeType, array $data): FeeType
    {
        $feeType->update($data);

        return $feeType;
    }

    /**
     * Delete a fee type record.
     *
     * @param FeeType $feeType
     * @return bool|null
     */
    public function deleteFeeType(FeeType $feeType): ?bool
    {
        return $feeType->delete();
    }
}
