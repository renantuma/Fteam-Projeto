<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    public $cached;
    public $cacheTtl;
    public $cacheKey;

    public function __construct($resource, $cached = false, $cacheTtl = null, $cacheKey = null)
    {
        parent::__construct($resource);
        $this->cached = $cached;
        $this->cacheTtl = $cacheTtl;
        $this->cacheKey = $cacheKey;
    }

    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'cached' => $this->cached,
                'cache_ttl' => $this->cacheTtl,
                'cache_key' => $this->cacheKey
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ]
        ];
    }
}