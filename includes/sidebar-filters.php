<?php
/**
 * Sidebar Filters Component
 * Renders accordion-style faceted filter sections
 * Search always shows most recent publication - no year/season filters here
 * 
 * Expects:
 *   $pdo - database connection
 *   $currentFilters - array with current filter values
 *   $filterCounts - array with counts for each filter option
 */

$currentFilters = $currentFilters ?? [];
$filterCounts = $filterCounts ?? [];

// Helper to check if a filter value is active
function isFilterActive($currentFilters, $key, $value) {
    if (!isset($currentFilters[$key])) return false;
    if (is_array($currentFilters[$key])) {
        return in_array($value, $currentFilters[$key]);
    }
    return $currentFilters[$key] == $value;
}
?>

<aside class="sidebar-filters">
    <form id="filter-form" method="get" action="search.php">
        
        <!-- State Section -->
        <div class="filter-section accordion-item">
            <button type="button" class="accordion-header" aria-expanded="true">
                State
            </button>
            <div class="accordion-content filter-options">
                <?php foreach ($filterCounts['states'] as $state): ?>
                    <label class="filter-option">
                        <input type="checkbox" 
                               name="state[]" 
                               value="<?= h($state['code']) ?>"
                               <?= isFilterActive($currentFilters, 'state', $state['code']) ? 'checked' : '' ?>>
                        <span class="option-label"><?= h($state['code']) ?></span>
                        <span class="option-count">(<?= $state['count'] ?>)</span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Institution Type Section -->
        <?php if (!empty($filterCounts['types'])): ?>
        <div class="filter-section accordion-item">
            <button type="button" class="accordion-header" aria-expanded="true">
                Institution Type
            </button>
            <div class="accordion-content filter-options">
                <?php foreach ($filterCounts['types'] as $type): ?>
                    <label class="filter-option">
                        <input type="checkbox" 
                               name="type[]" 
                               value="<?= h($type['code']) ?>"
                               <?= isFilterActive($currentFilters, 'type', $type['code']) ? 'checked' : '' ?>>
                        <span class="option-label"><?= h($type['name']) ?></span>
                        <span class="option-count">(<?= $type['count'] ?>)</span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- County Section -->
        <?php if (!empty($filterCounts['counties'])): ?>
        <div class="filter-section accordion-item">
            <button type="button" class="accordion-header" aria-expanded="false">
                County
            </button>
            <div class="accordion-content filter-options" style="display: none;">
                <?php 
                $countyLimit = 10;
                $showingAll = isset($_GET['show_all_counties']);
                $counties = $filterCounts['counties'];
                $totalCounties = count($counties);
                if (!$showingAll && $totalCounties > $countyLimit) {
                    $counties = array_slice($counties, 0, $countyLimit);
                }
                ?>
                <?php foreach ($counties as $county): ?>
                    <label class="filter-option">
                        <input type="checkbox" 
                               name="county[]" 
                               value="<?= h($county['county']) ?>"
                               <?= isFilterActive($currentFilters, 'county', $county['county']) ? 'checked' : '' ?>>
                        <span class="option-label"><?= h($county['county']) ?></span>
                        <span class="option-count">(<?= $county['count'] ?>)</span>
                    </label>
                <?php endforeach; ?>
                <?php if (!$showingAll && $totalCounties > $countyLimit): ?>
                    <a href="<?= h($_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '&' : '?') . 'show_all_counties=1') ?>" class="show-more">
                        Show all <?= $totalCounties ?> counties...
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Memberships Section -->
        <?php if (!empty($filterCounts['memberships'])): ?>
        <div class="filter-section accordion-item">
            <button type="button" class="accordion-header" aria-expanded="false">
                Memberships
            </button>
            <div class="accordion-content filter-options" style="display: none;">
                <?php foreach ($filterCounts['memberships'] as $membership): ?>
                    <label class="filter-option">
                        <input type="checkbox" 
                               name="membership[]" 
                               value="<?= h($membership['code']) ?>"
                               <?= isFilterActive($currentFilters, 'membership', $membership['code']) ? 'checked' : '' ?>>
                        <span class="option-label"><?= h($membership['name']) ?></span>
                        <span class="option-count">(<?= $membership['count'] ?>)</span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Asset Size Section -->
        <?php if (!empty($filterCounts['assetRanges'])): ?>
        <div class="filter-section accordion-item">
            <button type="button" class="accordion-header" aria-expanded="false">
                Asset Size
            </button>
            <div class="accordion-content filter-options" style="display: none;">
                <?php 
                $assetRanges = [
                    'under50' => 'Under $50M',
                    '50-100' => '$50M - $100M',
                    '100-500' => '$100M - $500M',
                    '500-1000' => '$500M - $1B',
                    'over1000' => 'Over $1B'
                ];
                ?>
                <?php foreach ($assetRanges as $code => $label): ?>
                    <?php $count = $filterCounts['assetRanges'][$code] ?? 0; ?>
                    <?php if ($count > 0): ?>
                    <label class="filter-option">
                        <input type="checkbox" 
                               name="assets[]" 
                               value="<?= h($code) ?>"
                               <?= isFilterActive($currentFilters, 'assets', $code) ? 'checked' : '' ?>>
                        <span class="option-label"><?= h($label) ?></span>
                        <span class="option-count">(<?= $count ?>)</span>
                    </label>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Text Search (preserve if set) -->
        <?php if (!empty($currentFilters['q'])): ?>
            <input type="hidden" name="q" value="<?= h($currentFilters['q']) ?>">
        <?php endif; ?>
        
        <noscript>
            <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
        </noscript>
        
    </form>
</aside>
