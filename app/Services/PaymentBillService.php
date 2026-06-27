<?php

namespace App\Services;

use App\Models\PaymentBill;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PaymentBillService
{
    /**
     * Get all payment bills with optional filtering and search.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaymentBills(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = PaymentBill::query()->with([
            'houseResident',
            'houseResident.house',
            'houseResident.resident',
            'feeType'
        ]);

        // Search by resident name or house number
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('houseResident', function ($q) use ($search) {
                $q->whereHas('resident', function ($qr) use ($search) {
                    $qr->where('full_name', 'like', "%{$search}%");
                })->orWhereHas('house', function ($qh) use ($search) {
                    $qh->where('house_number', 'like', "%{$search}%");
                });
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by payment_group_id
        if (isset($filters['payment_group_id'])) {
            $query->where('payment_group_id', $filters['payment_group_id']);
        }

        // Filter by house_resident_id
        if (!empty($filters['house_resident_id'])) {
            $query->where('house_resident_id', $filters['house_resident_id']);
        }

        // Filter by fee_type_id
        if (!empty($filters['fee_type_id'])) {
            $query->where('fee_type_id', $filters['fee_type_id']);
        }

        // Order by latest created
        return $query->latest()->paginate($perPage);
    }

    /**
     * Create bulk payment bills.
     *
     * @param array $data
     * @return array
     */
    public function createPaymentBills(array $data): array
    {
        $groupId = null;
        if (count($data['months']) > 1) {
            $groupId = (string) Str::uuid();
        }

        $bills = [];
        $now = now();

        foreach ($data['months'] as $month) {
            $bills[] = [
                'house_resident_id' => $data['house_resident_id'],
                'fee_type_id'       => $data['fee_type_id'],
                'billing_month'     => $month . '-01',
                'amount'            => $data['amount_per_month'],
                'status'            => 'paid',
                'paid_at'           => $data['paid_at'],
                'payment_group_id'  => $groupId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        PaymentBill::insert($bills);

        return [
            'total_months'     => count($bills),
            'total_amount'     => $data['amount_per_month'] * count($bills),
            'payment_group_id' => $groupId,
        ];
    }

    /**
     * Find a payment bill by ID or throw exception.
     *
     * @param int $id
     * @return PaymentBill
     */
    public function findPaymentBillById(int $id): PaymentBill
    {
        return PaymentBill::with([
            'houseResident',
            'houseResident.house',
            'houseResident.resident',
            'feeType'
        ])->findOrFail($id);
    }

    /**
     * Update a payment bill record.
     *
     * @param PaymentBill $bill
     * @param array $data
     * @return PaymentBill
     */
    public function updatePaymentBill(PaymentBill $bill, array $data): PaymentBill
    {
        $bill->update($data);
        return $bill->load([
            'houseResident',
            'houseResident.house',
            'houseResident.resident',
            'feeType'
        ]);
    }

    /**
     * Delete a payment bill record.
     *
     * @param PaymentBill $bill
     * @return bool|null
     */
    public function deletePaymentBill(PaymentBill $bill): ?bool
    {
        return $bill->delete();
    }
}
