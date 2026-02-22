<?php
/**
 * API: Get financial figures for a specific publication year
 * Returns JSON for AJAX requests
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$state = sanitizeState($_GET['state'] ?? null);
$bankNo = sanitizeBankNo($_GET['id'] ?? null);
$year = sanitizeYear($_GET['year'] ?? null);
$season = sanitizeSeason($_GET['season'] ?? null);

if (!$state || !$bankNo || !$year || !$season) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Get financial data
$stmt = $pdo->prepare("
    SELECT total_assets, total_loans, cash_due, securities, total_investments,
           fed_funds_sold, all_other_assets, capital_stock, surplus, 
           undivided_profits, retained_earnings, total_deposits, shares, net_income
    FROM institution_main
    WHERE pub_state = :state 
      AND bank_no = :bank_no
      AND pub_year = :year 
      AND pub_season = :season
");
$stmt->execute([
    'state' => $state,
    'bank_no' => $bankNo,
    'year' => $year,
    'season' => $season
]);
$financials = $stmt->fetch();

if (!$financials) {
    echo json_encode(['error' => 'Not found']);
    exit;
}

// Get most recent publication to determine if this is "current"
$latest = getLatestPublicationForInstitution($pdo, $state, $bankNo);
$isCurrent = ($year == $latest['year'] && $season == $latest['season']);

// Get older/newer publications
$availablePublications = getPublicationsForInstitution($pdo, $state, $bankNo);
$olderPub = null;
$newerPub = null;

foreach ($availablePublications as $i => $pub) {
    if ($pub['year'] == $year && $pub['season'] == $season) {
        // Older = higher index (further back in time)
        if ($i < count($availablePublications) - 1) {
            $olderPub = $availablePublications[$i + 1];
        }
        // Newer = lower index (closer to current)
        if ($i > 0) {
            $newerPub = $availablePublications[$i - 1];
        }
        break;
    }
}

// Format the financial values
$formatted = [];
$fields = ['total_assets', 'total_loans', 'cash_due', 'securities', 'total_investments',
           'fed_funds_sold', 'all_other_assets', 'capital_stock', 'surplus',
           'undivided_profits', 'retained_earnings', 'total_deposits', 'shares', 'net_income'];

foreach ($fields as $field) {
    $formatted[$field] = $financials[$field] ? formatCurrencyThousands($financials[$field]) : null;
}

echo json_encode([
    'success' => true,
    'year' => $year,
    'season' => $season,
    'displayYear' => formatPublication($year, $season),
    'isCurrent' => $isCurrent,
    'currentYear' => $latest['year'],
    'currentSeason' => $latest['season'],
    'currentDisplay' => formatPublication($latest['year'], $latest['season']),
    'olderPub' => $olderPub,
    'newerPub' => $newerPub,
    'figures' => $formatted
]);
