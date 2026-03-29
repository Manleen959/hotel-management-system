<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Finder - Premium Stays</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <!-- Main Style -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<nav class="navbar">
    <h2>🏨 Hotel Finder</h2>
    <div class="nav-links">
        <span style="font-weight: 500;"><i class="fa fa-user-circle"></i> <?= $_SESSION['username'] ?></span>
        <a href="index.php">Browse</a>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin/dashboard.php">Dashboard</a>
        <?php else: ?>
            <a href="user/my_bookings.php">My Bookings</a>
        <?php endif; ?>
        <a href="auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="main-container">
    <div class="sidebar">
        <h3><i class="fas fa-filter"></i> Filters</h3>
        <div class="search-container" style="flex-direction: column;">
            <input type="text" id="search" class="search-bar" placeholder="Search by name or city..." onkeyup="searchHotel()" style="width: 100%;">
        </div>
        
        <div class="filter-group">
            <h3>Category</h3>
            <button class="filter-btn active" onclick="filterCategory('All', this)"><i class="fas fa-globe"></i> All Types</button>
            <button class="filter-btn" onclick="filterCategory('Hotel', this)"><i class="fas fa-hotel"></i> Hotels</button>
            <button class="filter-btn" onclick="filterCategory('Resort', this)"><i class="fas fa-umbrella-beach"></i> Resorts</button>
            <button class="filter-btn" onclick="filterCategory('Villa', this)"><i class="fas fa-home"></i> Villas</button>
        </div>

        <div class="filter-group">
            <h3>Price Range</h3>
            <button class="filter-btn active" onclick="filterPrice(0, 99999, this)">Any Price</button>
            <button class="filter-btn" onclick="filterPrice(0, 5000, this)">Under ₹5,000</button>
            <button class="filter-btn" onclick="filterPrice(5000, 15000, this)">₹5,000 - ₹15,000</button>
            <button class="filter-btn" onclick="filterPrice(15000, 99999, this)">Over ₹15,000</button>
        </div>
    </div>

    <div class="content-area">
        <div id="map"></div>
        <div id="hotel-list">
            <!-- Hotels will be loaded here via JS -->
        </div>
    </div>
</div>

<!-- Gallery Modal -->
<div id="galleryModal" class="modal" onclick="closeModal(event)">
    <button class="btn-close" onclick="document.getElementById('galleryModal').classList.remove('active')">&times;</button>
    <div class="modal-content">
        <img id="modalImg" src="" class="modal-img">
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<!-- Main App JS -->
<script src="js/map.js"></script>

<!-- Chatbot UI -->
<div class="chatbot-bubble" onclick="toggleChat()">
    <i class="fas fa-robot"></i>
</div>

<div class="chatbot-window" id="chatWindow">
    <div class="chat-header">
        <h3><i class="fas fa-robot"></i> Hotel Concierge</h3>
        <i class="fas fa-times chat-close" onclick="toggleChat()"></i>
    </div>
    <div class="chat-body" id="chatBody">
        <div class="chat-msg bot">Hello! I am your Hotel Finder assistant. How can I help you plan your luxury stay today?</div>
    </div>
    <div class="typing-indicator" id="typingIndicator">Agent is typing...</div>
    <div class="chat-footer">
        <input type="text" class="chat-input" id="chatInput" placeholder="Type a message..." onkeypress="handleChatPress(event)">
        <button class="chat-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
    function toggleChat() {
        document.getElementById('chatWindow').classList.toggle('active');
    }

    function handleChatPress(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    }

    async function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        if (!message) return;

        // Add user message to UI
        appendMessage(message, 'user');
        input.value = '';
        
        const typingIndicator = document.getElementById('typingIndicator');
        typingIndicator.style.display = 'block';

        try {
            const response = await fetch('api/chat_bot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            });
            const data = await response.json();
            
            typingIndicator.style.display = 'none';

            if (data.error) {
                appendMessage("Sorry, I'm having trouble right now. Error: " + data.error, 'bot');
            } else {
                appendMessage(data.reply, 'bot');
            }
        } catch (error) {
            typingIndicator.style.display = 'none';
            appendMessage("Connection error. Please try again later.", 'bot');
            console.error(error);
        }
    }

    function appendMessage(text, sender) {
        const chatBody = document.getElementById('chatBody');
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-msg ${sender}`;
        msgDiv.textContent = text;
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    // Close modal when clicking outside content (Existing)
    function closeModal(e) {
        if (e.target.id === 'galleryModal') {
            document.getElementById('galleryModal').classList.remove('active');
        }
    }
</script>

</body>
</html>