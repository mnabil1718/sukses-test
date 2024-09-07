<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookWithAuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'publish_date' => $this->publish_date->format('Y-m-d'),
            'author' => $this->author == null ? null : [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'bio' => $this->author->bio,
            ],
        ];
    }
}
