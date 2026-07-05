<?php
require_once 'config/database.php';
require_once 'includes/language_functions.php';

$page_title = 'Privacy Policy';
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
                    <i class="fas fa-shield-alt me-2"></i>Your Privacy Matters
                </span>
                <h1 class="display-2 fw-bold mb-4" style="color: #000000; text-shadow: 0 2px 8px rgba(255,255,255,0.3);">
                    Privacy <span class="text-danger">Policy</span>
                </h1>
                <p class="lead mb-4 fs-4" style="color: #000000; opacity: 0.95; text-shadow: 0 2px 4px rgba(255,255,255,0.3);">
                    Your trust is our priority. Learn how we protect your data and respect your privacy.
                </p>
                <p class="mb-5 fs-5" style="color: #000000; opacity: 0.9; text-shadow: 0 2px 4px rgba(255,255,255,0.3);">
                    Last updated: <?php echo date('F j, Y'); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Privacy Policy Content -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="sticky-top" style="top: 100px;">
                    <div class="nav flex-column nav-pills privacy-nav" role="tablist">
                        <a class="nav-link active mb-2 rounded-pill" href="#information">Information We Collect</a>
                        <a class="nav-link mb-2 rounded-pill" href="#usage">How We Use Information</a>
                        <a class="nav-link mb-2 rounded-pill" href="#cookies">Cookies & Tracking</a>
                        <a class="nav-link mb-2 rounded-pill" href="#security">Data Security</a>
                        <a class="nav-link mb-2 rounded-pill" href="#rights">Your Rights</a>
                        <a class="nav-link mb-2 rounded-pill" href="#third-party">Third-Party Services</a>
                        <a class="nav-link mb-2 rounded-pill" href="#children">Children's Privacy</a>
                        <a class="nav-link mb-2 rounded-pill" href="#changes">Policy Changes</a>
                        <a class="nav-link mb-2 rounded-pill" href="#contact">Contact Us</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="privacy-content">
                    <!-- Information We Collect -->
                    <div id="information" class="privacy-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-database fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Information We Collect</h2>
                        </div>
                        <p class="text-muted mb-4">PK Live News collects certain information to provide better services to our users. We are transparent about what we collect and why.</p>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-card bg-light rounded-3 p-4">
                                    <h5 class="fw-bold text-danger mb-2"><i class="fas fa-user me-2"></i>Personal Information</h5>
                                    <p class="small text-muted mb-0">Name, email address, phone number when you register, subscribe, or contact us</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card bg-light rounded-3 p-4">
                                    <h5 class="fw-bold text-primary mb-2"><i class="fas fa-chart-line me-2"></i>Usage Data</h5>
                                    <p class="small text-muted mb-0">Pages visited, time spent on site, click patterns, reading preferences</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card bg-light rounded-3 p-4">
                                    <h5 class="fw-bold text-success mb-2"><i class="fas fa-laptop me-2"></i>Device Information</h5>
                                    <p class="small text-muted mb-0">IP address, browser type, operating system, device identifiers</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card bg-light rounded-3 p-4">
                                    <h5 class="fw-bold text-warning mb-2"><i class="fas fa-cookie-bite me-2"></i>Cookies</h5>
                                    <p class="small text-muted mb-0">To enhance user experience, remember preferences, and track analytics</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- How We Use Information -->
                    <div id="usage" class="privacy-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-cogs fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">How We Use Your Information</h2>
                        </div>
                        <p class="text-muted mb-4">We use collected information responsibly to improve your experience and provide relevant content.</p>
                        
                        <div class="usage-grid">
                            <div class="usage-item d-flex align-items-start mb-3">
                                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Personalized Content</h6>
                                    <p class="small text-muted mb-0">Deliver news content tailored to your interests and reading habits</p>
                                </div>
                            </div>
                            <div class="usage-item d-flex align-items-start mb-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Service Improvement</h6>
                                    <p class="small text-muted mb-0">Enhance website functionality and user experience based on feedback</p>
                                </div>
                            </div>
                            <div class="usage-item d-flex align-items-start mb-3">
                                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Communication</h6>
                                    <p class="small text-muted mb-0">Send newsletters and updates (only with your explicit consent)</p>
                                </div>
                            </div>
                            <div class="usage-item d-flex align-items-start mb-3">
                                <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Analytics</h6>
                                    <p class="small text-muted mb-0">Analyze traffic patterns to understand user behavior and preferences</p>
                                </div>
                            </div>
                            <div class="usage-item d-flex align-items-start">
                                <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="fas fa-ad"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Advertising</h6>
                                    <p class="small text-muted mb-0">Display relevant advertisements to support our free service</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cookies & Tracking -->
                    <div id="cookies" class="privacy-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-cookie-bite fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Cookies & Tracking Technologies</h2>
                        </div>
                        <p class="text-muted mb-4">We use cookies and similar technologies to enhance your browsing experience.</p>
                        
                        <div class="cookie-types">
                            <div class="cookie-type mb-2">
                                <h6 class="fw-bold text-danger mb-2">Essential Cookies</h6>
                                <p class="small text-muted mb-0">Required for basic website functionality and security. Cannot be disabled.</p>
                            </div>
                            <div class="cookie-type mb-2">
                                <h6 class="fw-bold text-primary mb-2">Analytics Cookies</h6>
                                <p class="small text-muted mb-0">Help us understand how visitors use our website to improve performance.</p>
                            </div>
                            <div class="cookie-type mb-2">
                                <h6 class="fw-bold text-success mb-2">Preference Cookies</h6>
                                <p class="small text-muted mb-0">Remember your settings and preferences for future visits.</p>
                            </div>
                            <div class="cookie-type">
                                <h6 class="fw-bold text-warning mb-2">Advertising Cookies</h6>
                                <p class="small text-muted mb-0">Used to deliver relevant advertisements and track ad performance.</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Cookie Control:</strong> You can manage cookie preferences through your browser settings. However, disabling essential cookies may affect website functionality.
                        </div>
                    </div>

                    <!-- Data Security -->
                    <div id="security" class="privacy-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-lock fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Data Security Measures</h2>
                        </div>
                        <p class="text-muted mb-4">We implement industry-standard security measures to protect your information.</p>
                        
                        <div class="security-features">
                            <div class="security-feature d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">SSL Encryption</h6>
                                    <p class="small text-muted mb-0">All data transmission is encrypted using SSL/TLS protocols</p>
                                </div>
                            </div>
                            <div class="security-feature d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Secure Storage</h6>
                                    <p class="small text-muted mb-0">Personal data stored in secure, access-controlled databases</p>
                                </div>
                            </div>
                            <div class="security-feature d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Regular Updates</h6>
                                    <p class="small text-muted mb-0">Security patches and updates applied regularly</p>
                                </div>
                            </div>
                            <div class="security-feature d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Limited Access</h6>
                                    <p class="small text-muted mb-0">Only authorized personnel can access personal data</p>
                                </div>
                            </div>
                            <div class="security-feature d-flex align-items-center">
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Data Backup</h6>
                                    <p class="small text-muted mb-0">Regular backups to prevent data loss</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Your Rights -->
                    <div id="rights" class="privacy-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-user-shield fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Your Privacy Rights</h2>
                        </div>
                        <p class="text-muted mb-4">You have the right to control your personal information. Here's how you can exercise your rights:</p>
                        
                        <div class="rights-grid">
                            <div class="right-card bg-light rounded-3 p-4 text-center">
                                <div class="bg-danger bg-opacity-10 text-danger rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Access</h6>
                                <p class="small text-muted mb-0">Request access to your personal information</p>
                            </div>
                            <div class="right-card bg-light rounded-3 p-4 text-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Correct</h6>
                                <p class="small text-muted mb-0">Update or correct inaccurate data</p>
                            </div>
                            <div class="right-card bg-light rounded-3 p-4 text-center">
                                <div class="bg-success bg-opacity-10 text-success rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-trash"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Delete</h6>
                                <p class="small text-muted mb-0">Request deletion of your account and data</p>
                            </div>
                            <div class="right-card bg-light rounded-3 p-4 text-center">
                                <div class="bg-warning bg-opacity-10 text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Opt-Out</h6>
                                <p class="small text-muted mb-0">Unsubscribe from marketing communications</p>
                            </div>
                            <div class="right-card bg-light rounded-3 p-4 text-center">
                                <div class="bg-info bg-opacity-10 text-info rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-sliders-h"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Control</h6>
                                <p class="small text-muted mb-0">Manage your cookie and privacy preferences</p>
                            </div>
                            <div class="right-card bg-light rounded-3 p-4 text-center">
                                <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-download"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Export</h6>
                                <p class="small text-muted mb-0">Download your personal data</p>
                            </div>
                        </div>
                    </div>

                    <!-- Third-Party Services -->
                    <div id="third-party" class="privacy-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-external-link-alt fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Third-Party Services</h2>
                        </div>
                        <p class="text-muted mb-4">We use third-party services to enhance our website functionality. These services have their own privacy policies.</p>
                        
                        <div class="third-party-list">
                            <div class="third-party-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold mb-2"><i class="fab fa-google me-2 text-danger"></i>Google Analytics</h6>
                                <p class="small text-muted mb-0">Analyzes website traffic anonymously. No personally identifiable information is shared.</p>
                            </div>
                            <div class="third-party-item bg-light rounded-3 p-4 mb-3">
                                <h6 class="fw-bold mb-2"><i class="fab fa-google me-2 text-primary"></i>Google AdSense</h6>
                                <p class="small text-muted mb-0">Displays advertisements based on your interests and browsing behavior.</p>
                            </div>
                            <div class="third-party-item bg-light rounded-3 p-4">
                                <h6 class="fw-bold mb-2"><i class="fas fa-globe me-2 text-success"></i>External Links</h6>
                                <p class="small text-muted mb-0">Our website may contain links to third-party sites. We are not responsible for their privacy practices.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Children's Privacy -->
                    <div id="children" class="privacy-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-child fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Children's Privacy</h2>
                        </div>
                        <p class="text-muted mb-4">PK Live News is not directed to children under 13 years of age.</p>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> We do not knowingly collect personal information from children under 13. If we become aware that we have collected such information, we will take immediate steps to delete it.
                        </div>
                    </div>

                    <!-- Policy Changes -->
                    <div id="changes" class="privacy-section bg-white rounded-4 p-5 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-sync-alt fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Changes to This Policy</h2>
                        </div>
                        <p class="text-muted mb-4">We may update this privacy policy from time to time to reflect changes in our practices.</p>
                        
                        <ul class="list-unstyled">
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check text-primary me-2 mt-1"></i>
                                <span class="text-muted">Posting the new policy on this page</span>
                            </li>
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check text-primary me-2 mt-1"></i>
                                <span class="text-muted">Updating the "Last updated" date</span>
                            </li>
                            <li class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check text-primary me-2 mt-1"></i>
                                <span class="text-muted">Sending email notifications for major changes</span>
                            </li>
                            <li class="d-flex align-items-start">
                                <i class="fas fa-check text-primary me-2 mt-1"></i>
                                <span class="text-muted">Website notifications for important updates</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact Information -->
                    <div id="contact" class="privacy-section bg-white rounded-4 p-5 shadow-sm">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-envelope fa-xl"></i>
                            </div>
                            <h2 class="fw-bold mb-0">Contact Information</h2>
                        </div>
                        <p class="text-muted mb-4">If you have questions about this Privacy Policy or need to exercise your rights, please contact us.</p>
                        
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
                                <h6 class="fw-bold mb-2"><i class="fas fa-clock me-2 text-success"></i>Response Time</h6>
                                <p class="small text-muted mb-0">Within 24-48 hours</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-success mt-4">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Agreement:</strong> By using PK Live News, you agree to the collection and use of information as described in this policy.
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
            <h2 class="display-5 fw-bold mb-3">Privacy <span class="text-primary">FAQ</span></h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                Find answers to frequently asked questions about our privacy practices.
            </p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion accordion-flush" id="privacyFaq">
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                Do you sell my personal information?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#privacyFaq">
                            <div class="accordion-body">
                                No, we never sell your personal information to third parties. We only share data with service providers who assist us in operating our website, and they are bound by strict confidentiality agreements.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                How long do you keep my data?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#privacyFaq">
                            <div class="accordion-body">
                                We retain your personal information only as long as necessary to provide our services. When you delete your account, we remove your personal data from our active databases within 30 days.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                Can I opt out of cookies?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#privacyFaq">
                            <div class="accordion-body">
                                Yes, you can manage cookie preferences through your browser settings. However, please note that essential cookies are required for basic website functionality and cannot be disabled.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                How do I delete my account?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#privacyFaq">
                            <div class="accordion-body">
                                You can request account deletion by contacting us at ibraheem@pk-news.com. We will process your request within 30 days and confirm when your data has been permanently deleted.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                Is my data secure?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#privacyFaq">
                            <div class="accordion-body">
                                Yes, we use industry-standard security measures including SSL encryption, secure databases, and regular security audits to protect your data. We continuously monitor and update our security practices.
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

