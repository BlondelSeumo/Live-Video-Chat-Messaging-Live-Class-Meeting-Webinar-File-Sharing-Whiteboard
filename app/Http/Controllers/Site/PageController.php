<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\PageRequest;
use App\Repositories\Site\PageRepository;
use App\Http\Resources\Site\Page as PageResource;

class PageController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        PageRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Get page pre requisites
     * @get ("/api/site/pages/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Get all pages
     * @get ("/api/site/pages")
     * @return array
     */
    public function index()
    {
        return $this->repo->paginate();
    }

    /**
     * Store page
     * @post ("/api/site/pages")
     * @param ({
     *      @Parameter("title", type="string", required="true", description="Page title"),
     *      @Parameter("description", type="text", required="optional", description="Page description"),
     *      @Parameter("code", type="string", required="true", description="Page code"),
     * })
     * @return array
     */
    public function store(PageRequest $request)
    {
        $page = $this->repo->create();

        $page = new PageResource($page);

        return $this->success(['message' => __('global.added', ['attribute' => __('site.page.page')]), 'page' => $page]);
    }

    /**
     * Get page detail
     * @get ("/api/site/pages/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Page unique id"),
     * })
     * @return PageResource
     */
    public function show($uuid)
    {
        $page = $this->repo->findByUuidOrFail($uuid);

        return new PageResource($page);
    }

    /**
     * Fetch page content
     * @get ("/pages/{page?}")
     * @param ({
     *      @Parameter("page", type="string", required="true", description="Page slug"),
     * })
     * @return PageResource
     */
    public function fetch($page)
    {
        $page = $this->repo->findBySlugOrFail($page);

        if(!$page->status) {
            return false;
        }

        $body = clean($page->body);
        $slug = $page->slug;
        $title = $page->title;
        $meta = $page->meta;
        $parent = $page->parent;

        if (view()->exists('templates.' . $page->template->slug)) {
            return view('templates.' . $page->template->slug, compact('body', 'slug', 'title', 'meta', 'parent'));
        }
        
        return view('blank', compact('body', 'slug', 'title', 'meta', 'parent'));
    }

    /**
     * Update page
     * @patch ("/api/site/pages/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Page unique id"),
     *      @Parameter("title", type="string", required="true", description="Page title"),
     *      @Parameter("description", type="text", required="optional", description="Page description"),
     *      @Parameter("code", type="string", required="true", description="Page code"),
     * })
     * @return array
     */
    public function update($uuid, PageRequest $request)
    {
        $page = $this->repo->findByUuidOrFail($uuid);

        $page = $this->repo->update($page);

        return $this->success(['message' => __('global.updated', ['attribute' => __('site.page.page')])]);
    }

    /**
     * Delete page
     * @delete ("/api/site/pages/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Page unique id"),
     * })
     * @return array
     */
    public function destroy($uuid)
    {
        $this->repo->delete($this->repo->findByUuidOrFail($uuid));

        return $this->success(['message' => __('global.deleted', ['attribute' => __('site.page.page')])]);
    }

    /**
     * Used to add media for a page
     * @post ("/api/site/pages/{uuid}/media")
     * @param ({
     *      @Parameter("ids", type="array", required="true", description="Id of Students"),
     *      @Parameter("action", type="string", required="true", description="Action to Perform"),
     * })
     * @return Response
     */
    public function addMedia($uuid)
    {
        $media = $this->repo->addMedia($this->repo->findByUuidOrFail($uuid));

        return $this->success(['message' => __('global.added', ['attribute' => __('site.page.props.media')]), 'upload' => $media]);
    }

    /**
     * Used to remove media for a page
     * @delete ("/api/site/pages/{uuid}/media")
     * @param ({
     *      @Parameter("ids", type="array", required="true", description="Id of Students"),
     *      @Parameter("action", type="string", required="true", description="Action to Perform"),
     * })
     * @return Response
     */
    public function removeMedia($uuid)
    {
        $this->repo->removeMedia($this->repo->findByUuidOrFail($uuid));

        return $this->success(['message' => __('global.deleted', ['attribute' => __('site.page.props.media')])]);
    }
}
