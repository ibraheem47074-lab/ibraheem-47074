-- Update Editor role with only the specified permissions
UPDATE admin_roles 
SET permissions = '["news_articles_manage","content_edit","comments_manage","polls_manage","analytics_view"]',
    description = 'Editor with content management and publishing permissions'
WHERE role_name = 'Editor';

-- Add new permission entries if they don't exist
INSERT IGNORE INTO admin_permissions (permission_key, permission_name, permission_group, description) VALUES
('news_articles_manage', 'Manage News Articles', 'content', 'Permission to manage news articles'),
('polls_manage', 'Manage Polls', 'content', 'Permission to manage polls and surveys');
