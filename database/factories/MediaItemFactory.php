<?php

namespace FeenstraDigital\LaravelCMS\Media\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use FeenstraDigital\LaravelCMS\Media\Models\MediaEmbed;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MediaItem>
 */
class MediaItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = rand(0,5) > 0
            ? (rand(0, 5) > 0 ? 'image' : 'video')
            : 'embed';

        return [
            'uuid' => Str::uuid()->toString(),
            'date' => rand(0, 3) > 0 ? fake()->date() : null,
            'author' => rand(0, 3) > 0 ? fake()->name() : null,
            'type' => $type,
            'media_embed_id' => $type === 'embed' ? MediaEmbed::inRandomOrder()->first()->id : null
        ];
    }
}
