<?php

namespace App\Hashing;

use Illuminate\Contracts\Hashing\Hasher;

class Md5Hasher implements Hasher
{
    /**
     * Get information about the given hashed value.
     */
    public function info($hashedValue): array
    {
        return ['algo' => 'md5'];
    }

    /**
     * Hash the given value.
     */
    public function make($value, array $options = []): string
    {
        return md5($value);
    }

    /**
     * Check the given plain value against a hash.
     */
    public function check($value, $hashedValue, array $options = []): bool
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return hash_equals($hashedValue, $this->make($value));
    }

    /**
     * Check if the given hash has been hashed using the given options.
     * MD5 hashes never need rehashing.
     */
    public function needsRehash($hashedValue, array $options = []): bool
    {
        return false;
    }
}
