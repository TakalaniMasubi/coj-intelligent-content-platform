<?php

namespace App\Http\Controllers\Api\V1;

class SystemController
{
    /**
     * Foundation readiness probe for the CoJ-MIP API.
     *
     * Confirms the versioned API layer responds and reports the
     * application environment. No business logic is exposed.
     */
    public function ping(): array
    {
        return [
            'service' => 'coj-mip',
            'version' => 'v1',
            'status' => 'ok',
            'environment' => app()->environment(),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}