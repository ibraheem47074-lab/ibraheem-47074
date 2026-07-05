<?php
require_once 'config/database.php';

echo "<h2>Creating Professional News Articles</h2>";

// Professional news articles for Pakistani audience
$articles = [
    [
        'title' => 'Pakistan Stock Exchange Shows Record Growth Amid Economic Recovery',
        'slug' => 'pakistan-stock-exchange-record-growth-economic-recovery',
        'content' => '<h2>KARACHI - Pakistan Stock Exchange (PSX) witnessed unprecedented growth today as the KSE-100 index surged by 1,200 points, marking the highest single-day gain in the past three years.</h2>
        
        <p>Financial analysts attribute this remarkable performance to improving economic indicators and investor confidence in the country\'s economic policies. The banking sector led the rally with major stocks showing substantial gains.</p>
        
        <blockquote>"This growth reflects the resilience of Pakistan\'s economy and the successful implementation of economic reforms," said Dr. Ahmed Khan, senior economist at the Institute of Business Administration.</blockquote>
        
        <p>The surge comes as Pakistan\'s foreign exchange reserves have shown significant improvement, reaching $16.5 billion, providing stability to the financial markets.</p>
        
        <h3>Key Highlights:</h3>
        <ul>
            <li>KSE-100 index gained 1,200 points</li>
            <li>Banking sector stocks led the rally</li>
            <li>Foreign exchange reserves at $16.5 billion</li>
            <li>Investor confidence at 3-year high</li>
        </ul>
        
        <p>Market experts predict continued positive momentum as the government announces new incentives for foreign investors and continues economic stabilization measures.</p>',
        'excerpt' => 'Pakistan Stock Exchange records historic 1,200-point surge as economic recovery gains momentum and investor confidence returns to three-year high.',
        'category_id' => 5, // Business
        'status' => 'published',
        'is_breaking' => 1,
        'featured_image' => 'uploads/news/stock-exchange.jpg',
        'author_id' => 1,
        'tags' => 'economy, stock market, business, finance'
    ],
    [
        'title' => 'Lahore Smart City Project Launched with $2 Billion Investment',
        'slug' => 'lahore-smart-city-project-2-billion-investment',
        'content' => '<h2>LAHORE - The Punjab government today unveiled the ambitious Lahore Smart City project, a $2 billion initiative set to transform Pakistan\'s cultural capital into a modern technological hub.</h2>
        
        <p>The project, spanning over 20,000 kanals, will feature state-of-the-art infrastructure, AI-powered traffic management, and sustainable energy solutions. Chief Minister Punjab Maryam Nawaz announced the project at a press conference today.</p>
        
        <blockquote>"Lahore Smart City will be a model for future urban development in Pakistan, combining our rich cultural heritage with cutting-edge technology," said the Chief Minister.</blockquote>
        
        <p>The smart city will include:
        <ul>
            <li>5G connectivity throughout the city</li>
            <li>Solar-powered street lighting and buildings</li>
            <li>AI-integrated public transportation</li>
            <li>Digital governance centers</li>
            <li>Green spaces covering 40% of the area</li>
        </ul></p>
        
        <p>Construction is set to begin in Q2 2024, with the first phase expected to complete by 2027. The project is expected to create over 50,000 jobs and attract significant foreign investment.</p>',
        'excerpt' => 'Punjab government launches $2 billion Lahore Smart City project featuring AI-powered infrastructure and sustainable technology solutions.',
        'category_id' => 6, // Technology
        'status' => 'published',
        'is_breaking' => 1,
        'featured_image' => 'uploads/news/smart-city.jpg',
        'author_id' => 1,
        'tags' => 'smart city, technology, development, punjab'
    ],
    [
        'title' => 'Pakistan Cricket Team Announces New T20 World Cup Squad',
        'slug' => 'pakistan-cricket-t20-world-cup-squad-announcement',
        'content' => '<h2>KARACHI - The Pakistan Cricket Board (PCB) today announced the 15-member squad for the upcoming T20 World Cup, featuring a mix of experienced players and emerging talent.</h2>
        
        <p>Babar Azam will continue to lead the side, with Shaheen Shah Afridi named as vice-captain. The squad includes several exciting newcomers who have impressed in domestic cricket.</p>
        
        <blockquote>"We have selected a balanced squad that can adapt to different conditions and has the firepower to win the World Cup," said PCB Chairman Mohsin Naqvi.</blockquote>
        
        <h3>Squad Highlights:</h3>
        <ul>
            <li>Babar Azam (Captain)</li>
            <li>Shaheen Shah Afridi (Vice-captain)</li>
            <li>3 debutants in the squad</li>
            <li>5 fast bowlers, 3 spinners</li>
            <li>Power-hitting finishers in the lower order</li>
        </ul>
        
        <p>Pakistan will begin their campaign against India in the tournament opener on June 1st. The team has been training intensively at the National Cricket Academy in Lahore.</p>',
        'excerpt' => 'PCB announces balanced T20 World Cup squad with Babar Azam as captain, featuring mix of experience and young talent.',
        'category_id' => 4, // Sports
        'status' => 'published',
        'is_breaking' => 0,
        'featured_image' => 'uploads/news/cricket-squad.jpg',
        'author_id' => 1,
        'tags' => 'cricket, t20 world cup, sports, pcb'
    ],
    [
        'title' => 'New Education Policy Announced: Digital Literacy Priority',
        'slug' => 'new-education-policy-digital-literacy-priority',
        'content' => '<h2>ISLAMABAD - The Federal Ministry of Education today unveiled a comprehensive new education policy that places digital literacy and technological skills at the forefront of Pakistan\'s educational framework.</h2>
        
        <p>The policy, set to be implemented from the next academic year, aims to revolutionize Pakistan\'s education system by integrating modern technology and preparing students for the digital economy.</p>
        
        <blockquote>"Our goal is to produce graduates who are not just academically sound but also technologically proficient and ready for the challenges of the 21st century," said Federal Education Minister.</blockquote>
        
        <h3>Key Policy Features:</h3>
        <ul>
            <li>Computer labs in all government schools</li>
            <li>Digital literacy curriculum from grade 6</li>
            <li>Teacher training programs for digital education</li>
            <li>Partnerships with tech companies for skill development</li>
            <li>E-learning platforms for remote areas</li>
        </ul>
        
        <p>The government has allocated Rs. 50 billion for the initial implementation phase, with plans to expand the program over the next five years.</p>',
        'excerpt' => 'Federal government announces revolutionary education policy prioritizing digital literacy and technological skills development.',
        'category_id' => 7, // Education
        'status' => 'published',
        'is_breaking' => 0,
        'featured_image' => 'uploads/news/education-policy.jpg',
        'author_id' => 1,
        'tags' => 'education, digital literacy, policy, technology'
    ],
    [
        'title' => 'Karachi Beach Cleanup Drive Removes 50 Tons of Waste',
        'slug' => 'karachi-beach-cleanup-50-tons-waste-removed',
        'content' => '<h2>KARACHI - A massive beach cleanup drive organized by the Sindh government and environmental NGOs successfully removed over 50 tons of waste from Karachi\'s coastline over the weekend.</h2>
        
        <p>The initiative, part of the "Clean Karachi, Green Karachi" campaign, saw participation from over 5,000 volunteers, including students, corporate employees, and local residents.</p>
        
        <blockquote>"This cleanup drive demonstrates what we can achieve when the community comes together for a noble cause," said Sindh Environment Minister.</blockquote>
        
        <p>The cleanup focused on Clifton Beach, Sea View, and Hawks Bay, removing plastic waste, fishing nets, and other debris that had accumulated over months.</p>
        
        <h3>Cleanup Achievements:</h3>
        <ul>
            <li>50+ tons of waste removed</li>
            <li>5,000+ volunteers participated</li>
            <li>8 km of coastline cleaned</li>
            <li>Recycled 15 tons of plastic waste</li>
            <li>Planted 1,000 mangrove saplings</li>
        </ul>
        
        <p>The Sindh government has announced plans to make this a monthly initiative and is installing waste management systems along the coastline.</p>',
        'excerpt' => 'Massive beach cleanup in Karachi removes 50 tons of waste with 5,000 volunteers participating in environmental conservation effort.',
        'category_id' => 8, // Environment
        'status' => 'published',
        'is_breaking' => 0,
        'featured_image' => 'uploads/news/beach-cleanup.jpg',
        'author_id' => 1,
        'tags' => 'environment, cleanup, karachi, conservation'
    ],
    [
        'title' => 'Pakistan Wins 3 Gold Medals at Asian Games 2024',
        'slug' => 'pakistan-wins-3-gold-medals-asian-games-2024',
        'content' => '<h2>HANGZHOU - Pakistani athletes made the nation proud by winning three gold medals at the Asian Games 2024, marking the country\'s best performance in the event\'s history.</h2>
        
        <p>The gold medals came in wrestling, weightlifting, and hockey, with Pakistan also securing several silver and bronze medals across various disciplines.</p>
        
        <blockquote>"Our athletes have shown exceptional skill and determination. These medals are a testament to their hard work and the support of the nation," said Pakistan Olympic Association President.</blockquote>
        
        <h3>Gold Medal Winners:</h3>
        <ul>
            <li><strong>Wrestling:</strong> Muhammad Sharif (74kg category)</li>
            <li><strong>Weightlifting:</strong> Talha Zubair (109kg category)</li>
            <li><strong>Hockey:</strong> Pakistan National Team (defeated Japan 3-1)</li>
        </ul>
        
        <p>The Prime Minister has announced cash prizes of Rs. 10 million for each gold medalist and has promised increased funding for sports development programs.</p>',
        'excerpt' => 'Pakistani athletes achieve historic success with 3 gold medals at Asian Games 2024 in wrestling, weightlifting, and hockey.',
        'category_id' => 4, // Sports
        'status' => 'published',
        'is_breaking' => 1,
        'featured_image' => 'uploads/news/asian-games.jpg',
        'author_id' => 1,
        'tags' => 'asian games, sports, medals, pakistan'
    ],
    [
        'title' => 'Tech Startup Hub Opens in Islamabad with Government Support',
        'slug' => 'tech-startup-hub-islamabad-government-support',
        'content' => '<h2>ISLAMABAD - A state-of-the-art technology startup hub was inaugurated in Islamabad today, aimed at fostering innovation and entrepreneurship among Pakistan\'s youth.</h2>
        
        <p>The "Islamabad Tech Valley" spans 100,000 square feet and will provide incubation services, funding opportunities, and mentorship to emerging tech companies.</p>
        
        <blockquote>"This hub will be the catalyst for Pakistan\'s digital transformation, creating thousands of jobs and positioning our country as a regional tech leader," said the Minister of Information Technology.</blockquote>
        
        <h3>Hub Features:</h3>
        <ul>
            <li>Co-working space for 500+ entrepreneurs</li>
            <li>$50 million startup fund</li>
            <li>Legal and business advisory services</li>
            <li>High-speed internet and cloud infrastructure</li>
            <li>Partnerships with international tech companies</li>
        </ul>
        
        <p>The hub has already received applications from over 200 startups, with the first batch of 50 companies set to begin operations next month.</p>',
        'excerpt' => 'Government launches Islamabad Tech Valley, a $50 million startup hub to foster innovation and entrepreneurship.',
        'category_id' => 6, // Technology
        'status' => 'published',
        'is_breaking' => 0,
        'featured_image' => 'uploads/news/tech-hub.jpg',
        'author_id' => 1,
        'tags' => 'startups, technology, innovation, islamabad'
    ],
    [
        'title' => 'Healthcare Revolution: AI Diagnosis System Launched in Major Hospitals',
        'slug' => 'ai-diagnosis-system-pakistan-hospitals-healthcare',
        'content' => '<h2>KARACHI - Major hospitals in Karachi and Lahore have launched an AI-powered diagnosis system that can detect diseases with 95% accuracy, revolutionizing healthcare in Pakistan.</h2>
        
        <p>The system, developed in collaboration with local tech companies and international medical institutions, uses machine learning algorithms to analyze medical images and patient data.</p>
        
        <blockquote>"This technology will help us diagnose diseases earlier and more accurately, potentially saving thousands of lives," said Dr. Sarah Ahmed, Chief Medical Officer at Aga Khan Hospital.</blockquote>
        
        <h3>System Capabilities:</h3>
        <ul>
            <li>Detects cancer, heart disease, and diabetes</li>
            <li>Reduces diagnosis time by 70%</li>
            <li>95% accuracy rate</li>
            <li>Integrates with existing hospital systems</li>
            <li>Available 24/7 for emergency cases</li>
        </ul>
        
        <p>The government plans to expand this system to all major public hospitals within the next two years, making advanced healthcare accessible to millions.</p>',
        'excerpt' => 'Pakistani hospitals launch AI diagnosis system with 95% accuracy, revolutionizing disease detection and healthcare delivery.',
        'category_id' => 9, // Health
        'status' => 'published',
        'is_breaking' => 1,
        'featured_image' => 'uploads/news/ai-healthcare.jpg',
        'author_id' => 1,
        'tags' => 'healthcare, AI, technology, medical'
    ],
    [
        'title' => 'Record Wheat Production Expected This Year: Agriculture Ministry',
        'slug' => 'record-wheat-production-pakistan-agriculture-ministry',
        'content' => '<h2>ISLAMABAD - The Ministry of National Food Security today announced that Pakistan is expected to achieve record wheat production this year, estimated at 27.5 million tons.</h2>
        
        <p>This represents a 10% increase from last year\'s production and will help Pakistan achieve food security while potentially allowing for wheat exports.</p>
        
        <blockquote>"The increased production is due to better seeds, favorable weather conditions, and government support to farmers," said Federal Agriculture Minister.</blockquote>
        
        <h3>Production Highlights:</h3>
        <ul>
            <li>27.5 million tons expected production</li>
            <li>10% increase from last year</li>
            <li>Punjab contributes 75% of total production</li>
            <li>New irrigation techniques implemented</li>
            <li>Farmers receive better price support</li>
        </ul>
        
        <p>The government has also announced plans to modernize storage facilities to reduce post-harvest losses and improve the wheat supply chain.</p>',
        'excerpt' => 'Pakistan expects record wheat production of 27.5 million tons this year, achieving food security and potential export opportunities.',
        'category_id' => 5, // Business
        'status' => 'published',
        'is_breaking' => 0,
        'featured_image' => 'uploads/news/wheat-production.jpg',
        'author_id' => 1,
        'tags' => 'agriculture, wheat, food security, economy'
    ],
    [
        'title' => 'New Metro Bus Service Launched in Multan',
        'slug' => 'multan-metro-bus-service-launched-public-transport',
        'content' => '<h2>MULTAN - The Punjab government today launched the much-awaited Metro Bus Service in Multan, providing modern public transportation to over 500,000 daily commuters.</h2>
        
        <p>The 31.5 km route connects major areas of the city, with 28 modern bus stations equipped with ticketing machines and passenger facilities.</p>
        
        <blockquote>"This project will transform public transport in Multan and provide affordable, comfortable travel to citizens," said Punjab Chief Minister.</blockquote>
        
        <h3>Service Features:</h3>
        <ul>
            <li>31.5 km route with 28 stations</li>
            <li>500,000 daily commuters capacity</li>
            <li>Modern air-conditioned buses</li>
            <li>Smart card ticketing system</li>
            <li>Free Wi-Fi at all stations</li>
        </ul>
        
        <p>The project cost Rs. 30 billion and was completed in record time. Similar projects are planned for other major cities in Punjab.</p>',
        'excerpt' => 'Multan Metro Bus Service launched with 31.5 km route serving 500,000 daily commuters with modern facilities.',
        'category_id' => 10, // Infrastructure
        'status' => 'published',
        'is_breaking' => 1,
        'featured_image' => 'uploads/news/metro-bus.jpg',
        'author_id' => 1,
        'tags' => 'transport, metro bus, multan, infrastructure'
    ],
    [
        'title' => 'Pakistani Scientists Develop Low-Cost Water Purification System',
        'slug' => 'pakistani-scientists-low-cost-water-purification-system',
        'content' => '<h2>KARACHI - Scientists at Karachi University have developed a revolutionary low-cost water purification system that can provide clean drinking water for just Rs. 2 per liter.</h2>
        
        <p>The system uses locally available materials and solar energy, making it ideal for rural areas where clean water access is limited.</p>
        
        <blockquote>"This innovation can solve Pakistan\'s water crisis and provide clean drinking water to millions," said Dr. Ali Hassan, lead researcher.</blockquote>
        
        <h3>System Features:</h3>
        <ul>
            <li>Costs only Rs. 2 per liter</li>
            <li>Uses solar power</li>
            <li>Removes 99.9% of bacteria and viruses</li>
            <li>Easy to maintain and operate</li>
            <li>Can purify 1,000 liters daily</li>
        </ul>
        
        <p>The government has approved mass production of the system and plans to install it in 1,000 villages across Pakistan within the next year.</p>',
        'excerpt' => 'Karachi University scientists develop revolutionary water purification system costing just Rs. 2 per liter using solar energy.',
        'category_id' => 6, // Technology
        'status' => 'published',
        'is_breaking' => 1,
        'featured_image' => 'uploads/news/water-purification.jpg',
        'author_id' => 1,
        'tags' => 'science, technology, water, innovation'
    ]
];

