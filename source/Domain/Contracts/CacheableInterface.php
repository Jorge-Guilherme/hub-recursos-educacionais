<?php

namespace Domain\Contracts;

interface CacheableInterface
{
    public function cache(string $key, mixed $value, int $ttl = 3600): void;
    public function getCached(string $key): mixed;
    public function clearCache(string $key): void;
}
