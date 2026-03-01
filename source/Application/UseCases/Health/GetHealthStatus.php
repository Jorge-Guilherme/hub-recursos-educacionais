<?php

namespace Application\UseCases\Health;

use Application\UseCases\UseCaseInterface;

class GetHealthStatus implements UseCaseInterface
{
    public function execute(mixed $input = null): array
    {
        return [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
        ];
    }
}
