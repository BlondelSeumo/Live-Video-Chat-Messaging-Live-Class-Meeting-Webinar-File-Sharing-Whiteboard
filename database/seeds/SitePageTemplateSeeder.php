<?php

use App\Models\Option;
use Illuminate\Database\Seeder;

class SitePageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $page_templates = array(
            'Default',
            'Blank'
        );

        foreach ($page_templates as $data) {
            $option = Option::firstOrCreate([
                'name' => $data
            ]);

            $option->slug        = str_slug($data);
            $option->type        = 'page_template';
            $option->description = '';
            $option->save();
        }
    }
}
