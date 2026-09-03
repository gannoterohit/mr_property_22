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
            $safe = collect($request->except(['password','password_confirmation','mail_password','razorpay_secret','razorpay_webhook_secret','_token','_method']))
                ->map(fn ($value) => is_string($value) ? mb_substr($value, 0, 300) : $value)->take(20)->all();
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
}
