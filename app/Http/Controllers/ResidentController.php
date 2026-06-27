<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Http\Resources\ResidentResource;
use App\Services\ResidentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    use ApiResponse;

    /**
     * The resident service instance.
     *
     * @var ResidentService
     */
    protected ResidentService $residentService;

    /**
     * Create a new controller instance.
     *
     * @param ResidentService $residentService
     */
    public function __construct(ResidentService $residentService)
    {
        $this->residentService = $residentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'resident_status', 'is_married']);
        $perPage = $request->query('per_page', 10);

        $residents = $this->residentService->getResidents($filters, (int) $perPage);

        return $this->successResponse(
            ResidentResource::collection($residents)->response()->getData(true),
            'Residents retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreResidentRequest $request
     * @return JsonResponse
     */
    public function store(StoreResidentRequest $request): JsonResponse
    {
        $resident = $this->residentService->createResident($request->validated());

        return $this->successResponse(
            new ResidentResource($resident),
            'Resident created successfully.',
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
        $resident = $this->residentService->findResidentById($id);

        return $this->successResponse(
            new ResidentResource($resident),
            'Resident details retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateResidentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateResidentRequest $request, int $id): JsonResponse
    {
        $resident = $this->residentService->findResidentById($id);
        $updatedResident = $this->residentService->updateResident($resident, $request->validated());

        return $this->successResponse(
            new ResidentResource($updatedResident),
            'Resident updated successfully.'
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
        $resident = $this->residentService->findResidentById($id);
        $this->residentService->deleteResident($resident);

        return $this->successResponse(
            null,
            'Resident deleted successfully.'
        );
    }
}
