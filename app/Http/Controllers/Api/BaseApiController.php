<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BaseApiController extends Controller
{
    /**
     * Send success response.
     */
    public function sendSuccess($data, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Send error response.
     */
    public function sendError(string $message, $errors = [], int $code = 404): JsonResponse
    {
        $response = [
            'status' => 'error',
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => empty($errors) ? (object) [] : $errors,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send a resource paginator without duplicating data, links or metadata.
     */
    protected function sendPaginated(
        AnonymousResourceCollection $collection,
        string $message = 'Records fetched successfully.'
    ): JsonResponse {
        $resource = $collection->response()->getData(true);
        $meta = $resource['meta'] ?? [];
        $links = $resource['links'] ?? [];

        return $this->sendSuccess([
            'items' => $resource['data'] ?? [],
            'pagination' => [
                'current_page' => (int) ($meta['current_page'] ?? 1),
                'last_page' => (int) ($meta['last_page'] ?? 1),
                'per_page' => (int) ($meta['per_page'] ?? 0),
                'total' => (int) ($meta['total'] ?? 0),
                'from' => $meta['from'] ?? null,
                'to' => $meta['to'] ?? null,
                'next_page_url' => $links['next'] ?? null,
                'prev_page_url' => $links['prev'] ?? null,
            ],
        ], $message);
    }

    /**
     * User-safe error message — logs technical details, returns friendly copy.
     */
    protected function safeErrorMessage(\Throwable $e, string $fallback = 'Something went wrong. Please try again.'): string
    {
        \Illuminate\Support\Facades\Log::error($e->getMessage(), ['exception' => $e]);

        return config('app.debug') ? $e->getMessage() : $fallback;
    }
}
