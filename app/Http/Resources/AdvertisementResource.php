<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AdvertisementResource extends JsonResource
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
            'subject' => $this->getTranslation('subject', $locale),
            'content' => $this->getTranslation('content', $locale),
            'cover' => $this->cover ? Storage::url($this->cover) : null,
            'destination_url' => $this->destination_url,
        ];
    }
}
