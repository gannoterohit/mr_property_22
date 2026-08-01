<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\PlatformAvailability::class);
        $middleware->api(append: [
            \App\Http\Middleware\NormalizeApiResponse::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'admin.permission' => \App\Http\Middleware\AdminPermission::class,
            'admin.activity' => \App\Http\Middleware\LogAdminActivity::class,
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'payment/razorpay/verify',
            'webhook/razorpay',
            'api/v1/webhook/razorpay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Please check your input and try again.',
                    'data' => null,
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Please login to continue.',
                    'data' => null,
                    'errors' => (object) [],
                ], 401);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Too many requests. Please wait a moment and try again.',
                    'data' => null,
                    'errors' => (object) [],
                ], 429);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'The requested resource was not found.',
                    'data' => null,
                    'errors' => (object) [],
                ], 404);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.',
                    'data' => null,
                    'errors' => (object) [],
                ], 403);
            }
        });

        $exceptions->respond(function ($response, $e, $request) {
            if (!$request->is('api/*')) {
                return $response;
            }

            $existing = json_decode($response->getContent(), true);
            if (is_array($existing) && isset($existing['status'])) {
                return $response;
            }

            $status = $response->getStatusCode() ?: 500;
            $message = config('app.debug')
                ? ($e->getMessage() ?: 'Something went wrong')
                : ($status >= 500 ? 'Something went wrong. Please try again later.' : 'Unable to complete your request.');

            if ($e instanceof HttpException && $status < 500 && !config('app.debug')) {
                $message = match ($status) {
                    401 => 'Please login to continue.',
                    403 => 'You do not have permission to perform this action.',
                    404 => 'The requested resource was not found.',
                    419 => 'Your session has expired. Please refresh and try again.',
                    429 => 'Too many requests. Please wait a moment and try again.',
                    default => 'Unable to complete your request.',
                };
            }

            $payload = [
                'status' => 'error',
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => (object) [],
            ];
            if (is_array($existing) && !empty($existing['errors'])) {
                $payload['errors'] = $existing['errors'];
            }

            return response()->json($payload, $status);
        });
    })->create();
