<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Default route
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Auth::routes();

// Home route
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Route to update agent's status
Route::post('/agents/update-status', [AgentController::class, 'updateStatus']);

// Route to fetch messages between users
Route::get('/messages/{userId}', [MessageController::class, 'index']);

// Route to store a new message
Route::post('/messages', [MessageController::class, 'store']);

// Route to fetch list of users
Route::get('/user-list', [MessageController::class, 'userList']);

// Route to display the chat interface
Route::get('/chat', function () {
    return view('chat'); // Assuming your chat interface view is named "chat.blade.php"
});

// Routes for managing user status
Route::prefix('users')->group(function () {
    Route::post('{user}/status', [UserController::class, 'updateStatus']);
});

// Route to view chat history between users
Route::get('/messages/history/{userId}', [MessageController::class, 'viewChatHistory']);

// Route to clear chat history between users
Route::delete('/messages/history/{userId}', [MessageController::class, 'clearChatHistory']);

Route::put('/messages/{userId}/mark-as-seen', [MessageController::class, 'markMessagesAsSeen']); // Route for marking messages as seen
