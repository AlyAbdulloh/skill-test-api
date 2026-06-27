<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentBillRequest;
use App\Http\Requests\UpdatePaymentBillRequest;
use App\Http\Resources\PaymentBillResource;
use App\Services\PaymentBillService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentBillController extends Controller
{
    use ApiResponse;

    /**
     * The payment bill service instance.
     *
     * @var PaymentBillService
     */
    protected PaymentBillService $paymentBillService;

    /**
     * Create a new controller instance.
     *
     * @param PaymentBillService $paymentBillService
     */
    public function __construct(PaymentBillService $paymentBillService)
    {
        $this->paymentBillService = $paymentBillService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'payment_group_id', 'house_resident_id', 'fee_type_id']);
        $perPage = $request->query('per_page', 10);

        $bills = $this->paymentBillService->getPaymentBills($filters, (int) $perPage);

        return $this->successResponse(
            PaymentBillResource::collection($bills)->response()->getData(true),
            'Payment bills retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePaymentBillRequest $request
     * @return JsonResponse
     */
    public function store(StorePaymentBillRequest $request): JsonResponse
    {
        $result = $this->paymentBillService->createPaymentBills($request->validated());

        return $this->successResponse(
            $result,
            'Pembayaran berhasil disimpan',
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
        $bill = $this->paymentBillService->findPaymentBillById($id);

        return $this->successResponse(
            new PaymentBillResource($bill),
            'Payment bill details retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePaymentBillRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdatePaymentBillRequest $request, int $id): JsonResponse
    {
        $bill = $this->paymentBillService->findPaymentBillById($id);
        $updated = $this->paymentBillService->updatePaymentBill($bill, $request->validated());

        return $this->successResponse(
            new PaymentBillResource($updated),
            'Payment bill updated successfully.'
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
        $bill = $this->paymentBillService->findPaymentBillById($id);
        $this->paymentBillService->deletePaymentBill($bill);

        return $this->successResponse(
            null,
            'Payment bill deleted successfully.'
        );
    }
}
