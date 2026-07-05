<?php
require_once 'config/database.php';
require_once 'includes/language_functions.php';

$page_title = 'About Us';
$current_lang = get_current_language();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-gradient position-relative overflow-hidden" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 80vh; display: flex; align-items: center;">
    <div class="absolute-pattern position-absolute top-0 start-0 w-100 h-100 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20100%20100%22%3E%3Ccircle%20cx%3D%2250%22%20cy%3D%2250%22%20r%3D%2240%22%20fill%3D%22none%22%20stroke%3D%22white%22%20stroke-width%3D%220.5%22%2F%3E%3C%2Fsvg%3E'); background-size: 50px 50px;"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <span class="badge bg-danger bg-opacity-75 text-white mb-3 px-4 py-2 rounded-pill" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    <i class="fas fa-broadcast-tower me-2"></i>Pakistan's Leading News Platform
                </span>
                <h1 class="display-2 fw-bold mb-4" style="color: #000000; text-shadow: 0 2px 8px rgba(255,255,255,0.3);">
                    PK <span class="text-danger">LIVE</span> NEWS
                </h1>
                <p class="lead mb-4 fs-4" style="color: #000000; opacity: 0.95; text-shadow: 0 2px 4px rgba(255,255,255,0.3);">
                    Your premier destination for high-quality journalism, real-time updates, and comprehensive news coverage across Pakistan and the globe.
                </p>
                <p class="mb-5 fs-5" style="color: #000000; opacity: 0.9; text-shadow: 0 2px 4px rgba(255,255,255,0.3);">
                    Founded with a vision to provide unbiased and accurate information, PK Live News has grown into a leading digital news platform. We leverage cutting-edge technology, including AI-driven sentiment analysis, to bring you deeper insights into the stories that matter most to our community.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="#mission" class="btn btn-danger btn-lg px-5 py-3 fw-bold rounded-pill shadow-lg hover-lift">
                        <i class="fas fa-rocket me-2"></i>Our Mission
                    </a>
                    <a href="#team" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold rounded-pill">
                        <i class="fas fa-users me-2"></i>Meet Our Team
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <div class="hero-image-wrapper position-relative rounded-4 overflow-hidden shadow-2xl">
                        <img src="assets/images/about-hero.jpg" alt="About PK Live News" class="img-fluid w-100" style="min-height: 500px; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=800&h=600&fit=crop'">
                        <div class="position-absolute bottom-0 start-0 end-0 bg-gradient-dark p-4" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                            <div class="d-flex align-items-center gap-4">
                                <div class="text-white">
                                    <div class="display-6 fw-bold">24/7</div>
                                    <div class="small text-white opacity-75">Live Coverage</div>
                                </div>
                                <div class="vr bg-white opacity-25" style="height: 40px;"></div>
                                <div class="text-white">
                                    <div class="display-6 fw-bold">100+</div>
                                    <div class="small text-white opacity-75">News Sources</div>
                                </div>
                                <div class="vr bg-white opacity-25" style="height: 40px;"></div>
                                <div class="text-white">
                                    <div class="display-6 fw-bold">AI</div>
                                    <div class="small text-white opacity-75">Powered</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="position-absolute top-10 end-10 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 100px; height: 100px; animation: pulse 2s infinite;">
                        <i class="fas fa-play fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="stat-card text-center p-4 rounded-3 bg-light hover-lift">
                    <div class="display-4 fw-bold text-danger mb-2" id="stat1">0</div>
                    <div class="text-muted fw-medium">Daily Readers</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center p-4 rounded-3 bg-light hover-lift">
                    <div class="display-4 fw-bold text-primary mb-2" id="stat2">0</div>
                    <div class="text-muted fw-medium">News Articles</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center p-4 rounded-3 bg-light hover-lift">
                    <div class="display-4 fw-bold text-success mb-2" id="stat3">0</div>
                    <div class="text-muted fw-medium">Team Members</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center p-4 rounded-3 bg-light hover-lift">
                    <div class="display-4 fw-bold text-warning mb-2" id="stat4">0</div>
                    <div class="text-muted fw-medium">Years Active</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section id="mission" class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-12 text-center mb-5">
                <span class="badge bg-danger bg-opacity-10 text-danger mb-3 px-4 py-2 rounded-pill">Our Purpose</span>
                <h2 class="display-5 fw-bold mb-3">Driving Change Through <span class="text-danger">Truth</span></h2>
                <p class="lead text-muted mx-auto" style="max-width: 700px;">
                    We believe in the power of accurate information to shape societies and empower communities.
                </p>
            </div>
        </div>
        
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="mission-card bg-white rounded-4 p-5 shadow-sm h-100 border-start border-5 border-danger">
                    <div class="mb-4">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-bullseye fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-4 h2">Our Mission</h3>
                    <p class="text-muted mb-4 fs-5">
                        To empower our audience with truthful, timely, and impactful news while maintaining the highest standards of journalistic integrity and transparency. We strive to be the most trusted source of information in Pakistan, delivering news that matters to the people who matter most.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-danger me-3 mt-1"></i>
                            <span class="text-muted">Unbiased reporting across all topics</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-danger me-3 mt-1"></i>
                            <span class="text-muted">Real-time updates on breaking stories</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-danger me-3 mt-1"></i>
                            <span class="text-muted">In-depth analysis and expert opinions</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="fas fa-check-circle text-danger me-3 mt-1"></i>
                            <span class="text-muted">Community-driven storytelling</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="vision-card bg-white rounded-4 p-5 shadow-sm h-100 border-start border-5 border-primary">
                    <div class="mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-eye fa-2x"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-4 h2">Our Vision</h3>
                    <p class="text-muted mb-4 fs-5">
                        To become Pakistan's most influential digital news platform, setting new standards for journalism excellence and technological innovation. We envision a future where every citizen has access to accurate information that enables informed decision-making.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-star text-primary me-3 mt-1"></i>
                            <span class="text-muted">Leading the digital transformation of news</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-star text-primary me-3 mt-1"></i>
                            <span class="text-muted">Pioneering AI-powered journalism</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-star text-primary me-3 mt-1"></i>
                            <span class="text-muted">Building a global network of correspondents</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="fas fa-star text-primary me-3 mt-1"></i>
                            <span class="text-muted">Fostering media literacy and awareness</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-success bg-opacity-10 text-success mb-3 px-4 py-2 rounded-pill">What We Stand For</span>
            <h2 class="display-5 fw-bold mb-3">Our Core <span class="text-success">Values</span></h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                The principles that guide every decision we make and every story we tell.
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="value-card text-center p-4 rounded-3 bg-light hover-lift h-100">
                    <div class="mb-3">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Integrity</h4>
                    <p class="text-muted small">We maintain unwavering commitment to truth and accuracy in all our reporting.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="value-card text-center p-4 rounded-3 bg-light hover-lift h-100">
                    <div class="mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-balance-scale fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Fairness</h4>
                    <p class="text-muted small">We present multiple perspectives and give voice to all sides of important issues.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="value-card text-center p-4 rounded-3 bg-light hover-lift h-100">
                    <div class="mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-bolt fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Speed</h4>
                    <p class="text-muted small">We deliver breaking news quickly without compromising accuracy or quality.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="value-card text-center p-4 rounded-3 bg-light hover-lift h-100">
                    <div class="mb-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Community</h4>
                    <p class="text-muted small">We serve our readers and prioritize stories that impact local communities.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- What Makes Us Different -->
