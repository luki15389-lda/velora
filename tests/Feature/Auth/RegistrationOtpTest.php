<?php

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;

uses(RefreshDatabase::class);

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Luki',
        'email' => 'luki@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ], $overrides);
}

it('registers and sends the OTP email', function () {
    Mail::fake();

    $this->post('/register', registrationPayload())->assertRedirect(route('verification.notice'));

    Mail::assertSent(OtpMail::class);
    expect(User::where('email', 'luki@example.com')->exists())->toBeTrue();
});

it('does not crash when the mail server is unreachable', function () {
    Mail::shouldReceive('to->send')->andThrow(new TransportException('Connection could not be established'));

    $this->post('/register', registrationPayload())
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('error');
});

it('lets an unverified email register again without creating a duplicate', function () {
    Mail::fake();

    $this->post('/register', registrationPayload());
    $this->post('/register', registrationPayload(['name' => 'Luki Updated']))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionDoesntHaveErrors();

    expect(User::where('email', 'luki@example.com')->count())->toBe(1)
        ->and(User::where('email', 'luki@example.com')->value('name'))->toBe('Luki Updated');
});

it('still rejects an email that is already verified', function () {
    User::factory()->create(['email' => 'luki@example.com', 'email_verified_at' => now()]);

    $this->post('/register', registrationPayload())->assertSessionHasErrors('email');
});
