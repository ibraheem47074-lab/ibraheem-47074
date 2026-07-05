<?php
require_once 'config/database.php';
require_once 'includes/language_functions.php';
require_once 'includes/email_functions.php';

$page_title = 'Contact Us';
$current_lang = get_current_language();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $subject = clean_input($_POST['subject'] ?? '');
    $message = clean_input($_POST['message'] ?? '');
    $inquiry_type = clean_input($_POST['inquiry_type'] ?? 'general');
    
    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($subject)) $errors[] = "Subject is required";
    if (empty($message)) $errors[] = "Message is required";
    
    if (empty($errors)) {
        // Prepare data based on inquiry type
        if ($inquiry_type === 'advertising') {
            $ad_data = [
                'company_name' => clean_input($_POST['company_name'] ?? ''),
                'contact_name' => $name,
                'email' => $email,
                'phone' => clean_input($_POST['phone'] ?? ''),
                'budget' => clean_input($_POST['budget'] ?? ''),
                'message' => $message
            ];
            
            if (sendAdvertisingInquiry($ad_data)) {
                sendAutoResponse($email, 'advertising');
                $success = "Your advertising inquiry has been sent successfully! We'll respond within 24-48 hours.";
            } else {
                $errors[] = "Failed to send inquiry. Please try again.";
            }
        } else {
            $support_data = [
                'name' => $name,
                'email' => $email,
                'priority' => clean_input($_POST['priority'] ?? 'medium'),
                'subject' => $subject,
                'message' => $message
            ];
            
            if (sendSupportTicket($support_data)) {
                sendAutoResponse($email, 'support');
                $success = "Your message has been sent successfully! We'll respond within 24 hours.";
            } else {
                $errors[] = "Failed to send message. Please try again.";
            }
        }
    }
}

