<?php

// declare(strict_types=1);

// namespace App\Services;

// use Redis;
// use Exception;

// /**
//  * Thin wrapper around the PHP Redis extension.
//  * Provides get/set/delete with JSON serialization.
//  */
// class RedisService
// {
//     private ?Redis $redis = null;

//     public function __construct()
//     {
//         $this->connect();
//     }

//     /**
//      * Establishes Redis connection from environment variables.
//      */
//     private function connect(): void
//     {
//         try {
//             $this->redis = new Redis();
//             $this->redis->connect(
//                 $_ENV['REDIS_HOST'] ?? 'redis',
//                 (int) ($_ENV['REDIS_PORT'] ?? 6379)
//             );
//         } catch (Exception $e) {
//             // Gracefully degrade — cache miss handled by callers
//             $this->redis = null;
//         }
//     }

//     /**
//      * Retrieve a value by key. Returns null on miss or error.
//      *
//      * @param string $key
//      * @return mixed|null
//      */
//     public function get(string $key): mixed
//     {
//         if ($this->redis === null) return null;
//         try {
//             $value = $this->redis->get($key);
//             return $value !== false ? json_decode($value, true) : null;
//         } catch (Exception) {
//             return null;
//         }
//     }

//     /**
//      * Store a value under the given key with an optional TTL (seconds).
//      *
//      * @param string $key
//      * @param mixed  $value
//      * @param int    $ttl   Seconds until expiry (0 = no expiry)
//      * @return bool
//      */
//     public function set(string $key, mixed $value, int $ttl = 60): bool
//     {
//         if ($this->redis === null) return false;
//         try {
//             $serialized = json_encode($value);
//             if ($ttl > 0) {
//                 return $this->redis->setex($key, $ttl, $serialized);
//             }
//             return $this->redis->set($key, $serialized);
//         } catch (Exception) {
//             return false;
//         }
//     }

//     /**
//      * Delete a key from Redis.
//      *
//      * @param string $key
//      * @return bool
//      */
//     public function delete(string $key): bool
//     {
//         if ($this->redis === null) return false;
//         try {
//             return (bool) $this->redis->del($key);
//         } catch (Exception) {
//             return false;
//         }
//     }
// }

declare(strict_types=1);

namespace App\Services;

/**
 * Thin wrapper around the PHP Redis extension.
 * Gracefully disabled if Redis extension is not loaded.
 */
class RedisService
{
    private mixed $redis = null;

    public function __construct()
    {
        if (!extension_loaded('redis')) {
            return;
        }
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $this->redis = new \Redis();
            $this->redis->connect(
                $_ENV['REDIS_HOST'] ?? 'redis',
                (int) ($_ENV['REDIS_PORT'] ?? 6379)
            );
        } catch (\Exception $e) {
            $this->redis = null;
        }
    }

    public function get(string $key): mixed
    {
        if ($this->redis === null) return null;
        try {
            $value = $this->redis->get($key);
            return $value !== false ? json_decode($value, true) : null;
        } catch (\Exception) {
            return null;
        }
    }

    public function set(string $key, mixed $value, int $ttl = 60): bool
    {
        if ($this->redis === null) return false;
        try {
            $serialized = json_encode($value);
            if ($ttl > 0) {
                return $this->redis->setex($key, $ttl, $serialized);
            }
            return $this->redis->set($key, $serialized);
        } catch (\Exception) {
            return false;
        }
    }

    public function delete(string $key): bool
    {
        if ($this->redis === null) return false;
        try {
            return (bool) $this->redis->del($key);
        } catch (\Exception) {
            return false;
        }
    }
}