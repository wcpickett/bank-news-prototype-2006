<?php
/**
 * Search Page Template
 * Shows current directory (most recent publication) only
 * 
 * Expects these variables from router:
 *   $pdo, $currentFilters, $filterCounts
 *   $results, $totalCount, $hasFilters
 */

include 'includes/header.php';

// Get active filters for pills
$activeFilters = getActiveFilters($pdo, $currentFilters);
?>

<div class="page-header">
    <h1>Search Institutions</h1>
</div>

<div class="search-layout">
    
    <!-- Sidebar -->
    <?php include 'includes/sidebar-filters.php'; ?>
    
    <!-- Main Content -->
    <div class="search-content">
        
        <?php if ($hasFilters): ?>
        
            <!-- Filter Pills -->
            <?php if (!empty($activeFilters)): ?>
                <?php include 'includes/filter-pills.php'; ?>
            <?php endif; ?>
            
            <!-- Text Search Bar -->
            <div class="text-search-bar">
                <form method="get" action="search.php" class="text-search-form">
                    <?php // Preserve all current filters ?>
                    <?php foreach ($currentFilters['state'] ?? [] as $v): ?>
                        <input type="hidden" name="state[]" value="<?= h($v) ?>">
                    <?php endforeach; ?>
                    <?php foreach ($currentFilters['type'] ?? [] as $v): ?>
                        <input type="hidden" name="type[]" value="<?= h($v) ?>">
                    <?php endforeach; ?>
                    <?php foreach ($currentFilters['county'] ?? [] as $v): ?>
                        <input type="hidden" name="county[]" value="<?= h($v) ?>">
                    <?php endforeach; ?>
                    <?php foreach ($currentFilters['membership'] ?? [] as $v): ?>
                        <input type="hidden" name="membership[]" value="<?= h($v) ?>">
                    <?php endforeach; ?>
                    <?php foreach ($currentFilters['assets'] ?? [] as $v): ?>
                        <input type="hidden" name="assets[]" value="<?= h($v) ?>">
                    <?php endforeach; ?>
                    
                    <input type="text" 
                           name="q" 
                           value="<?= h($currentFilters['q'] ?? '') ?>" 
                           placeholder="Search by name or city..."
                           class="search-input">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
            
            <!-- Results -->
            <div class="search-results">
                <div class="results-header">
                    <h2>
                        <?php if (!empty($currentFilters['q'])): ?>
                            Results for "<?= h($currentFilters['q']) ?>"
                        <?php else: ?>
                            Institutions
                        <?php endif; ?>
                        <span class="results-count">(<?= $totalCount ?> found)</span>
                    </h2>
                </div>
                
                <?php if (empty($results)): ?>
                    <div class="no-results">
                        <p>No institutions found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>City</th>
                                <th>County</th>
                                <th>Type</th>
                                <th class="text-right">Total Assets</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <?php
                                $detailPage = getDetailPage($row['type_code']);
                                $detailUrl = buildUrl($detailPage, [
                                    'state' => $row['pub_state'],
                                    'id' => $row['bank_no'],
                                    'year' => $row['pub_year'],
                                    'season' => $row['pub_season']
                                ]);
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?= h($detailUrl) ?>"><?= h($row['name']) ?></a>
                                    </td>
                                    <td><?= displayValue($row['city']) ?></td>
                                    <td><?= displayValue($row['county']) ?></td>
                                    <td><?= h($row['type_name']) ?></td>
                                    <td class="text-right"><?= formatCurrencyThousands($row['total_assets']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="table-note">* Assets shown in thousands</p>
                <?php endif; ?>
            </div>
        
        <?php else: ?>
        
            <div class="no-selection">
                <?php if (empty($currentFilters['state'])): ?>
                    <p><strong>Select a state to begin.</strong></p>
                    <p>Use the State filter in the sidebar to choose which state's institutions to browse.</p>
                <?php else: ?>
                    <p>No results. Try adjusting your filters.</p>
                <?php endif; ?>
            </div>
        
        <?php endif; ?>
        
    </div><!-- /.search-content -->
    
</div><!-- /.search-layout -->

<?php include 'includes/footer.php'; ?>
