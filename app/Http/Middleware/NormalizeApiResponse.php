<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeApiResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->is('api/*') || ! $response instanceof JsonResponse) {
            return $response;
        }

        $payload = $response->getData(true);
        if (! is_array($payload) || ! isset($payload['status'])) {
            return $response;
        }

        if ($payload['status'] === 'success') {
            $payload['success'] = true;
            $payload['message'] ??= 'Request completed successfully.';

            // Laravel API resources put pagination beside the top-level data,
            // while raw paginators put it inside data. Expose one Flutter-safe
            // shape in both cases without removing legacy response fields.
            if (isset($payload['meta'], $payload['links']) && is_array($payload['data'] ?? null)) {
                $normalized = $this->resourcePaginator($payload['data'], $payload['meta'], $payload['links']);
                $payload['items'] = $normalized['items'];
                $payload['pagination'] = $normalized['pagination'];
            }

            $payload['data'] = $this->enrichNestedPaginators($payload['data'] ?? null);
        } elseif (in_array($payload['status'], ['error', 'fail', 'unavailable'], true)) {
            $payload['success'] = false;
            $payload['message'] ??= 'Unable to complete your request.';
            $payload['data'] ??= null;
            $payload['errors'] = empty($payload['errors']) ? (object) [] : $payload['errors'];
        }

        $response->setData($payload);

        return $response;
    }

    private function enrichNestedPaginators(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (isset($value['current_page'], $value['per_page'], $value['data'])) {
            $value['items'] = array_map(fn ($item) => $this->enrichNestedPaginators($item), $value['data']);
            $value['pagination'] = [
                'current_page' => (int) $value['current_page'],
                'last_page' => (int) ($value['last_page'] ?? $value['current_page']),
                'per_page' => (int) $value['per_page'],
                'total' => (int) ($value['total'] ?? count($value['data'])),
                'from' => $value['from'] ?? null,
                'to' => $value['to'] ?? null,
                'next_page_url' => $value['next_page_url'] ?? null,
                'prev_page_url' => $value['prev_page_url'] ?? null,
            ];

            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->enrichNestedPaginators($item);
        }

        return $value;
    }

    private function resourcePaginator(array $items, array $meta, array $links): array
    {
        return [
            'items' => array_map(fn ($item) => $this->enrichNestedPaginators($item), $items),
            'pagination' => [
                'current_page' => (int) ($meta['current_page'] ?? 1),
                'last_page' => (int) ($meta['last_page'] ?? 1),
                'per_page' => (int) ($meta['per_page'] ?? count($items)),
                'total' => (int) ($meta['total'] ?? count($items)),
                'from' => $meta['from'] ?? null,
                'to' => $meta['to'] ?? null,
                'next_page_url' => $links['next'] ?? null,
                'prev_page_url' => $links['prev'] ?? null,
            ],
        ];
    }
}
