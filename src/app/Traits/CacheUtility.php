<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheUtility
{

    /**
     * Cache the result of a callback with a given cache key and TTL.
     *
     * @param string $cacheKey
     * @param int $ttl
     * @param \Closure $callback
     * @return mixed
     */
    public function cacheWithCallback(string $cacheTag, string $cacheKey, int $ttl, \Closure $callback)
    {
        return Cache::tags([$cacheTag])->remember($cacheKey, $ttl, $callback);
    }

    /**
     * Flush cache of certain tag
     *
     * @param string $tag
     * @return void
     */
    public function flushCache(string $tag)
    {
        Cache::tags([$tag])->flush();
    }
}
