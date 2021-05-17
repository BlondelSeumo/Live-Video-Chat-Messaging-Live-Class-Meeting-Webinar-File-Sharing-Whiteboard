<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\ContactImportRepository;

class ContactImportController extends Controller
{
	protected $repo;

    /**
     * Instantiate a new instance
     * @return void
     */
	public function __construct(
		ContactImportRepository $repo
	) {
		$this->repo = $repo;
	}

    /**
     * Upload file for import
     * @post ("/api/contact/import/start")
     * @param ({
     *      @Parameter("file", type="file", required="required", description="File to be uploaded")
     * })
     * @return Response
     */
    public function startImport()
    {	
    	return $this->success($this->repo->startImport());
    }

    /**
     * Finish import
     * @post ("/api/contact/import/finish")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="required", description="Unique Id of File"),
     *      @Parameter("columns", type="array", required="required", description="Array of Column")
     * })
     * @return Response
     */
    public function finishImport()
    {	
    	$this->repo->finishImport();

        return $this->success(['message' => trans('global.imported', ['attribute' => trans('contact.contact')])]);
    }
}