<?php

namespace FeenstraDigital\LaravelCMS\Media\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use FeenstraDigital\LaravelCMS\Media\Models\MediaEmbed;

class MediaEmbedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i=0; $i < rand(20, 45); $i++) { 
            MediaEmbed::create([
                'embed_url' => 'https://www.youtube.com/watch?v=YE7VzlLtp-4',
                'oembed_data' => json_decode('{"title":"Big Buck Bunny","author_name":"Blender","author_url":"https://www.youtube.com/@BlenderOfficial","type":"video","height":113,"width":200,"version":"1.0","provider_name":"YouTube","provider_url":"https://www.youtube.com/","thumbnail_height":360,"thumbnail_width":480,"thumbnail_url":"https://i.ytimg.com/vi/YE7VzlLtp-4/hqdefault.jpg","html":"\u003ciframe width=\u0022200\u0022 height=\u0022113\u0022 src=\u0022https://www.youtube.com/embed/YE7VzlLtp-4?feature=oembed\u0022 frameborder=\u00220\u0022 allow=\u0022accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\u0022 referrerpolicy=\u0022strict-origin-when-cross-origin\u0022 allowfullscreen title=\u0022Big Buck Bunny\u0022\u003e\u003c/iframe\u003e"}')
            ]);
        }
    }
}
