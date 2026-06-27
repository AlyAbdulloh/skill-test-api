<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseResidentRequest;
use App\Http\Requests\UpdateHouseResidentRequest;
use App\Http\Resources\HouseResidentResource;
use App\Services\HouseResidentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HouseResidentController extends Controller
{
    use ApiResponse;

    /**
     * The house-resident association service instance.
     *
     * @var HouseResidentService
     */
    protected HouseResidentService $houseResidentService;

    /**
     * Create a new controller instance.
     *
     * @param HouseResidentService $houseResidentService
     */
    public function __construct(HouseResidentService $houseResidentService)
    {
        $this->houseResidentService = $houseResidentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active', 'house_id', 'resident_id']);
        $perPage = $request->query('per_page', 10);

        $associations = $this->houseResidentService->getHouseResidents($filters, (int) $perPage);

        return $this->successResponse(
            HouseResidentResource::collection($associations)->response()->getData(true),
            'House resident associations retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreHouseResidentRequest $request
     * @return JsonResponse
     */
    public function store(StoreHouseResidentRequest $request): JsonResponse
    {
        $association = $this->houseResidentService->createHouseResident($request->validated());

        return $this->successResponse(
            new HouseResidentResource($association),
            'House resident association created successfully.',
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
        $association = $this->houseResidentService->findHouseResidentById($id);

        return $this->successResponse(
            new HouseResidentResource($association),
            'House resident association details retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateHouseResidentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateHouseResidentRequest $request, int $id): JsonResponse
    {
        $association = $this->houseResidentService->findHouseResidentById($id);
        $updated = $this->houseResidentService->updateHouseResident($association, $request->validated());

        return $this->successResponse(
            new HouseResidentResource($updated),
            'House resident association updated successfully.'
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
        $association = $this->houseResidentService->findHouseResidentById($id);
        $this->houseResidentService->deleteHouseResident($association);

        return $this->successResponse(
            null,
            'House resident association deleted successfully.'
        );
    }
}
