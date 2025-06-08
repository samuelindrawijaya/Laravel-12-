<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
            ],
        ];
    }
}
// This collection resource formats a collection of User resources for API responses.
// It includes the data as well as pagination metadata such as total items, items per page, current page, and last page.
// This is useful for APIs that support pagination, allowing clients to understand the structure of the data and navigate through pages of results.
