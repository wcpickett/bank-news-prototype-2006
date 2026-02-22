<?php
/**
 * Publication Select Component
 * Renders state, year, and season dropdowns
 * 
 * Expects these variables:
 *   $pdo           - Database connection
 *   $currentState  - Currently selected state (or null)
 *   $currentYear   - Currently selected year (or null)
 *   $currentSeason - Currently selected season (or null)
 *   $baseUrl       - Form action URL
 *   $extraParams   - Array of additional params to preserve (e.g., ['id' => '00001'])
 */

$currentState = $currentState ?? null;
$currentYear = $currentYear ?? null;
$currentSeason = $currentSeason ?? null;
$extraParams = $extraParams ?? [];

// Get available states
$states = getAvailableStates($pdo);

// Get years and seasons if state is selected
$years = $currentState ? getAvailableYears($pdo, $currentState) : [];
$seasons = ($currentState && $currentYear) ? getAvailableSeasons($pdo, $currentState, $currentYear) : [];

// Default to first available if not set
if ($currentState && empty($currentYear) && !empty($years)) {
    $currentYear = $years[0];
    $seasons = getAvailableSeasons($pdo, $currentState, $currentYear);
}
if ($currentState && $currentYear && empty($currentSeason) && !empty($seasons)) {
    $currentSeason = $seasons[0];
}
?>

<div class="publication-select">
    <form method="get" action="<?= h($baseUrl) ?>" id="publication-form">
        
        <?php // Preserve extra params as hidden fields ?>
        <?php foreach ($extraParams as $key => $value): ?>
            <input type="hidden" name="<?= h($key) ?>" value="<?= h($value) ?>">
        <?php endforeach; ?>
        
        <div class="select-group">
            <label for="state-select">State</label>
            <select name="state" id="state-select" onchange="this.form.submit()">
                <option value="">Select State</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?= h($state) ?>" <?= $state === $currentState ? 'selected' : '' ?>>
                        <?= h($state) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="select-group">
            <label for="year-select">Year</label>
            <select name="year" id="year-select" onchange="this.form.submit()" <?= empty($years) ? 'disabled' : '' ?>>
                <option value="">Select Year</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?= h($year) ?>" <?= (int)$year === (int)$currentYear ? 'selected' : '' ?>>
                        <?= h($year) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="select-group">
            <label for="season-select">Season</label>
            <select name="season" id="season-select" onchange="this.form.submit()" <?= empty($seasons) ? 'disabled' : '' ?>>
                <option value="">Select Season</option>
                <?php foreach ($seasons as $season): ?>
                    <option value="<?= h($season) ?>" <?= $season === $currentSeason ? 'selected' : '' ?>>
                        <?= h(formatSeason($season)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <noscript>
            <button type="submit">Update</button>
        </noscript>
    </form>
</div>
