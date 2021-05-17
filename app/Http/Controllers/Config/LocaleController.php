<?php

namespace App\Http\Controllers\Config;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Config\LocaleRequest;
use App\Repositories\Config\LocaleRepository;

class LocaleController extends Controller
{
    protected $request;
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        Request $request,
        LocaleRepository $repo
    ) {
        $this->request = $request;
        $this->repo = $repo;
    }

    /**
     * Get pre requisites
     * @get ("/api/config/locales/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Get all locales
     * @get ("/api/config/locales")
     * @return array
     */
    public function index()
    {
        return $this->repo->paginate($this->request->all());
    }

    /**
     * Store a locale
     * @post ("/api/config/locales")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Locale name"),
     *      @Parameter("locale", type="string", required="true", description="Locale code"),
     * })
     * @return array
     */
    public function store(LocaleRequest $request)
    {
        $locale = $this->repo->create();

        return $this->success(['message' => __('global.added', ['attribute' => __('config.locale.locale')])]);
    }

    /**
     * Get a locale detail
     * @get ("/api/config/locales/{locale}")
     * @param ({
     *      @Parameter("locale", type="string", required="true", description="Locale code"),
     * })
     * @return array
     */
    public function show($locale)
    {
        return $this->repo->findLocale($locale);
    }

    /**
     * Update a locale
     * @patch ("/api/config/locales/{locale}")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Locale name"),
     *      @Parameter("locale", type="string", required="true", description="Locale code"),
     * })
     * @return array
     */
    public function update(LocaleRequest $request, $locale)
    {
        $this->repo->update($locale);

        return $this->success(['message' => __('global.updated', ['attribute' => __('config.locale.locale')])]);
    }

    /**
     * Delete locale
     * @delete ("/api/config/locales/{locale}")
     * @param ({
     *      @Parameter("locale", type="string", required="true", description="Locale code"),
     * })
     * @return array
     */
    public function destroy($locale)
    {
        $this->repo->delete($locale);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('config.locale.locale')])]);
    }

    /**
     * Translate locale words
     * @post ("/api/config/locales/{locale}/translate")
     * @param ({
     *      @Parameter("module", type="string", required="true", description="Locale module"),
     *      @Parameter("words", type="array", required="true", description="Locale words"),
     * })
     * @return array
     */
    public function translate($locale)
    {
        $this->repo->translate($locale);

        return $this->success(['message' => __('global.updated', ['attribute' => __('config.locale.translation')])]);
    }

    /**
     * Sync locale words with english locale
     * @post ("/locale/{locale}/sync")
     * @param ({
     *      @Parameter("locale", type="string", required="true", description="Locale"),
     * })
     * @return array
     */
    public function sync($locale)
    {
        return $this->repo->sync($locale);
    }
}