<section class="py-5 bg-gradient-dark position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f3460 0%, #16213e 100%);">
    <div class="container position-relative" style="z-index: 2;">
        <div class="text-center mb-5">
            <span class="badge bg-white bg-opacity-10 text-white mb-3 px-4 py-2 rounded-pill">Why Choose Us</span>
            <h2 class="display-5 fw-bold mb-3 text-white">What Sets Us <span class="text-danger">Apart</span></h2>
            <p class="lead text-white opacity-75 mx-auto" style="max-width: 700px;">
                Discover the unique advantages that make PK Live News your trusted news source.
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="feature-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-5 h-100 border border-white border-opacity-20">
                    <div class="mb-4">
                        <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-tv fa-xl"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-white mb-3 h3">24/7 Live Coverage</h4>
                    <p class="text-white opacity-75 mb-4">
                        Stay ahead with our round-the-clock live streaming and instant breaking news alerts. Our dedicated team ensures you're always connected to the pulse of the nation.
                    </p>
                    <ul class="list-unstyled text-white opacity-50">
                        <li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Real-time updates</li>
                        <li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Live TV streaming</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Push notifications</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="feature-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-5 h-100 border border-white border-opacity-20">
                    <div class="mb-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-brain fa-xl"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-white mb-3 h3">AI-Powered Insights</h4>
                    <p class="text-white opacity-75 mb-4">
                        We utilize advanced AI algorithms to analyze public sentiment on trending topics, providing a balanced and multi-dimensional view of complex issues.
                    </p>
                    <ul class="list-unstyled text-white opacity-50">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Sentiment analysis</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Content verification</li>
                        <li><i class="fas fa-check text-primary me-2"></i>Smart recommendations</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="feature-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-5 h-100 border border-white border-opacity-20">
                    <div class="mb-4">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-globe fa-xl"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-white mb-3 h3">Global Network</h4>
                    <p class="text-white opacity-75 mb-4">
                        Access news from over 100 trusted sources worldwide, including international bureaus and local correspondents bringing you comprehensive coverage.
                    </p>
                    <ul class="list-unstyled text-white opacity-50">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>100+ news sources</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>International coverage</li>
                        <li><i class="fas fa-check text-success me-2"></i>Local correspondents</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-4 py-2 rounded-pill">Our Journey</span>
            <h2 class="display-5 fw-bold mb-3">Milestones & <span class="text-primary">Achievements</span></h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                A timeline of our growth and the key moments that shaped PK Live News.
            </p>
        </div>
        
        <div class="timeline position-relative">
            <div class="timeline-line position-absolute top-0 bottom-0 start-50 translate-middle-x bg-primary" style="width: 4px;"></div>
            
            <div class="row mb-5">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="timeline-card bg-white rounded-4 p-5 shadow-sm ms-auto" style="max-width: 90%;">
                        <div class="timeline-badge bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Foundation</h4>
                        <p class="text-danger fw-bold mb-3">2020</p>
                        <p class="text-muted">PK Live News was founded with a vision to revolutionize digital journalism in Pakistan. Started as a small team with big dreams.</p>
                    </div>
                </div>
                <div class="col-lg-6"></div>
            </div>
            
            <div class="row mb-5">
                <div class="col-lg-6"></div>
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="timeline-card bg-white rounded-4 p-5 shadow-sm me-auto" style="max-width: 90%;">
                        <div class="timeline-badge bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-broadcast-tower"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Live Streaming Launch</h4>
                        <p class="text-primary fw-bold mb-3">2021</p>
                        <p class="text-muted">Launched our 24/7 live streaming service, bringing real-time news coverage to millions of viewers across Pakistan.</p>
                    </div>
                </div>
            </div>
            
            <div class="row mb-5">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="timeline-card bg-white rounded-4 p-5 shadow-sm ms-auto" style="max-width: 90%;">
                        <div class="timeline-badge bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h4 class="fw-bold mb-2">AI Integration</h4>
                        <p class="text-success fw-bold mb-3">2022</p>
                        <p class="text-muted">Integrated AI-powered sentiment analysis and content verification, setting new standards for intelligent news delivery.</p>
                    </div>
                </div>
                <div class="col-lg-6"></div>
            </div>
            
            <div class="row">
                <div class="col-lg-6"></div>
                <div class="col-lg-6">
                    <div class="timeline-card bg-white rounded-4 p-5 shadow-sm me-auto" style="max-width: 90%;">
                        <div class="timeline-badge bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Market Leadership</h4>
                        <p class="text-warning fw-bold mb-3">2023-Present</p>
                        <p class="text-muted">Established as Pakistan's leading digital news platform with over 1 million daily readers and expanding global reach.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section id="team" class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-danger bg-opacity-10 text-danger mb-3 px-4 py-2 rounded-pill">The People Behind PK Live News</span>
            <h2 class="display-5 fw-bold mb-3">Meet Our <span class="text-danger">Leadership</span></h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                The visionaries and experts driving innovation in digital journalism.
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-6 col-md-6">
                <div class="team-card bg-light rounded-4 p-5 text-center h-100 hover-lift">
                    <div class="mb-4 position-relative d-inline-block">
                        <img src="assets/images/team/ibraheem.jpg" alt="Muhammad Ibraheem" class="rounded-circle shadow-lg" style="width: 200px; height: 200px; object-fit: cover; border: 6px solid white;" onerror="this.src='https://ui-avatars.com/api/?name=Muhammad+Ibraheem&size=200&background=dc3545&color=fff&font-size=0.4'">
                        <div class="position-absolute bottom-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border: 4px solid white;">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-2 h4">Muhammad Ibraheem</h3>
                    <p class="text-danger fw-bold text-uppercase small mb-3 tracking-wide">Founder & Lead Architect</p>
                    <p class="text-muted mb-4">
                        Lead engineer with expertise in system scalability, AI integration, and content verification systems. Passionate about leveraging technology to enhance journalism.
                    </p>
                    <div class="social-links mb-3">
                        <a href="#" class="btn btn-outline-danger btn-sm me-2 rounded-circle" style="width: 40px; height: 40px; padding: 0;">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="btn btn-outline-dark btn-sm me-2 rounded-circle" style="width: 40px; height: 40px; padding: 0;">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 40px; height: 40px; padding: 0;">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                    <div class="team-skills">
                        <span class="badge bg-light text-dark border me-1 mb-1">System Architecture</span>
                        <span class="badge bg-light text-dark border me-1 mb-1">AI/ML</span>
                        <span class="badge bg-light text-dark border mb-1">Full-Stack Development</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 col-md-6">
                <div class="team-card bg-light rounded-4 p-5 text-center h-100 hover-lift">
                    <div class="mb-4 position-relative d-inline-block">
                        <img src="assets/images/team/kashif.jpg" alt="Muhammad Kashif" class="rounded-circle shadow-lg" style="width: 200px; height: 200px; object-fit: cover; border: 6px solid white;" onerror="this.src='https://ui-avatars.com/api/?name=Muhammad+Kashif&size=200&background=343a40&color=fff&font-size=0.4'">
                        <div class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border: 4px solid white;">
                            <i class="fas fa-palette"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-2 h4">Muhammad Kashif</h3>
                    <p class="text-primary fw-bold text-uppercase small mb-3 tracking-wide">Co-Founder & UI/UX Expert</p>
                    <p class="text-muted mb-4">
                        Expert in real-time data streaming, responsive design, and user experience optimization. Dedicated to crafting seamless interfaces across all devices.
                    </p>
                    <div class="social-links mb-3">
                        <a href="#" class="btn btn-outline-primary btn-sm me-2 rounded-circle" style="width: 40px; height: 40px; padding: 0;">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="btn btn-outline-info btn-sm me-2 rounded-circle" style="width: 40px; height: 40px; padding: 0;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 40px; height: 40px; padding: 0;">
                            <i class="fab fa-dribbble"></i>
                        </a>
                    </div>
                    <div class="team-skills">
                        <span class="badge bg-light text-dark border me-1 mb-1">UI/UX Design</span>
                        <span class="badge bg-light text-dark border me-1 mb-1">Frontend Development</span>
                        <span class="badge bg-light text-dark border mb-1">Real-time Systems</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Extended Team -->
        <div class="row g-4 mt-4">
            <div class="col-lg-3 col-md-6">
                <div class="team-mini-card bg-white rounded-3 p-4 text-center shadow-sm hover-lift">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=Ahmed+Ali&size=100&background=0d6efd&color=fff" alt="Ahmed Ali" class="rounded-circle mx-auto" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                    <h5 class="fw-bold mb-1">Ahmed Ali</h5>
                    <p class="text-primary small fw-bold mb-2">Senior Editor</p>
                    <p class="text-muted small">10+ years in journalism</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="team-mini-card bg-white rounded-3 p-4 text-center shadow-sm hover-lift">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=Fatima+Khan&size=100&background=dc3545&color=fff" alt="Fatima Khan" class="rounded-circle mx-auto" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                    <h5 class="fw-bold mb-1">Fatima Khan</h5>
                    <p class="text-danger small fw-bold mb-2">News Anchor</p>
                    <p class="text-muted small">Award-winning broadcaster</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="team-mini-card bg-white rounded-3 p-4 text-center shadow-sm hover-lift">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=Hassan+Raza&size=100&background=198754&color=fff" alt="Hassan Raza" class="rounded-circle mx-auto" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                    <h5 class="fw-bold mb-1">Hassan Raza</h5>
                    <p class="text-success small fw-bold mb-2">Tech Lead</p>
                    <p class="text-muted small">Infrastructure specialist</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="team-mini-card bg-white rounded-3 p-4 text-center shadow-sm hover-lift">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=Ayesha+Malik&size=100&background=ffc107&color=000" alt="Ayesha Malik" class="rounded-circle mx-auto" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                    <h5 class="fw-bold mb-1">Ayesha Malik</h5>
                    <p class="text-warning small fw-bold mb-2">Correspondent</p>
                    <p class="text-muted small">International bureau</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-4 py-2 rounded-pill">What People Say</span>
            <h2 class="display-5 fw-bold mb-3">Trusted by <span class="text-primary">Millions</span></h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                Hear from our readers and partners about their experience with PK Live News.
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="testimonial-card bg-white rounded-4 p-5 shadow-sm h-100">
                    <div class="mb-4">
                        <div class="text-warning mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-4 fst-italic">
                        "PK Live News has become my go-to source for accurate and timely information. Their live coverage during breaking news events is exceptional. The AI-powered insights provide a unique perspective I can't find elsewhere."
                    </p>
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name=Ali+Hassan&size=50&background=dc3545&color=fff" alt="Ali Hassan" class="rounded-circle me-3" style="width: 50px; height: 50px;">
                        <div>
                            <h6 class="fw-bold mb-0">Ali Hassan</h6>
                            <p class="text-muted small mb-0">Business Analyst, Karachi</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="testimonial-card bg-white rounded-4 p-5 shadow-sm h-100">
                    <div class="mb-4">
                        <div class="text-warning mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-4 fst-italic">
                        "As a journalist myself, I appreciate PK Live News' commitment to unbiased reporting and factual accuracy. Their team maintains high journalistic standards while embracing modern technology."
                    </p>
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name=Sara+Ahmed&size=50&background=0d6efd&color=fff" alt="Sara Ahmed" class="rounded-circle me-3" style="width: 50px; height: 50px;">
                        <div>
                            <h6 class="fw-bold mb-0">Sara Ahmed</h6>
                            <p class="text-muted small mb-0">Freelance Journalist, Lahore</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="testimonial-card bg-white rounded-4 p-5 shadow-sm h-100">
                    <div class="mb-4">
                        <div class="text-warning mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-4 fst-italic">
                        "The mobile app is fantastic! I can stay updated on breaking news wherever I am. The personalized news feed based on my interests is a game-changer. Highly recommended for anyone who values staying informed."
                    </p>
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name=Usman+Khan&size=50&background=198754&color=fff" alt="Usman Khan" class="rounded-circle me-3" style="width: 50px; height: 50px;">
                        <div>
                            <h6 class="fw-bold mb-0">Usman Khan</h6>
                            <p class="text-muted small mb-0">Software Engineer, Islamabad</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partners Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-success bg-opacity-10 text-success mb-3 px-4 py-2 rounded-pill">Our Network</span>
            <h2 class="display-5 fw-bold mb-3">Trusted <span class="text-success">Partners</span></h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                We collaborate with leading organizations to bring you comprehensive news coverage.
            </p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-card bg-light rounded-3 p-4 text-center hover-lift">
                    <div class="partner-logo mb-3">
                        <i class="fas fa-newspaper fa-3x text-muted"></i>
                    </div>
                    <p class="fw-bold small text-muted">Reuters</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-card bg-light rounded-3 p-4 text-center hover-lift">
                    <div class="partner-logo mb-3">
                        <i class="fas fa-broadcast-tower fa-3x text-muted"></i>
                    </div>
                    <p class="fw-bold small text-muted">BBC News</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-card bg-light rounded-3 p-4 text-center hover-lift">
                    <div class="partner-logo mb-3">
                        <i class="fas fa-tv fa-3x text-muted"></i>
                    </div>
                    <p class="fw-bold small text-muted">ARY News</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-card bg-light rounded-3 p-4 text-center hover-lift">
                    <div class="partner-logo mb-3">
                        <i class="fas fa-globe fa-3x text-muted"></i>
                    </div>
                    <p class="fw-bold small text-muted">Al Jazeera</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-card bg-light rounded-3 p-4 text-center hover-lift">
                    <div class="partner-logo mb-3">
                        <i class="fas fa-chart-line fa-3x text-muted"></i>
                    </div>
                    <p class="fw-bold small text-muted">Bloomberg</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-card bg-light rounded-3 p-4 text-center hover-lift">
                    <div class="partner-logo mb-3">
                        <i class="fas fa-satellite-dish fa-3x text-muted"></i>
                    </div>
                    <p class="fw-bold small text-muted">CNN</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient position-relative overflow-hidden" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
    <div class="absolute-pattern position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20100%20100%22%3E%3Ccircle%20cx%3D%2250%22%20cy%3D%2250%22%20r%3D%2240%22%20fill%3D%22none%22%20stroke%3D%22white%22%20stroke-width%3D%220.5%22%2F%3E%3C%2Fsvg%3E'); background-size: 50px 50px;"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="display-4 fw-bold text-white mb-3">Have a Story to Share?</h2>
                <p class="text-white-90 fs-5 mb-0">
                    We are always looking for news tips, freelance contributors, and community reporting. Let's make your voice heard and help shape the narrative.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column gap-3">
                    <a href="contact.php" class="btn btn-light btn-lg px-5 py-3 fw-bold rounded-pill shadow-lg hover-lift">
                        <i class="fas fa-paper-plane me-2"></i>Contact Our Desk
                    </a>
                    <a href="signup.php" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold rounded-pill">
                        <i class="fas fa-user-plus me-2"></i>Join Our Team
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="contact-card bg-white rounded-4 p-5 shadow-sm text-center h-100">
                    <div class="mb-4">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-map-marker-alt fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Visit Us</h4>
                    <p class="text-muted mb-0">
                        PK Live News Headquarters<br>
                        Nowshera, KPK, Pakistan<br>
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="contact-card bg-white rounded-4 p-5 shadow-sm text-center h-100">
                    <div class="mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-phone fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Call Us</h4>
                    <p class="text-muted mb-0">
                        Main: +92 311 8195630<br>
                        News Desk: +92 330 394061<br>
                        Advertising: +92 311 8195630
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="contact-card bg-white rounded-4 p-5 shadow-sm text-center h-100">
                    <div class="mb-4">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Email Us</h4>
                    <p class="text-muted mb-0">
                        General: ibraheem@pk-news.com<br>
                        News: kashif@pk-news.com<br>
                        Careers: careers@pk-news.com
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.15) !important;
}

