<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->query('locale', app()->getLocale());

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $locale),
            'slug' => $this->slug,
            'cover' => $this->cover ? (str_starts_with($this->cover, 'http') ? $this->cover : Storage::url($this->cover)) : null,
            'content' => $this->getTranslation('content', $locale),
            'excerpt' => Str::limit(strip_tags($this->getTranslation('content', $locale)), 200),
            'source_url' => $this->source_url,
            'published_at' => $this->published_at,
            'views' => $this->views,
            'news_site' => new NewsSiteResource($this->whenLoaded('newsSite')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
