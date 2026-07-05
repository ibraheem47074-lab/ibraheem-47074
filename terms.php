<?php
require_once 'config/database.php';
require_once 'includes/language_functions.php';

$page_title = 'Terms of Service';
$current_lang = get_current_language();
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section bg-gradient position-relative overflow-hidden" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 50vh; display: flex; align-items: center;">
    <div class="absolute-pattern position-absolute top-0 start-0 w-100 h-100 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20100%20100%22%3E%3Ccircle%20cx%3D%2250%22%20cy%3D%2250%22%20r%3D%2240%22%20fill%3D%22none%22%20stroke%3D%22white%22%20stroke-width%3D%220.5%22%2F%3E%3C%2Fsvg%3E'); background-size: 50px 50px;"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <span class="badge bg-danger bg-opacity-75 text-white mb-3 px-4 py-2 rounded-pill" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    <i class="fas fa-file-contract me-2"></i>Legal Agreement
                </span>
                <h1 class="display-2 fw-bold mb-4" style="color: #000000; text-shadow: 0 2px 8px rgba(255,255,255,0.3);">
                    Terms of <span class="text-danger">Service</span>
                </h1>
                <p class="lead mb-4 fs-4" style="color: #000000; opacity: 0.95; text-shadow: 0 2px 4px rgba(255,255,255,0.3);">
                    Please read these terms carefully before using PK Live News. Your use of our platform constitutes acceptance of these terms.
                </p>
                <p class="mb-5 fs-5" style="color: #000000; opacity: 0.9; text-shadow: 0 2px 4px rgba(255,255,255,0.3);">
                    Last updated: <?php echo date('F j, Y'); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Terms of Service Content -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="sticky-top" style="top: 100px;">
                    <div class="nav flex-column nav-pills terms-nav" role="tablist">
                        <a class="nav-link active mb-2 rounded-pill" href="#acceptance">Acceptance of Terms</a>
                        <a class="nav-link mb-2 rounded-pill" href="#license">Use License</a>
                        <a class="nav-link mb-2 rounded-pill" href="#conduct">User Conduct</a>
                        <a class="nav-link mb-2 rounded-pill" href="#disclaimers">Disclaimers</a>
                        <a class="nav-link mb-2 rounded-pill" href="#third-party">Third-Party Content</a>
                        <a class="nav-link mb-2 rounded-pill" href="#liability">Limitation of Liability</a>
                        <a class="nav-link mb-2 rounded-pill" href="#privacy">Privacy Policy</a>
                        <a class="nav-link mb-2 rounded-pill" href="#termination">Termination</a>
                        <a class="nav-link mb-2 rounded-pill" href="#governing">Governing Law</a>
                        <a class="nav-link mb-2 rounded-pill" href="#contact">Contact Us</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="terms-content">
                    <!-- Acceptance of Terms -->
                    <div id="acceptance" class="terms-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-handshake fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Acceptance of Terms</h2>
                        </div>
                        <p class="text-muted mb-4">By accessing and using <strong>PK Live News</strong> (pk-news.com), you agree to be bound by these Terms of Service and all applicable laws and regulations.</p>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> If you do not agree with any of these terms, you are prohibited from using or accessing this site.
                        </div>
                        
                        <div class="acceptance-points">
                            <div class="point-item d-flex align-items-start mb-3">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span class="text-muted">Accessing our website constitutes acceptance of these terms</span>
                            </div>
                            <div class="point-item d-flex align-items-start mb-3">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span class="text-muted">Terms may be updated periodically without prior notice</span>
                            </div>
                            <div class="point-item d-flex align-items-start">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span class="text-muted">Continued use after changes indicates acceptance of updated terms</span>
                            </div>
                        </div>
                    </div>

                    <!-- Use License -->
                    <div id="license" class="terms-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-copyright fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Use License & Intellectual Property</h2>
                        </div>
                        <p class="text-muted mb-4">All content published on PK Live News is protected by international copyright laws and intellectual property rights.</p>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="license-card bg-light rounded-3 p-4">
                                    <h5 class="fw-bold text-success mb-2"><i class="fas fa-check me-2"></i>Permitted Uses</h5>
                                    <ul class="small text-muted mb-0">
                                        <li>View content for personal use</li>
                                        <li>Print articles for offline reading</li>
                                        <li>Share links to our content</li>
                                        <li>Use for educational purposes</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="license-card bg-light rounded-3 p-4">
                                    <h5 class="fw-bold text-danger mb-2"><i class="fas fa-times me-2"></i>Prohibited Uses</h5>
                                    <ul class="small text-muted mb-0">
                                        <li>Republish without consent</li>
                                        <li>Sell or rent our content</li>
                                        <li>Remove attribution</li>
                                        <li>Commercial use without permission</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>AI Content:</strong> AI-driven sentiment analysis results are provided for informational purposes only and should not be used for commercial applications.
                        </div>
                    </div>

                    <!-- User Conduct -->
                    <div id="conduct" class="terms-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-user-check fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">User Conduct & Comments</h2>
                        </div>
                        <p class="text-muted mb-4">Users are encouraged to engage with our content through comments. However, you agree not to post content that violates our community standards.</p>
                        
                        <div class="conduct-rules">
                            <div class="rule-item bg-danger bg-opacity-5 rounded-3 p-3 mb-2">
                                <h6 class="fw-bold text-danger mb-1"><i class="fas fa-ban me-2"></i>Prohibited Content</h6>
                                <p class="small text-muted mb-0">Defamatory, abusive, harassing, or threatening language</p>
                            </div>
                            <div class="rule-item bg-danger bg-opacity-5 rounded-3 p-3 mb-2">
                                <h6 class="fw-bold text-danger mb-1"><i class="fas fa-ban me-2"></i>Illegal Content</h6>
                                <p class="small text-muted mb-0">Obscene, indecent, or unlawful material</p>
                            </div>
                            <div class="rule-item bg-danger bg-opacity-5 rounded-3 p-3 mb-2">
                                <h6 class="fw-bold text-danger mb-1"><i class="fas fa-ban me-2"></i>Spam & Advertising</h6>
                                <p class="small text-muted mb-0">Unauthorized promotional material or spam</p>
                            </div>
                            <div class="rule-item bg-danger bg-opacity-5 rounded-3 p-3">
                                <h6 class="fw-bold text-danger mb-1"><i class="fas fa-ban me-2"></i>Copyright Infringement</h6>
                                <p class="small text-muted mb-0">Content that infringes on third-party intellectual property rights</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-secondary mt-4">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Moderation:</strong> We reserve the right to moderate, edit, or remove any comment at our sole discretion without prior notice.
                        </div>
                    </div>

                    <!-- Disclaimers -->
                    <div id="disclaimers" class="terms-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-exclamation-triangle fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Disclaimers</h2>
                        </div>
                        <p class="text-muted mb-4">Please read the following disclaimers carefully before using our platform.</p>
                        
                        <div class="disclaimer-items">
                            <div class="disclaimer-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold text-danger mb-2"><i class="fas fa-newspaper me-2"></i>Information Accuracy</h6>
                                <p class="small text-muted mb-0">While we strive for accuracy, news is fast-moving. PK Live News does not warrant the completeness or accuracy of the information published.</p>
                            </div>
                            <div class="disclaimer-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold text-primary mb-2"><i class="fas fa-tools me-2"></i>As Is Basis</h6>
                                <p class="small text-muted mb-0">Materials on this website are provided on an 'as is' basis. We make no warranties, expressed or implied, regarding merchantability or fitness for a particular purpose.</p>
                            </div>
                            <div class="disclaimer-item bg-light rounded-3 p-4">
                                <h6 class="fw-bold text-warning mb-2"><i class="fas fa-clock me-2"></i>Timeliness</h6>
                                <p class="small text-muted mb-0">Information may become outdated quickly. We encourage users to verify critical information from multiple sources.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Third-Party Content -->
                    <div id="third-party" class="terms-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-external-link-alt fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Third-Party Content & Advertising</h2>
                        </div>
                        <p class="text-muted mb-4">Our site contains links to external websites and displays advertisements through third-party services.</p>
                        
                        <div class="third-party-list">
                            <div class="third-party-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold mb-2"><i class="fab fa-google me-2 text-danger"></i>Google AdSense</h6>
                                <p class="small text-muted mb-0">Displays advertisements based on your interests and browsing behavior. We do not control ad content.</p>
                            </div>
                            <div class="third-party-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold mb-2"><i class="fas fa-rss me-2 text-primary"></i>RSS Feeds</h6>
                                <p class="small text-muted mb-0">Content from external news sources with proper attribution. We are not responsible for their content accuracy.</p>
                            </div>
                            <div class="third-party-item bg-light rounded-3 p-4">
                                <h6 class="fw-bold mb-2"><i class="fas fa-link me-2 text-success"></i>External Links</h6>
                                <p class="small text-muted mb-0">Links to third-party websites are provided for convenience. We are not responsible for their content or privacy practices.</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Your Risk:</strong> Your use of external links and third-party content is at your own risk. Please review their terms and privacy policies.
                        </div>
                    </div>

                    <!-- Limitation of Liability -->
                    <div id="liability" class="terms-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-shield-alt fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Limitation of Liability</h2>
                        </div>
                        <p class="text-muted mb-4">Please understand the limitations of our liability regarding the use of our platform.</p>
                        
                        <div class="liability-content">
                            <p class="text-muted mb-3">In no event shall PK Live News or its developers be liable for any damages including, without limitation:</p>
                            
                            <div class="liability-grid">
                                <div class="liability-item bg-light rounded-3 p-3 text-center">
                                    <i class="fas fa-database text-danger fa-2x mb-2"></i>
                                    <h6 class="fw-bold small mb-1">Loss of Data</h6>
                                    <p class="small text-muted mb-0">Data corruption or loss</p>
                                </div>
                                <div class="liability-item bg-light rounded-3 p-3 text-center">
                                    <i class="fas fa-chart-line text-primary fa-2x mb-2"></i>
                                    <h6 class="fw-bold small mb-1">Loss of Profit</h6>
                                    <p class="small text-muted mb-0">Business or financial losses</p>
                                </div>
                                <div class="liability-item bg-light rounded-3 p-3 text-center">
                                    <i class="fas fa-briefcase text-warning fa-2x mb-2"></i>
                                    <h6 class="fw-bold small mb-1">Business Interruption</h6>
                                    <p class="small text-muted mb-0">Operational disruptions</p>
                                </div>
                                <div class="liability-item bg-light rounded-3 p-3 text-center">
                                    <i class="fas fa-laptop text-success fa-2x mb-2"></i>
                                    <h6 class="fw-bold small mb-1">System Access</h6>
                                    <p class="small text-muted mb-0">Inability to use the platform</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-danger mt-4">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Even if notified:</strong> This limitation applies even if PK Live News has been advised of the possibility of such damages.
                        </div>
                    </div>

                    <!-- Privacy Policy -->
                    <div id="privacy" class="terms-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-user-shield fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Privacy Policy</h2>
                        </div>
                        <p class="text-muted mb-4">Your privacy is important to us. Please review our Privacy Policy to understand how we collect, use, and protect your information.</p>
                        
                        <div class="privacy-summary">
                            <div class="privacy-item d-flex align-items-start mb-3">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span class="text-muted">We collect personal information only when necessary</span>
                            </div>
                            <div class="privacy-item d-flex align-items-start mb-3">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span class="text-muted">We implement security measures to protect your data</span>
                            </div>
                            <div class="privacy-item d-flex align-items-start mb-3">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span class="text-muted">We never sell your personal information</span>
                            </div>
                            <div class="privacy-item d-flex align-items-start">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span class="text-muted">You have rights to access, correct, and delete your data</span>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="privacy-policy.php" class="btn btn-primary px-5 py-3 rounded-pill">
                                <i class="fas fa-shield-alt me-2"></i>View Full Privacy Policy
                            </a>
                        </div>
                    </div>

                    <!-- Termination -->
                    <div id="termination" class="terms-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-dark bg-opacity-10 text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-ban fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Termination</h2>
                        </div>
                        <p class="text-muted mb-4">We reserve the right to terminate or suspend your access to our platform under certain circumstances.</p>
                        
                        <div class="termination-reasons">
                            <div class="reason-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Violation of Terms</h6>
                                <p class="small text-muted mb-0">Breach of any provision of these Terms of Service</p>
                            </div>
                            <div class="reason-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Illegal Activity</h6>
                                <p class="small text-muted mb-0">Engagement in illegal activities through our platform</p>
                            </div>
                            <div class="reason-item bg-light rounded-3 p-4">
                                <h6 class="fw-bold mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Security Concerns</h6>
                                <p class="small text-muted mb-0">Activities that compromise platform security or other users</p>
                            </div>
                        </div>
                    </div>

                    <!-- Governing Law -->
                    <div id="governing" class="terms-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-balance-scale fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Governing Law</h2>
                        </div>
                        <p class="text-muted mb-4">These terms and conditions are governed by and construed in accordance with the laws of Pakistan.</p>
                        
                        <div class="governing-info">
                            <div class="governing-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold mb-2"><i class="fas fa-globe-asia me-2 text-primary"></i>Jurisdiction</h6>
                                <p class="small text-muted mb-0">Islamic Republic of Pakistan</p>
                            </div>
                            <div class="governing-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold mb-2"><i class="fas fa-landmark me-2 text-success"></i>Court Jurisdiction</h6>
                                <p class="small text-muted mb-0">Exclusive jurisdiction of courts in Pakistan</p>
                            </div>
                            <div class="governing-item bg-light rounded-3 p-4">
                                <h6 class="fw-bold mb-2"><i class="fas fa-file-alt me-2 text-warning"></i>Legal Compliance</h6>
                                <p class="small text-muted mb-0">All applicable laws and regulations of Pakistan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div id="contact" class="terms-section bg-white rounded-4 p-5 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-envelope fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Contact Information</h2>
                        </div>
                        <p class="text-muted mb-4">If you have any questions regarding these Terms of Service, please contact our legal desk.</p>
                        
                        <div class="contact-info-grid">
                            <div class="contact-item bg-light rounded-3 p-4">
                                <h6 class="fw-bold mb-2"><i class="fas fa-envelope me-2 text-danger"></i>Email</h6>
                                <p class="small text-muted mb-0">ibraheem@pk-news.com</p>
                            </div>
                            <div class="contact-item bg-light rounded-3 p-4">
                                <h6 class="fw-bold mb-2"><i class="fas fa-globe me-2 text-primary"></i>Website</h6>
                                <p class="small text-muted mb-0">www.pk-news.com</p>
                            </div>
                            <div class="contact-item bg-light rounded-3 p-4">
                                <h6 class="fw-bold mb-2"><i class="fas fa-map-marker-alt me-2 text-success"></i>Location</h6>
                                <p class="small text-muted mb-0">Nowshera, KPK, Pakistan</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-success mt-4">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Agreement:</strong> By using PK Live News, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-4 py-2 rounded-pill">Common Questions</span>
            <h2 class="display-5 fw-bold mb-3">Terms <span class="text-primary">FAQ</span></h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                Find answers to frequently asked questions about our Terms of Service.
            </p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion accordion-flush" id="termsFaq">
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tfaq1">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                Can I use PK Live News content for my website?
                            </button>
                        </h2>
                        <div id="tfaq1" class="accordion-collapse collapse show" data-bs-parent="#termsFaq">
                            <div class="accordion-body">
                                You may view and print content for personal, non-commercial use only. Republishing, selling, or using our content for commercial purposes requires explicit written consent from PK Live News.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tfaq2">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                What happens if I violate the terms?
                            </button>
                        </h2>
                        <div id="tfaq2" class="accordion-collapse collapse" data-bs-parent="#termsFaq">
                            <div class="accordion-body">
                                We reserve the right to terminate or suspend your access to the platform for violations of these terms. This may include removing comments, blocking accounts, or taking legal action if necessary.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tfaq3">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                Are the news articles always accurate?
                            </button>
                        </h2>
                        <div id="tfaq3" class="accordion-collapse collapse" data-bs-parent="#termsFaq">
                            <div class="accordion-body">
                                While we strive for accuracy, news is fast-moving and information may become outdated quickly. We do not warrant the completeness or accuracy of published information. We encourage users to verify critical information from multiple sources.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tfaq4">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                Can I post comments on articles?
                            </button>
                        </h2>
                        <div id="tfaq4" class="accordion-collapse collapse" data-bs-parent="#termsFaq">
                            <div class="accordion-body">
                                Yes, we encourage engagement through comments. However, you must follow our community guidelines and not post defamatory, abusive, spam, or illegal content. We reserve the right to moderate or remove comments at our discretion.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tfaq5">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                How often are the terms updated?
                            </button>
                        </h2>
                        <div id="tfaq5" class="accordion-collapse collapse" data-bs-parent="#termsFaq">
                            <div class="accordion-body">
                                We may update these terms periodically without prior notice. Continued use of the platform after changes indicates acceptance of the updated terms. We recommend reviewing this page regularly for any updates.
                            </div>
                        </div>
                    </div>
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

