<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    /**
     * Get all expenses with optional search and filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getExpenses(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Expense::query()->with('details');

        // Search by notes or detail title
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('details', function ($qd) use ($search) {
                      $qd->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if (!empty($filters['start_date'])) {
            $query->whereDate('expense_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('expense_date', '<=', $filters['end_date']);
        }

        // Order by latest created
        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new expense and its details.
     *
     * @param array $data
     * @return Expense
     */
    public function createExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            $expense = Expense::create([
                'expense_date' => $data['expense_date'],
                'notes' => $data['notes'],
            ]);

            if (isset($data['details']) && is_array($data['details'])) {
                $expense->details()->createMany($data['details']);
            }

            return $expense->load('details');
        });
    }

    /**
     * Find an expense by ID or throw exception.
     *
     * @param int $id
     * @return Expense
     */
    public function findExpenseById(int $id): Expense
    {
        return Expense::with('details')->findOrFail($id);
    }

    /**
     * Update an expense and its details.
     *
     * @param Expense $expense
     * @param array $data
     * @return Expense
     */
    public function updateExpense(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $expense->update(array_filter([
                'expense_date' => $data['expense_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]));

            if (isset($data['details']) && is_array($data['details'])) {
                // Delete old details
                $expense->details()->delete();
                // Create new details
                $expense->details()->createMany($data['details']);
            }

            return $expense->load('details');
        });
    }

    /**
     * Delete an expense and its details.
     *
     * @param Expense $expense
     * @return bool|null
     */
    public function deleteExpense(Expense $expense): ?bool
    {
        return DB::transaction(function () use ($expense) {
            $expense->details()->delete();
            return $expense->delete();
        });
    }
}
