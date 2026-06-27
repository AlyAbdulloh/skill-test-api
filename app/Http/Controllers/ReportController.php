<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetSummaryReportRequest;
use App\Http\Resources\SummaryReportResource;
use App\Services\ReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    use ApiResponse;

    /**
     * The report service instance.
     *
     * @var ReportService
     */
    protected ReportService $reportService;

    /**
     * Create a new controller instance.
     *
     * @param ReportService $reportService
     */
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the summary report data.
     *
     * @param GetSummaryReportRequest $request
     * @return JsonResponse
     */
    public function index(GetSummaryReportRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $reportData = $this->reportService->getSummaryReport($filters);

        return $this->successResponse(
            new SummaryReportResource($reportData),
            'Summary report retrieved successfully.'
        );
    }

    /**
     * Display the detailed monthly report data.
     *
     * @param GetSummaryReportRequest $request
     * @return JsonResponse
     */
    public function detail(GetSummaryReportRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $reportData = $this->reportService->getMonthlyDetailReport($filters);

        return $this->successResponse(
            new SummaryReportResource($reportData),
            'Detailed report retrieved successfully.'
        );
    }
}
