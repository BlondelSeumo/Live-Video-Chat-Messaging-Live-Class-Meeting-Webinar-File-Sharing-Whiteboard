<?php

namespace App\Traits;

use Spatie\Valuestore\Valuestore as Storage;

trait LocalStorage
{
    /**
     * Get local storage
     */
    public function getStorage() : Storage
    {
        return Storage::make(database_path('storage.json'));
    }

    /**
     * Store values
     * @param array $items
     * @param string $key
     */
    private function saveStorage($key, $items = array()) : void
    {
        $storage = $this->getStorage();
        
        $storage->put($key, array_values($items));
    }
}
