<?php

use App\Models\Option;
use App\Models\Site\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class SitePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pages = array(
            array(
                'title' => "Privacy Policy",
                'slug' => "privacy", 
                'template' => "blank",
                'body' => "Here goes your privacy policy"
            ),
            array(
                'title' => "Terms of Service",
                'slug' => "terms", 
                'template' => "blank",
                'body' => "Here goes your terms of service"
            )
        );

        $page_templates = Option::whereType('page_template')->get();

        foreach ($pages as $data) {
            $page = Page::firstOrCreate([
                'title' => Arr::get($data, 'title')
            ]);

            $page_template = $page_templates->firstWhere('slug', Arr::get($data, 'template'));

            $page->uuid        = Str::uuid();
            $page->slug        = Arr::get($data, 'slug');
            $page->body        = Arr::get($data, 'body');
            $page->status      = 1;
            $page->template_id = optional($page_template)->id;
            $page->meta = array(
                'seo_title' => null,
                'keywords' => null,
                'description' => null
            );
            $page->save();
        }
    }
}
