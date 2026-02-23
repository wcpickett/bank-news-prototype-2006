/**
 * Bank Directory Prototype Scripts
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Restore scroll position after page load
    restoreScrollPosition();
    
    // Accordion functionality with memory
    initAccordions();
    
    // Auto-submit on checkbox change (for filters that should apply immediately)
    initFilterAutoSubmit();
    
    // AJAX figures navigation on bank detail page
    initFiguresNav();
    
    // Financial chart functionality
    initFinancialCharts();
    
});

/**
 * Save scroll position before page unload
 */
function saveScrollPosition() {
    sessionStorage.setItem('scrollPosition', window.scrollY);
}

/**
 * Restore scroll position after page load
 */
function restoreScrollPosition() {
    const scrollPos = sessionStorage.getItem('scrollPosition');
    if (scrollPos !== null) {
        window.scrollTo(0, parseInt(scrollPos, 10));
        sessionStorage.removeItem('scrollPosition');
    }
}

/**
 * Initialize accordion expand/collapse with localStorage memory
 */
function initAccordions() {
    const headers = document.querySelectorAll('.accordion-header');
    
    headers.forEach((header, index) => {
        // Generate a unique key for this accordion based on its position and text
        const sectionName = header.textContent.trim().toLowerCase().replace(/\s+/g, '-');
        const storageKey = 'accordion-' + sectionName;
        
        // Restore saved state from localStorage
        const savedState = localStorage.getItem(storageKey);
        if (savedState !== null) {
            const isExpanded = savedState === 'true';
            header.setAttribute('aria-expanded', isExpanded);
            const content = header.nextElementSibling;
            if (content) {
                content.style.display = isExpanded ? 'block' : 'none';
            }
        }
        
        // Add click handler
        header.addEventListener('click', function(e) {
            // Don't toggle if clicking on a form element inside
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'LABEL') {
                return;
            }
            
            const content = this.nextElementSibling;
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const newState = !isExpanded;
            
            // Toggle state
            this.setAttribute('aria-expanded', newState);
            
            if (newState) {
                content.style.display = 'block';
            } else {
                content.style.display = 'none';
            }
            
            // Save state to localStorage
            localStorage.setItem(storageKey, newState);
        });
    });
}

/**
 * Initialize auto-submit for filter checkboxes
 * This submits the form when checkboxes are changed
 */
function initFilterAutoSubmit() {
    const filterForm = document.getElementById('filter-form');
    if (!filterForm) return;
    
    // Auto-submit on checkbox change
    filterForm.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            saveScrollPosition();
            filterForm.submit();
        });
    });
    
    // Auto-submit on radio change for asset size (if any radios exist)
    filterForm.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            saveScrollPosition();
            filterForm.submit();
        });
    });
}

/**
 * Initialize AJAX navigation for financial figures on bank detail page
 */
function initFiguresNav() {
    const section = document.getElementById('financial-section');
    if (!section) return;
    
    const state = section.dataset.state;
    const bankNo = section.dataset.bankNo;
    
    // Attach click handlers to nav buttons
    section.addEventListener('click', function(e) {
        // Handle arrow buttons
        const btn = e.target.closest('.fig-nav-btn');
        if (btn && !btn.classList.contains('fig-nav-disabled')) {
            e.preventDefault();
            
            const year = btn.dataset.year;
            const season = btn.dataset.season;
            
            if (year && season) {
                loadFigures(state, bankNo, year, season);
            }
            return;
        }
        
        // Handle "View current figures" link
        const currentLink = e.target.closest('.fig-current-link');
        if (currentLink) {
            e.preventDefault();
            
            const year = currentLink.dataset.year;
            const season = currentLink.dataset.season;
            
            if (year && season) {
                loadFigures(state, bankNo, year, season);
            }
        }
    });
}

/**
 * Load financial figures via AJAX
 */
