<?php
/**
 * Filter Pills Component
 * Renders breadcrumb-style removable filter tags
 * 
 * Expects:
 *   $activeFilters - array of active filter info from getActiveFilters()
 *   $currentFilters - the full current filters array
 */

$activeFilters = $activeFilters ?? [];

if (empty($activeFilters)) {
    return;
}

/**
 * Build URL with one filter value removed
 */
function buildUrlWithoutFilter($currentFilters, $removeKey, $removeValue = null) {
    $params = [];
    
    foreach ($currentFilters as $key => $value) {
        if ($key === 'q' && !empty($value)) {
            $params[$key] = $value;
            continue;
        }
        
        if ($key === $removeKey) {
            if ($removeValue !== null && is_array($value)) {
                // Remove specific value from array
                $newArray = array_filter($value, fn($v) => $v != $removeValue);
                if (!empty($newArray)) {
                    $params[$key] = array_values($newArray);
                }
            }
            // If removeValue is null, skip this key entirely
        } elseif (!empty($value)) {
            $params[$key] = $value;
        }
    }
    
    return 'search.php' . (!empty($params) ? '?' . http_build_query($params) : '');
}

$currentFilters = $currentFilters ?? [];
?>

<div class="filter-pills">
    <span class="pills-label">Filters:</span>
    
    <?php foreach ($activeFilters as $filter): ?>
        <a href="<?= h(buildUrlWithoutFilter($currentFilters, $filter['key'], $filter['value'])) ?>" 
           class="filter-pill" 
           title="Remove filter">
            <?= h($filter['label']) ?>
            <span class="pill-remove">×</span>
        </a>
    <?php endforeach; ?>
    
    <?php if (count($activeFilters) > 1): ?>
        <a href="search.php" class="filter-pill filter-pill-clear">
            Clear All
        </a>
    <?php endif; ?>
</div>
