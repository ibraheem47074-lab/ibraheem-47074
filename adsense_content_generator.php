<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

// AdSense Content Generator - Creates sample high-quality articles
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Generator for AdSense Approval</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h1 class="mb-0"><i class="fas fa-pen-fancy me-2"></i>AdSense Content Generator</h1>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This tool helps you create high-quality, original articles to meet AdSense content requirements.
                        </div>

                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $title = clean_input($_POST['title']);
                            $content = clean_input($_POST['content']);
                            $category_id = (int)$_POST['category_id'];
                            $image_url = clean_input($_POST['image_url']);
                            
                            // Generate slug
                            $slug = create_slug($title);
                            
                            // Check if slug exists
                            $check = mysqli_query($conn, "SELECT id FROM news WHERE slug = '$slug'");
                            if (mysqli_num_rows($check) > 0) {
                                $slug .= '-' . time();
                            }
                            
                            // Check which columns exist
                            $image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
                            $source_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source'");
                            $published_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'published_at'");
                            $created_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'created_at'");
                            
                            $has_image = mysqli_num_rows($image_check) > 0;
                            $has_source = mysqli_num_rows($source_check) > 0;
                            $has_published = mysqli_num_rows($published_check) > 0;
                            $has_created = mysqli_num_rows($created_check) > 0;
                            
                            // Build dynamic INSERT query based on available columns
                            $columns = ['title', 'slug', 'content', 'category_id', 'status'];
                            $placeholders = ['?', '?', '?', '?', '?'];
                            $types = 'sssis';
                            $values = [$title, $slug, $content, $category_id, 'published'];
                            
                            if ($has_image) {
                                $columns[] = 'image_url';
                                $placeholders[] = '?';
                                $types .= 's';
                                $values[] = $image_url;
                            }
                            
                            if ($has_source) {
                                $columns[] = 'source';
                                $placeholders[] = '?';
                                $types .= 's';
                                $values[] = 'PK-LIVE';
                            }
                            
                            if ($has_published) {
                                $columns[] = 'published_at';
                                $placeholders[] = 'NOW()';
                            }
                            
                            if ($has_created) {
                                $columns[] = 'created_at';
                                $placeholders[] = 'NOW()';
                            }
                            
                            $query = "INSERT INTO news (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                            
                            // Prepare statement
                            $stmt = mysqli_prepare($conn, $query);
                            
                            // Bind parameters using references
                            $refs = [];
                            $refs[] = $stmt;
                            $refs[] = $types;
                            foreach ($values as &$value) {
                                $refs[] = &$value;
                            }
                            unset($value);
                            
                            call_user_func_array('mysqli_stmt_bind_param', $refs);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                echo '<div class="alert alert-success">✅ Article published successfully!</div>';
                            } else {
                                echo '<div class="alert alert-danger">❌ Error: ' . mysqli_error($conn) . '</div>';
                            }
                        }
                        
                        // Get categories
                        $categories = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name");
                        ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="title" class="form-label">Article Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       placeholder="Enter a compelling, descriptive title">
                            </div>
                            
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image_url" class="form-label">Image URL *</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" required
                                       placeholder="https://example.com/image.jpg">
                                <small class="text-muted">Use high-quality, relevant images (min 800x600px)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Article Content *</label>
                                <textarea class="form-control" id="content" name="content" rows="15" required
                                          placeholder="Write your article here (minimum 500 words recommended)"></textarea>
                                <small class="text-muted">Articles should be 500+ words for AdSense approval</small>
                            </div>
                            
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateSampleContent()">
                                    <i class="fas fa-magic me-2"></i>Generate Sample Content
                                </button>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Publish Article
                            </button>
                        </form>

                        <hr>
                        
                        <h5><i class="fas fa-lightbulb me-2"></i>Sample Article Templates</h5>
                        
                        <div class="accordion" id="templatesAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#template1">
                                        Technology News Template
                                    </button>
                                </h2>
                                <div id="template1" class="accordion-collapse collapse" data-bs-parent="#templatesAccordion">
                                    <div class="accordion-body">
                                        <h6>Title:</h6>
                                        <p class="text-muted">Pakistan's Tech Sector Sees Record Growth in 2024</p>
                                        <h6>Content:</h6>
                                        <textarea class="form-control" rows="8" readonly>Pakistan's technology sector has experienced unprecedented growth in 2024, with startups raising over $500 million in funding. The government's Digital Pakistan initiative has played a crucial role in this expansion, providing incentives for tech companies and creating a more favorable business environment.

Key developments include the rise of fintech solutions, with companies like JazzCash and Easypaisa leading digital payment adoption. E-commerce platforms have also seen significant growth, with Daraz and other local players expanding their reach across the country.