.backdrop-blur {
    backdrop-filter: blur(10px);
}

.tracking-wide {
    letter-spacing: 1px;
}

.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.bg-gradient-dark {
    background: linear-gradient(135deg, #0f3460 0%, #16213e 100%);
}

.contact-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.hero-image-wrapper {
    transition: transform 0.3s ease;
}

.hero-image-wrapper:hover {
    transform: scale(1.02);
}

.stat-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.mission-card,
.vision-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.mission-card:hover,
.vision-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.value-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.feature-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.timeline-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.timeline-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.team-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.team-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.team-mini-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.team-mini-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.testimonial-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

@media (max-width: 991px) {
    .timeline-line {
        left: 20px !important;
    }
    
    .timeline-card {
        max-width: 100% !important;
        margin-left: 40px !important;
    }
}
</style>

<script>
// Animate statistics on scroll
const animateStats = () => {
    const stats = [
        { id: 'stat1', target: 500000, suffix: '+' },
        { id: 'stat2', target: 15000, suffix: '+' },
        { id: 'stat3', target: 25, suffix: '' },
        { id: 'stat4', target: 4, suffix: '+' }
    ];
    
    stats.forEach(stat => {
        const element = document.getElementById(stat.id);
        if (element) {
            let current = 0;
            const increment = stat.target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= stat.target) {
                    current = stat.target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString() + stat.suffix;
            }, 30);
        }
    });
};

// Trigger animation when section is visible
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateStats();
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

const statsSection = document.querySelector('.stat-card');
if (statsSection) {
    observer.observe(statsSection);
}
</script>

<?php include 'includes/footer.php'; ?>