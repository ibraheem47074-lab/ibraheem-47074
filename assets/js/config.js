// Development configuration
const CONFIG = {
    // Set this to true for development, false for production
    isDevelopment: true,
    
    // Base URL for API calls
    get baseUrl() {
        if (this.isDevelopment) {
            // Force HTTP for development to avoid SSL issues
            return 'http://localhost/PK-LIVE%20NEWS';
        }
        return window.location.origin;
    },
    
    // API endpoints
    api: {
        breakingNews: '/api/breaking-news.php',
        liveStatus: '/api/live-status.php',
        votePoll: '/api/vote-poll.php',
        submitComment: '/api/submit-comment.php',
        search: '/api/search.php'
    }
};
