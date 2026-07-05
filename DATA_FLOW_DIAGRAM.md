# PK Live News - Data Flow Diagram

## 📊 Overall System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                        PK LIVE NEWS SYSTEM                          │
├─────────────────────────────────────────────────────────────────────┤
│  FRONTEND (Bootstrap 5 + Vanilla JS)                                │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐   │
│  │   Public    │ │   Admin     │ │   Live       │ │   API       │   │
│  │   Website   │ │   Panel     │ │   Streaming  │ │   Endpoints │   │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      BACKEND (Pure PHP)                             │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐   │
│  │   Content   │ │   User      │ │   Live      │ │   Analytics │   │
│  │ Management  │ │ Management  │ Streaming    │   Engine    │   │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    DATABASE (MySQL/MariaDB)                          │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐   │
│  │   News      │ │   Users     │ │   Live      │ │   Analytics │   │
│  │   Data      │ │   Data      │ Streaming    │   Data      │   │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
```

## 🔄 Detailed Data Flow Process

### 1. **News Content Flow**
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Admin Panel   │───▶│   Content       │───▶│   Database      │
│   (Create/Edit) │    │   Processing    │    │   Storage       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Validation    │    │   Sentiment     │    │   Indexing      │
│   & Security    │    │   Analysis      │    │   & SEO         │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 ▼
                    ┌─────────────────┐
                    │   Public        │
                    │   Website       │
                    │   Display       │
                    └─────────────────┘
```

### 2. **User Authentication Flow**
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Login Page    │───▶│   Session       │───▶│   Permission    │
│   (Input)       │    │   Management    │    │   Check         │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Validation    │    │   User Role     │    │   Dashboard     │
│   (Credentials) │    │   Assignment    │    │   Access        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### 3. **Live Streaming Data Flow**
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Stream        │───▶│   Deployment    │───▶│   Live          │
│   Configuration │    │   Criteria      │    │   Broadcasting  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Camera        │    │   Health        │    │   Real-time     │
│   Setup         │    │   Monitoring    │    │   Viewer Count  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 ▼
                    ┌─────────────────┐
                    │   WebSocket     │
                    │   / SSE         │
                    │   Updates       │
                    └─────────────────┘
```

### 4. **Analytics & Monitoring Flow**
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   User Actions  │───▶│   Event         │───▶│   Analytics     │
│   (Views/Clicks)│    │   Tracking      │    │   Database      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   JavaScript    │    │   Server-side    │    │   Dashboard     │
│   Events        │    │   Processing    │    │   Visualization │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🗄️ Database Schema Flow

### **News Content Tables**
```
news ──┐
       ├── categories (Many-to-One)
       ├── users (author_id)
       ├── tags (Many-to-Many via news_tags)
       ├── comments (One-to-Many)
       ├── analytics (One-to-Many)
       └── editions (Many-to-Many)
```

### **User Management Tables**
```
users ──┐
        ├── news (author_id)
        ├── comments (user_id)
        ├── bookmarks (user_id)
        ├── notifications (user_id)
        └── polls (user_id)
```

## 📡 API Data Flow

### **RESTful API Endpoints**
```
/api/ ├── breaking-news.php     (GET - Breaking news updates)
      ├── notifications.php     (GET/POST - Notification management)
      ├── live-viewers.php      (GET - Live viewer count)
      ├── comments.php         (GET/POST - Comment management)
      └── polls.php            (GET/POST - Poll management)
```

### **Data Exchange Format**
```
Request Format:
├── Method (GET/POST/PUT/DELETE)
├── Headers (Content-Type, Authorization)
├── Parameters (Query/Body)
└── Session Data

Response Format:
├── Status Code (200/400/404/500)
├── Headers (Content-Type: application/json)
├── Body (JSON data)
│   ├── success (boolean)
│   ├── data (array/object)
│   ├── message (string)
│   └── error (string - if applicable)
└── Session Updates
```

## 🔔 Real-time Data Flow

### **WebSocket/SSE Implementation**
```
Client ──┐
         ├── Connection Request
         ├── Event Subscription
         └── Message Reception
Server ──┐
         ├── Connection Management
         ├── Event Broadcasting
         └── Client Filtering
Events ──┐
         ├── Live Stream Updates
         ├── Breaking News
         ├── Viewer Count Changes
         └── System Alerts
```

## 🛡️ Security Data Flow

### **Input Validation & Sanitization**
```
User Input ──┐
             ├── Client-side Validation
             ├── Server-side Validation
             ├── SQL Injection Prevention
             ├── XSS Prevention
             └── CSRF Protection
Database ──┘
```

### **Authentication & Authorization**
```
Login Request ──┐
                ├── Credential Verification
                ├── Session Creation
                ├── Role Assignment
                └── Permission Check
Resource Access ──┘
```

## 📊 File Upload Data Flow

```
User Upload ──┐
              ├── File Validation (Type/Size)
              ├── Virus Scanning
              ├── Image Processing
              ├── Storage (uploads/ directory)
              └── Database Reference (file path)
Display ──┘
```

## 🔄 Cache & Performance Flow

```
Database Query ──┐
                 ├── Result Caching
                 ├── Page Caching
                 ├── Static Asset Caching
                 └── CDN Integration
Client Display ──┘
```

## 📱 Mobile Responsiveness Flow

```
Device Detection ──┐
                   ├── Responsive CSS (Bootstrap)
                   ├── Adaptive Images
                   ├── Touch Events
                   └── Performance Optimization
User Experience ──┘
```

---

## 🎯 Key Data Flow Patterns

### **1. Request-Response Cycle**
```
Client Request → Server Processing → Database Query → Response Generation → Client Display
```

### **2. Real-time Updates**
```
Event Trigger → Server Processing → WebSocket/SSE Broadcast → Client Update
```

### **3. Content Management**
```
Admin Input → Validation → Database Storage → Public Display → Analytics Tracking
```

### **4. User Interaction**
```
User Action → Event Capture → Analytics Recording → Dashboard Update
```

This data flow diagram illustrates how information moves through the PK Live News system, from user input to database storage and back to user display, including all the processing, validation, and real-time updates that occur along the way.
