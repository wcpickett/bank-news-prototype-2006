<?php
/**
 * Bank Detail Page Router
 * Shows most recent directory data, with separate year navigation for financials
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get and sanitize parameters
$state = sanitizeState($_GET['state'] ?? null);
$bankNo = sanitizeBankNo($_GET['id'] ?? null);

// Optional: specific year/season for financial figures only
$figYear = sanitizeYear($_GET['fig_year'] ?? null);
$figSeason = sanitizeSeason($_GET['fig_season'] ?? null);

// Validate required params
if (!$state || !$bankNo) {
    header('Location: search.php');
    exit;
}

// Get the most recent publication for this institution
$latestPub = getLatestPublicationForInstitution($pdo, $state, $bankNo);
if (!$latestPub) {
    header('Location: search.php?state=' . urlencode($state));
    exit;
}

$currentYear = $latestPub['year'];
$currentSeason = $latestPub['season'];

// Get institution main record (always most recent for contact info, etc.)
$stmt = $pdo->prepare("
    SELECT m.*, t.code as type_code, t.name as type_name
    FROM institution_main m
    JOIN institution_types t ON m.institution_type_id = t.id
    WHERE m.pub_state = :state 
      AND m.bank_no = :bank_no
      AND m.pub_year = :year 
      AND m.pub_season = :season
");
$stmt->execute([
    'state' => $state,
    'bank_no' => $bankNo,
    'year' => $currentYear,
    'season' => $currentSeason
]);
$institution = $stmt->fetch();

if (!$institution) {
    header('Location: search.php?state=' . urlencode($state));
    exit;
}

// Get available publications for financial figures navigation
$availablePublications = getPublicationsForInstitution($pdo, $state, $bankNo);

// Default figures to most recent if not specified
if (!$figYear || !$figSeason) {
    $figYear = $currentYear;
    $figSeason = $currentSeason;
}

// Check if figures year is valid for this institution
$figPubValid = false;
foreach ($availablePublications as $pub) {
    if ($pub['year'] == $figYear && $pub['season'] == $figSeason) {
        $figPubValid = true;
        break;
    }
}
if (!$figPubValid) {
    $figYear = $currentYear;
    $figSeason = $currentSeason;
}

// Get financial data for the selected figures year
if ($figYear == $currentYear && $figSeason == $currentSeason) {
    // Same as main record, use what we have
    $financials = $institution;
} else {
    // Load financial data from different year
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
        'year' => $figYear,
        'season' => $figSeason
    ]);
    $financials = $stmt->fetch();
    
    if (!$financials) {
        // Fallback to current
        $financials = $institution;
        $figYear = $currentYear;
        $figSeason = $currentSeason;
    }
}

// Determine older/newer publications for figures navigation
$figOlderPub = null;
$figNewerPub = null;
$currentFigIndex = null;

// Publications are ordered newest first
foreach ($availablePublications as $i => $pub) {
    if ($pub['year'] == $figYear && $pub['season'] == $figSeason) {
        $currentFigIndex = $i;
        break;
    }
}

if ($currentFigIndex !== null) {
    // Older = higher index (further back in time)
    if ($currentFigIndex < count($availablePublications) - 1) {
        $figOlderPub = $availablePublications[$currentFigIndex + 1];
    }
    // Newer = lower index (closer to current)
    if ($currentFigIndex > 0) {
        $figNewerPub = $availablePublications[$currentFigIndex - 1];
    }
}

// Is figures showing most recent?
$figIsCurrent = ($figYear == $currentYear && $figSeason == $currentSeason);

// Get city branches
$stmt = $pdo->prepare("
    SELECT * FROM institution_city_branch
    WHERE main_id = :main_id
    ORDER BY city, address
");
$stmt->execute(['main_id' => $institution['id']]);
$cityBranches = $stmt->fetchAll();

// Get other branches
$stmt = $pdo->prepare("
    SELECT * FROM institution_other_branch
    WHERE main_id = :main_id
    ORDER BY city, address
");
$stmt->execute(['main_id' => $institution['id']]);
$otherBranches = $stmt->fetchAll();

// Get memberships
$stmt = $pdo->prepare("
    SELECT o.* 
    FROM institution_memberships im
    JOIN membership_orgs o ON im.org_id = o.id
    WHERE im.main_id = :main_id
    ORDER BY o.name
");
$stmt->execute(['main_id' => $institution['id']]);
$memberships = $stmt->fetchAll();

// Set page title
$pageTitle = $institution['name'];

// Current parameters for template
$currentState = $state;

// Include the template
include 'templates/page-template-bank.php';
