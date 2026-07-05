</main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Admin Panel JavaScript -->
    <script>
        // Auto-refresh for live data
        let refreshInterval;
        
        function startAutoRefresh(interval = 30000) {
            refreshInterval = setInterval(() => {
                if (document.querySelector('[data-auto-refresh]')) {
                    location.reload();
                }
            }, interval);
        }
        
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        // Confirm dangerous actions
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
        
        // Show loading state
        function showLoading(element) {
            element.disabled = true;
            element.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
        }
        
        // Reset loading state
        function resetLoading(element, originalText) {
            element.disabled = false;
            element.innerHTML = originalText;
        }
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Start auto-refresh if needed
            if (document.querySelector('[data-auto-refresh]')) {
                startAutoRefresh();
            }
        });
        
        // Handle form submissions with loading states
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.hasAttribute('data-no-loading')) {
                    showLoading(submitBtn);
                }
            });
        });
    </script>
</body>
</html>
