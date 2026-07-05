// Fixed JavaScript code for live streaming functionality

// Global variables
let currentChannelId = null;
let viewerUpdateInterval = null;
let chatUpdateInterval = null;

// Load channel function
function loadChannel(channelId) {
    currentChannelId = channelId;
    
    fetch(`api/get_channel.php?id=${channelId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateChannelInfo(data.channel);
                updateActiveChannel(channelId);
                updateChat(channelId);
                
                // Start intervals
                if (viewerUpdateInterval) clearInterval(viewerUpdateInterval);
                if (chatUpdateInterval) clearInterval(chatUpdateInterval);
                
                viewerUpdateInterval = setInterval(updateViewerCount, 3000);
                chatUpdateInterval = setInterval(() => updateChat(channelId), 5000);
            }
        })
        .catch(error => console.error('Error loading channel:', error));
}

// Update viewer count
function updateViewerCount() {
    if (!currentChannelId) return;
    
    fetch(`api/get_viewer_count.php?channel_id=${currentChannelId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const viewerElement = document.getElementById('liveViewerCount');
                if (viewerElement) {
                    viewerElement.textContent = data.count.toLocaleString();
                }
            }
        })
        .catch(error => console.error('Error updating viewer count:', error));
}

// Generate player HTML
function generatePlayerHtml(channel) {
    let playerHtml = '';
    
    if (channel.stream_type === 'hls') {
        playerHtml = `
            <video-js id="liveVideo" controls preload="auto" width="100%" height="400" data-setup='{"fluid": true}'>
                <source src="${channel.stream_url}" type="application/x-mpegURL">
            </video-js>
        `;
    } else if (channel.stream_type === 'iframe') {
        playerHtml = `<iframe src="${channel.stream_url}" width="100%" height="400" frameborder="0" allowfullscreen></iframe>`;
    } else {
        playerHtml = `<div class="alert alert-warning">Stream type not supported</div>`;
    }
    
    return playerHtml;
}

// Extract YouTube video ID
function extractYouTubeId(url) {
    let videoId = '';
    
    if (url.includes('youtube.com/watch?v=')) {
        videoId = url.substring(url.indexOf('v=') + 2);
    } else if (url.includes('youtu.be/')) {
        videoId = url.substring(url.indexOf('youtu.be/') + 9);
    } else if (url.includes('youtube.com/embed/')) {
        videoId = url.substring(url.indexOf('embed/') + 6);
    }
    
    return videoId.split('?')[0];
}

// Update channel information
function updateChannelInfo(channel) {
    // Update header
    const channelName = document.querySelector('.live-header h4');
    if (channelName) {
        channelName.textContent = channel.name;
    }
    
    // Update live badge
    const liveBadge = document.querySelector('.live-header .badge');
    if (liveBadge) {
        if (channel.status === 'live') {
            liveBadge.className = 'badge bg-warning ms-2';
            liveBadge.textContent = '🔴 LIVE';
        } else {
            liveBadge.className = 'badge bg-secondary ms-2';
            liveBadge.textContent = 'OFFLINE';
        }
    }
    
    // Update viewer count
    const viewerCount = document.getElementById('liveViewerCount');
    if (viewerCount) {
        viewerCount.textContent = channel.viewer_count.toLocaleString();
    }
    
    // Update description
    const description = document.querySelector('.card-text');
    if (description) {
        description.textContent = channel.description;
    }
    
    // Update category
    const category = document.querySelector('.stream-stats .d-flex:nth-child(2) strong');
    if (category) {
        category.textContent = channel.category.charAt(0).toUpperCase() + channel.category.slice(1);
    }
    
    // Update language
    const language = document.querySelector('.stream-stats .d-flex:last-child strong');
    if (language) {
        language.textContent = channel.language.toUpperCase();
    }
    
    // Enable/disable chat
    const chatInput = document.getElementById('chatInput');
    const chatSubmit = document.getElementById('chatSubmit');
    if (chatInput && chatSubmit) {
        if (channel.status === 'live') {
            chatInput.disabled = false;
            chatSubmit.disabled = false;
        } else {
            chatInput.disabled = true;
            chatSubmit.disabled = true;
        }
    }
}

// Update active channel in sidebar
function updateActiveChannel(channelId) {
    document.querySelectorAll('.channel-item').forEach(item => {
        item.classList.remove('active');
    });
    
    const activeItem = document.querySelector(`[data-channel-id="${channelId}"]`);
    if (activeItem) {
        activeItem.classList.add('active');
    }
}

