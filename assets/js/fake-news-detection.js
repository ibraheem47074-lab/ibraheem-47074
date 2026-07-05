/**
 * AI Fake News Detection JavaScript
 * Real-time credibility scoring and user interaction functionality
 */

class FakeNewsDetection {
    constructor() {
        this.apiEndpoint = 'api/fake_news_detection_api.php';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupRealTimeUpdates();
        this.loadCredibilityStyles();
    }

    setupEventListeners() {
        // Credibility badge hover effects
        document.addEventListener('mouseover', (e) => {
            if (e.target.closest('.credibility-badge')) {
                this.showCredibilityTooltip(e.target.closest('.credibility-badge'));
            }
        });

        document.addEventListener('mouseout', (e) => {
            if (e.target.closest('.credibility-badge')) {
                this.hideCredibilityTooltip();
            }
        });

        // Report fake news button
        document.addEventListener('click', (e) => {
            if (e.target.closest('.report-fake-news-btn')) {
                e.preventDefault();
                this.showReportModal(e.target.closest('.report-fake-news-btn').dataset.newsId);
            }
        });

        // View credibility details
        document.addEventListener('click', (e) => {
            if (e.target.closest('.view-credibility-details')) {
                e.preventDefault();
                this.showCredibilityDetails(e.target.closest('.view-credibility-details').dataset.newsId);
            }
        });
    }

    setupRealTimeUpdates() {
        // Check for credibility updates every 30 seconds for articles being analyzed
        setInterval(() => {
            this.checkCredibilityUpdates();
        }, 30000);

        // Monitor articles with "Analyzing..." status
        this.monitorAnalyzingArticles();
    }

