<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user.
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'role' => 'customer', // Default role for new registrations
        ]);

        return $user;
    }

    /**
     * Authenticate a user.
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws ValidationException
     */
    public function login(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user;
    }

    /**
     * Logout a user.
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}