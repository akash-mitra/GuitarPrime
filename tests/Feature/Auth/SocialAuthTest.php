<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

uses(RefreshDatabase::class);

it('redirects to the correct Google sign in url', function () {
    $driver = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
    $driver->shouldReceive('redirect')
        ->andReturn(new RedirectResponse('https://redirect.url'));

    Socialite::shouldReceive('driver')->andReturn($driver);

    $this->get(route('oauth.redirect', 'google'))
        ->assertRedirect('https://redirect.url');
});

it('signs in with Google', function () {
    $user = User::factory()->create();
    $socialiteUser = Mockery::mock('Laravel\Socialite\Two\User');

    $socialiteUser
        ->shouldReceive('getId')->andReturn($googleId = '12345654321345')
        ->shouldReceive('getName')->andReturn($user->name)
        ->shouldReceive('getEmail')->andReturn($user->email)
        ->shouldReceive('getAvatar')->andReturn($avatarUrl = 'https://en.gravatar.com/userimage');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $this->get(route('oauth.callback', 'google'))->assertRedirect(route('dashboard'));
    expect(auth()->check())->toBeTrue();

    expect($user->refresh())
        ->avatar->toBe($avatarUrl);
});
