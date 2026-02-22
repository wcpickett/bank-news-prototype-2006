<?php
/**
 * Search Page Router
 * Always shows most recent publication data (current directory)
 * Year/season are not user-selectable filters here
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Collect filter parameters (no year/season - those are automatic)
$currentFilters = [
    'state' => isset($_GET['state']) ? (array)$_GET['state'] : [],
    'type' => isset($_GET['type']) ? (array)$_GET['type'] : [],
    'county' => isset($_GET['county']) ? (array)$_GET['county'] : [],
    'membership' => isset($_GET['membership']) ? (array)$_GET['membership'] : [],
    'assets' => isset($_GET['assets']) ? (array)$_GET['assets'] : [],
    'q' => trim($_GET['q'] ?? '')
];

// Clean up empty values
foreach ($currentFilters as $key => $value) {
    if (is_array($value)) {
        $currentFilters[$key] = array_filter($value, fn($v) => $v !== '' && $v !== null);
    }
}

// Get filter counts for sidebar (only counts from most recent publications)
$filterCounts = getFilterCounts($pdo, $currentFilters);

// Build query for search results
$results = [];
$totalCount = 0;
$hasFilters = !empty($currentFilters['state']);

if ($hasFilters) {
    $filterResult = buildFilterQuery($pdo, $currentFilters);
    
    $sql = "
        SELECT DISTINCT m.*, t.code as type_code, t.name as type_name
        FROM institution_main m
        JOIN institution_types t ON m.institution_type_id = t.id
        {$filterResult['joins']}
        WHERE {$filterResult['where']}
        ORDER BY m.name
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($filterResult['params']);
    $results = $stmt->fetchAll();
    $totalCount = count($results);
}

// Set page title
$pageTitle = 'Search Institutions';

// Include the template
include 'templates/page-template-search.php';
