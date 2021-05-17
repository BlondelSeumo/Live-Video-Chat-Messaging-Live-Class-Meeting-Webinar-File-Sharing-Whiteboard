<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        DashboardRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Dashboard meeting summary
     * @post ("/api/dashboard")
     * @return array
     */
    public function index()
    {
        return $this->ok($this->repo->getMeetings());
    }

    /**
     * Dashboard stats
     * @post ("/api/dashboar/stats")
     * @return array
     */
    public function getStats()
    {
        return $this->ok($this->repo->getStats());
    }

    /**
     * Dashboard chart
     * @post ("/api/dashboar/chart")
     * @return array
     */
    public function getChart()
    {
        return $this->ok($this->repo->getCharts());
    }
}
