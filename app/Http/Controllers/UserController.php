<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Update the status of the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, User $user)
    {
        // Validate the incoming request data
        $request->validate([
            'status' => 'required|in:online,offline',
        ]);

        // Set the status of the user
        $user->setStatus($request->status);

        // Return a success response
        return response()->json(['message' => 'Status updated successfully'], 200);
    }
}
