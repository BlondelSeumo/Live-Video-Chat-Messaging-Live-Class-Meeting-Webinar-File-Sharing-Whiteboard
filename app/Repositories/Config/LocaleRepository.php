<?php
namespace App\Repositories\Config;

use Illuminate\Support\Arr;
use App\Traits\LocalStorage;
use App\Traits\CollectionPaginator;
use Illuminate\Validation\ValidationException;

class LocaleRepository
{
    use LocalStorage, CollectionPaginator;

    protected $storage_key = 'locales';

    /**
     * Get all locale modules
     */
    public function getModules() : array
    {
        $modules = array();
        foreach (\File::allFiles(base_path('/resources/lang/en')) as $file) {
            $modules[] = basename($file, '.php');
        }

        return $modules;
    }

    /**
     * Get locale pre requisite
     */
    public function getPreRequisite() : array
    {
        $modules = $this->getModules();

        $locales = $this->getLocales();

        $words = [];
        foreach ($locales as $locale) {
            $locale = Arr::get($locale, 'locale');
            $words[$locale] = $this->getWords($locale, request('module'));
        }

        return compact('locales', 'modules', 'words');
    }

    /**
     * Get all the words for given locale & module
     * @param string $locale
     * @param string $module
     */
    public function getWords($locale = 'en', $module = null) : array
    {
        if (! $module) {
            return [];
        }

        $words = \File::getRequire($this->validateModule($locale, $module));
        ksort($words);
        return $words;
    }

    /**
     * Sort translations of all modules
     */
    public function sortWords() : void
    {
        // $locale = 'en';
        // foreach ($this->getModules() as $module) {
        //     $words = \File::getRequire($this->validateModule($locale, $module));
        //     ksort($words);
        //     $this->writeToFile('en', $module, $words);
        // }
    }

    /**
     * Validate locale directory
     * @param string $locale
     */
    public function validateModuleDirectory($locale) : void
    {
        $dir_path = base_path('/resources/lang/'.$locale);

        if (! \File::isDirectory($dir_path)) {
            \File::makeDirectory($dir_path, 0751, true, true);
        }
    }

    /**
     * Validate locale modules, if doesn't exist create new file for module.
     * @param string $locale
     * @param string $module
     */
    public function validateModule($locale, $module) : string
    {
        $this->validateModuleDirectory($locale);
        
        $file = base_path('/resources/lang/'.$locale.'/'.$module.'.php');

        if (! \File::exists($file)) {
            \File::put($file, var_export([], true));
            \File::prepend($file, '<?php return ');
            \File::append($file, ';');
        }

        return $file;
    }

    /**
     * Write to translation file
     * @param string $locale
     * @param string $module
     * @param array $words
     */
    public function writeToFile($locale = 'en', $module = null, $words = array()) : void
    {
        if (! $module) {
            return;
        }

        ksort($words);
        
        $file = base_path('/resources/lang/'.$locale.'/'.$module.'.php');
        \File::put($file, var_export($words, true));
        \File::prepend($file, '<?php return ');
        \File::append($file, ';');
    }

    /**
     * Get locales
     */
    public function getLocales() : array
    {
        $local_storage = $this->getStorage();

        return $local_storage->get('locales');
    }

    /**
     * Find locale by locale
     * @param string $locale
     */
    private function findByLocale($locale) : void
    {
        $locale_collection = collect($this->getLocales());

        $filtered = $locale_collection->filter(function ($item, $key) use ($locale) {
            return Arr::get($item, 'locale') === $locale;
        });

        if (! $filtered->count()) {
            throw ValidationException::withMessages(['message' => __('global.could_not_find', ['attribute' => __('config.locale.locale')])]);
        }
    }

    /**
     * Validate default locale
     * @param string $locale
     */
    private function validateDefault($locale) : void
    {
        if ($locale === 'en') {
            throw ValidationException::withMessages(['message' => __('global.cannot_modify_default', ['attribute' => __('config.locale.locale')])]);
        }
    }

