// PK Live News - Heat Map JavaScript
// Interactive news heatmap visualization

class NewsHeatMap {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error('Heatmap container not found');
            return;
        }
        
        this.options = {
            width: options.width || 800,
            height: options.height || 400,
            radius: options.radius || 20,
            maxOpacity: options.maxOpacity || 0.8,
            minOpacity: options.minOpacity || 0.1,
            blur: options.blur || 0.85,
            gradient: options.gradient || {
                0.4: 'blue',
                0.6: 'cyan',
                0.7: 'lime',
                0.8: 'yellow',
                1.0: 'red'
            },
            ...options
        };
        
        this.data = [];
        this.canvas = null;
        this.ctx = null;
        this.heatmap = null;
        
        this.init();
    }
    
    init() {
        this.createCanvas();
        this.createHeatmap();
        this.bindEvents();
    }
    
    createCanvas() {
        this.canvas = document.createElement('canvas');
        this.canvas.width = this.options.width;
        this.canvas.height = this.options.height;
        this.canvas.style.width = '100%';
        this.canvas.style.height = 'auto';
        this.canvas.style.border = '1px solid #ddd';
        this.canvas.style.borderRadius = '8px';
        
        this.container.appendChild(this.canvas);
        this.ctx = this.canvas.getContext('2d');
    }
    
    createHeatmap() {
        // Simple heatmap implementation
        this.heatmap = {
            data: [],
            width: this.canvas.width,
            height: this.canvas.height
        };
    }
    
    addDataPoint(x, y, value = 1) {
        this.data.push({ x, y, value });
        this.updateHeatmap();
    }
    
    setData(data) {
        this.data = data || [];
        this.updateHeatmap();
    }
    
    updateHeatmap() {
        if (!this.ctx) return;
        
        // Clear canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Draw background
        this.ctx.fillStyle = '#f8f9fa';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Draw heatmap points
        this.data.forEach(point => {
            this.drawHeatPoint(point.x, point.y, point.value);
        });
        
        // Draw legend
        this.drawLegend();
    }
    
    drawHeatPoint(x, y, value) {
        const radius = this.options.radius;
        const maxOpacity = this.options.maxOpacity;
        const minOpacity = this.options.minOpacity;
        
        // Calculate opacity based on value
        const opacity = minOpacity + (value * (maxOpacity - minOpacity));
        
        // Create gradient
        const gradient = this.ctx.createRadialGradient(x, y, 0, x, y, radius);
        gradient.addColorStop(0, `rgba(255, 0, 0, ${opacity})`);
        gradient.addColorStop(0.5, `rgba(255, 255, 0, ${opacity * 0.8})`);
        gradient.addColorStop(1, `rgba(0, 0, 255, 0)`);
        
        // Draw point
        this.ctx.fillStyle = gradient;
        this.ctx.beginPath();
        this.ctx.arc(x, y, radius, 0, 2 * Math.PI);
        this.ctx.fill();
    }
    
    drawLegend() {
        const legendX = this.canvas.width - 100;
        const legendY = 20;
        const legendHeight = 100;
        const legendWidth = 20;
        
        // Draw legend background
        this.ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
        this.ctx.fillRect(legendX - 5, legendY - 5, legendWidth + 60, legendHeight + 30);
        
        // Draw gradient
        const gradient = this.ctx.createLinearGradient(0, legendY, 0, legendY + legendHeight);
        gradient.addColorStop(0, 'red');
        gradient.addColorStop(0.5, 'yellow');
        gradient.addColorStop(1, 'blue');
        
        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(legendX, legendY, legendWidth, legendHeight);
        
        // Draw labels
        this.ctx.fillStyle = '#333';
        this.ctx.font = '12px Arial';
        this.ctx.fillText('High', legendX + legendWidth + 5, legendY + 5);
        this.ctx.fillText('Low', legendX + legendWidth + 5, legendY + legendHeight);
    }
    
    bindEvents() {
        if (this.canvas) {
            this.canvas.addEventListener('mousemove', (e) => {
                this.handleMouseMove(e);
            });
            
            this.canvas.addEventListener('click', (e) => {
                this.handleClick(e);
            });
        }
    }
    
    handleMouseMove(e) {
        const rect = this.canvas.getBoundingClientRect();
        const x = (e.clientX - rect.left) * (this.canvas.width / rect.width);
        const y = (e.clientY - rect.top) * (this.canvas.height / rect.height);
        
        // Find nearest data point
        const nearest = this.findNearestPoint(x, y);
        if (nearest) {
            this.showTooltip(e.clientX, e.clientY, nearest);
        } else {
            this.hideTooltip();
        }
    }
    
    handleClick(e) {
        const rect = this.canvas.getBoundingClientRect();
        const x = (e.clientX - rect.left) * (this.canvas.width / rect.width);
        const y = (e.clientY - rect.top) * (this.canvas.height / rect.height);
        
        const nearest = this.findNearestPoint(x, y);
        if (nearest && nearest.data) {
            this.showDetails(nearest.data);
        }
    }
    
    findNearestPoint(x, y) {
        let nearest = null;
        let minDistance = Infinity;
        
        this.data.forEach(point => {
            const distance = Math.sqrt(Math.pow(point.x - x, 2) + Math.pow(point.y - y, 2));
            if (distance < minDistance && distance < this.options.radius) {
                minDistance = distance;
                nearest = point;
            }
        });
        
        return nearest;
    }
    
    showTooltip(x, y, point) {
        // Remove existing tooltip
        this.hideTooltip();
        
        const tooltip = document.createElement('div');
        tooltip.className = 'heatmap-tooltip';
        tooltip.style.cssText = `
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
            pointer-events: none;
        `;
        
        tooltip.innerHTML = `
            <div>Activity Level: ${point.value}</div>
            ${point.data ? `<div>${point.data.title || 'News Item'}</div>` : ''}
        `;
        
        document.body.appendChild(tooltip);
        
        // Position tooltip
        tooltip.style.left = (x + 10) + 'px';
        tooltip.style.top = (y - 30) + 'px';
        
        this.currentTooltip = tooltip;
    }
    
    hideTooltip() {
        if (this.currentTooltip) {
            this.currentTooltip.remove();
            this.currentTooltip = null;
        }
    }
    
    showDetails(data) {
        // Show detailed information in a modal or alert
        if (data.url) {
            window.open(data.url, '_blank');
        } else {
            alert(`News Details:\n\nTitle: ${data.title || 'N/A'}\nCategory: ${data.category || 'N/A'}\nActivity: ${data.value}`);
        }
    }
    
    resize(width, height) {
        this.options.width = width;
        this.options.height = height;
        
        if (this.canvas) {
            this.canvas.width = width;
            this.canvas.height = height;
            this.updateHeatmap();
        }
    }
    
    destroy() {
        if (this.canvas && this.canvas.parentNode) {
            this.canvas.parentNode.removeChild(this.canvas);
        }
        this.hideTooltip();
    }
}

// Initialize heatmap when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const heatmapContainer = document.getElementById('news-heatmap');
    if (heatmapContainer) {
        // Create heatmap instance
        const heatmap = new NewsHeatMap('news-heatmap', {
            width: 800,
            height: 400
        });
        
        // Sample data - replace with actual news data
        const sampleData = [
            { x: 100, y: 100, value: 0.8, data: { title: 'Breaking News', category: 'Politics' } },
            { x: 200, y: 150, value: 0.6, data: { title: 'Sports Update', category: 'Sports' } },
            { x: 300, y: 200, value: 0.9, data: { title: 'Tech News', category: 'Technology' } },
            { x: 400, y: 250, value: 0.4, data: { title: 'Business Report', category: 'Business' } },
            { x: 500, y: 300, value: 0.7, data: { title: 'Health Alert', category: 'Health' } }
        ];
        
        heatmap.setData(sampleData);
        
        // Make heatmap globally accessible
        window.newsHeatmap = heatmap;
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NewsHeatMap;
}
