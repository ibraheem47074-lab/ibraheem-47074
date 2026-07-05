
<!-- Main Footer -->
    <footer class="main-footer bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4 class="text-danger">PK News</h4>
                    <p>Your trusted source for breaking news, live updates, and comprehensive coverage of events across Pakistan and internationally.</p>
                    <div class="social-links mt-3">
                        <a href="https://pk-news.com" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://pk-news.com" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="https://pk-news.com" class="text-white me-3"><i class="fab fa-youtube"></i></a>
                        <a href="https://pk-news.com" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="about.php" class="text-white text-decoration-none">About Us</a></li>
                        <li><a href="live.php" class="text-white text-decoration-none">Live TV</a></li>
                        <li><a href="contact.php" class="text-white text-decoration-none">Contact Us</a></li>
                        <li><a href="privacy-policy.php" class="text-white text-decoration-none">Privacy Policy</a></li>
                        <li><a href="terms.php" class="text-white text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-envelope me-2"></i> ibraheem@pk-news.com</p>
                    <p><i class="fas fa-phone me-2"></i> +92 311 8195630</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Islamabad, Pakistan</p>
                    
                    <!-- Newsletter -->
                    <div class="newsletter mt-3">
                        <h6>Subscribe to Newsletter</h6>
                        <form class="d-flex">
                            <input type="email" class="form-control me-2" placeholder="Your email">
                            <button type="submit" class="btn btn-danger">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <hr class="bg-white my-4">
            
            <div class="row">
                <div class="col-md-12 text-center">
                   <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved. Developed by Muhammad Ibraheem & Muhammad Kashif</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-danger back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Configuration JS -->
    <script src="assets/js/config.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <!-- Heat Map JS -->
    <script src="assets/js/heatmap.js"></script>
    <!-- Image Lightbox JS -->
    <script src="assets/js/image-lightbox.js"></script>
    <!-- Video Lightbox JS -->
    <script src="assets/js/video-lightbox.js"></script>
    
    <!-- Service Worker Registration for Android PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                // Only register service worker on HTTPS
                const isSecure = window.location.protocol === 'https:';
                
                if (isSecure) {
                    const swPath = '/service-worker.js';
                    const swScope = '/';
                    navigator.serviceWorker.register(swPath, { scope: swScope })
                        .then(registration => {
                            console.log('Service Worker registered with scope:', registration.scope);
                        
                        // Check for service worker updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New version available
                                    if (confirm('New version of PK Live News is available. Reload to update?')) {
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Service Worker registration failed:', error);
                    });
                } else {
                    console.log('Service Worker registration skipped: requires HTTPS');
                }
            });
        }

        // PWA Installation Prompt for Android
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show install button after a delay
            setTimeout(() => {
                showInstallButton();
            }, 3000);
        });

        function showInstallButton() {
            const installBtn = document.createElement('div');
            installBtn.innerHTML = `
                <div class="pwa-install-prompt position-fixed bottom-0 start-0 w-100 p-3 bg-dark text-white" style="z-index: 9999;">
                    <div class="container">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-download me-3"></i>
                                <div>
                                    <strong>Install PK Live News</strong><br>
                                    <small>Get our app for faster news updates</small>
                                </div>
                            </div>
                            <div>
                                <button onclick="installPWA()" class="btn btn-danger btn-sm me-2">Install</button>
                                <button onclick="dismissInstallPrompt()" class="btn btn-secondary btn-sm">Not now</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(installBtn);
        }

        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('PWA installation accepted');
                        // Track installation event
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'pwa_install', {
                                'event_category': 'engagement',
                                'event_label': 'android_pwa'
                            });
                        }
                    }
                    deferredPrompt = null;
                    dismissInstallPrompt();
                });
            }
        }

        function dismissInstallPrompt() {
            const prompt = document.querySelector('.pwa-install-prompt');
            if (prompt) {
                prompt.remove();
            }
        }

        // Handle app installed event
        window.addEventListener('appinstalled', (evt) => {
            console.log('PK Live News PWA was installed');
            // Track successful installation
            if (typeof gtag !== 'undefined') {
                gtag('event', 'pwa_installed', {
                    'event_category': 'engagement',
                    'event_label': 'android_pwa_success'
                });
            }
        });
    </script>
    
    <script>
        // Dark Mode Toggle
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        }
        
        // Load dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
        
        // Back to Top
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        document.getElementById('backToTop').addEventListener('click', function() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
        
        // Live Indicator Animation
        setInterval(function() {
            const indicators = document.querySelectorAll('.live-indicator');
            indicators.forEach(function(indicator) {
                indicator.style.opacity = indicator.style.opacity === '0' ? '1' : '0';
            });
        }, 1000);
        
        // Video Sharing Functions
        function shareVideoOnFacebook(videoUrl, title) {
            const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(videoUrl)}&quote=${encodeURIComponent(title)}`;
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
        
        function shareVideoOnTwitter(videoUrl, title) {
            const shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(videoUrl)}&text=${encodeURIComponent(title)}`;
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
        
        function shareVideoOnWhatsApp(videoUrl, title) {
            const shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + videoUrl)}`;
            window.open(shareUrl, '_blank');
        }
        
        function shareVideoOnTelegram(videoUrl, title) {
            const shareUrl = `https://t.me/share/url?url=${encodeURIComponent(videoUrl)}&text=${encodeURIComponent(title)}`;
            window.open(shareUrl, '_blank');
        }
        
        function copyVideoUrl(videoUrl) {
            navigator.clipboard.writeText(videoUrl).then(function() {
                // Show success message
                const toast = document.createElement('div');
                toast.className = 'position-fixed top-0 end-0 m-3 alert alert-success';
                toast.innerHTML = '<i class="fas fa-check-circle me-2"></i>Video link copied to clipboard!';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }).catch(function(err) {
                console.error('Failed to copy video URL: ', err);
                alert('Failed to copy video URL. Please copy manually.');
            });
        }
    </script>
</body>
</html>