    /**
     * Paginate all locales
     */
    public function paginate()
    {
        $sort_by      = request('sort_by', 'name');
        $order        = request('order', 'asc');
        $per_page     = request('per_page', config('config.system.per_page'));
        $current_page = request('current_page');

        return $this->collectionPaginate($this->getLocales(), $per_page, $current_page);
    }

    /**
     * Create a new locale
     */
    public function create() : void
    {
        $locale_collection = collect($this->getLocales());

        $locales = Arr::pluck($locale_collection, 'locale');
        $names   = Arr::pluck($locale_collection, 'name');

        if (in_array(request('locale'), $locales)) {
            throw ValidationException::withMessages(['message' => __('validation.unique', ['attribute' => __('config.locale.locale')])]);
        }

        if (in_array(request('name'), $locales)) {
            throw ValidationException::withMessages(['message' => __('validation.unique', ['attribute' => __('config.locale.name')])]);
        }

        $locale_collection->prepend([
            'uuid' => request('locale'),
            'locale' => request('locale'),
            'name' => request('name')
        ]);

        if (request('locale') != 'en') {
            \File::copyDirectory(base_path('/resources/lang/en'), base_path('/resources/lang/' . request('locale')));
        }

        $this->sortWords();

        $this->saveStorage($this->storage_key, $locale_collection->all());
    }

    /**
     * Update given locale
     * @param string $locale
     */
    public function update($locale)
    {
        $this->findByLocale($locale);

        $this->validateDefault($locale);

        if ($locale != request('locale')) {
            throw ValidationException::withMessages(['locale' => __('config.locale.cannot_change_locale')]);
        }

        $locale_collection = collect($this->getLocales());

        $locale_collection->transform(function ($item, $key) use ($locale) {
            if (Arr::get($item, 'locale') === $locale) {
                return array(
                    'name' => request('name'),
                    'locale' => $locale
                );
            } else {
                return $item;
            }
        });

        $this->saveStorage($this->storage_key, $locale_collection->all());
    }

    /**
     * Delete given locale
     */
    public function delete($locale) : void
    {
        $this->findByLocale($locale);

        $this->validateDefault($locale);
        
        $locale_collection = collect($this->getLocales());
        
        $filtered = $locale_collection->filter(function ($item, $key) use ($locale) {
            return Arr::get($item, 'locale') != $locale;
        });

        $this->saveStorage($this->storage_key, $filtered->all());

        \File::deleteDirectory(base_path('/resources/lang/'.$locale));
    }

    /**
     * Translate locale module
     * @param string $locale
     */
    public function translate($locale) : void
    {
        $this->findByLocale($locale);

        $this->writeToFile($locale, request('module'), request('words'));

        $this->sortWords();
    }

    /**
     * Sync locale
     *
     * @param string $locale
     */
    public function sync($locale)
    {
        $this->findByLocale($locale);

        $show_diff = request('diff', false);

        $lang_diff = array();
        foreach ($this->getModules() as $module) {
            $words = \File::getRequire($this->validateModule('en', $module));
            $locale_words = \File::getRequire($this->validateModule($locale, $module));
            $diff = $this->getLocaleDifference($words, $locale_words);

            if ($diff) {
                if (! $show_diff) {
                    $new = array_replace_recursive($diff, $locale_words);
                    $this->writeToFile($locale, $module, $new);
                } else {
                    $lang_diff[$module.'.php'] = $diff;
                }
            }
        }

        return $show_diff ? $lang_diff : 'Locale synched.';
    }

    private function getLocaleDifference($array1, $array2) {
        $difference=array();
        foreach($array1 as $key => $value) {
            if( is_array($value) ) {
                if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->getLocaleDifference($value, $array2[$key]);
                    if( !empty($new_diff) )
                        $difference[$key] = $new_diff;
                }
            } else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
                $difference[$key] = $value;
            }
        }
        return $difference;
    }
}
