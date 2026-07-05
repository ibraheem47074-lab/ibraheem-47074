ALTER TABLE news_editions ADD COLUMN edition_type ENUM('morning','evening','breaking','special','weekend','regional') NOT NULL DEFAULT 'morning' AFTER edition_name;
