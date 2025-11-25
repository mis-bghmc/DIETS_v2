<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Console\Kernel;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // $exceptions->reportable(function (\Throwable $e) {
        //     \Log::error('Caught Exception: ' . get_class($e), [
        //         'message' => $e->getMessage(),
        //         'file' => $e->getFile(),
        //         'line' => $e->getLine(),
        //         'trace' => $e->getTraceAsString(),
        //     ]);
        // });
    
        $exceptions->renderable(function (\Throwable $e) {

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 503) {
                return response()->view('errors.503', [], 503);
            }

            $status = $e->getCode() ?? 500;

            return response()->json([
                'error_title' => 'SERVER Error',
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_code' => $status
            ], 500);
        });

    })->create();


