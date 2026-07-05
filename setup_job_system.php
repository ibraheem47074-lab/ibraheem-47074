<?php
require_once 'config/database.php';

/**
 * One-time setup script for the Job Portal System
 * This script will:
 * 1. Add job categories and subcategories
 * 2. Add job-specific fields to news table
 * 3. Create necessary database tables
 * 4. Set up the complete job system
 */

echo "Setting up PK Live News Job Portal System...\n";
echo "==========================================\n\n";

// Step 1: Add parent_id column to categories table
echo "Step 1: Updating categories table...\n";
$add_parent_column = "ALTER TABLE categories ADD COLUMN IF NOT EXISTS parent_id INT DEFAULT NULL";
if (mysqli_query($conn, $add_parent_column)) {
    echo "✓ Added parent_id column to categories table\n";
} else {
    echo "✗ Error adding parent_id column: " . mysqli_error($conn) . "\n";
}

// Add foreign key constraint
$add_fk = "ALTER TABLE categories ADD CONSTRAINT IF NOT EXISTS fk_category_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL";
if (mysqli_query($conn, $add_fk)) {
    echo "✓ Added foreign key constraint\n";
} else {
    echo "✗ Error adding foreign key: " . mysqli_error($conn) . "\n";
}

// Step 2: Add job-specific columns to news table
echo "\nStep 2: Updating news table with job fields...\n";
$job_columns = [
    "company_name VARCHAR(255) DEFAULT NULL",
    "job_location VARCHAR(255) DEFAULT NULL", 
    "salary VARCHAR(255) DEFAULT NULL",
    "last_date_to_apply DATE DEFAULT NULL",
    "job_type ENUM('Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship') DEFAULT NULL",
    "apply_url VARCHAR(500) DEFAULT NULL",
    "requirements TEXT DEFAULT NULL",
    "is_job_posting BOOLEAN DEFAULT FALSE"
];

foreach ($job_columns as $column) {
    $alter_query = "ALTER TABLE news ADD COLUMN IF NOT EXISTS $column";
    if (mysqli_query($conn, $alter_query)) {
        echo "✓ Added column: $column\n";
    } else {
        echo "✗ Error adding column $column: " . mysqli_error($conn) . "\n";
    }
}

// Step 3: Create job applications table
echo "\nStep 3: Creating job applications table...\n";
$create_applications_table = "
CREATE TABLE IF NOT EXISTS job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    position VARCHAR(255),
    cover_letter TEXT,
    resume_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES news(id) ON DELETE CASCADE,
    INDEX idx_job_id (job_id),
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_applications_table)) {
    echo "✓ Created job_applications table\n";
} else {
    echo "✗ Error creating job_applications table: " . mysqli_error($conn) . "\n";
}

// Step 4: Create Jobs category and subcategories
echo "\nStep 4: Creating job categories...\n";

// Check if Jobs category already exists
$check_jobs = mysqli_query($conn, "SELECT id FROM categories WHERE slug = 'jobs'");
if (mysqli_num_rows($check_jobs) == 0) {
    // Insert main Jobs category
    $insert_jobs = "INSERT INTO categories (name, slug, description, status, parent_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_jobs);
    $name = 'Jobs';
    $slug = 'jobs';
    $description = 'All job opportunities and career updates';
    $status = 'active';
    $parent_id = null;
    
    if (mysqli_stmt_bind_param($stmt, 'ssssi', $name, $slug, $description, $status, $parent_id)) {
        if (mysqli_stmt_execute($stmt)) {
            $jobs_category_id = mysqli_insert_id($conn);
            echo "✓ Created main Jobs category (ID: $jobs_category_id)\n";
            
            // Insert subcategories
            $subcategories = [
                ['Govt Jobs', 'govt-jobs', 'Government job opportunities and vacancies'],
                ['Private Jobs', 'private-jobs', 'Private sector job opportunities'],
                ['Overseas Jobs', 'overseas-jobs', 'International job opportunities'],
                ['Freelance Jobs', 'freelance-jobs', 'Freelance and remote work opportunities']
            ];
            
            foreach ($subcategories as $subcat) {
                $insert_subcat = "INSERT INTO categories (name, slug, description, status, parent_id) VALUES (?, ?, ?, ?, ?)";
                $stmt_sub = mysqli_prepare($conn, $insert_subcat);
                mysqli_stmt_bind_param($stmt_sub, 'ssssi', $subcat[0], $subcat[1], $subcat[2], $status, $jobs_category_id);
                
                if (mysqli_stmt_execute($stmt_sub)) {
                    echo "✓ Created subcategory: {$subcat[0]}\n";
                } else {
                    echo "✗ Error creating subcategory {$subcat[0]}: " . mysqli_error($conn) . "\n";
                }
            }
        } else {
            echo "✗ Error creating Jobs category: " . mysqli_error($conn) . "\n";
        }
    }
} else {
    echo "✓ Jobs category already exists\n";
}