.privacy-nav .nav-link {
    background: white;
    border: 1px solid #dee2e6;
    padding: 12px 20px;
    transition: all 0.3s ease;
}

.privacy-nav .nav-link:hover,
.privacy-nav .nav-link.active {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
    transform: translateX(5px);
}

.privacy-section {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.privacy-section:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.info-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.usage-item {
    transition: background-color 0.3s ease;
}

.usage-item:hover {
    background-color: rgba(0,0,0,0.02);
}

.cookie-type {
    padding: 15px;
    border-left: 4px solid #dc3545;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 8px;
}

.security-feature {
    transition: transform 0.3s ease;
}

.security-feature:hover {
    transform: translateX(5px);
}

.rights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.right-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.right-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.third-party-item {
    transition: transform 0.3s ease;
}

.third-party-item:hover {
    transform: translateX(5px);
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
    
    .privacy-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .privacy-nav .nav-link {
        flex: 1;
        min-width: 150px;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .rights-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .contact-info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .rights-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Smooth scroll for navigation links
document.querySelectorAll('.privacy-nav a').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            
            // Update active state
            document.querySelectorAll('.privacy-nav .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');
        }
    });
});

// Update active nav link on scroll
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('.privacy-section');
    const navLinks = document.querySelectorAll('.privacy-nav .nav-link');
    
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
