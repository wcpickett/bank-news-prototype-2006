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
        const btn = e.target.closest('.fig-nav-btn');
        if (!btn || btn.classList.contains('fig-nav-disabled')) return;
        
        e.preventDefault();
        
        const year = btn.dataset.year;
        const season = btn.dataset.season;
        
        if (!year || !season) return;
        
        loadFigures(state, bankNo, year, season);
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
            
            // Update figures values
            const figures = data.figures;
            for (const [field, value] of Object.entries(figures)) {
                const el = document.querySelector(`[data-field="${field}"]`);
                if (el && value) {
                    el.textContent = value;
                }
            }
            
            // Update year display
            let displayHtml = data.displayYear;
            if (data.isCurrent) {
                displayHtml += ' <span class="current-badge">Current</span>';
            }
            display.innerHTML = displayHtml;
            
            // Update nav buttons
            updateNavButton('.fig-nav-prev', data.prevPub, state, bankNo);
            updateNavButton('.fig-nav-next', data.nextPub, state, bankNo);
            
            // Update "view current" link
            const note = document.getElementById('figures-note');
            if (note) {
                if (data.isCurrent) {
                    note.innerHTML = '';
                } else {
                    note.innerHTML = `<a href="bank.php?state=${encodeURIComponent(state)}&id=${encodeURIComponent(bankNo)}">View current figures (${data.currentDisplay})</a>`;
                }
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
 * Update a navigation button (prev/next)
 */
function updateNavButton(selector, pubData, state, bankNo) {
    const container = document.querySelector('#financial-section .figures-nav');
    const oldBtn = container.querySelector(selector);
    if (!oldBtn) return;
    
    const isPrev = selector.includes('prev');
    
    if (pubData) {
        // Create active link
        const newBtn = document.createElement('a');
        newBtn.href = `bank.php?state=${encodeURIComponent(state)}&id=${encodeURIComponent(bankNo)}&fig_year=${pubData.year}&fig_season=${pubData.season}`;
        newBtn.className = `fig-nav-btn ${isPrev ? 'fig-nav-prev' : 'fig-nav-next'}`;
        newBtn.dataset.year = pubData.year;
        newBtn.dataset.season = pubData.season;
        newBtn.title = `${isPrev ? 'Newer' : 'Older'}: ${formatPublication(pubData.year, pubData.season)}`;
        newBtn.innerHTML = isPrev ? '&lt;' : '&gt;';
        oldBtn.replaceWith(newBtn);
    } else {
        // Create disabled span
        const newBtn = document.createElement('span');
        newBtn.className = `fig-nav-btn fig-nav-disabled ${isPrev ? 'fig-nav-prev' : 'fig-nav-next'}`;
        newBtn.innerHTML = isPrev ? '&lt;' : '&gt;';
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