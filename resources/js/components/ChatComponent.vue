<template>
    <div>
        <!-- User list -->
        <div v-for="user in userList" :key="user.id" @click="openChat(user)" class="user-list-item">
            <span>{{ user.name }}</span>
            <span v-if="user.status === 'online'" class="online-status">Online</span>
            <span v-else class="offline-status">Offline</span>
        </div>
        
        <!-- Chat interface -->
        <div v-if="showChat" class="chat-container">
            <!-- Messages -->
            <div v-for="message in sortedMessages" :key="message.id" :class="messageClass(message)">
                <div class="message-content">
                    <span class="message-sender">{{ message.sender_name }}:</span>
                    <span>{{ message.body }}</span>
                </div>
                <div class="message-meta">
                    <span class="timestamp">{{ message.created_at }}</span>
                    <span v-if="message.seen" class="read-receipt">Seen</span>
                </div>
            </div>

            <!-- Typing indicator -->
            <div v-if="typing" class="typing-indicator">Typing...</div>
            
            <!-- Input for sending new message -->
            <input type="text" class="form-control" v-model="newMessage" @keyup="handleTyping" @keyup.enter="sendMessage" placeholder="Type your message here..." />

            <!-- Buttons for chat history -->
            <div class="chat-history-buttons">
                <button @click="viewChatHistory">View Chat History</button>
                <button @click="clearChatHistory">Clear Chat History</button>
            </div>

            <!-- Display area for chat history -->
            <div v-if="showingChatHistory" class="chat-history">
                <h3>Chat History</h3>
                <div v-for="message in chatHistory" :key="message.id" class="chat-history-message">
                    <p><strong>{{ message.sender_name }}:</strong> {{ message.body }}</p>
                    <p><em>{{ message.created_at }}</em></p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            userList: [],
            showChat: false,
            selectedUser: null,
            messages: [],
            newMessage: '',
            currentUserId: 1, // Assuming you have a way to get the current user's ID
            typing: false,
            chatHistory: [],
            showingChatHistory: false
        };
    },
    mounted() {
        this.fetchUserList();
    },
    computed: {
        sortedMessages() {
            // Sort messages based on sender's role (agent or customer)
            return this.messages.sort((a, b) => {
                if (a.sender_role === 'agent' && b.sender_role !== 'agent') {
                    return -1;
                } else if (a.sender_role !== 'agent' && b.sender_role === 'agent') {
                    return 1;
                } else {
                    // If both are agents or both are customers, sort by created_at in descending order
                    return new Date(b.created_at) - new Date(a.created_at);
                }
            });
        }
    },
    methods: {
        // Toggle chat history display
        async viewChatHistory() {
            try {
                const response = await axios.get(`/live-chat/public/messages/history/${this.selectedUser.id}`);
                this.chatHistory = response.data;
                this.showingChatHistory = true;
            } catch (error) {
                console.error('Error fetching chat history:', error);
            }
        },

        // Clear chat history between the selected user and the current user
        async clearChatHistory() {
            try {
                await axios.delete(`/live-chat/public/messages/history/${this.selectedUser.id}`);
                this.chatHistory = []; // Clear chat history from UI
                this.showingChatHistory = false; // Hide chat history display
            } catch (error) {
                console.error('Error clearing chat history:', error);
            }
        },
        async fetchUserList() {
            try {
                const response = await axios.get('/live-chat/public/user-list');
                this.userList = response.data;
            } catch (error) {
                console.error('Error fetching user list:', error);
            }
        },
        async openChat(user) {
            this.selectedUser = user;
            this.showChat = true;

            try {
                const response = await axios.get(`/live-chat/public/messages/${user.id}`);
                this.messages = response.data.map(message => ({ ...message, seen: false })); // Initialize seen property
                
                // Call the endpoint to mark messages as seen
                await axios.put(`/live-chat/public/messages/${user.id}/mark-as-seen`);
                
                // Update the read_at timestamp for messages marked as seen
                this.messages.forEach(message => {
                    if (message.seen) {
                        message.read_at = new Date(); // Update read_at with current timestamp
                    }
                });
            } catch (error) {
                console.error('Error fetching messages:', error);
            }
        },
        async sendMessage() {
            try {
                const response = await axios.post('/live-chat/public/messages', {
                    recipient_id: this.selectedUser.id,
                    body: this.newMessage
                });
                this.messages.push(response.data);
                this.newMessage = '';
            } catch (error) {
                console.error('Error sending message:', error);
            }
        },
        async handleTyping() {
            // Emit a typing event to indicate the user is typing
            // You may need to implement this based on your backend logic
            // For example, using sockets to emit typing events
            this.typing = true;
        },
        messageClass(message) {
            return message.sender_role === 'agent' ? 'message-sent' : 'message-received';
        }
    }
};
</script>

<style scoped>
.user-list-item {
    cursor: pointer;
    margin-bottom: 10px;
}

.online-status {
    color: green;
    margin-left: 5px;
}

.offline-status {
    color: red;
    margin-left: 5px;
}

.chat-container {
    border: 1px solid #ccc;
    padding: 10px;
    margin-top: 20px;
}

.message-sent {
    text-align: right;
    margin-bottom: 10px;
}

.message-received {
    text-align: left;
    margin-bottom: 10px;
}

.message-content {
    display: inline-block;
    background-color: #f4f4f4;
    border-radius: 10px;
    padding: 5px 10px;
}

.message-sender {
    font-weight: bold;
}

.message-meta {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.typing-indicator {
    font-style: italic;
    color: #999;
}

.chat-history-buttons {
    margin-top: 20px;
}

.chat-history-buttons button {
    margin-right: 10px;
}

.chat-history {
    border-top: 1px solid #ccc;
    margin-top: 20px;
    padding-top: 20px;
}

.chat-history-message {
    margin-bottom: 10px;
}
</style>