    loadCredibilityStyles() {
        // Load CSS if not already loaded
        if (!document.querySelector('link[href*="fake-news-detection.css"]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'assets/css/fake-news-detection.css';
            document.head.appendChild(link);
        }
    }

    showCredibilityTooltip(badge) {
        const tooltip = document.createElement('div');
        tooltip.className = 'credibility-tooltip';
        tooltip.innerHTML = this.generateTooltipContent(badge);
        
        document.body.appendChild(tooltip);
        
        // Position tooltip
        const rect = badge.getBoundingClientRect();
        tooltip.style.position = 'absolute';
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
        tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
        tooltip.style.zIndex = '1000';
        
        // Add animation
        tooltip.style.opacity = '0';
        tooltip.style.transform = 'translateY(-10px)';
        
        requestAnimationFrame(() => {
            tooltip.style.transition = 'all 0.3s ease';
            tooltip.style.opacity = '1';
            tooltip.style.transform = 'translateY(0)';
        });
    }

    hideCredibilityTooltip() {
        const tooltip = document.querySelector('.credibility-tooltip');
        if (tooltip) {
            tooltip.style.opacity = '0';
            tooltip.style.transform = 'translateY(-10px)';
            setTimeout(() => tooltip.remove(), 300);
        }
    }

    generateTooltipContent(badge) {
        const score = badge.querySelector('.credibility-score-text')?.textContent || '0';
        const riskLevel = this.extractRiskLevel(badge);
        
        return `
            <div class="credibility-tooltip-content">
                <div class="credibility-score-display">
                    <strong>AI Credibility Score: ${score}</strong>
                </div>
                <div class="credibility-risk-level">
                    Risk Level: <span class="risk-${riskLevel.toLowerCase()}">${riskLevel}</span>
                </div>
                <div class="credibility-explanation">
                    ${this.getScoreExplanation(parseInt(score))}
                </div>
                <div class="credibility-actions">
                    <button class="btn btn-sm btn-outline-primary view-credibility-details" data-news-id="${this.getNewsIdFromBadge(badge)}">
                        <i class="fas fa-chart-line"></i> View Details
                    </button>
                </div>
            </div>
        `;
    }

    extractRiskLevel(badge) {
        const riskIndicator = badge.querySelector('.credibility-risk-indicator');
        if (riskIndicator) {
            if (riskIndicator.classList.contains('bg-success')) return 'LOW';
            if (riskIndicator.classList.contains('bg-info')) return 'MEDIUM';
            if (riskIndicator.classList.contains('bg-warning')) return 'HIGH';
            if (riskIndicator.classList.contains('bg-danger')) return 'CRITICAL';
        }
        return 'UNKNOWN';
    }

    getScoreExplanation(score) {
        if (score >= 80) {
            return 'High credibility content from verified sources with strong factual support.';
        } else if (score >= 60) {
            return 'Generally credible content with minor concerns about source verification.';
        } else if (score >= 40) {
            return 'Moderate credibility concerns. Content may contain unverified claims or biased sources.';
        } else {
            return 'Low credibility content with significant concerns about accuracy and source reliability.';
        }
    }

    getNewsIdFromBadge(badge) {
        // Extract news ID from the page or data attribute
        const article = badge.closest('article');
        if (article) {
            return article.dataset.newsId || article.querySelector('[data-news-id]')?.dataset.newsId;
        }
        
        // Fallback: try to get from URL
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id') || urlParams.get('news_id');
    }

    checkCredibilityUpdates() {
        const analyzingBadges = document.querySelectorAll('.credibility-badge .fa-robot');
        
        analyzingBadges.forEach(badge => {
            const credibilityBadge = badge.closest('.credibility-badge');
            const newsId = this.getNewsIdFromBadge(credibilityBadge);
            
            if (newsId) {
                this.fetchCredibilityScore(newsId, credibilityBadge);
            }
        });
    }

    monitorAnalyzingArticles() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const analyzingBadge = node.querySelector?.('.credibility-badge .fa-robot');
                        if (analyzingBadge) {
                            this.startMonitoringArticle(analyzingBadge.closest('.credibility-badge'));
                        }
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    startMonitoringArticle(badge) {
        const newsId = this.getNewsIdFromBadge(badge);
        if (!newsId) return;

        const checkInterval = setInterval(() => {
            this.fetchCredibilityScore(newsId, badge, (success) => {
                if (success) {
                    clearInterval(checkInterval);
                }
            });
        }, 5000);

        // Stop monitoring after 2 minutes
        setTimeout(() => clearInterval(checkInterval), 120000);
    }

    fetchCredibilityScore(newsId, badgeElement, callback) {
        fetch(`${this.apiEndpoint}?action=get_score&news_id=${newsId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success && data.credibility_score !== null) {
                    this.updateCredibilityBadge(badgeElement, data);
                    if (callback) callback(true);
                } else {
                    console.warn('Invalid credibility score data:', data);
                    if (callback) callback(false);
                }
            })
            .catch(error => {
                console.error('Error fetching credibility score:', error);
                // Show error state on badge
                const errorIcon = badgeElement.querySelector('.fa-robot');
                if (errorIcon) {
                    errorIcon.className = 'fas fa-exclamation-triangle text-warning';
                    errorIcon.title = 'Unable to fetch credibility score';
                }
                if (callback) callback(false);
            });
    }

    updateCredibilityBadge(badgeElement, data) {
        const scoreText = badgeElement.querySelector('.credibility-score-text');
        const progressBar = badgeElement.querySelector('.progress-bar');
        const riskIndicator = badgeElement.querySelector('.credibility-risk-indicator');
        
        if (scoreText) {
            scoreText.textContent = Math.round(data.credibility_score) + '%';
            scoreText.classList.add('credibility-score-update');
            setTimeout(() => scoreText.classList.remove('credibility-score-update'), 600);
        }
        
        if (progressBar) {
            progressBar.style.width = data.credibility_score + '%';
            progressBar.className = `progress-bar bg-${this.getScoreColor(data.credibility_score)}`;
        }
        
        if (riskIndicator) {
            riskIndicator.className = `credibility-risk-indicator bg-${this.getRiskColor(data.risk_level)}`;
        }
        
        // Update tooltip
        badgeElement.title = `AI Credibility Analysis: ${Math.round(data.credibility_score)}% (${data.risk_level} risk)`;
        
        // Add verified badge if source is verified
        if (data.source_verified && !badgeElement.querySelector('.source-verified')) {
            const verifiedBadge = document.createElement('span');
            verifiedBadge.className = 'badge bg-success ms-1';
            verifiedBadge.title = 'Source Verified';
            verifiedBadge.innerHTML = '<i class="fas fa-check-circle"></i> Verified';
            badgeElement.parentElement.appendChild(verifiedBadge);
        }
    }

    getScoreColor(score) {
        if (score >= 80) return 'success';
        if (score >= 60) return 'info';
        if (score >= 40) return 'warning';
        return 'danger';
    }

    getRiskColor(riskLevel) {
        const colors = {
            'LOW': 'success',
            'MEDIUM': 'info',
            'HIGH': 'warning',
            'CRITICAL': 'danger'
        };
        return colors[riskLevel] || 'secondary';
    }

    showReportModal(newsId) {
        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="reportFakeNewsModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-flag"></i> Report Fake News
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="reportFakeNewsForm">
                                <input type="hidden" name="news_id" value="${newsId}">
                                <div class="mb-3">
                                    <label class="form-label">Report Reason</label>
                                    <select name="report_reason" class="form-select" required>
                                        <option value="">Select a reason...</option>
                                        <option value="MISLEADING">Misleading Information</option>
                                        <option value="FALSE_INFORMATION">False Information</option>
                                        <option value="BIASED">Biased Content</option>
                                        <option value="CLICKBAIT">Clickbait Title</option>
                                        <option value="SPAM">Spam</option>
                                        <option value="OTHER">Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Additional Details</label>
                                    <textarea name="report_details" class="form-control" rows="4" 
                                              placeholder="Please provide additional information about why you believe this content is problematic..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Evidence URLs (Optional)</label>
                                    <textarea name="evidence_urls" class="form-control" rows="2" 
                                              placeholder="Enter URLs that support your report (one per line)..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" onclick="fakeNewsDetection.submitReport()">
                                <i class="fas fa-flag"></i> Submit Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if present
        const existingModal = document.getElementById('reportFakeNewsModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('reportFakeNewsModal'));
        modal.show();
    }

    submitReport() {
        const form = document.getElementById('reportFakeNewsForm');
        const formData = new FormData(form);
        
        const reportData = {
            action: 'submit_report',
            news_id: formData.get('news_id'),
            report_reason: formData.get('report_reason'),
            report_details: formData.get('report_details'),
            evidence_urls: formData.get('evidence_urls')
        };
        
        fetch(this.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(reportData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('reportFakeNewsModal'));
                if (modal) modal.hide();
                
                // Show success message
                this.showNotification('Report submitted successfully. Thank you for helping maintain content quality.', 'success');
            } else {
                this.showNotification('Error submitting report: ' + (data?.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error submitting report:', error);
            this.showNotification('Error submitting report. Please check your connection and try again.', 'error');
        });
    }

    showCredibilityDetails(newsId) {
        fetch(`${this.apiEndpoint}?action=get_details&news_id=${newsId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    this.showDetailsModal(data);
                } else {
                    this.showNotification('Error loading credibility details: ' + (data?.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error loading credibility details:', error);
                this.showNotification('Error loading credibility details. Please try again.', 'error');
            });
    }

    showDetailsModal(data) {
        const modalHtml = `
            <div class="modal fade" id="credibilityDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-shield-alt"></i> AI Credibility Analysis Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${data.html}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="fakeNewsDetection.reportFakeNews(${data.analysis.news_id})">
                                <i class="fas fa-flag"></i> Report Issue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if present
        const existingModal = document.getElementById('credibilityDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('credibilityDetailsModal'));
        modal.show();
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Public method to manually trigger analysis
    analyzeArticle(newsId) {
        return fetch(`${this.apiEndpoint}?action=analyze&news_id=${newsId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            });
    }

    // Public method to get credibility score
    getCredibilityScore(newsId) {
        return fetch(`${this.apiEndpoint}?action=get_score&news_id=${newsId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.fakeNewsDetection = new FakeNewsDetection();
});

// Export for global access
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FakeNewsDetection;
}