function loadFigures(state, bankNo, year, season) {
    const url = `api-figures.php?state=${encodeURIComponent(state)}&id=${encodeURIComponent(bankNo)}&year=${encodeURIComponent(year)}&season=${encodeURIComponent(season)}`;
    
    // Show loading state
    const display = document.getElementById('figures-year-display');
    if (display) {
        display.innerHTML = 'Loading...';
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading figures:', data.error);
                return;
            }
            
            // Update figures values - target only spans, not rows
            const figures = data.figures;
            for (const [field, value] of Object.entries(figures)) {
                const el = document.querySelector(`span[data-field="${field}"]`);
                if (el && value) {
                    el.textContent = value;
                }
            }
            
            // Update year display
            display.textContent = data.displayYear;
            
            // Update current/historical badge visibility
            const currentBadge = document.getElementById('current-badge');
            const historicalBadge = document.getElementById('historical-badge');
            if (currentBadge && historicalBadge) {
                if (data.isCurrent) {
                    currentBadge.classList.remove('current-badge-hidden');
                    historicalBadge.classList.add('historical-badge-hidden');
                } else {
                    currentBadge.classList.add('current-badge-hidden');
                    historicalBadge.classList.remove('historical-badge-hidden');
                }
            }
            
            // Update nav buttons
            updateNavButton('.fig-nav-older', data.olderPub, state, bankNo, true);
            updateNavButton('.fig-nav-newer', data.newerPub, state, bankNo, false);
            
            // Update "view current" link
            const note = document.getElementById('figures-note');
            if (note) {
                if (data.isCurrent) {
                    note.innerHTML = '';
                } else {
                    note.innerHTML = `<a href="bank.php?state=${encodeURIComponent(state)}&id=${encodeURIComponent(bankNo)}" class="fig-current-link" data-year="${data.currentYear}" data-season="${data.currentSeason}">View current figures (${data.currentDisplay})</a>`;
                }
            }
            
            // Update data attributes for chart
            section.dataset.figYear = year;
            section.dataset.figSeason = season;
            
            // Refresh chart if open
            if (section.refreshChart) {
                section.refreshChart();
            }
            
            // Update URL without reload
            const newUrl = `bank.php?state=${encodeURIComponent(state)}&id=${encodeURIComponent(bankNo)}&fig_year=${year}&fig_season=${season}`;
            history.pushState({year, season}, '', newUrl);
        })
        .catch(err => {
            console.error('Failed to load figures:', err);
            display.innerHTML = 'Error loading data';
        });
}

/**
 * Update a navigation button (older/newer)
 */
function updateNavButton(selector, pubData, state, bankNo, isOlder) {
    const container = document.querySelector('#financial-section .figures-nav');
    const oldBtn = container.querySelector(selector);
    if (!oldBtn) return;
    
    if (pubData) {
        // Create active link
        const newBtn = document.createElement('a');
        newBtn.href = `bank.php?state=${encodeURIComponent(state)}&id=${encodeURIComponent(bankNo)}&fig_year=${pubData.year}&fig_season=${pubData.season}`;
        newBtn.className = `fig-nav-btn ${isOlder ? 'fig-nav-older' : 'fig-nav-newer'}`;
        newBtn.dataset.year = pubData.year;
        newBtn.dataset.season = pubData.season;
        newBtn.title = `${isOlder ? 'Older' : 'Newer'}: ${formatPublication(pubData.year, pubData.season)}`;
        newBtn.innerHTML = isOlder ? '&lt;' : '&gt;';
        oldBtn.replaceWith(newBtn);
    } else {
        // Create disabled span
        const newBtn = document.createElement('span');
        newBtn.className = `fig-nav-btn fig-nav-disabled ${isOlder ? 'fig-nav-older' : 'fig-nav-newer'}`;
        newBtn.innerHTML = isOlder ? '&lt;' : '&gt;';
        oldBtn.replaceWith(newBtn);
    }
}

/**
 * Format publication year/season for display (JS version)
 */
function formatPublication(year, season) {
    const seasonName = season === 'fall' ? 'Fall' : 'Spring';
    return `${seasonName} ${year}`;
}

/**
 * Future: Chart.js initialization for historical data
 * This is a placeholder for when you add charts
 */
function initFinancialChart(elementId, data) {
    // Will implement when Chart.js is added
}

/**
 * Initialize clickable financial rows for charting
 */