The software export industry has reached new heights, with IT exports crossing $3 billion for the first time. This growth has been driven by increased demand for Pakistani IT professionals globally, particularly in the United States and Europe.

However, challenges remain, including infrastructure limitations and the need for more skilled professionals. The government and private sector are working together to address these issues through various training programs and infrastructure development projects.

Industry experts predict that this growth trend will continue in the coming years, with Pakistan potentially becoming a major tech hub in the region.</textarea>
                                        <button class="btn btn-sm btn-primary mt-2" onclick="useTemplate(1)">Use This Template</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#template2">
                                        Business & Economy Template
                                    </button>
                                </h2>
                                <div id="template2" class="accordion-collapse collapse" data-bs-parent="#templatesAccordion">
                                    <div class="accordion-body">
                                        <h6>Title:</h6>
                                        <p class="text-muted">Pakistan's Economy Shows Signs of Recovery Amid Global Challenges</p>
                                        <h6>Content:</h6>
                                        <textarea class="form-control" rows="8" readonly>Pakistan's economy is showing promising signs of recovery in 2024, despite global economic challenges. The country's GDP growth has reached 3.5%, exceeding initial projections, while inflation has started to stabilize.

The agricultural sector has performed exceptionally well, with bumper crops contributing to food security and export earnings. The textile industry, a major export earner, has also seen increased demand in international markets.

Foreign remittances have remained strong, providing crucial support to the country's foreign exchange reserves. The overseas Pakistani community continues to play a vital role in the economy through their remittances.

The government's economic reforms, including fiscal discipline and structural adjustments, have started to yield positive results. The State Bank of Pakistan's monetary policy has also contributed to economic stability.

However, challenges persist, including energy shortages and the need for continued structural reforms. International financial institutions have expressed cautious optimism about Pakistan's economic trajectory.

Business leaders are calling for continued focus on ease of doing business and investment-friendly policies to sustain this recovery momentum.</textarea>
                                        <button class="btn btn-sm btn-primary mt-2" onclick="useTemplate(2)">Use This Template</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#template3">
                                        Health & Education Template
                                    </button>
                                </h2>
                                <div id="template3" class="accordion-collapse collapse" data-bs-parent="#templatesAccordion">
                                    <div class="accordion-body">
                                        <h6>Title:</h6>
                                        <p class="text-muted">Pakistan Makes Strides in Healthcare and Education Sector Development</p>
                                        <h6>Content:</h6>
                                        <textarea class="form-control" rows="8" readonly>Pakistan has made significant progress in healthcare and education sectors in 2024, with several new initiatives launched to improve access and quality of services.

In healthcare, the government has expanded the Sehat Sahulat Program, providing health insurance coverage to millions of additional families. New hospitals and healthcare facilities have been established in underserved areas, particularly in rural regions.

The country has also made strides in combating infectious diseases, with successful vaccination campaigns and improved disease surveillance systems. Mental health awareness has increased, with new programs launched to address this often-overlooked aspect of healthcare.

In education, enrollment rates have improved, particularly at the primary level. The government's focus on technical and vocational education has started to bear fruit, with more youth acquiring marketable skills.

Higher education institutions have strengthened their research capabilities, with several universities achieving international recognition. Online learning platforms have expanded access to education, especially in remote areas.

