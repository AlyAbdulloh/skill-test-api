<?php

namespace App\Services;
use Carbon\Carbon;
use App\Models\House;
use App\Models\Resident;
use App\Models\HouseResident;
use App\Models\FeeType;
use App\Models\PaymentBill;
use App\Models\Expense;
use App\Models\ExpenseDetail;

class ReportService
{
    /**
     * Get summary report data with optional filtering.
     *
     * @param array $filters
     * @return array
     */
    public function getSummaryReport(array $filters = []): array
    {
        $month = $filters['month'] ?? Carbon::now()->format('Y-m');
        $year = Carbon::parse($month)->year;

        // Query total collected income from payment bills (status = 'paid')
        $income = PaymentBill::selectRaw('MONTH(billing_month) as month, SUM(amount) as total_income')
                    ->whereYear('billing_month', $year)
                    ->where('status', 'paid')
                    ->groupByRaw('MONTH(billing_month)')
                    ->orderByRaw('MONTH(billing_month)')
                    ->get();

        // Query total expenses from tr_expenses joined with tr_expense_details
        $expense = Expense::join('tr_expense_details', 'tr_expenses.id', '=', 'tr_expense_details.expense_id')
                    ->selectRaw('MONTH(tr_expenses.expense_date) as month, SUM(tr_expense_details.amount) as total_expense')
                    ->whereYear('tr_expenses.expense_date', $year)
                    ->groupByRaw('MONTH(tr_expenses.expense_date)')
                    ->orderByRaw('MONTH(tr_expenses.expense_date)')
                    ->get();
        
        return [
            'year' => $year,
            'income' => $income,
            'expense' => $expense,
        ];
    }

    /**
     * Get detailed report for a specific month (incomes and expenses).
     *
     * @param array $filters
     * @return array
     */
    public function getMonthlyDetailReport(array $filters = []): array
    {
        $month = $filters['month'] ?? Carbon::now()->format('Y-m');

        // Fetch paid payment bills in that month
        $incomes = PaymentBill::with(['houseResident.resident', 'houseResident.house', 'feeType'])
            ->whereRaw("DATE_FORMAT(billing_month, '%Y-%m') = ?", [$month])
            ->where('status', 'paid')
            ->latest('paid_at')
            ->get();

        // Fetch expense details in that month
        $expenses = ExpenseDetail::with('expense')
            ->whereHas('expense', function ($query) use ($month) {
                $query->whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$month]);
            })
            ->get();

        return [
            'month' => $month,
            'incomes' => $incomes,
            'expenses' => $expenses,
            'total_income' => (float) $incomes->sum('amount'),
            'total_expense' => (float) $expenses->sum('amount'),
        ];
    }
}
