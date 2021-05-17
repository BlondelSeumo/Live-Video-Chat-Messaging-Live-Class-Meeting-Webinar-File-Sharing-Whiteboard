<?php
namespace Mint\Service\Controllers;

use App\Http\Controllers\Controller;
use Mint\Service\Repositories\InitRepository;

class HomeController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance.
     */
    public function __construct(
        InitRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Used to get product information
     */
    public function info()
    {
        return $this->repo->info();
    }

    /**
     * Used to validate service for license request
     */
    public function licenseValidate()
    {
        return $this->repo->licenseValidate();
    }
}