Despite these achievements, challenges remain, including quality concerns and regional disparities. Continued investment and policy focus are needed to sustain and build upon these gains.</textarea>
                                        <button class="btn btn-sm btn-primary mt-2" onclick="useTemplate(3)">Use This Template</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generateSampleContent() {
            const titles = [
                'Breaking: Major Policy Change Announced by Government',
                'Local Community Initiative Transforms Neighborhood',
                'New Infrastructure Project Set to Boost Regional Economy',
                'Education Reform Shows Promising Results in Pilot Program',
                'Healthcare Access Improves in Rural Areas'
            ];
            
            const contents = [
                'In a significant development, the government has announced a new policy that aims to address long-standing issues in the sector. The policy, which comes after months of consultation with stakeholders, is expected to bring substantial changes to how services are delivered.\n\nKey provisions of the policy include increased funding, streamlined processes, and greater accountability measures. Experts have welcomed the move, noting that it addresses many of the concerns raised by industry leaders and the public alike.\n\nImplementation will begin immediately, with full rollout expected within the next six months. The government has assured that all necessary support will be provided to ensure smooth transition.\n\nThis development comes at a crucial time, as the sector has been facing several challenges in recent years. Stakeholders are optimistic that this policy will help overcome these hurdles and set the sector on a path to sustainable growth.',
                'A remarkable community initiative has transformed a once-neglected neighborhood into a thriving example of grassroots development. The project, led by local residents with support from various organizations, has brought about significant positive changes.\n\nThe initiative included cleaning drives, plantation of trees, establishment of community centers, and creation of safe spaces for children and elderly residents. What started as a small effort has grown into a comprehensive development program.\n\nThe success of this initiative has attracted attention from other communities, with several expressing interest in replicating the model. Local authorities have also taken note and have pledged support for similar initiatives in other areas.\n\nThis project demonstrates the power of community engagement and collective action. It serves as an inspiring example of what can be achieved when people come together with a shared vision and determination.',
                'A major new infrastructure project is set to boost the regional economy, creating jobs and improving connectivity. The project, which has been in planning for several years, has finally received approval and construction is expected to begin soon.\n\nThe project includes construction of roads, bridges, and other critical infrastructure that will connect previously isolated areas to major economic centers. This improved connectivity is expected to facilitate trade, reduce transportation costs, and attract new businesses to the region.\n\nEconomic analysts predict that the project could create thousands of jobs during construction and many more indirect jobs through increased economic activity. Local businesses are already preparing for the opportunities that this development will bring.\n\nThe project is part of a broader infrastructure development program aimed at reducing regional disparities and promoting balanced economic growth across the country.'
            ];
            
            document.getElementById('title').value = titles[Math.floor(Math.random() * titles.length)];
            document.getElementById('content').value = contents[Math.floor(Math.random() * contents.length)];
        }
        
        function useTemplate(num) {
            const templates = {
                1: {
                    title: "Pakistan's Tech Sector Sees Record Growth in 2024",
                    content: "Pakistan's technology sector has experienced unprecedented growth in 2024, with startups raising over $500 million in funding. The government's Digital Pakistan initiative has played a crucial role in this expansion, providing incentives for tech companies and creating a more favorable business environment.\n\nKey developments include the rise of fintech solutions, with companies like JazzCash and Easypaisa leading digital payment adoption. E-commerce platforms have also seen significant growth, with Daraz and other local players expanding their reach across the country.\n\nThe software export industry has reached new heights, with IT exports crossing $3 billion for the first time. This growth has been driven by increased demand for Pakistani IT professionals globally, particularly in the United States and Europe.\n\nHowever, challenges remain, including infrastructure limitations and the need for more skilled professionals. The government and private sector are working together to address these issues through various training programs and infrastructure development projects.\n\nIndustry experts predict that this growth trend will continue in the coming years, with Pakistan potentially becoming a major tech hub in the region."
                },
                2: {
                    title: "Pakistan's Economy Shows Signs of Recovery Amid Global Challenges",
                    content: "Pakistan's economy is showing promising signs of recovery in 2024, despite global economic challenges. The country's GDP growth has reached 3.5%, exceeding initial projections, while inflation has started to stabilize.\n\nThe agricultural sector has performed exceptionally well, with bumper crops contributing to food security and export earnings. The textile industry, a major export earner, has also seen increased demand in international markets.\n\nForeign remittances have remained strong, providing crucial support to the country's foreign exchange reserves. The overseas Pakistani community continues to play a vital role in the economy through their remittances.\n\nThe government's economic reforms, including fiscal discipline and structural adjustments, have started to yield positive results. The State Bank of Pakistan's monetary policy has also contributed to economic stability.\n\nHowever, challenges persist, including energy shortages and the need for continued structural reforms. International financial institutions have expressed cautious optimism about Pakistan's economic trajectory.\n\nBusiness leaders are calling for continued focus on ease of doing business and investment-friendly policies to sustain this recovery momentum."
                },
                3: {
                    title: "Pakistan Makes Strides in Healthcare and Education Sector Development",
                    content: "Pakistan has made significant progress in healthcare and education sectors in 2024, with several new initiatives launched to improve access and quality of services.\n\nIn healthcare, the government has expanded the Sehat Sahulat Program, providing health insurance coverage to millions of additional families. New hospitals and healthcare facilities have been established in underserved areas, particularly in rural regions.\n\nThe country has also made strides in combating infectious diseases, with successful vaccination campaigns and improved disease surveillance systems. Mental health awareness has increased, with new programs launched to address this often-overlooked aspect of healthcare.\n\nIn education, enrollment rates have improved, particularly at the primary level. The government's focus on technical and vocational education has started to bear fruit, with more youth acquiring marketable skills.\n\nHigher education institutions have strengthened their research capabilities, with several universities achieving international recognition. Online learning platforms have expanded access to education, especially in remote areas.\n\nDespite these achievements, challenges remain, including quality concerns and regional disparities. Continued investment and policy focus are needed to sustain and build upon these gains."
                }
            };
            
            document.getElementById('title').value = templates[num].title;
            document.getElementById('content').value = templates[num].content;
        }
    </script>
</body>
</html>
