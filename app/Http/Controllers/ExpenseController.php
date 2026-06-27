<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    use ApiResponse;

    /**
     * The expense service instance.
     *
     * @var ExpenseService
     */
    protected ExpenseService $expenseService;

    /**
     * Create a new controller instance.
     *
     * @param ExpenseService $expenseService
     */
    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'start_date', 'end_date']);
        $perPage = $request->query('per_page', 10);

        $expenses = $this->expenseService->getExpenses($filters, (int) $perPage);

        return $this->successResponse(
            ExpenseResource::collection($expenses)->response()->getData(true),
            'Expenses retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreExpenseRequest $request
     * @return JsonResponse
     */
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->expenseService->createExpense($request->validated());

        return $this->successResponse(
            new ExpenseResource($expense),
            'Expense created successfully.',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $expense = $this->expenseService->findExpenseById($id);

        return $this->successResponse(
            new ExpenseResource($expense),
            'Expense details retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateExpenseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateExpenseRequest $request, int $id): JsonResponse
    {
        $expense = $this->expenseService->findExpenseById($id);
        $updated = $this->expenseService->updateExpense($expense, $request->validated());

        return $this->successResponse(
            new ExpenseResource($updated),
            'Expense updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $expense = $this->expenseService->findExpenseById($id);
        $this->expenseService->deleteExpense($expense);

        return $this->successResponse(
            null,
            'Expense deleted successfully.'
        );
    }
}
