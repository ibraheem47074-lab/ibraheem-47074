# PK Live News Database - Entity Relationship Diagram (ERD)

## Database Schema Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     users      │    │   categories   │    │     news       │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ id (PK)       │    │ id (PK)        │    │ id (PK)        │
│ name           │    │ name           │    │ title           │
│ email          │    │ slug           │    │ slug           │
│ password       │    │ description    │    │ content         │
│ role           │    │ image          │    │ excerpt         │
│ status         │    │ status         │    │ image           │
│ image          │    │ created_at     │    │ video_url       │
│ phone          │    └─────────────────┘    │ category_id (FK)│
│ bio            │                         │    │ author_id (FK)  │
│ created_at     │                         │    │ status          │
│ updated_at     │                         │    │ is_breaking     │
└─────────────────┘                         │    │ views           │
                                            │    │ published_at    │
                                            │    │ created_at      │
                                            │    │ updated_at      │
                                            └─────────────────┘
                                                    │
                                                    │
┌─────────────────┐    ┌─────────────────┐    │    ┌─────────────────┐
│     tags       │    │  news_tags    │    │    comments     │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ id (PK)        │    │ news_id (FK)   │    │ id (PK)        │
│ name           │    │ tag_id (FK)    │    │ news_id (FK)   │
│ color          │    └─────────────────┘    │ name           │
│ usage_count    │                         │    │ email          │
│ created_at     │                         │    │ comment        │
└─────────────────┘                         │    │ user_id (FK)   │
                                            │    │ created_at     │
                                            │    └─────────────────┘
                                            │
┌─────────────────┐    ┌─────────────────┐    │
│ notifications  │    │ news_sources   │    │
├─────────────────┤    ├─────────────────┤    │
│ id (PK)        │    │ id (PK)        │    │
│ user_id (FK)   │    │ name           │    │
│ type           │    │ url            │    │
│ title          │    │ category_id    │    │
│ message        │    │ scrape_freq    │    │
│ related_id     │    │ last_scraped   │    │
│ url            │    │ status         │    │
│ is_read        │    │ created_at     │    │
│ is_email_sent  │    └─────────────────┘    │
│ priority       │                         │    │
│ expires_at     │                         │    │
│ created_at     │                         │    │
│ read_at        │                         │    │
└─────────────────┘                         │    │
                                            │    │
                                            │    │
┌─────────────────┐    ┌─────────────────┐    │
│ bookmarks      │    │ analytics      │    │
├─────────────────┤    ├─────────────────┤    │
│ id (PK)        │    │ id (PK)        │    │
│ user_id (FK)   │    │ news_id (FK)   │    │
│ news_id (FK)   │    │ views          │    │
│ created_at     │    │ read_time      │    │
└─────────────────┘    │ date           │    │
                                            │ user_agent      │    │
                                            │ ip_address     │    │
                                            │ created_at     │    │
                                            └─────────────────┘
                                            │
                                            │
┌─────────────────┐    ┌─────────────────┐    │
│ live_stream   │    │ editions      │    │
├─────────────────┤    ├─────────────────┤    │
│ id (PK)        │    │ id (PK)        │    │
│ title          │    │ name           │    │
│ description    │    │ description    │    │
│ stream_url     │    │ status         │    │
│ is_live        │    │ created_at     │    │
│ viewers_count  │    │ updated_at     │    │
│ created_at     │    └─────────────────┘    │
│ updated_at     │                         │    │
└─────────────────┘                         │    │
                                            │    │
                                            │    │
┌─────────────────┐    ┌─────────────────┐    │
│ polls         │    │ ads           │    │
├─────────────────┤    ├─────────────────┤    │
│ id (PK)        │    │ id (PK)        │    │
│ question       │    │ title          │    │
│ options       │    │ image_url     │    │
│ total_votes   │    │ redirect_url   │    │
│ status        │    │ position       │    │
│ created_at     │    │ status         │    │
│ expires_at     │    │ created_at     │    │
│ updated_at     │    │ updated_at     │    │
└─────────────────┘    └─────────────────┘    │
                                            │
                                            │
┌─────────────────┐    ┌─────────────────┐    │
│ deployment    │    │ email_queue   │    │
├─────────────────┤    ├─────────────────┤    │
│ id (PK)        │    │ id (PK)        │    │
│ version       │    │ to_email      │    │
│ status        │    │ subject        │    │
│ deployed_by   │    │ message        │    │
│ deployed_at    │    │ template       │    │
│ notes         │    │ variables      │    │
│ created_at     │    │ priority       │    │
│ updated_at     │    │ attempts       │    │
└─────────────────┘    │ max_attempts   │    │
                                            │ sent_at        │    │
                                            │ next_attempt   │    │
                                            │ created_at     │    │
                                            └─────────────────┘
```

## Relationship Lines

```
users.id ──┬─ news.author_id
            │
            ├─ comments.user_id
            │
            ├─ bookmarks.user_id
            │
            ├─ notifications.user_id
            │
            └─ deployment.deployed_by

categories.id ──┬─ news.category_id
                │
                └─ news_sources.category_id

news.id ──┬─ news_tags.news_id
          │
          ├─ comments.news_id
          │
          ├─ bookmarks.news_id
          │
          └─ analytics.news_id

tags.id ──┬─ news_tags.tag_id
```

## Key Relationships

### 1. **Core Content Management**
- **users** → **news** (one-to-many: author_id)
- **categories** → **news** (one-to-many: category_id)
- **news** ↔ **tags** (many-to-many via news_tags)

### 2. **User Interactions**
- **users** → **comments** (one-to-many: user_id)
- **users** → **bookmarks** (one-to-many: user_id)
- **users** → **notifications** (one-to-many: user_id)

### 3. **Content Analytics**
- **news** → **analytics** (one-to-many: news_id)
- **news** → **comments** (one-to-many: news_id)

### 4. **System Features**
- **users** → **deployment** (one-to-many: deployed_by)
- **categories** → **news_sources** (one-to-many: category_id)

## Table Details

### Primary Tables
1. **users** - User management and authentication
2. **categories** - News categorization
3. **news** - Main content storage
4. **tags** - Content tagging system

### Relationship Tables
1. **news_tags** - Many-to-many relationship between news and tags
2. **comments** - User comments on news articles
3. **bookmarks** - User saved articles
4. **notifications** - System notifications
5. **analytics** - News view tracking

### Feature Tables
1. **news_sources** - RSS/Website sources for scraping
2. **editions** - News editions/collections
3. **live_stream** - Live streaming data
4. **polls** - User polls system
5. **ads** - Advertisement management
6. **deployment** - Version deployment tracking
7. **email_queue** - Email notification queue

## Data Flow

```
Content Creation:
users → news → categories
news → news_tags ← tags

User Interaction:
users → comments ← news
users → bookmarks ← news
users → notifications

Analytics:
news → analytics
news → comments

System Operations:
users → deployment
categories → news_sources
```

## Foreign Key Constraints

- All `*_id` fields reference primary keys
- ON DELETE CASCADE for related data
- ON UPDATE CASCADE for data integrity
- NULL values allowed for optional relationships

## Indexes

- Primary keys automatically indexed
- Foreign keys automatically indexed
- Additional indexes on frequently queried fields:
  - news.slug, news.status, news.created_at
  - users.email, users.status
  - categories.slug
  - notifications.is_read, notifications.created_at
