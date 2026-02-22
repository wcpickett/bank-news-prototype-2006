<?php
/**
 * Credit Union Detail Page Router
 * Nearly identical to bank.php - just uses different template
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get and sanitize parameters
$state = sanitizeState($_GET['state'] ?? null);
$bankNo = sanitizeBankNo($_GET['id'] ?? null);
$year = sanitizeYear($_GET['year'] ?? null);
$season = sanitizeSeason($_GET['season'] ?? null);

// Validate required params
if (!$state || !$bankNo) {
    header('Location: search.php');
    exit;
}

// Default to latest publication for this institution if year/season missing
if (!$year || !$season) {
    $latest = getLatestPublicationForInstitution($pdo, $state, $bankNo);
    if ($latest) {
        $year = $latest['year'];
        $season = $latest['season'];
    } else {
        header('Location: search.php?state=' . urlencode($state));
        exit;
    }
}

// Get institution main record
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
    'year' => $year,
    'season' => $season
]);
$institution = $stmt->fetch();

if (!$institution) {
    header('Location: search.php?state=' . urlencode($state));
    exit;
}

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

// Get available publications for this institution
$availablePublications = getPublicationsForInstitution($pdo, $state, $bankNo);

// Set page title
$pageTitle = $institution['name'];

// Current parameters for template
$currentState = $state;
$currentYear = $year;
$currentSeason = $season;

// Include the template
include 'templates/page-template-credit-union.php';
