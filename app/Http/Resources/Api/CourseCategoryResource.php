<?php

namespace App\Http\Resources\Api;
// app/Http/Resources/Api/CourseCategoryResource.php


use Illuminate\Http\Resources\Json\JsonResource;

class CourseCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
        ];
    }
}