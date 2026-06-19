<?php

namespace App\Factory;

final class RedisFactory
{
    public function create(string $host = 'redis', int $port = 6379): \Redis
    {
        $redis = new \Redis();
        $redis->connect($host, $port);

        return $redis;
    }
}
