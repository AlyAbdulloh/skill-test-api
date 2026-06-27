<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeTypeRequest;
use App\Http\Requests\UpdateFeeTypeRequest;
use App\Http\Resources\FeeTypeResource;
use App\Services\FeeTypeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeTypeController extends Controller
{
    use ApiResponse;

    /**
     * The fee type service instance.
     *
     * @var FeeTypeService
     */
    protected FeeTypeService $feeTypeService;

    /**
     * Create a new controller instance.
     *
     * @param FeeTypeService $feeTypeService
     */
    public function __construct(FeeTypeService $feeTypeService)
    {
        $this->feeTypeService = $feeTypeService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active']);
        $perPage = $request->query('per_page', 10);

        $feeTypes = $this->feeTypeService->getFeeTypes($filters, (int) $perPage);

        return $this->successResponse(
            FeeTypeResource::collection($feeTypes)->response()->getData(true),
            'Fee types retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreFeeTypeRequest $request
     * @return JsonResponse
     */
    public function store(StoreFeeTypeRequest $request): JsonResponse
    {
        $feeType = $this->feeTypeService->createFeeType($request->validated());

        return $this->successResponse(
            new FeeTypeResource($feeType),
            'Fee type created successfully.',
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
        $feeType = $this->feeTypeService->findFeeTypeById($id);

        return $this->successResponse(
            new FeeTypeResource($feeType),
            'Fee type details retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateFeeTypeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateFeeTypeRequest $request, int $id): JsonResponse
    {
        $feeType = $this->feeTypeService->findFeeTypeById($id);
        $updatedFeeType = $this->feeTypeService->updateFeeType($feeType, $request->validated());

        return $this->successResponse(
            new FeeTypeResource($updatedFeeType),
            'Fee type updated successfully.'
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
        $feeType = $this->feeTypeService->findFeeTypeById($id);
        $this->feeTypeService->deleteFeeType($feeType);

        return $this->successResponse(
            null,
            'Fee type deleted successfully.'
        );
    }
}
