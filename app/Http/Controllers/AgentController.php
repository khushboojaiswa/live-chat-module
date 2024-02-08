<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AgentController extends Controller
{
    /**
     * Update agent's online status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        // Get the authenticated user
        $user = auth()->user();
        
        // Update the online status of the user
        $user->update(['online' => $request->status]);
        
        // Return success response
        return response()->json(['status' => 'success']);
    }
}
