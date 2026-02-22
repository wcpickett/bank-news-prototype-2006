<?php
/**
 * API: Get financial history across all publications for an institution
 * Returns JSON with all years' data for charting
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$state = sanitizeState($_GET['state'] ?? null);
$bankNo = sanitizeBankNo($_GET['id'] ?? null);

if (!$state || !$bankNo) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Get all publications for this institution, ordered oldest to newest for charting
$stmt = $pdo->prepare("
    SELECT pub_year, pub_season,
           total_assets, total_loans, cash_due, securities, total_investments,
           fed_funds_sold, all_other_assets, capital_stock, surplus, 
           undivided_profits, retained_earnings, total_deposits, shares, net_income
    FROM institution_main
    WHERE pub_state = :state 
      AND bank_no = :bank_no
    ORDER BY pub_year ASC, 
             CASE pub_season WHEN 'spring' THEN 1 ELSE 2 END ASC
");
$stmt->execute([
    'state' => $state,
    'bank_no' => $bankNo
]);

$history = [];
while ($row = $stmt->fetch()) {
    $label = formatPublication($row['pub_year'], $row['pub_season']);
    
    $history[] = [
        'label' => $label,
        'year' => $row['pub_year'],
        'season' => $row['pub_season'],
        'total_assets' => (int)$row['total_assets'],
        'total_loans' => (int)$row['total_loans'],
        'cash_due' => (int)$row['cash_due'],
        'securities' => (int)$row['securities'],
        'total_investments' => (int)$row['total_investments'],
        'fed_funds_sold' => (int)$row['fed_funds_sold'],
        'all_other_assets' => (int)$row['all_other_assets'],
        'capital_stock' => (int)$row['capital_stock'],
        'surplus' => (int)$row['surplus'],
        'undivided_profits' => (int)$row['undivided_profits'],
        'retained_earnings' => (int)$row['retained_earnings'],
        'total_deposits' => (int)$row['total_deposits'],
        'shares' => (int)$row['shares'],
        'net_income' => (int)$row['net_income']
    ];
}

echo json_encode([
    'success' => true,
    'history' => $history
]);