.bg-gradient-dark {
    background: linear-gradient(135deg, #0f3460 0%, #16213e 100%);
}

.terms-nav .nav-link {
    background: white;
    border: 1px solid #dee2e6;
    padding: 12px 20px;
    transition: all 0.3s ease;
}

.terms-nav .nav-link:hover,
.terms-nav .nav-link.active {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
    transform: translateX(5px);
}

.terms-section {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.terms-section:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.license-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.license-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.rule-item {
    transition: transform 0.3s ease;
}

.rule-item:hover {
    transform: translateX(5px);
}

.disclaimer-item,
.third-party-item,
.reason-item,
.governing-item {
    transition: transform 0.3s ease;
}

.disclaimer-item:hover,
.third-party-item:hover,
.reason-item:hover,
.governing-item:hover {
    transform: translateX(5px);
}

.liability-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.liability-item {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.liability-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.contact-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.contact-item {
    transition: transform 0.3s ease;
}

.contact-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.accordion-button:not(.collapsed) {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .sticky-top {
        position: static !important;
    }
    
    .terms-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .terms-nav .nav-link {
        flex: 1;
        min-width: 150px;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .liability-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .contact-info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .liability-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Smooth scroll for navigation links
document.querySelectorAll('.terms-nav a').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            
            // Update active state
            document.querySelectorAll('.terms-nav .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');
        }
    });
});

// Update active nav link on scroll
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('.terms-section');
    const navLinks = document.querySelectorAll('.terms-nav .nav-link');
    
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if (pageYOffset >= sectionTop - 200) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + current) {
            link.classList.add('active');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
