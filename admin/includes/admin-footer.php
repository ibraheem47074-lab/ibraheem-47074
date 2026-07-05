</main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Auto-refresh for dashboard
        let autoRefreshInterval;
        
        function startAutoRefresh() {
            autoRefreshInterval = setInterval(() => {
                if (typeof refreshDashboard === 'function') {
                    refreshDashboard();
                }
            }, 30000); // Refresh every 30 seconds
        }
        
        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        }
        
        // Start auto-refresh when page loads
        document.addEventListener('DOMContentLoaded', function() {
            startAutoRefresh();
        });
        
        // Stop auto-refresh when page is hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });
    </script>
</body>
</html>