// Get site settings for contact info
$settings_query = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'contact_email', 'facebook_url', 'twitter_url', 'youtube_url')";
$settings_result = mysqli_query($conn, $settings_query);
$settings = [];
while ($row = mysqli_fetch_assoc($settings_result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section bg-gradient position-relative overflow-hidden" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 60vh; display: flex; align-items: center;">
    <div class="absolute-pattern position-absolute top-0 start-0 w-100 h-100 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20100%20100%22%3E%3Ccircle%20cx%3D%2250%22%20cy%3D%2250%22%20r%3D%2240%22%20fill%3D%22none%22%20stroke%3D%22white%22%20stroke-width%3D%220.5%22%2F%3E%3C%2Fsvg%3E'); background-size: 50px 50px;"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <span class="badge bg-danger bg-opacity-75 text-white mb-3 px-4 py-2 rounded-pill" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    <i class="fas fa-envelope-open-text me-2"></i>Get In Touch
                </span>
                <h1 class="display-2 fw-bold mb-4" style="color: #000000; text-shadow: 0 2px 8px rgba(255,255,255,0.3);">
                    Contact <span class="text-danger">PK Live News</span>
                </h1>
                <p class="lead mb-4 fs-4" style="color: #000000; opacity: 0.95; text-shadow: 0 2px 4px rgba(255,255,255,0.3);">
                    We'd love to hear from you. Whether you have a question, feedback, or a story to share, our team is here to help.
                </p>
                <p class="mb-5 fs-5" style="color: #000000; opacity: 0.9; text-shadow: 0 2px 4px rgba(255,255,255,0.3);">
                    Reach out to us through any of the channels below. Our dedicated team responds to all inquiries within 24 hours.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="#contact-form" class="btn btn-danger btn-lg px-5 py-3 fw-bold rounded-pill shadow-lg hover-lift">
                        <i class="fas fa-paper-plane me-2"></i>Send Message
                    </a>
                    <a href="#faq" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold rounded-pill">
                        <i class="fas fa-question-circle me-2"></i>FAQ
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <div class="hero-image-wrapper position-relative rounded-4 overflow-hidden shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=800&h=600&fit=crop" alt="Contact Us" class="img-fluid w-100" style="min-height: 400px; object-fit: cover;">
                        <div class="position-absolute bottom-0 start-0 end-0 bg-gradient-dark p-4" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                            <div class="d-flex align-items-center gap-4">
                                <div class="text-white">
                                    <div class="display-6 fw-bold">24/7</div>
                                    <div class="small text-white opacity-75">Support Available</div>
                                </div>
                                <div class="vr bg-white opacity-25" style="height: 40px;"></div>
                                <div class="text-white">
                                    <div class="display-6 fw-bold">&lt;24h</div>
                                    <div class="small text-white opacity-75">Response Time</div>
                                </div>
                                <div class="vr bg-white opacity-25" style="height: 40px;"></div>
                                <div class="text-white">
                                    <div class="display-6 fw-bold">100%</div>
                                    <div class="small text-white opacity-75">Satisfaction</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Cards Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="contact-card bg-light rounded-4 p-5 text-center h-100 hover-lift border-start border-5 border-danger">
                    <div class="mb-4">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-map-marker-alt fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Visit Our Office</h4>
                    <p class="text-muted mb-0">
                        PK Live News Headquarters<br>
                        Nowshera, KPK, Pakistan<br>
                       
                    </p>
                    <a href="https://maps.google.com" target="_blank" class="btn btn-outline-danger btn-sm mt-3 rounded-pill">
                        <i class="fas fa-directions me-2"></i>Get Directions
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="contact-card bg-light rounded-4 p-5 text-center h-100 hover-lift border-start border-5 border-primary">
                    <div class="mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-phone-alt fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3">Call Us</h4>
                    <p class="text-muted mb-0">
                        Main: +92 311 8195630<br>
                        News Desk: +92 330 394061<br>
                        Advertising: +92 311 8195630
                    </p>
                    <a href="tel:+923118195630" class="btn btn-outline-primary btn-sm mt-3 rounded-pill">
                        <i class="fas fa-phone me-2"></i>Call Now
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="contact-card bg-light rounded-4 p-5 text-center h-100 hover-lift border-start border-5 border-success">
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
                    <a href="mailto:ibraheem@pk-news.com" class="btn btn-outline-success btn-sm mt-3 rounded-pill">
                        <i class="fas fa-paper-plane me-2"></i>Send Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section id="contact-form" class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-12 text-center mb-5">
                <span class="badge bg-danger bg-opacity-10 text-danger mb-3 px-4 py-2 rounded-pill">Send Us a Message</span>
                <h2 class="display-5 fw-bold mb-3">We're Here to <span class="text-danger">Help</span></h2>
                <p class="lead text-muted mx-auto" style="max-width: 700px;">
                    Fill out the form below and our team will get back to you within 24 hours.
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="contact-form-wrapper bg-white rounded-4 p-5 shadow-lg">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x me-3"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Success!</h5>
                                    <?php echo $success; ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Please fix the following errors:</h5>
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="contactForm">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="name" class="form-label fw-bold">Your Name *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 bg-light" id="name" name="name" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                           placeholder="Enter your full name" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="email" class="form-label fw-bold">Email Address *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0 bg-light" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           placeholder="Enter your email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-phone text-muted"></i>
                                    </span>
                                    <input type="tel" class="form-control border-start-0 bg-light" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                           placeholder="+92 XXX XXXXXXX">
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="subject" class="form-label fw-bold">Subject *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-heading text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 bg-light" id="subject" name="subject" 
                                           value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" 
                                           placeholder="What is this about?" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="inquiry_type" class="form-label fw-bold">Inquiry Type</label>
                            <select class="form-select bg-light" id="inquiry_type" name="inquiry_type">
                                <option value="general" <?php echo (isset($_POST['inquiry_type']) && $_POST['inquiry_type'] === 'general') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="news_tip" <?php echo (isset($_POST['inquiry_type']) && $_POST['inquiry_type'] === 'news_tip') ? 'selected' : ''; ?>>News Tip / Story</option>
                                <option value="advertising" <?php echo (isset($_POST['inquiry_type']) && $_POST['inquiry_type'] === 'advertising') ? 'selected' : ''; ?>>Advertising Inquiry</option>
                                <option value="careers" <?php echo (isset($_POST['inquiry_type']) && $_POST['inquiry_type'] === 'careers') ? 'selected' : ''; ?>>Career Opportunities</option>
                                <option value="support" <?php echo (isset($_POST['inquiry_type']) && $_POST['inquiry_type'] === 'support') ? 'selected' : ''; ?>>Technical Support</option>
                                <option value="feedback" <?php echo (isset($_POST['inquiry_type']) && $_POST['inquiry_type'] === 'feedback') ? 'selected' : ''; ?>>Feedback</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label fw-bold">Your Message *</label>
                            <textarea class="form-control bg-light" id="message" name="message" rows="6" 
                                      placeholder="Tell us more about your inquiry..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    <i class="fas fa-newspaper me-2 text-muted"></i>Subscribe to our newsletter for latest news updates
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="privacy" name="privacy" required>
                                <label class="form-check-label" for="privacy">
                                    <i class="fas fa-shield-alt me-2 text-muted"></i>I agree to the <a href="privacy-policy.php" class="text-danger">Privacy Policy</a> and <a href="terms.php" class="text-danger">Terms of Service</a>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger btn-lg px-5 py-3 fw-bold rounded-pill shadow-lg hover-lift w-100">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Office Hours Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-4 py-2 rounded-pill">When to Reach Us</span>
                <h2 class="display-5 fw-bold mb-3">Office <span class="text-primary">Hours</span></h2>
                <p class="lead text-muted mb-4">
                    Our team is available during the following hours. For urgent matters outside these hours, please use our emergency contact.
                </p>
                
                <div class="hours-list">
                    <div class="hours-item d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Monday - Friday</h6>
                                <p class="text-muted small mb-0">Regular business hours</p>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">9:00 AM - 6:00 PM</span>
                    </div>
                    
                    <div class="hours-item d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Saturday</h6>
                                <p class="text-muted small mb-0">Limited hours</p>
                            </div>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">10:00 AM - 4:00 PM</span>
                    </div>
                    
                    <div class="hours-item d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Sunday</h6>
                                <p class="text-muted small mb-0">Closed</p>
                            </div>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Closed</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="map-wrapper rounded-4 overflow-hidden shadow-lg">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d105518.63686074665!2d71.8775!3d34.0151!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38de9e6e3e6e6e6e%3A0x6e6e6e6e6e6e6e6e!2sNowshera%2C%20KPK%2C%20Pakistan!5e0!3m2!1sen!2s!4v1620000000000!5m2!1sen!2s" 
                            width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media Section -->
<section class="py-5 bg-gradient-dark position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f3460 0%, #16213e 100%);">
    <div class="container position-relative" style="z-index: 2;">
        <div class="text-center mb-5">
            <span class="badge bg-white bg-opacity-10 text-white mb-3 px-4 py-2 rounded-pill">Stay Connected</span>
            <h2 class="display-5 fw-bold mb-3 text-white">Follow Us on <span class="text-danger">Social Media</span></h2>
            <p class="lead text-white opacity-75 mx-auto" style="max-width: 700px;">
                Stay updated with the latest news and updates by following our social media channels.
            </p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-lg-2 col-md-4 col-6">
                <a href="<?php echo isset($settings['facebook_url']) ? htmlspecialchars($settings['facebook_url']) : '#'; ?>" target="_blank" class="social-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-4 text-center text-decoration-none hover-lift border border-white border-opacity-20 d-block">
                    <div class="mb-3">
                        <i class="fab fa-facebook-f fa-3x text-white"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-0">Facebook</h5>
                    <p class="text-white opacity-75 small mb-0">Like & Follow</p>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="<?php echo isset($settings['twitter_url']) ? htmlspecialchars($settings['twitter_url']) : '#'; ?>" target="_blank" class="social-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-4 text-center text-decoration-none hover-lift border border-white border-opacity-20 d-block">
                    <div class="mb-3">
                        <i class="fab fa-twitter fa-3x text-white"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-0">Twitter</h5>
                    <p class="text-white opacity-75 small mb-0">Follow Us</p>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="<?php echo isset($settings['youtube_url']) ? htmlspecialchars($settings['youtube_url']) : '#'; ?>" target="_blank" class="social-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-4 text-center text-decoration-none hover-lift border border-white border-opacity-20 d-block">
                    <div class="mb-3">
                        <i class="fab fa-youtube fa-3x text-white"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-0">YouTube</h5>
                    <p class="text-white opacity-75 small mb-0">Subscribe</p>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="#" target="_blank" class="social-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-4 text-center text-decoration-none hover-lift border border-white border-opacity-20 d-block">
                    <div class="mb-3">
                        <i class="fab fa-instagram fa-3x text-white"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-0">Instagram</h5>
                    <p class="text-white opacity-75 small mb-0">Follow Us</p>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="#" target="_blank" class="social-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-4 text-center text-decoration-none hover-lift border border-white border-opacity-20 d-block">
                    <div class="mb-3">
                        <i class="fab fa-linkedin-in fa-3x text-white"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-0">LinkedIn</h5>
                    <p class="text-white opacity-75 small mb-0">Connect</p>
                </a>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="#" target="_blank" class="social-card bg-white bg-opacity-10 backdrop-blur rounded-4 p-4 text-center text-decoration-none hover-lift border border-white border-opacity-20 d-block">
                    <div class="mb-3">
                        <i class="fab fa-telegram fa-3x text-white"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-0">Telegram</h5>
                    <p class="text-white opacity-75 small mb-0">Join Channel</p>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-4 py-2 rounded-pill">Common Questions</span>
            <h2 class="display-5 fw-bold mb-3">Frequently Asked <span class="text-primary">Questions</span></h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                Find quick answers to the most common questions about PK Live News.
            </p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion accordion-flush" id="faqAccordion">
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                How can I submit news tips or story ideas?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can submit news tips through our contact form above or email us directly at contact@pklivenews.com. 
                                Our editorial team reviews all submissions and may contact you for further information. We value citizen journalism and encourage our readers to share stories that matter to their communities.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                How do I advertise on PK Live News?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                For advertising inquiries, please contact our advertising team at ibraheem@pk-news.com or call +92 311 8195630. 
                                We offer various advertising packages including banner ads, sponsored content, video advertisements, and social media promotions. Our team will work with you to create a customized advertising strategy that meets your goals and budget.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                Can I work as a reporter or contributor?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We're always looking for talented journalists and contributors! Send your resume and writing samples 
                                to careers@pk-news.com. We offer both full-time positions and freelance opportunities. We value diverse perspectives and are committed to building an inclusive team that represents the communities we serve.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                How do I report an error in a news article?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We take accuracy seriously. If you spot an error in any of our articles, please email corrections@pklivenews.com with the article URL and details of the error. 
                                Our editorial team will review and correct any verified mistakes promptly. We appreciate our readers helping us maintain the highest standards of journalism.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                Do you offer internships for students?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we offer internship programs for students interested in journalism, media, and technology. Internships provide hands-on experience in news reporting, digital media production, and web development. 
                                Send your resume and a cover letter to careers@pk-news.com with the subject line "Internship Application."
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item bg-white rounded-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                How can I subscribe to your newsletter?
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can subscribe to our newsletter by checking the "Subscribe to newsletter" box in the contact form above, or by visiting our homepage and entering your email in the subscription box. 
                                Our newsletter delivers the top stories, breaking news, and exclusive content directly to your inbox every morning.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Links Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-success bg-opacity-10 text-success mb-3 px-4 py-2 rounded-pill">Explore More</span>
            <h2 class="display-5 fw-bold mb-3">Quick <span class="text-success">Links</span></h2>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <a href="index.php" class="quick-link-card bg-light rounded-4 p-4 text-decoration-none d-block hover-lift">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-home fa-xl"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Home</h5>
                            <p class="text-muted small mb-0">Latest news updates</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="live.php" class="quick-link-card bg-light rounded-4 p-4 text-decoration-none d-block hover-lift">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-broadcast-tower fa-xl"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Live TV</h5>
                            <p class="text-muted small mb-0">24/7 live streaming</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="category.php" class="quick-link-card bg-light rounded-4 p-4 text-decoration-none d-block hover-lift">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-tags fa-xl"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Categories</h5>
                            <p class="text-muted small mb-0">Browse by topic</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="search.php" class="quick-link-card bg-light rounded-4 p-4 text-decoration-none d-block hover-lift">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-search fa-xl"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Search</h5>
                            <p class="text-muted small mb-0">Find specific news</p>
                        </div>
                    </div>
                </a>
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
                <h2 class="display-4 fw-bold text-white mb-3">Ready to Get Started?</h2>
                <p class="text-white opacity-90 fs-5 mb-0">
                    Whether you have a question, feedback, or want to explore partnership opportunities, we're here to help. Reach out today and let's start a conversation.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#contact-form" class="btn btn-light btn-lg px-5 py-3 fw-bold rounded-pill shadow-lg hover-lift">
                    <i class="fas fa-arrow-up me-2"></i>Back to Form
                </a>
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

.contact-form-wrapper {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.175);
}

.social-card {
    transition: all 0.3s ease;
}

.social-card:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.quick-link-card {
    transition: all 0.3s ease;
}

.quick-link-card:hover {
    background: #f8f9fa !important;
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.hero-image-wrapper {
    transition: transform 0.3s ease;
}

.hero-image-wrapper:hover {
    transform: scale(1.02);
}

.hours-item {
    transition: background-color 0.3s ease;
}

.hours-item:hover {
    background-color: rgba(0,0,0,0.05);
}

.map-wrapper {
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.175);
}

.input-group-text {
    border-radius: 0.375rem 0 0 0.375rem !important;
}

.form-control.border-start-0 {
    border-radius: 0 0.375rem 0.375rem 0 !important;
}

.accordion-button:not(.collapsed) {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .contact-form-wrapper {
        padding: 1.5rem;
    }
    
    .contact-card {
        padding: 1.5rem;
    }
    
    .hero-image-wrapper {
        min-height: 300px;
    }
    
    .map-wrapper iframe {
        height: 300px;
    }
}
</style>

<script>
// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Form validation enhancement
const form = document.getElementById('contactForm');
if (form) {
    form.addEventListener('submit', function(e) {
        const privacyCheckbox = document.getElementById('privacy');
        if (!privacyCheckbox.checked) {
            e.preventDefault();
            alert('Please agree to the Privacy Policy and Terms of Service to continue.');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>