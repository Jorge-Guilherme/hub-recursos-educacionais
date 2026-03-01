<?php

namespace Infrastructure\Http\Controllers;

use Application\UseCases\Health\GetHealthStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class HealthController extends Controller
{
    private GetHealthStatus $getHealthStatus;

    public function __construct(GetHealthStatus $getHealthStatus)
    {
        $this->getHealthStatus = $getHealthStatus;
    }

    public function index(): JsonResponse
    {
        $data = $this->getHealthStatus->execute();
        return response()->json($data);
    }

    public function info(): JsonResponse
    {
        return response()->json([
            'app_name' => config('app.name'),
            'environment' => config('app.env'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
        ]);
    }
}
