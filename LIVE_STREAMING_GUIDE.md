# PK Live News - Live Streaming System Guide

## Overview
This comprehensive live streaming system provides a professional TV-like experience with multiple channels, real-time chat, and advanced features perfect for your FYP project.

## 🚀 Features Implemented

### 1. **Multi-Channel Support**
- **Categories**: News, Sports, Entertainment, Business, Technology, International
- **Channel Types**: YouTube Live, HLS Streams, iFrame Embeds, RTMP
- **Live Indicators**: Real-time status badges with animations
- **Featured Channels**: Priority display and highlighting

### 2. **Streaming Methods**
- **YouTube Live** (Easiest for FYP)
  - Auto-detects YouTube URLs
  - Supports watch, embed, and youtu.be formats
  - Example: `https://www.youtube.com/watch?v=jfKfPfyJRdk`

- **HLS Streaming** (Advanced)
  - Video.js integration
  - Adaptive bitrate support
  - Example: `https://example.com/live.m3u8`

- **iFrame Embeds** (Flexible)
  - Custom embed codes
  - Third-party players
  - Example: Any embeddable iframe

### 3. **User Interface**
- **Category Tabs**: Organized channel browsing
- **Channel List Sidebar**: Quick channel switching
- **Live Chat System**: Real-time messaging during live streams
- **Schedule Display**: Programming schedule with NOW/UPCOMING indicators
- **Responsive Design**: Works on all devices

### 4. **Admin Panel**
- **Channel Management**: Add, edit, delete channels
- **Status Control**: Toggle live/offline status
- **Featured Settings**: Mark channels as featured
- **Bulk Operations**: Multiple channel management

## 📊 Database Structure

### Channels Table
```sql
channels
├── id (Primary Key)
├── name (Channel Name)
├── category (news/sports/entertainment/business/technology/international)
├── stream_url (Stream URL)
├── stream_type (youtube/hls/rtmp/iframe)
├── thumbnail (Channel Image)
├── description (Channel Description)
├── status (live/offline/scheduled)
├── viewer_count (Current Viewers)
├── language (Channel Language)
├── country (Channel Country)
├── is_featured (Featured Channel)
└── sort_order (Display Order)
```

### Live Chat Table
```sql
live_chat
├── id (Primary Key)
├── channel_id (Foreign Key)
├── username (User Name)
├── message (Chat Message)
└── timestamp (Message Time)
```

### Channel Schedule Table
```sql
channel_schedule
├── id (Primary Key)
├── channel_id (Foreign Key)
├── program_title (Program Name)
├── description (Program Description)
├── start_time (Start Time)
├── end_time (End Time)
└── is_recurring (Recurring Program)
```

## 🛠 Setup Instructions

### 1. Database Setup
Run the setup script to create tables:
```bash
# Access via browser
http://localhost/PK-LIVE%20NEWS/setup_channels.php
```

### 2. Add Channels
Access admin panel:
```
http://localhost/PK-LIVE%20NEWS/admin/manage-channels.php
```

### 3. Configure Streams
Add your YouTube Live streams or HLS URLs:
- **YouTube**: Use live stream URLs or existing videos
- **HLS**: Use `.m3u8` playlist URLs
- **iFrame**: Use custom embed codes

## 🎯 Usage Examples

### YouTube Live Channel
```
Name: PK News Live
Category: News
Stream Type: YouTube
Stream URL: https://www.youtube.com/watch?v=jfKfPfyJRdk
Status: Live
```

### HLS Channel
```
Name: Sports Central
Category: Sports
Stream Type: HLS
Stream URL: https://example.com/live.m3u8
Status: Live
```

### Custom Embed Channel
```
Name: Entertainment Tonight
Category: Entertainment
Stream Type: iFrame
Stream URL: https://player.example.com/embed/123
Status: Scheduled
```

## 📱 API Endpoints

### Get Channel Details
```
GET /api/get_channel.php?id={channel_id}
```

### Get Chat Messages
```
GET /api/get_chat.php?channel_id={channel_id}
```

### Send Chat Message
```
POST /api/send_chat.php
Content-Type: application/json

{
    "channel_id": 1,
    "message": "Hello!",
    "username": "Guest"
}
```

### Get Channels List
```
GET /api/get_channels.php?category={category}&limit={limit}&offset={offset}
```

## 🎨 Customization

### CSS Classes
- `.channel-item`: Channel list item
- `.channel-card`: Channel card
- `.live-badge`: Live indicator
- `.chat-messages`: Chat container
- `.video-wrapper`: Video player container

### JavaScript Functions
- `loadChannel(channelId)`: Switch to channel
- `sendChatMessage(message)`: Send chat message
- `toggleFullscreen()`: Fullscreen mode
- `updateViewerCount()`: Update viewer count

## 🔧 Advanced Features

### 1. **Real-time Updates**
- Viewer count updates every 3 seconds
- Chat messages update every 5 seconds
- Live status indicators with animations

### 2. **Channel Switching**
- Seamless channel switching without page reload
- Loading animations during switch
- Active channel highlighting

### 3. **Chat System**
- Real-time messaging
- User identification
- Message history
- Auto-scroll to latest messages

### 4. **Schedule Management**
- Program scheduling
- Recurring programs support
- NOW/UPCOMING/LATER indicators

## 📈 Performance Tips

### 1. **Optimize Images**
- Use WebP format for thumbnails
- Compress channel images
- Lazy loading for channel lists

### 2. **Stream Optimization**
- Use CDN for HLS streams
- Optimize YouTube embed settings
- Enable adaptive bitrate

### 3. **Database Optimization**
- Index frequently queried columns
- Cache channel lists
- Optimize chat message queries

## 🚀 Future Enhancements

### 1. **User Authentication**
- Login system for chat
- User profiles
- Chat moderation

### 2. **Advanced Streaming**
- WebRTC support
- Low-latency streaming
- Multi-bitrate streams

### 3. **Analytics**
- Viewer statistics
- Channel performance
- User engagement metrics

### 4. **Mobile App**
- React Native app
- Push notifications
- Offline support

## 📞 Support

For issues and questions:
1. Check browser console for errors
2. Verify database connections
3. Test stream URLs separately
4. Check API endpoint responses

## 🎓 FYP Tips

### For Your Final Year Project:
1. **Demo Channels**: Use YouTube live streams for easy demo
2. **Documentation**: Explain the architecture clearly
3. **Testing**: Test all streaming types
4. **Presentation**: Show channel switching and live chat
5. **Innovation**: Highlight the multi-channel aggregation feature

### Key Points to Emphasize:
- **Legal Compliance**: Using official YouTube APIs/embeds
- **Scalability**: Multi-channel architecture
- **User Experience**: Seamless switching and real-time features
- **Technical Depth**: HLS streaming, real-time chat, API design

---

**Note**: This system is designed for educational purposes. For production use, ensure proper licensing and content rights for all streamed content.