function initFinancialCharts() {
    const section = document.getElementById('financial-section');
    if (!section) return;
    
    const state = section.dataset.state;
    const bankNo = section.dataset.bankNo;
    const chartContainer = document.getElementById('financial-chart-container');
    const chartTitle = document.getElementById('chart-title');
    const chartClose = document.getElementById('chart-close');
    const canvas = document.getElementById('financial-chart');
    
    if (!chartContainer || !canvas) return;
    
    let chartInstance = null;
    let historyData = null;
    let currentField = null;
    let currentLabel = null;
    
    // Handle row clicks
    section.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', async function() {
            const field = this.dataset.field;
            const label = this.dataset.label;
            
            // Highlight selected row
            section.querySelectorAll('.clickable-row').forEach(r => r.classList.remove('selected'));
            this.classList.add('selected');
            
            currentField = field;
            currentLabel = label;
            
            // Load history data if not cached
            if (!historyData) {
                chartTitle.textContent = 'Loading...';
                chartContainer.style.display = 'block';
                
                try {
                    const url = `api-financial-history.php?state=${encodeURIComponent(state)}&id=${encodeURIComponent(bankNo)}`;
                    const response = await fetch(url);
                    historyData = await response.json();
                    
                    if (historyData.error) {
                        chartTitle.textContent = 'Error loading data';
                        return;
                    }
                } catch (err) {
                    console.error('Failed to load history:', err);
                    chartTitle.textContent = 'Error loading data';
                    return;
                }
            }
            
            // Render chart
            renderChart();
        });
    });
    
    // Close chart
    if (chartClose) {
        chartClose.addEventListener('click', function() {
            chartContainer.style.display = 'none';
            section.querySelectorAll('.clickable-row').forEach(r => r.classList.remove('selected'));
            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
            }
            currentField = null;
            currentLabel = null;
        });
    }
    
    // Expose function to refresh chart when year changes
    section.refreshChart = function() {
        if (currentField && historyData) {
            renderChart();
        }
    };
    
    function renderChart() {
        chartTitle.textContent = currentLabel + ' Over Time';
        chartContainer.style.display = 'block';
        
        // Get current fig year/season from data attributes
        const figYear = section.dataset.figYear;
        const figSeason = section.dataset.figSeason;
        const currentEditionLabel = formatPublication(figYear, figSeason);
        
        // Extract labels and values
        const labels = historyData.history.map(h => h.label);
        const values = historyData.history.map(h => h[currentField] || 0);
        
        // Find index of current edition
        const currentIndex = labels.indexOf(currentEditionLabel);
        
        // Create point styling arrays
        const pointRadius = labels.map((_, i) => i === currentIndex ? 8 : 5);
        const pointBackgroundColor = labels.map((_, i) => 
            i === currentIndex ? '#e74c3c' : '#2c5aa0'
        );
        const pointBorderColor = labels.map((_, i) => 
            i === currentIndex ? '#c0392b' : '#1a3d6e'
        );
        const pointBorderWidth = labels.map((_, i) => i === currentIndex ? 3 : 1);
        
        // Destroy existing chart
        if (chartInstance) {
            chartInstance.destroy();
        }
        
        // Vertical line plugin
        const verticalLinePlugin = {
            id: 'verticalLine',
            afterDraw: (chart) => {
                if (currentIndex === -1) return;
                
                const ctx = chart.ctx;
                const xAxis = chart.scales.x;
                const yAxis = chart.scales.y;
                const x = xAxis.getPixelForValue(currentIndex);
                
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(x, yAxis.top);
                ctx.lineTo(x, yAxis.bottom);
                ctx.lineWidth = 2;
                ctx.strokeStyle = 'rgba(231, 76, 60, 0.5)';
                ctx.setLineDash([5, 5]);
                ctx.stroke();
                ctx.restore();
            }
        };
        
        // Create new chart
        chartInstance = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: currentLabel + ' ($000s)',
                    data: values,
                    borderColor: '#2c5aa0',
                    backgroundColor: 'rgba(44, 90, 160, 0.1)',
                    fill: true,
                    tension: 0.1,
                    pointRadius: pointRadius,
                    pointHoverRadius: 9,
                    pointBackgroundColor: pointBackgroundColor,
                    pointBorderColor: pointBorderColor,
                    pointBorderWidth: pointBorderWidth
                }]
            },
            plugins: [verticalLinePlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toLocaleString() + ',000';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
}
