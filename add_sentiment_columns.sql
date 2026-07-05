-- Add sentiment analysis columns to news table if they don't exist
ALTER TABLE news ADD COLUMN IF NOT EXISTS sentiment_score decimal(3,2) DEFAULT 0.00;
ALTER TABLE news ADD COLUMN IF NOT EXISTS sentiment_label varchar(20) DEFAULT 'neutral';