// Insert articles
$success_count = 0;
$error_count = 0;

foreach ($articles as $article) {
    $title = mysqli_real_escape_string($conn, $article['title']);
    $slug = mysqli_real_escape_string($conn, $article['slug']);
    $content = mysqli_real_escape_string($conn, $article['content']);
    $excerpt = mysqli_real_escape_string($conn, $article['excerpt']);
    $category_id = $article['category_id'];
    $status = $article['status'];
    $is_breaking = $article['is_breaking'];
    $featured_image = $article['featured_image'];
    $author_id = $article['author_id'];
    
    $published_at = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
    
    $insert_query = "INSERT INTO news (title, slug, content, excerpt, category_id, status, is_breaking, image, author_id, published_at, created_at, views) 
                     VALUES ('$title', '$slug', '$content', '$excerpt', $category_id, '$status', $is_breaking, '$featured_image', $author_id, '$published_at', '$published_at', " . rand(100, 5000) . ")";
    
    // Check if slug already exists
    $check_query = "SELECT id FROM news WHERE slug = '$slug'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        if (mysqli_query($conn, $insert_query)) {
            $success_count++;
            echo "<p class='text-success'>✓ Created: " . htmlspecialchars($article['title']) . "</p>";
        } else {
            $error_count++;
            echo "<p class='text-danger'>✗ Error creating " . htmlspecialchars($article['title']) . ": " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p class='text-warning'>⚠ Skipped: " . htmlspecialchars($article['title']) . " (slug already exists)</p>";
    }
}

echo "<h3>Article Creation Complete!</h3>";
echo "<p class='text-success'><strong>Success:</strong> $success_count articles created</p>";
echo "<p class='text-danger'><strong>Errors:</strong> $error_count articles failed</p>";

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='index.php'>View articles on homepage</a></li>";
echo "<li><a href='admin/manage-news.php'>Manage articles in admin panel</a></li>";
echo "<li><a href='setup_live_streaming.php'>Set up live streaming schedule</a></li>";
echo "<li><a href='setup_social_media.php'>Configure social media sharing</a></li>";
echo "</ul>";
?>

<style>
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
body { font-family: Arial, sans-serif; padding: 20px; }
</style>
