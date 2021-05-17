<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\SearchRepository;

class Search extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        SearchRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Get search item
     * @post ("/api/search")
     * @param ({
     *      @Parameter("term", type="string", required="true", description="Search term"),
     * })
     * @return array
     */
    public function __invoke()
    {
        return $this->ok($this->repo->search());
    }
}