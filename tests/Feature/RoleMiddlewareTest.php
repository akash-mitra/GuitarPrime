<?php

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;

test('role middleware allows access for authorized roles', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $request = request();
    $request->setUserResolver(fn () => $admin);

    $middleware = new RoleMiddleware;
    $response = $middleware->handle($request, fn () => response('success'), 'admin');

    expect($response->getContent())->toBe('success');
});

test('role middleware blocks access for unauthorized roles', function () {
    $student = User::factory()->create(['role' => 'student']);

    $request = request();
    $request->setUserResolver(fn () => $student);

    $middleware = new RoleMiddleware;

    expect(fn () => $middleware->handle($request, fn () => response('success'), 'admin'))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

test('role middleware allows access for multiple roles', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $request = request();
    $request->setUserResolver(fn () => $coach);

    $middleware = new RoleMiddleware;
    $response = $middleware->handle($request, fn () => response('success'), 'admin', 'coach');

    expect($response->getContent())->toBe('success');
});

test('role middleware redirects unauthenticated users', function () {
    $request = request();
    $request->setUserResolver(fn () => null);

    $middleware = new RoleMiddleware;
    $response = $middleware->handle($request, fn () => response('success'), 'admin');

    expect($response->getStatusCode())->toBe(302);
});

test('user model role helper methods work correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = User::factory()->create(['role' => 'coach']);
    $student = User::factory()->create(['role' => 'student']);

    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->hasRole('coach'))->toBeFalse();

    expect($coach->hasAnyRole(['admin', 'coach']))->toBeTrue();
    expect($student->hasAnyRole(['admin', 'coach']))->toBeFalse();
});

test('user role scope filters correctly', function () {
    User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'coach']);
    User::factory()->create(['role' => 'student']);

    expect(User::withRole('admin')->count())->toBe(1);
    expect(User::withRole('coach')->count())->toBe(1);
    expect(User::withRole('student')->count())->toBe(1);
});
