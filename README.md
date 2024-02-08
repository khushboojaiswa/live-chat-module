Laravel Real-Time Chat Application

This is a real-time chat application built with Laravel that includes user registration, authentication, user roles, real-time communication, chat interface, typing indicators, read receipts, online/offline status, chat history, and notifications.

Setup
Create a New Laravel Project:


laravel new real-time-chat-app
Navigate to the Project Directory:


cd real-time-chat-app
Install Composer Dependencies:


composer install
Copy Environment File:


cp .env.example .env
Generate Application Key:


php artisan key:generate
Configure Environment Variables:

Update the .env file with your database settings and broadcasting credentials.

Migrate the Database:


php artisan migrate
Start the Development Server:


php artisan serve

User Registration and Authentication
Implement user registration and authentication functionality.
Allow users to register using their email and password.
Allow users to log in using their registered email and password.

User Roles
Define two user roles: Customer and Support Agent.
Customers can initiate chats and communicate with support agents.
Support agents can view and respond to incoming chats.
User roles are managed using Laravel's built-in authorization system.

Real-Time Communication
Implement real-time chat functionality using Laravel Broadcasting.
Utilize Laravel Echo and a broadcasting service (e.g., Pusher) for real-time event broadcasting.
Ensure messages are delivered instantly without manual refreshing.

Chat Interface
Create a user-friendly chat interface for both customers and support agents.
Display messages in a conversation-like format with timestamps and sender indicators.

Typing Indicator and Read Receipts
Show typing indicators when a user is composing a message.
Implement read receipts to indicate when a message has been seen by the recipient.

Online/Offline Status
Display the online/offline status of support agents.
Implement a mechanism to update the status based on agent availability.

Chat History
Allow users to view their chat history with support agents.
Provide an option to clear chat history.

Notifications
Implement real-time notifications to alert users of new chat messages.
