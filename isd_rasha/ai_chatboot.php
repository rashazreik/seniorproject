<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photography Assistant - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3a6ea5;
            --secondary-color: #004e98;
            --accent-color: #ff9505;
            --light-bg: #f7f9fc;
            --dark-text: #2d3748;
            --light-text: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--dark-text);
        }
        
        .chat-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        .chat-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0 80px;
            position: relative;
            margin-bottom: -40px;
        }
        
        .chat-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }
        
        .chat-box-container {
            padding: 30px;
        }
        
        .chat-box {
            height: 450px;
            overflow-y: auto;
            padding: 20px;
            border-radius: 12px;
            background-color: var(--light-bg);
            margin-bottom: 20px;
            scroll-behavior: smooth;
        }
        
        .message {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 18px;
            max-width: 75%;
            position: relative;
            line-height: 1.5;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .user-message {
            background-color: var(--primary-color);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }
        
        .bot-message {
            background-color: white;
            color: var(--dark-text);
            border-bottom-left-radius: 4px;
            border-left: 4px solid var(--accent-color);
        }
        
        .bot-message:first-child {
            background-color: #e9f5ff;
            border-left: 4px solid var(--primary-color);
        }
        
        .typing-indicator {
            display: none;
            background-color: white;
            color: var(--dark-text);
            padding: 15px 20px;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            width: fit-content;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--accent-color);
            animation: wave 1.3s linear infinite;
            margin-right: 4px;
        }
        
        .dot:nth-child(2) {
            animation-delay: -1.1s;
        }
        
        .dot:nth-child(3) {
            animation-delay: -0.9s;
        }
        
        @keyframes wave {
            0%, 60%, 100% {
                transform: initial;
            }
            30% {
                transform: translateY(-5px);
            }
        }
        
        .chat-form {
            padding: 0 20px 20px;
        }
        
        .chat-form .input-group {
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .chat-form input {
            border: none;
            padding: 15px 25px;
            font-size: 16px;
        }
        
        .chat-form input:focus {
            box-shadow: none;
        }
        
        .chat-form button {
            border-radius: 0 30px 30px 0;
            padding: 0 25px;
            background-color: var(--accent-color);
            border: none;
            font-weight: 600;
        }
        
        .chat-form button:hover {
            background-color: #e08500;
        }
        
        .chat-form button i {
            margin-left: 5px;
        }
        
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 18px;
        }
        
        .message-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .user-row {
            flex-direction: row-reverse;
        }
        
        .user-row .avatar {
            margin-right: 0;
            margin-left: 12px;
            background-color: var(--secondary-color);
            color: white;
        }
        
        .bot-row .avatar {
            background-color: var(--accent-color);
            color: white;
        }
        
        .message-container {
            display: flex;
            flex-direction: column;
            max-width: calc(75% - 48px);
        }
        
        .message-time {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
            align-self: flex-end;
        }
        
        .user-row .message-time {
            align-self: flex-start;
        }
        
        .features-section {
            padding: 60px 0;
            background-color: white;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px;
            background-color: var(--light-bg);
            height: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: rgba(58, 110, 165, 0.1);
            color: var(--primary-color);
            font-size: 30px;
            margin-bottom: 20px;
        }
        
        /* Custom scrollbar for chat box */
        .chat-box::-webkit-scrollbar {
            width: 8px;
        }
        
        .chat-box::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .chat-box::-webkit-scrollbar-thumb {
            background: #c5c5c5;
            border-radius: 10px;
        }
        
        .chat-box::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>

<!-- Include User Navbar -->
<?php include 'includes/navbar_user.php'; ?>

<!-- Chat Header -->
<section class="chat-header">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Photography AI Assistant</h1>
        <p class="lead">Your personal guide to cameras, photography techniques, and equipment recommendations</p>
    </div>
</section>