// Update chat
function updateChat(channelId) {
    fetch(`api/get_chat.php?channel_id=${channelId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    if (data.messages.length > 0) {
                        chatMessages.innerHTML = data.messages.map(msg => `
                            <div class="chat-message">
                                <strong>${msg.username}:</strong> ${msg.message}
                                <small class="text-muted d-block">${new Date(msg.timestamp * 1000).toLocaleTimeString()}</small>
                            </div>
                        `).reverse().join('');
                    } else {
                        chatMessages.innerHTML = '<div class="text-muted">No messages yet. Start conversation!</div>';
                    }
                }
            }
        })
        .catch(error => console.error('Error loading chat:', error));
}

// Send chat message
function sendChatMessage(message) {
    if (!currentChannelId || !message.trim()) return;
    
    fetch('api/send_chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            channel_id: currentChannelId,
            message: message,
            username: 'Guest' // In a real app, this would be logged-in user
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateChat(currentChannelId);
        } else {
            console.error('Error sending message:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Channel click handlers
    document.querySelectorAll('.clickable-channel').forEach(element => {
        element.addEventListener('click', function() {
            const channelId = this.getAttribute('data-channel-id');
            if (channelId) {
                loadChannel(channelId);
            }
        });
    });
    
    // Chat form
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const chatInput = document.getElementById('chatInput');
            if (chatInput && chatInput.value.trim()) {
                sendChatMessage(chatInput.value.trim());
                chatInput.value = '';
            }
        });
    }
    
    // Start intervals
    if (currentChannelId) {
        viewerUpdateInterval = setInterval(updateViewerCount, 3000);
        chatUpdateInterval = setInterval(() => updateChat(currentChannelId), 5000);
    }
});

// Clean up intervals on page unload
window.addEventListener('beforeunload', function() {
    if (viewerUpdateInterval) clearInterval(viewerUpdateInterval);
    if (chatUpdateInterval) clearInterval(chatUpdateInterval);
});

// Live Broadcasting Functions
let broadcastStream = null;
let broadcastViewerCount = 0;
let broadcastSeconds = 0;
let broadcastInterval = null;

// Simulate viewer count increase for broadcast
setInterval(() => {
    if (broadcastStream) {
        broadcastViewerCount += Math.floor(Math.random() * 5);
        const viewerElement = document.getElementById('liveViewerCount');
        if (viewerElement) {
            viewerElement.textContent = broadcastViewerCount.toLocaleString();
        }
    }
}, 2000);

// Update broadcast duration
setInterval(() => {
    if (broadcastStream) {
        broadcastSeconds++;
        const minutes = Math.floor(broadcastSeconds / 60);
        const seconds = broadcastSeconds % 60;
        const durationElement = document.getElementById('streamDuration');
        if (durationElement) {
            durationElement.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }
    }
}, 1000);

function startCameraBroadcast() {
    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(stream => {
            const video = document.getElementById('broadcastVideo');
            video.srcObject = stream;
            broadcastStream = stream;
            broadcastViewerCount = 1;
            broadcastSeconds = 0;
            showNotification('Camera broadcast started!', 'success');
        })
        .catch(err => {
            showNotification('Camera access denied: ' + err.message, 'error');
        });
}

function startScreenBroadcast() {
    navigator.mediaDevices.getDisplayMedia({ video: true, audio: true })
        .then(stream => {
            const video = document.getElementById('broadcastVideo');
            video.srcObject = stream;
            broadcastStream = stream;
            broadcastViewerCount = 1;
            broadcastSeconds = 0;
            showNotification('Screen broadcast started!', 'success');
            
            // Handle screen share end
            stream.getVideoTracks()[0].onended = () => {
                stopBroadcast();
            };
        })
        .catch(err => {
            showNotification('Screen share failed: ' + err.message, 'error');
        });
}

function startFileBroadcast() {
    const file = document.getElementById('videoFile').files[0];
    if (file) {
        const video = document.getElementById('broadcastVideo');
        video.src = URL.createObjectURL(file);
        video.play();
        broadcastStream = true; // Mark as active
        broadcastViewerCount = 1;
        broadcastSeconds = 0;
        showNotification('File broadcast started!', 'success');
        
        // Handle video end
        video.onended = () => {
            stopBroadcast();
        };
    } else {
        showNotification('Please select a video file first', 'error');
    }
}

function loadExternalBroadcast() {
    const url = document.getElementById('externalUrl').value;
    if (url) {
        const video = document.getElementById('broadcastVideo');
        video.src = url;
        video.play();
        broadcastStream = true; // Mark as active
        broadcastViewerCount = 1;
        broadcastSeconds = 0;
        showNotification('External stream loaded!', 'success');
    } else {
        showNotification('Please enter a stream URL', 'error');
    }
}

function toggleBroadcastFullscreen() {
    const video = document.getElementById('broadcastVideo');
    if (video.requestFullscreen) {
        video.requestFullscreen();
    }
}

function stopBroadcast() {
    if (broadcastStream && broadcastStream.getTracks) {
        broadcastStream.getTracks().forEach(track => track.stop());
    }
    
    broadcastStream = null;
    broadcastViewerCount = 0;
    broadcastSeconds = 0;
    
    const video = document.getElementById('broadcastVideo');
    if (video) {
        video.srcObject = null;
        video.src = '';
    }
    
    showNotification('Broadcast ended', 'info');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-' + (type === 'error' ? 'danger' : 'success') + ' position-fixed top-0 end-0 m-3';
    notification.style.zIndex = '9999';
    notification.innerHTML = '<i class="fas fa-info-circle me-2"></i>' + message;
    
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.remove();
    }, 3000);
}
