<?php
/**
 * Membership Organization Page Router
 * Shows org details and all member institutions
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get and sanitize parameters
$state = sanitizeState($_GET['state'] ?? null);
$code = trim($_GET['code'] ?? '');
$year = sanitizeYear($_GET['year'] ?? null);
$season = sanitizeSeason($_GET['season'] ?? null);

// Validate code
if (!preg_match('/^[a-z_]+$/', $code)) {
    header('Location: search.php');
    exit;
}

// Get the org details
$stmt = $pdo->prepare("SELECT * FROM membership_orgs WHERE code = :code");
$stmt->execute(['code' => $code]);
$org = $stmt->fetch();

if (!$org) {
    header('Location: search.php');
    exit;
}

// Default state if none selected
if (!$state) {
    $states = getAvailableStates($pdo);
    $state = $states[0] ?? null;
}

// Default to latest publication if state is set but year/season missing
if ($state && (!$year || !$season)) {
    $latest = getLatestPublication($pdo, $state);
    if ($latest) {
        $year = $year ?? $latest['year'];
        $season = $season ?? $latest['season'];
    }
}

// Get all institutions with this membership in the selected publication
$members = [];
if ($state && $year && $season) {
    $stmt = $pdo->prepare("
        SELECT m.*, t.code as type_code, t.name as type_name
        FROM institution_main m
        JOIN institution_types t ON m.institution_type_id = t.id
        JOIN institution_memberships im ON m.id = im.main_id
        WHERE im.org_id = :org_id
          AND m.pub_state = :state
          AND m.pub_year = :year
          AND m.pub_season = :season
        ORDER BY t.name, m.name
    ");
    $stmt->execute([
        'org_id' => $org['id'],
        'state' => $state,
        'year' => $year,
        'season' => $season
    ]);
    $members = $stmt->fetchAll();
}

// Set page title
$pageTitle = $org['name'];

// Current parameters for template
$currentState = $state;
$currentYear = $year;
$currentSeason = $season;

// Include the template
include 'templates/page-template-membership-org.php';