<!-- Chat Interface -->
<div class="container mb-5">
    <div class="chat-container">
        <div class="chat-box-container">
            <div class="chat-box" id="chatBox">
                <div class="message-row bot-row">
                    <div class="avatar">
                        <i class="fas fa-camera"></i>
                    </div>
                    <div class="message-container">
                        <div class="message bot-message">
                            Hello, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>! I'm your photography assistant. How can I help you today? I can recommend cameras, explain features, or give photography tips.
                        </div>
                        <div class="message-time">
                            <?php echo date('h:i A'); ?>
                        </div>
                    </div>
                </div>
                <div class="typing-indicator" id="typingIndicator">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            </div>
            
            <form id="chatForm" class="chat-form">
                <div class="input-group">
                    <input type="text" id="userMessage" class="form-control" placeholder="Ask about cameras, photography tips, or equipment..." required>
                    <button type="submit" class="btn btn-primary">Send <i class="fas fa-paper-plane"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="text-center mb-5">How Can Our Assistant Help You?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-camera-retro"></i>
                    </div>
                    <h4>Camera Recommendations</h4>
                    <p>Get personalized camera suggestions based on your budget, experience level, and photography goals.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Photography Tips</h4>
                    <p>Learn techniques for composition, lighting, exposure, and more to improve your photography skills.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h4>Technical Guidance</h4>
                    <p>Understand camera features, settings, and accessories to help you make informed decisions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatBox = document.getElementById('chatBox');
    const chatForm = document.getElementById('chatForm');
    const userMessage = document.getElementById('userMessage');
    const typingIndicator = document.getElementById('typingIndicator');
    
    // Store conversation history
    const conversationHistory = [
        {
            role: 'assistant',
            content: `Hello, ${<?php echo json_encode($_SESSION["full_name"]); ?>}! I'm your photography assistant. How can I help you today? I can recommend cameras, explain features, or give photography tips.`
        }
    ];
    
    // OpenRouter API key and configuration
    const OPENROUTER_API_KEY = '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';
    const MODEL = 'meta-llama/llama-3.3-8b-instruct:free';
    const SITE_URL = window.location.origin;
    const SITE_NAME = 'Camera Shop AI Assistant';
    
    // Function to get current time formatted as h:mm AM/PM
    function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
    
    // Function to add a message to the chat box
    function addMessage(content, isUser = false) {
        const messageRow = document.createElement('div');
        messageRow.className = `message-row ${isUser ? 'user-row' : 'bot-row'}`;
        
        const avatar = document.createElement('div');
        avatar.className = 'avatar';
        
        const icon = document.createElement('i');
        icon.className = isUser ? 'fas fa-user' : 'fas fa-camera';
        avatar.appendChild(icon);
        
        const messageContainer = document.createElement('div');
        messageContainer.className = 'message-container';
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        messageDiv.textContent = content;
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.textContent = getCurrentTime();
        
        messageContainer.appendChild(messageDiv);
        messageContainer.appendChild(timeDiv);
        
        messageRow.appendChild(avatar);
        messageRow.appendChild(messageContainer);
        
        // Insert before typing indicator
        if (typingIndicator && typingIndicator.parentNode === chatBox) {
            chatBox.insertBefore(messageRow, typingIndicator);
        } else {
            chatBox.appendChild(messageRow);
        }
        
        // Scroll to bottom
        chatBox.scrollTop = chatBox.scrollHeight;
        
        // Add to conversation history
        conversationHistory.push({
            role: isUser ? 'user' : 'assistant',
            content
        });
    }
    
    // Function to show typing indicator
    function showTypingIndicator() {
        if (typingIndicator) {
            typingIndicator.style.display = 'block';
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }
    
    // Function to hide typing indicator
    function hideTypingIndicator() {
        if (typingIndicator) {
            typingIndicator.style.display = 'none';
        }
    }
    
    // Function to get response from AI
    async function getAIResponse(prompt) {
        try {
            const apiMessages = conversationHistory.map(msg => ({
                role: msg.role,
                content: msg.content
            }));
            
            // Add system message to guide the assistant
            apiMessages.unshift({
                role: 'system',
                content: 'You are a helpful photography assistant for a camera shop. Provide knowledgeable advice about cameras, photography techniques, and equipment. Be friendly, professional, and concise. Always try to recommend products a camera shop might sell when appropriate. Avoid giving lengthy technical explanations unless specifically asked.'
            });
            
            // Show typing indicator while waiting for response
            showTypingIndicator();
            
            const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${OPENROUTER_API_KEY}`,
                    'HTTP-Referer': SITE_URL,
                    'X-Title': SITE_NAME,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    model: MODEL,
                    messages: apiMessages
                })
            });
            
            const data = await response.json();
            
            // Hide typing indicator
            hideTypingIndicator();
            
            if (data.choices && data.choices.length > 0 && data.choices[0].message) {
                return data.choices[0].message.content;
            } else {
                throw new Error('Invalid response from API');
            }
        } catch (error) {
            console.error('Error fetching AI response:', error);
            hideTypingIndicator();
            return 'Sorry, I encountered an error processing your request. Please try again later.';
        }
    }
    
    // Form submit event handler
    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const message = userMessage.value.trim();
            if (!message) return;
            
            // Add user message to chat
            addMessage(message, true);
            
            // Clear input
            userMessage.value = '';
            
            // Get AI response
            const aiResponse = await getAIResponse(message);
            
            // Add AI response to chat
            addMessage(aiResponse);
        });
    }
});
</script>

</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?>