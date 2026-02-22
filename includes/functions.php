<?php
/**
 * Helper Functions
 * Common utilities used across templates
 */

/**
 * Get all states that have data in the database
 */
function getAvailableStates($pdo) {
    $stmt = $pdo->query("
        SELECT DISTINCT pub_state 
        FROM institution_main 
        ORDER BY pub_state
    ");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get available years for a given state
 */
function getAvailableYears($pdo, $state) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT pub_year 
        FROM institution_main 
        WHERE pub_state = ? 
        ORDER BY pub_year DESC
    ");
    $stmt->execute([$state]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get available seasons for a given state and year
 */
function getAvailableSeasons($pdo, $state, $year) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT pub_season 
        FROM institution_main 
        WHERE pub_state = ? AND pub_year = ?
        ORDER BY pub_season DESC
    ");
    $stmt->execute([$state, $year]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get the most recent publication for a state
 * Returns ['year' => YYYY, 'season' => 'fall'|'spring']
 */
function getLatestPublication($pdo, $state) {
    $stmt = $pdo->prepare("
        SELECT pub_year, pub_season 
        FROM institution_main 
        WHERE pub_state = ?
        ORDER BY pub_year DESC, 
                 CASE pub_season WHEN 'fall' THEN 1 ELSE 2 END
        LIMIT 1
    ");
    $stmt->execute([$state]);
    $row = $stmt->fetch();
    
    if ($row) {
        return ['year' => $row['pub_year'], 'season' => $row['pub_season']];
    }
    return null;
}

/**
 * Get the most recent publication for a specific institution
 */
function getLatestPublicationForInstitution($pdo, $state, $bankNo) {
    $stmt = $pdo->prepare("
        SELECT pub_year, pub_season 
        FROM institution_main 
        WHERE pub_state = ? AND bank_no = ?
        ORDER BY pub_year DESC, 
                 CASE pub_season WHEN 'fall' THEN 1 ELSE 2 END
        LIMIT 1
    ");
    $stmt->execute([$state, $bankNo]);
    $row = $stmt->fetch();
    
    if ($row) {
        return ['year' => $row['pub_year'], 'season' => $row['pub_season']];
    }
    return null;
}

/**
 * Get available publications for a specific institution (for dropdown)
 */
function getPublicationsForInstitution($pdo, $state, $bankNo) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT pub_year, pub_season 
        FROM institution_main 
        WHERE pub_state = ? AND bank_no = ?
        ORDER BY pub_year DESC, 
                 CASE pub_season WHEN 'fall' THEN 1 ELSE 2 END
    ");
    $stmt->execute([$state, $bankNo]);
    return $stmt->fetchAll();
}

/**
 * Expand a title abbreviation to full text
 */
function expandTitle($pdo, $abbrev) {
    static $cache = null;
    
    // Load cache on first call
    if ($cache === null) {
        $stmt = $pdo->query("SELECT abbrev, full_title FROM title_abbreviations");
        $cache = [];
        while ($row = $stmt->fetch()) {
            $cache[$row['abbrev']] = $row['full_title'];
        }
    }
    
    // Handle compound titles like "EVP-CFO-Cash"
    $parts = explode('-', $abbrev);
    $expanded = [];
    
    foreach ($parts as $part) {
        $part = trim($part);
        if (isset($cache[$part])) {
            $expanded[] = $cache[$part];
        } else {
            $expanded[] = $part; // Keep original if not found
        }
    }
    
    return implode(', ', $expanded);
}

/**
 * Format currency amount (stored in thousands)
 */
function formatCurrency($amount) {
    if ($amount === null || $amount === '') {
        return '—';
    }
    // Amount is in thousands, display as full dollars
    $full = (int)$amount * 1000;
    return '$' . number_format($full);
}

/**
 * Format currency as thousands (for display showing "in thousands")
 */
function formatCurrencyThousands($amount) {
    if ($amount === null || $amount === '') {
        return '—';
    }
    return '$' . number_format((int)$amount);
}

/**
 * Format phone number
 */
function formatPhone($phone) {
    if (empty($phone)) {
        return '';
    }
    // Remove non-digits
    $digits = preg_replace('/\D/', '', $phone);
    
    if (strlen($digits) === 10) {
        return sprintf("(%s) %s-%s", 
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 4)
        );
    }
    return $phone; // Return original if not standard format
}

/**
 * Get institution type code from ID
 */
function getInstitutionTypeCode($pdo, $typeId) {
    static $cache = null;
    
    if ($cache === null) {
        $stmt = $pdo->query("SELECT id, code FROM institution_types");
        $cache = [];
        while ($row = $stmt->fetch()) {
            $cache[$row['id']] = $row['code'];
        }
    }
    
    return $cache[$typeId] ?? 'bank';
}

/**
 * Get institution type ID from code
 */
function getInstitutionTypeId($pdo, $code) {
    static $cache = null;
    
    if ($cache === null) {
        $stmt = $pdo->query("SELECT id, code FROM institution_types");
        $cache = [];
        while ($row = $stmt->fetch()) {
            $cache[$row['code']] = $row['id'];
        }
    }
    
    return $cache[$code] ?? 1;
}

/**
 * Get institution type name from ID
 */
function getInstitutionTypeName($pdo, $typeId) {
    static $cache = null;
    
    if ($cache === null) {
        $stmt = $pdo->query("SELECT id, name FROM institution_types");
        $cache = [];
        while ($row = $stmt->fetch()) {
            $cache[$row['id']] = $row['name'];
        }
    }
    
    return $cache[$typeId] ?? 'Bank';
}

/**
 * Build URL with parameters
 */
function buildUrl($page, $params = []) {
    if (empty($params)) {
        return $page;
    }
    return $page . '?' . http_build_query($params);
}

/**
 * Get the correct detail page for an institution type
 */
function getDetailPage($typeCode) {
    switch ($typeCode) {
        case 'credit_union':
            return 'credit-union.php';
        case 'savings_loan':
            return 'savings-and-loan.php';
        case 'bank':
        default:
            return 'bank.php';
    }
}

/**
 * Sanitize and validate state parameter
 */
function sanitizeState($state) {
    $state = strtoupper(trim($state ?? ''));
    if (preg_match('/^[A-Z]{2}$/', $state)) {
        return $state;
    }
    return null;
}

/**
 * Sanitize and validate year parameter
 */
function sanitizeYear($year) {
    $year = (int)$year;
    if ($year >= 1900 && $year <= 2100) {
        return $year;
    }
    return null;
}

/**
 * Sanitize and validate season parameter
 */
function sanitizeSeason($season) {
    $season = strtolower(trim($season ?? ''));
    if (in_array($season, ['spring', 'fall'])) {
        return $season;
    }
    return null;
}

/**
 * Sanitize bank_no parameter
 */
function sanitizeBankNo($bankNo) {
    $bankNo = trim($bankNo ?? '');
    if (preg_match('/^[0-9a-zA-Z]{1,5}$/', $bankNo)) {
        return $bankNo;
    }
    return null;
}

/**
 * Safe HTML output
 */
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Output a value or a placeholder if empty
 */
function displayValue($value, $placeholder = '—') {
    $value = trim($value ?? '');
    return $value !== '' ? h($value) : $placeholder;
}

/**
 * Format season for display (capitalize)
 */
function formatSeason($season) {
    return ucfirst($season);
}

/**
 * Format publication as string
 */
function formatPublication($year, $season) {
    return formatSeason($season) . ' ' . $year;
}

// ============================================================
// FILTER FUNCTIONS
// ============================================================

/**
 * Get the most recent publication for each state
 * Fall comes after Spring in the calendar year, so Fall is more recent
 * Spring is the "major" publication (primary release), Fall is supplemental
 * Not all states have Fall publications
 * Returns array: ['KS' => ['year' => 2019, 'season' => 'fall'], ...]
 */
function getMostRecentPublications($pdo) {
    // Year DESC, then Fall > Spring (Fall is later in the year)
    $stmt = $pdo->query("
        SELECT pub_state, pub_year, pub_season
        FROM institution_main
        GROUP BY pub_state, pub_year, pub_season
        ORDER BY pub_state, pub_year DESC, 
                 CASE WHEN pub_season = 'fall' THEN 1 ELSE 2 END
    ");
    
    $results = [];
    while ($row = $stmt->fetch()) {
        $state = $row['pub_state'];
        // Only keep first (most recent) per state
        if (!isset($results[$state])) {
            $results[$state] = [
                'year' => $row['pub_year'],
                'season' => $row['pub_season']
            ];
        }
    }
    return $results;
}

/**
 * Get counts for all filter sections
 * Cascading: each section filtered by selections above it
 * Only counts from most recent publication per state
 */
function getFilterCounts($pdo, $currentFilters = []) {
    $counts = [];
    
    // Get most recent publication per state
    $mostRecent = getMostRecentPublications($pdo);
    
    // States - show all with counts from their most recent pub only
    $counts['states'] = [];
    foreach ($mostRecent as $state => $pub) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM institution_main 
            WHERE pub_state = ? AND pub_year = ? AND pub_season = ?
        ");
        $stmt->execute([$state, $pub['year'], $pub['season']]);
        $counts['states'][] = [
            'code' => $state,
            'count' => $stmt->fetchColumn()
        ];
    }
    
    // If no states selected, return early with just state counts
    if (empty($currentFilters['state'])) {
        $counts['types'] = [];
        $counts['counties'] = [];
        $counts['memberships'] = [];
        $counts['assetRanges'] = [];
        return $counts;
    }
    
    // Build base WHERE for selected states (most recent pub only)
    $selectedStates = $currentFilters['state'];
    $stateConditions = [];
    $stateParams = [];
    
    foreach ($selectedStates as $state) {
        if (isset($mostRecent[$state])) {
            $stateConditions[] = "(m.pub_state = ? AND m.pub_year = ? AND m.pub_season = ?)";
            $stateParams[] = $state;
            $stateParams[] = $mostRecent[$state]['year'];
            $stateParams[] = $mostRecent[$state]['season'];
        }
    }
    
    if (empty($stateConditions)) {
        $counts['types'] = [];
        $counts['counties'] = [];
        $counts['memberships'] = [];
        $counts['assetRanges'] = [];
        return $counts;
    }
    
    $stateWhere = "(" . implode(" OR ", $stateConditions) . ")";
    
    // === INSTITUTION TYPES ===
    // Filtered by: state only
    $stmt = $pdo->prepare("
        SELECT t.code, t.name, COUNT(*) as count
        FROM institution_main m
        JOIN institution_types t ON m.institution_type_id = t.id
        WHERE $stateWhere
        GROUP BY t.id, t.code, t.name
        ORDER BY t.name
    ");
    $stmt->execute($stateParams);
    $counts['types'] = $stmt->fetchAll();
    
    // === COUNTIES ===
    // Filtered by: state + type
    $countyWhere = $stateWhere;
    $countyParams = $stateParams;
    
    if (!empty($currentFilters['type'])) {
        $placeholders = implode(',', array_fill(0, count($currentFilters['type']), '?'));
        $countyWhere .= " AND t.code IN ($placeholders)";
        $countyParams = array_merge($countyParams, $currentFilters['type']);
    }
    
    $stmt = $pdo->prepare("
        SELECT m.county, COUNT(*) as count
        FROM institution_main m
        JOIN institution_types t ON m.institution_type_id = t.id
        WHERE $countyWhere AND m.county IS NOT NULL AND m.county != ''
        GROUP BY m.county
        ORDER BY m.county
    ");
    $stmt->execute($countyParams);
    $counts['counties'] = $stmt->fetchAll();
    
    // === MEMBERSHIPS ===
    // Filtered by: state + type + county
    $membershipWhere = $countyWhere;
    $membershipParams = $countyParams;
    
    if (!empty($currentFilters['county'])) {
        $placeholders = implode(',', array_fill(0, count($currentFilters['county']), '?'));
        $membershipWhere .= " AND m.county IN ($placeholders)";
        $membershipParams = array_merge($membershipParams, $currentFilters['county']);
    }
    
    $stmt = $pdo->prepare("
        SELECT o.code, o.name, COUNT(DISTINCT m.id) as count
        FROM institution_main m
        JOIN institution_types t ON m.institution_type_id = t.id
        JOIN institution_memberships im ON m.id = im.main_id
        JOIN membership_orgs o ON im.org_id = o.id
        WHERE $membershipWhere
        GROUP BY o.id, o.code, o.name
        ORDER BY count DESC, o.name
    ");
    $stmt->execute($membershipParams);
    $counts['memberships'] = $stmt->fetchAll();
    
    // === ASSET RANGES ===
    // Filtered by: state + type + county + membership
    $assetWhere = $membershipWhere;
    $assetParams = $membershipParams;
    $assetJoins = "";
    
    if (!empty($currentFilters['membership'])) {
        $placeholders = implode(',', array_fill(0, count($currentFilters['membership']), '?'));
        $assetJoins = " JOIN institution_memberships im_asset ON m.id = im_asset.main_id
                        JOIN membership_orgs mo_asset ON im_asset.org_id = mo_asset.id";
        $assetWhere .= " AND mo_asset.code IN ($placeholders)";
        $assetParams = array_merge($assetParams, $currentFilters['membership']);
    }
    
    $assetRanges = [
        'under50' => [0, 50000],
        '50-100' => [50000, 100000],
        '100-500' => [100000, 500000],
        '500-1000' => [500000, 1000000],
        'over1000' => [1000000, null]
    ];
    
    $counts['assetRanges'] = [];
    foreach ($assetRanges as $code => $range) {
        $sql = "SELECT COUNT(DISTINCT m.id) FROM institution_main m 
                JOIN institution_types t ON m.institution_type_id = t.id
                $assetJoins
                WHERE $assetWhere AND m.total_assets IS NOT NULL";
        $params = $assetParams;
        
        if ($range[1] !== null) {
            $sql .= " AND m.total_assets >= ? AND m.total_assets < ?";
            $params[] = $range[0];
            $params[] = $range[1];
        } else {
            $sql .= " AND m.total_assets >= ?";
            $params[] = $range[0];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $counts['assetRanges'][$code] = $stmt->fetchColumn();
    }
    
    return $counts;
}

/**
 * Build complete query from filters
 * Auto-filters to most recent publication per selected state
 */
function buildFilterQuery($pdo, $filters) {
    $where = [];
    $params = [];
    $joins = '';
    
    // Get most recent publications
    $mostRecent = getMostRecentPublications($pdo);
    
    // State filter - must also filter to most recent pub
    if (!empty($filters['state'])) {
        $stateConditions = [];
        foreach ($filters['state'] as $state) {
            if (isset($mostRecent[$state])) {
                $stateConditions[] = "(m.pub_state = ? AND m.pub_year = ? AND m.pub_season = ?)";
                $params[] = $state;
                $params[] = $mostRecent[$state]['year'];
                $params[] = $mostRecent[$state]['season'];
            }
        }
        if (!empty($stateConditions)) {
            $where[] = "(" . implode(" OR ", $stateConditions) . ")";
        }
    }
    
    // Type filter
    if (!empty($filters['type'])) {
        $placeholders = implode(',', array_fill(0, count($filters['type']), '?'));
        $where[] = "t.code IN ($placeholders)";
        $params = array_merge($params, $filters['type']);
    }
    
    // County filter
    if (!empty($filters['county'])) {
        $placeholders = implode(',', array_fill(0, count($filters['county']), '?'));
        $where[] = "m.county IN ($placeholders)";
        $params = array_merge($params, $filters['county']);
    }
    
    // Membership filter
    if (!empty($filters['membership'])) {
        $placeholders = implode(',', array_fill(0, count($filters['membership']), '?'));
        $joins .= " JOIN institution_memberships im_filter ON m.id = im_filter.main_id
                    JOIN membership_orgs mo_filter ON im_filter.org_id = mo_filter.id";
        $where[] = "mo_filter.code IN ($placeholders)";
        $params = array_merge($params, $filters['membership']);
    }
    
    // Asset range filter (can be multiple ranges)
    if (!empty($filters['assets'])) {
        $assetRanges = [
            'under50' => [0, 50000],
            '50-100' => [50000, 100000],
            '100-500' => [100000, 500000],
            '500-1000' => [500000, 1000000],
            'over1000' => [1000000, null]
        ];
        
        $assetConditions = [];
        foreach ($filters['assets'] as $rangeCode) {
            if (isset($assetRanges[$rangeCode])) {
                $range = $assetRanges[$rangeCode];
                if ($range[1] !== null) {
                    $assetConditions[] = "(m.total_assets >= ? AND m.total_assets < ?)";
                    $params[] = $range[0];
                    $params[] = $range[1];
                } else {
                    $assetConditions[] = "(m.total_assets >= ?)";
                    $params[] = $range[0];
                }
            }
        }
        if (!empty($assetConditions)) {
            $where[] = "(" . implode(" OR ", $assetConditions) . ")";
        }
    }
    
    // Text search
    if (!empty($filters['q'])) {
        $where[] = "(m.name LIKE ? OR m.city LIKE ?)";
        $params[] = '%' . $filters['q'] . '%';
        $params[] = '%' . $filters['q'] . '%';
    }
    
    return [
        'where' => !empty($where) ? implode(' AND ', $where) : '1=1',
        'joins' => $joins,
        'params' => $params
    ];
}

/**
 * Get active filters for pills display (not used on search page anymore)
 */
function getActiveFilters($pdo, $filters) {
    $active = [];
    
    // States
    if (!empty($filters['state'])) {
        foreach ($filters['state'] as $state) {
            $active[] = [
                'key' => 'state',
                'value' => $state,
                'label' => $state
            ];
        }
    }
    
    // Institution types
    if (!empty($filters['type'])) {
        $typeNames = ['bank' => 'Banks', 'credit_union' => 'Credit Unions', 'savings_loan' => 'Savings & Loans'];
        foreach ($filters['type'] as $type) {
            $active[] = [
                'key' => 'type',
                'value' => $type,
                'label' => $typeNames[$type] ?? $type
            ];
        }
    }
    
    // Counties
    if (!empty($filters['county'])) {
        foreach ($filters['county'] as $county) {
            $active[] = [
                'key' => 'county',
                'value' => $county,
                'label' => $county . ' County'
            ];
        }
    }
    
    // Memberships
    if (!empty($filters['membership'])) {
        $placeholders = implode(',', array_fill(0, count($filters['membership']), '?'));
        $stmt = $pdo->prepare("SELECT code, name FROM membership_orgs WHERE code IN ($placeholders)");
        $stmt->execute($filters['membership']);
        $orgNames = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($filters['membership'] as $code) {
            $active[] = [
                'key' => 'membership',
                'value' => $code,
                'label' => $orgNames[$code] ?? strtoupper($code)
            ];
        }
    }
    
    // Asset ranges
    if (!empty($filters['assets'])) {
        $rangeLabels = [
            'under50' => 'Under $50M',
            '50-100' => '$50M - $100M',
            '100-500' => '$100M - $500M',
            '500-1000' => '$500M - $1B',
            'over1000' => 'Over $1B'
        ];
        foreach ($filters['assets'] as $range) {
            $active[] = [
                'key' => 'assets',
                'value' => $range,
                'label' => $rangeLabels[$range] ?? $range
            ];
        }
    }
    
    // Text search
    if (!empty($filters['q'])) {
        $active[] = [
            'key' => 'q',
            'value' => null,
            'label' => 'Search: "' . $filters['q'] . '"'
        ];
    }
    
    return $active;
}