// Step 5: Create upload directories
echo "\nStep 5: Creating upload directories...\n";
$directories = [
    'uploads/resumes',
    'uploads/jobs'
];

foreach ($directories as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (!is_dir($full_path)) {
        if (mkdir($full_path, 0755, true)) {
            echo "✓ Created directory: $dir\n";
        } else {
            echo "✗ Error creating directory: $dir\n";
        }
    } else {
        echo "✓ Directory already exists: $dir\n";
    }
}

// Step 6: Add menu item to navigation (if needed)
echo "\nStep 6: Navigation setup...\n";
echo "✓ Manual step: Add 'Jobs' link to your website navigation\n";
echo "  - Link to: jobs.php\n";
echo "  - Add to main menu for easy access\n";

// Step 7: Create sample job posting (optional)
echo "\nStep 7: Creating sample job posting...\n";
$sample_job = [
    'title' => 'Sample Job Position - Web Developer',
    'slug' => 'sample-job-web-developer-' . time(),
    'content' => 'We are looking for a talented Web Developer to join our team. This is an excellent opportunity for someone who is passionate about web development and wants to work in a dynamic environment.',
    'excerpt' => 'Exciting opportunity for a Web Developer to join our growing team.',
    'category_id' => $jobs_category_id ?? 1,
    'author_id' => 1,
    'status' => 'published',
    'company_name' => 'Sample Company',
    'job_location' => 'Lahore',
    'salary' => '50,000 - 80,000 PKR',
    'job_type' => 'Full-time',
    'apply_url' => 'https://example.com/apply',
    'requirements' => 'Bachelor\'s degree in Computer Science or related field. 2+ years of experience in web development. Strong knowledge of HTML, CSS, JavaScript, and PHP.',
    'is_job_posting' => 1
];

$insert_sample = "INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, company_name, job_location, salary, job_type, apply_url, requirements, is_job_posting, created_at, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

$stmt = mysqli_prepare($conn, $insert_sample);
mysqli_stmt_bind_param($stmt, 'ssssisssssssi', 
    $sample_job['title'], $sample_job['slug'], $sample_job['content'], $sample_job['excerpt'],
    $sample_job['category_id'], $sample_job['author_id'], $sample_job['status'],
    $sample_job['company_name'], $sample_job['job_location'], $sample_job['salary'],
    $sample_job['job_type'], $sample_job['apply_url'], $sample_job['requirements'],
    $sample_job['is_job_posting']
);

if (mysqli_stmt_execute($stmt)) {
    echo "✓ Created sample job posting\n";
} else {
    echo "✗ Error creating sample job: " . mysqli_error($conn) . "\n";
}

// Final summary
echo "\n==========================================\n";
echo "Job Portal System Setup Complete!\n";
echo "==========================================\n\n";

echo "Next Steps:\n";
echo "1. Visit admin/add-job-categories.php to set up categories (if not done automatically)\n";
echo "2. Visit admin/add-job.php to post new jobs\n";
echo "3. Visit jobs.php to view job listings\n";
echo "4. Add 'Jobs' link to your website navigation menu\n";
echo "5. Set up cron job for automatic RSS imports:\n";
echo "   php api/import_jobs_rss.php\n\n";

echo "Files Created/Modified:\n";
echo "- admin/add-job.php (Job posting form)\n";
echo "- admin/add-job-categories.php (Category setup)\n";
echo "- job.php (Individual job display)\n";
echo "- jobs.php (Job listings page)\n";
echo "- components/job_card.php (Job card component)\n";
echo "- api/submit_application.php (Application handler)\n";
echo "- api/import_jobs_rss.php (RSS importer)\n\n";

echo "Database Changes:\n";
echo "- Added parent_id column to categories table\n";
echo "- Added job-specific columns to news table\n";
echo "- Created job_applications table\n";
echo "- Created Jobs category with subcategories\n\n";

echo "Your Job Portal is now ready to use! 🎉\n";
?>
