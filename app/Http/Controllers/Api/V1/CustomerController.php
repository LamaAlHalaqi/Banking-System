<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Get customer profile.
     *
     * @param Request $request
     * @return UserResource
     */
    public function profile(Request $request)
    {
        return new UserResource($request->user()->load('accounts'));
    }

    /**
     * Update customer profile.
     *
     * @param Request $request
     * @return UserResource
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'date_of_birth' => 'sometimes|date',
        ]);

        $user = $request->user();
        $user->update($request->only(['name', 'phone', 'address', 'date_of_birth']));

        return new UserResource($user->load('accounts'));
    }
}