<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\MediaRepository;

class MediaController extends Controller
{
    protected $repo;

    public function __construct(
        MediaRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Get pre requisites
     * @get ("/api/medias/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Upload a file
     * @post ("/api/medias")
     * @param ({
     *      @Parameter("files", type="file", required="true", description="Array of file"),
     * })
     * @return array
     */
    public function upload()
    {
        $tokens = $this->repo->upload();

        return $this->success(['message' => __('global.stored', ['attribute' => __('upload.file')]), 'tokens' => $tokens]);
    }

    /**
     * Upload image in html editor
     * @post ("/api/medias/image")
     * @param ({
     *      @Parameter("image", type="file", required="true", description="Image file"),
     * })
     * @return array
     */
    public function image()
    {
        request()->validate([
           'image' => [
               'required',
               'image',
               'mimes:jpeg,bmp,png,svg,gif'
           ],
        ], [], [
           'image' => __('upload.image')
        ]);

        $image = \Storage::disk('public')->putFile('editor-images', request()->file('image'));

        return $this->success(['image' => '/storage/'.$image]);
    }
}
