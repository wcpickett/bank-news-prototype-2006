<?php
/**
 * Membership Organization Page Template
 * 
 * Expects these variables from router:
 *   $pdo, $currentState, $currentYear, $currentSeason
 *   $org, $members, $code
 */

include 'includes/header.php';
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="<?= buildUrl('search.php', ['state' => $currentState, 'year' => $currentYear, 'season' => $currentSeason]) ?>">Search</a>
        <span class="separator">/</span>
        <span class="current"><?= h($org['name']) ?></span>
    </nav>
    <h1><?= h($org['name']) ?></h1>
    <p class="org-code"><?= h(strtoupper($org['code'])) ?></p>
</div>

<?php
$baseUrl = 'membership.php';
$extraParams = ['code' => $code];
include 'includes/publication-select.php';
?>

<div class="publication-label-bar">
    Viewing: <?= h(formatPublication($currentYear, $currentSeason)) ?> | <?= h($currentState) ?>
</div>

<div class="org-detail">
    
    <!-- Organization Info -->
    <section class="detail-section">
        <h2>About This Organization</h2>
        <div class="info-grid">
            <?php if ($org['description']): ?>
            <div class="info-item full-width">
                <label>Description</label>
                <div><?= h($org['description']) ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($org['website']): ?>
            <div class="info-item">
                <label>Website</label>
                <?php 
                $url = $org['website'];
                if (!preg_match('/^https?:\/\//', $url)) {
                    $url = 'https://' . $url;
                }
                ?>
                <div><a href="<?= h($url) ?>" target="_blank" rel="noopener"><?= h($org['website']) ?></a></div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Member Institutions -->
    <section class="detail-section">
        <h2>Member Institutions</h2>
        
        <?php if ($currentState && $currentYear && $currentSeason): ?>
            <p class="members-count">
                <strong><?= count($members) ?></strong> members in <?= h($currentState) ?> for <?= h(formatPublication($currentYear, $currentSeason)) ?>
            </p>
            
            <?php if (empty($members)): ?>
                <div class="no-results">
                    <p>No member institutions found for this publication.</p>
                </div>
            <?php else: ?>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>City</th>
                            <th>Type</th>
                            <th class="text-right">Total Assets</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <?php
                            $detailPage = getDetailPage($member['type_code']);
                            $detailUrl = buildUrl($detailPage, [
                                'state' => $currentState,
                                'id' => $member['bank_no'],
                                'year' => $currentYear,
                                'season' => $currentSeason
                            ]);
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= h($detailUrl) ?>"><?= h($member['name']) ?></a>
                                </td>
                                <td><?= displayValue($member['city']) ?></td>
                                <td><?= h($member['type_name']) ?></td>
                                <td class="text-right"><?= formatCurrencyThousands($member['total_assets']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="table-note">* Assets shown in thousands</p>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-selection">
                <p>Please select a state, year, and season to view members.</p>
            </div>
        <?php endif; ?>
    </section>
    
</div>

<?php include 'includes/footer.php'; ?>
