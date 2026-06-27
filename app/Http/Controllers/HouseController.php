<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseRequest;
use App\Http\Requests\UpdateHouseRequest;
use App\Http\Resources\HouseResource;
use App\Http\Resources\HouseResidentResource;
use App\Services\HouseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    use ApiResponse;

    /**
     * The house service instance.
     *
     * @var HouseService
     */
    protected HouseService $houseService;

    /**
     * Create a new controller instance.
     *
     * @param HouseService $houseService
     */
    public function __construct(HouseService $houseService)
    {
        $this->houseService = $houseService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'occupancy_status']);
        $perPage = $request->query('per_page', 10);

        $houses = $this->houseService->getHouses($filters, (int) $perPage);

        return $this->successResponse(
            HouseResource::collection($houses)->response()->getData(true),
            'Houses retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreHouseRequest $request
     * @return JsonResponse
     */
    public function store(StoreHouseRequest $request): JsonResponse
    {
        $house = $this->houseService->createHouse($request->validated());

        return $this->successResponse(
            new HouseResource($house),
            'House created successfully.',
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
        $house = $this->houseService->findHouseById($id);

        return $this->successResponse(
            new HouseResource($house),
            'House details retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateHouseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateHouseRequest $request, int $id): JsonResponse
    {
        $house = $this->houseService->findHouseById($id);
        $updatedHouse = $this->houseService->updateHouse($house, $request->validated());

        return $this->successResponse(
            new HouseResource($updatedHouse),
            'House updated successfully.'
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
        $house = $this->houseService->findHouseById($id);
        $this->houseService->deleteHouse($house);

        return $this->successResponse(
            null,
            'House deleted successfully.'
        );
    }

    /**
     * Display the resident history for a specific house.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function residents(int $id): JsonResponse
    {
        $house = $this->houseService->findHouseById($id);
        $perPage = request()->query('per_page', 10);

        $history = $this->houseService->getResidentHistory($house, (int) $perPage);

        return $this->successResponse(
            HouseResidentResource::collection($history)->response()->getData(true),
            'House resident history retrieved successfully.'
        );
    }
}
