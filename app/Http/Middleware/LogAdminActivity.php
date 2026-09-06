<?php

namespace App\Http\Middleware;

use App\Models\AdminActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LogAdminActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if ($request->user()?->role === 'admin' && !in_array($request->method(), ['GET','HEAD'], true)
            && $response->getStatusCode() < 400 && Schema::hasTable('admin_activity_logs')) {
            $route = $request->route();
            $parameters = collect($route?->parameters() ?? []);
            $subject = $parameters->first(fn ($value) => is_object($value) && method_exists($value, 'getKey'));
            $safe = $this->redact($request->except(['_token', '_method']));
            AdminActivityLog::create([
                'actor_id' => $request->user()->id,
                'action' => strtolower($request->method()).':' . ($route?->getName() ?? 'admin.action'),
                'description' => $this->description($request),
                'route_name' => $route?->getName(), 'method' => $request->method(),
                'subject_type' => $subject ? get_class($subject) : null, 'subject_id' => $subject?->getKey(),
                'ip_address' => $request->ip(), 'user_agent' => mb_substr((string)$request->userAgent(), 0, 500),
                'metadata' => ['input' => $safe],
            ]);
        }
        return $response;
    }

    private function description(Request $request): string
    {
        return ucfirst(strtolower($request->method())).' action on '.str_replace(['admin.','.', '-'], ['', ' ', ' '], (string)$request->route()?->getName());
    }

    private function redact(array $input): array
    {
        $redacted = [];

        foreach (array_slice($input, 0, 20, true) as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            $isSecret = preg_match('/password|secret|api[_-]?key|server[_-]?key|vapid[_-]?key|access[_-]?key|token/', $normalizedKey) === 1;

            if ($isSecret) {
                $redacted[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $redacted[$key] = $this->redact($value);
                continue;
            }

            $redacted[$key] = is_string($value) ? mb_substr($value, 0, 300) : $value;
        }

        return $redacted;
    }
}
