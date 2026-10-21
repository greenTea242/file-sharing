<?php

declare(strict_types=1);

namespace App\Services;

class UuidService {
    public function createUuid(): string
    {
        return bin2hex(random_bytes(16));
    }
}