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
 * Future: Chart.js initialization for historical data
 * This is a placeholder for when you add charts
 */
function initFinancialChart(elementId, data) {
    // Will implement when Chart.js is added
}
