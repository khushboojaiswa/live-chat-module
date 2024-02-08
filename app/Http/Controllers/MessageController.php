<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Events\NewMessage;
use Carbon\Carbon;

class MessageController extends Controller
{

    public function index($userId)
    {
        // Get the current authenticated user
        $currentUser = Auth::user();

        // Determine if the current user is an agent or customer
        $isAgent = $currentUser->role === 'agent';
        
        // Fetch messages where the current user is either the sender or recipient
        $messages = Message::where(function ($query) use ($userId, $currentUser, $isAgent) {
            if ($isAgent) {
                $query->where('sender_id', $userId)->where('recipient_id', $currentUser->id);
            } else {
                $query->where('sender_id', $currentUser->id)->where('recipient_id', $userId);
            }
            $query->orderBy('created_at', 'desc'); // Order by created_at column in descending order
        })->orWhere(function ($query) use ($userId, $currentUser, $isAgent) {
            if ($isAgent) {
                $query->where('sender_id', $currentUser->id)->where('recipient_id', $userId);
            } else {
                $query->where('sender_id', $userId)->where('recipient_id', $currentUser->id);
            }
            $query->orderBy('created_at', 'desc'); // Order by created_at column in descending order
        })->with('sender', 'recipient')
        ->get();

        // Ensure $messages is a collection
        $messages = new Collection($messages);

        if ($messages->isEmpty()) {
            return response()->json([]);
        }

        // Format the retrieved messages
        $formattedMessages = $messages->map(function ($message) use ($currentUser, $isAgent) {
            return [
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender ? $message->sender->name : 'Unknown',
                'recipient_id' => $message->recipient_id,
                'recipient_name' => $message->recipient ? $message->recipient->name : 'Unknown',
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
                'is_sent_by_current_user' => $message->sender_id === $currentUser->id,
            ];
        });

        // Return the formatted messages
        return response()->json($formattedMessages);
    }

    // Store a new message
    public function store(Request $request)
    {
        $sender = Auth::user();

        // Create a new message
        $message = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $request->input('recipient_id'),
            'body' => $request->input('body')
        ]);

        // Add sender and recipient names to the message object
        $message->sender_name = $sender->name;
        $message->recipient_name = User::find($request->input('recipient_id'))->name;
        broadcast(new NewMessage($message))->toOthers();
        return $message;
    }

    // Get the list of users for the chat
    public function userList()
    {
        $currentUser = Auth::user();

        // Get users based on the current user's role
        if ($currentUser->role === 'agent') {
            $users = User::where('role', 'customer')->get();
        } elseif ($currentUser->role === 'customer') {
            $users = User::where('role', 'agent')->get();
        } else {
            $users = [];
        }

        return $users;
    }

    // Fetch chat history between the selected user and the current user
    public function viewChatHistory($userId)
    {
        $currentUser = Auth::user();
        
        // Retrieve messages between the current user and the selected user
        $messages = Message::where(function ($query) use ($userId, $currentUser) {
                $query->where('sender_id', $currentUser->id)->where('recipient_id', $userId);
            })->orWhere(function ($query) use ($userId, $currentUser) {
                $query->where('sender_id', $userId)->where('recipient_id', $currentUser->id);
            })->orderBy('created_at', 'asc')
            ->with('sender') // Eager load the sender relationship
            ->get();

        // Format the retrieved messages with sender's name
        $formattedMessages = $messages->map(function ($message) use ($currentUser) {
            return [
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name, // Get sender's name from the sender relationship
                'recipient_id' => $message->recipient_id,
                'recipient_name' => $message->recipient ? $message->recipient->name : 'Unknown',
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
                'is_sent_by_current_user' => $message->sender_id === $currentUser->id,
            ];
        });

        // Return the formatted messages
        return response()->json($formattedMessages);
    }


    // Clear chat history between the selected user and the current user
    public function clearChatHistory($userId)
    {
        $currentUser = Auth::user();
        
        // Delete messages between the current user and the selected user
        Message::where(function ($query) use ($userId, $currentUser) {
            $query->where('sender_id', $currentUser->id)->where('recipient_id', $userId);
        })->orWhere(function ($query) use ($userId, $currentUser) {
            $query->where('sender_id', $userId)->where('recipient_id', $currentUser->id);
        })->delete();

        return response()->json(['message' => 'Chat history cleared successfully']);
    }

    /**
     * Mark messages as seen when the chat is opened.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function markMessagesAsSeen($userId)
    {
        try {
            $messages = Message::where('recipient_id', $userId)
                                ->where('seen', false)
                                ->get();
            
            foreach ($messages as $message) {
                $message->seen = true;
                $message->read_at = Carbon::now(); // Update read_at with current timestamp
                $message->save();
            }
            
            return response()->json(['message' => 'Messages marked as seen successfully'], 200);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to mark messages as seen: ' . $e->getMessage());
            
            // Return a response indicating failure
            return response()->json(['message' => 'Failed to mark messages as seen'], 500);
        }
    }
}
