-- Add evidence_type column to role_applications table
ALTER TABLE role_applications 
ADD COLUMN IF NOT EXISTS evidence_type ENUM('cv_resume', 'portfolio', 'certificates', 'work_samples', 'references', 'publications', 'other') DEFAULT 'cv_resume' AFTER cv_file_size;

-- Add evidence_description column for additional details
ALTER TABLE role_applications 
ADD COLUMN IF NOT EXISTS evidence_description TEXT DEFAULT NULL AFTER evidence_type;

-- Add evidence_files column to store multiple file references (JSON array)
ALTER TABLE role_applications 
ADD COLUMN IF NOT EXISTS evidence_files TEXT DEFAULT NULL AFTER evidence_description;
