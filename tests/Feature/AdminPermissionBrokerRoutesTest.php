<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminPermission;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AdminPermissionBrokerRoutesTest extends TestCase
{
    #[Test]
    public function it_maps_broker_routes_to_broker_permissions(): void
    {
        $this->assertSame('brokers.view', $this->permissionFor('admin.brokers.index', 'GET'));
        $this->assertSame('brokers.manage', $this->permissionFor('admin.brokers.approve', 'POST'));
        $this->assertSame('brokers.settings', $this->permissionFor('admin.broker-settings.index', 'GET'));
        $this->assertSame('brokers.plans.manage', $this->permissionFor('admin.broker-plans.index', 'GET'));
    }

    private function permissionFor(string $routeName, string $method): ?string
    {
        $route = new Route([$method], '/test', ['as' => $routeName]);
        $request = Request::create('/test', $method);
        $request->setRouteResolver(fn () => $route);

        $middleware = new AdminPermission();
        $methodRef = new \ReflectionMethod($middleware, 'permissionFor');
        $methodRef->setAccessible(true);

        return $methodRef->invoke($middleware, $request);
    }
}
