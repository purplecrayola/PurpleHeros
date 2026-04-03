<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $timestamp = now()->addSeconds(fake()->unique()->numberBetween(1, 86400))->format('Y-m-d H:i:s');

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'join_date' => $timestamp,
            'last_login' => $timestamp,
            'phone_number' => fake()->phoneNumber(),
            'status' => 'Active',
            'role_name' => 'Admin',
            'avatar' => null,
            'position' => 'Manager',
            'department' => 'Operations',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
