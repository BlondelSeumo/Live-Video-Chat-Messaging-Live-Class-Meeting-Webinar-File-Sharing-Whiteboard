<?php
namespace App\Repositories\Site;

use App\Http\Resources\Media as MediaResource;
use App\Http\Resources\Site\PageCollection;
use App\Models\Site\Page;
use App\Repositories\OptionRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PageRepository
{
    protected $page;
    protected $option_repo;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Page $page,
        OptionRepository $option_repo
    ) {
        $this->page = $page;
        $this->option_repo = $option_repo;
    }

    /**
     * Find page with given id or throw an error
     * @param integer $id
     */
    public function findOrFail($id, $field = 'message') : Page
    {
        $page = $this->page->find($id);

        if (! $page) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('site.page.page')])]);
        }

        return $page;
    }

    /**
     * Find page with given uuid or throw an error
     * @param uuid $uuid
     */
    public function findByUuidOrFail($uuid, $field = 'message') : Page
    {
        $page = $this->page->filterByUuid($uuid)->first();

        if (! $page) {
            throw ValidationException::withMessages([$field => __('global.could_not_find', ['attribute' => __('site.page.page')])]);
        }

        return $page;
    }

    /**
     * Find page with given slug or throw 404
     * @param string $slug
     */
    public function findBySlugOrFail($slug) : Page
    {
        $page = $this->page->with('template')->whereSlug($slug)->first();

        if (! $page) {
            abort(404);
        }

        return $page;
    }

    /**
     * Get all filtered data
     */
    public function getData($params = array())
    {
        $sort_by = request('sort_by', 'created_at');
        $order   = request('order', 'desc');

        $slug = Arr::get($params, 'slug', request('slug'));
        $title = Arr::get($params, 'title', request('title'));
        $status = Arr::get($params, 'status', request('status', 0));

        return $this->page->filterByTitle($title)->filterBySlug($slug)->filterByStatus($status)->orderBy($sort_by, $order);
    }

    /**
     * List all pages using given params.
     */

    public function list($params = array()) : PageCollection
    {
        return new PageCollection($this->getData($params)->get());
    }

    /**
     * List all pages using given params.
     */

    public function listOrderedByName($params = array()) : PageCollection
    {
        $params['sort_by'] = 'name';
        $params['order'] = 'asc';
        
        return new PageCollection($this->getData($params)->get());
    }

    /**
     * Paginate all pages
     */
    public function paginate() : PageCollection
    {
        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return new PageCollection($this->getData()->paginate((int) $per_page, ['*'], 'current_page'));
    }

    /**
     * Create a new page
     */
    public function create() : Page
    {
        return $this->page->forceCreate($this->formatParams());
    }

    /**
     * Prepare given params for inserting into database
     * @param uuid $uuid
     */
    private function formatParams($uuid = null) : array
    {
        $template = request('template');
        $template = Arr::get($template, 'uuid');
        $template = $this->option_repo->findByUuidOrFail($template);

        $formatted = [
            'title'           => request('title'),
            'body'            => request('body'),
            'slug'            => request('slug'),
            'status'          => request('status') ? : false,
            'template_id'     => $template->id,
            'parent_id'       => null,
            'meta'            => request('meta'),
        ];

        $parent = request('parent');
        $parent = Arr::get($parent, 'uuid');

        if ($parent) {
            $parent = $this->findByUuidOrFail($parent);
            $formatted['parent_id'] = $parent->id != $uuid ? $parent->id : null;
        }

        if (! $uuid) {
            $formatted['uuid'] = Str::uuid();
        }

        return $formatted;
    }

    /**
     * Update given page
     * @param Page $page
     */
    public function update(Page $page) : Page
    {
        $page->forceFill($this->formatParams($page->uuid))->save();

        return $page;
    }

    /**
     * Delete page
     * @param Page $page
     */
    public function delete(Page $page) : void
    {
        $page->delete();
    }

    /**
     * Get pre requisite.
     *
     * @return Array
     */
    public function getPreRequisite()
    {
        $templates = $this->option_repo->listOrderedByName([ 'type' => 'page_template' ]);
        $pages = $this->list();

        return compact('templates', 'pages');
    }

    /**
     * Add media to given page
     * @param Page $page
     */
    public function addMedia(Page $page) : MediaResource
    {
        $media = $page
            ->clearMediaCollection()
            ->addMediaFromRequest('file')
            ->sanitizingFileName(function ($fileName) {
                return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
            })->toMediaCollection();
        return new MediaResource($media);
    }

    /**
     * Remove media from given page
     * @param Page $page
     */
    public function removeMedia(Page $page) : void
    {
        $page->clearMediaCollection();
    }
}
