<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Modules\Finance\Services\FinanceDashboardService;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly FinanceDashboardService $dashboardService
    ) {}

    public function kpiSummary()
    {
        $data = $this->dashboardService->getKpiSummary();
        return $this->apiSuccess($data, 'Data KPI summary berhasil diambil');
    }

    public function revenueChart()
    {
        $data = $this->dashboardService->getRevenueChartData();
        return $this->apiSuccess($data, 'Data grafik pendapatan 6 bulan terakhir');
    }
}
