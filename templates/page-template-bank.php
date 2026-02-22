<?php
/**
 * Bank Detail Page Template
 * 
 * Expects these variables from router:
 *   $pdo, $currentState, $currentYear, $currentSeason
 *   $institution, $cityBranches, $otherBranches, $memberships
 *   $financials, $figYear, $figSeason, $figIsCurrent
 *   $figPrevPub, $figNextPub, $availablePublications, $bankNo
 */

include 'includes/header.php';

// Build base URL for figures navigation
$figBaseUrl = 'bank.php?state=' . urlencode($currentState) . '&id=' . urlencode($bankNo);
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="<?= buildUrl('search.php', ['state' => $currentState]) ?>">Search</a>
        <span class="separator">/</span>
        <span class="current"><?= h($institution['name']) ?></span>
    </nav>
    <h1><?= h($institution['name']) ?></h1>
    <p class="institution-type"><?= h($institution['type_name']) ?></p>
</div>

<div class="institution-detail">
    
    <!-- Contact Information -->
    <section class="detail-section">
        <h2>Contact Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Address</label>
                <div><?= displayValue($institution['address']) ?></div>
                <div><?= h($institution['city']) ?>, <?= h($institution['state']) ?> <?= h($institution['zip']) ?></div>
            </div>
            
            <?php if ($institution['mail_address'] && $institution['mail_address'] !== $institution['address']): ?>
            <div class="info-item">
                <label>Mailing Address</label>
                <div><?= displayValue($institution['mail_address']) ?></div>
            </div>
            <?php endif; ?>
            
            <div class="info-item">
                <label>County</label>
                <div><?= displayValue($institution['county']) ?></div>
            </div>
            
            <div class="info-item">
                <label>Phone</label>
                <div><?= formatPhone($institution['phone1']) ?: '—' ?></div>
                <?php if ($institution['phone2']): ?>
                    <div><?= formatPhone($institution['phone2']) ?></div>
                <?php endif; ?>
            </div>
            
            <?php if ($institution['fax']): ?>
            <div class="info-item">
                <label>Fax</label>
                <div><?= formatPhone($institution['fax']) ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($institution['email']): ?>
            <div class="info-item">
                <label>Email</label>
                <div><a href="mailto:<?= h($institution['email']) ?>"><?= h($institution['email']) ?></a></div>
            </div>
            <?php endif; ?>
            
            <?php if ($institution['website']): ?>
            <div class="info-item">
                <label>Website</label>
                <?php 
                $url = $institution['website'];
                if (!preg_match('/^https?:\/\//', $url)) {
                    $url = 'https://' . $url;
                }
                ?>
                <div><a href="<?= h($url) ?>" target="_blank" rel="noopener"><?= h($institution['website']) ?></a></div>
            </div>
            <?php endif; ?>
            
            <?php if ($institution['hours']): ?>
            <div class="info-item">
                <label>Hours</label>
                <div><?= displayValue($institution['hours']) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Bank Details -->
    <section class="detail-section">
        <h2>Institution Details</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Charter Year</label>
                <div><?= displayValue($institution['charter_year']) ?></div>
            </div>
            
            <div class="info-item">
                <label>Bank Number</label>
                <div><?= h($institution['bank_no']) ?></div>
            </div>
            
            <?php if ($institution['transit_no']): ?>
            <div class="info-item">
                <label>Transit Number</label>
                <div><?= displayValue($institution['transit_no']) ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($institution['micr']): ?>
            <div class="info-item">
                <label>MICR</label>
                <div><?= displayValue($institution['micr']) ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($institution['holding_company']): ?>
            <div class="info-item">
                <label>Holding Company</label>
                <div><?= displayValue($institution['holding_company']) ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($institution['employer_id']): ?>
            <div class="info-item">
                <label>Employer ID</label>
                <div><?= displayValue($institution['employer_id']) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Officers -->
    <section class="detail-section">
        <h2>Officers &amp; Management</h2>
        
        <?php if ($institution['ceo']): ?>
        <div class="officer-primary">
            <strong><?= h($institution['ceo']) ?></strong>
            <?php if ($institution['ceo_title']): ?>
                <span class="officer-title"><?= h($institution['ceo_title']) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="officers-list">
            <?php
            $officers = [];
            for ($i = 1; $i <= 5; $i++) {
                $field = "officer_$i";
                if (!empty($institution[$field])) {
                    $officers[] = $institution[$field];
                }
            }
            ?>
            
            <?php if (!empty($officers)): ?>
            <h3>Key Officers</h3>
            <ul>
                <?php foreach ($officers as $officer): ?>
                    <li><?= h($officer) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            
            <?php
            $roles = [
                'off_marketing' => 'Marketing',
                'off_trust' => 'Trust',
                'off_operations' => 'Operations',
                'off_chief_lending' => 'Chief Lending Officer',
                'off_finance_accounting' => 'Finance & Accounting',
                'off_cto' => 'Chief Technology Officer',
                'off_it_security' => 'IT/Security',
                'off_agriculture' => 'Agriculture'
            ];
            
            $roleOfficers = [];
            foreach ($roles as $field => $label) {
                if (!empty($institution[$field])) {
                    $roleOfficers[$label] = $institution[$field];
                }
            }
            ?>
            
            <?php if (!empty($roleOfficers)): ?>
            <h3>Department Contacts</h3>
            <div class="info-grid">
                <?php foreach ($roleOfficers as $label => $name): ?>
                <div class="info-item">
                    <label><?= h($label) ?></label>
                    <div><?= h($name) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Financial Information -->
    <section class="detail-section" id="financial-section" 
             data-state="<?= h($currentState) ?>" 
             data-bank-no="<?= h($bankNo) ?>"
             data-current-year="<?= h($currentYear) ?>"
             data-current-season="<?= h($currentSeason) ?>">
        <div class="section-header-with-nav">
            <h2>Financial Information</h2>
            <div class="figures-nav">
                <?php if ($figIsCurrent): ?>
                    <span class="current-badge" id="current-badge">Current</span>
                    <span class="historical-badge historical-badge-hidden" id="historical-badge">Historical</span>
                <?php else: ?>
                    <span class="current-badge current-badge-hidden" id="current-badge">Current</span>
                    <span class="historical-badge" id="historical-badge">Historical</span>
                <?php endif; ?>
                
                <?php if ($figOlderPub): ?>
                    <a href="<?= h($figBaseUrl . '&fig_year=' . $figOlderPub['year'] . '&fig_season=' . $figOlderPub['season']) ?>" 
                       class="fig-nav-btn fig-nav-older" 
                       data-year="<?= h($figOlderPub['year']) ?>"
                       data-season="<?= h($figOlderPub['season']) ?>"
                       title="Older: <?= h(formatPublication($figOlderPub['year'], $figOlderPub['season'])) ?>">
                        &lt;
                    </a>
                <?php else: ?>
                    <span class="fig-nav-btn fig-nav-disabled fig-nav-older">&lt;</span>
                <?php endif; ?>
                
                <span class="figures-year-display" id="figures-year-display">
                    <?= h(formatPublication($figYear, $figSeason)) ?>
                </span>
                
                <?php if ($figNewerPub): ?>
                    <a href="<?= h($figBaseUrl . '&fig_year=' . $figNewerPub['year'] . '&fig_season=' . $figNewerPub['season']) ?>" 
                       class="fig-nav-btn fig-nav-newer" 
                       data-year="<?= h($figNewerPub['year']) ?>"
                       data-season="<?= h($figNewerPub['season']) ?>"
                       title="Newer: <?= h(formatPublication($figNewerPub['year'], $figNewerPub['season'])) ?>">
                        &gt;
                    </a>
                <?php else: ?>
                    <span class="fig-nav-btn fig-nav-disabled fig-nav-newer">&gt;</span>
                <?php endif; ?>
            </div>
        </div>
        
        <p class="table-note">* All figures in thousands — click any row to see historical chart</p>
        
        <div class="financial-tables" id="financial-tables">
            <div class="financial-table">
                <h3>Assets</h3>
                <table>
                    <tbody>
                        <?php if ($financials['total_assets']): ?>
                        <tr class="total-row clickable-row" data-field="total_assets" data-label="Total Assets">
                            <td><strong>Total Assets</strong></td>
                            <td class="text-right"><strong><span data-field="total_assets"><?= formatCurrencyThousands($financials['total_assets']) ?></span></strong></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['total_loans']): ?>
                        <tr class="clickable-row" data-field="total_loans" data-label="Total Loans">
                            <td>Total Loans</td>
                            <td class="text-right"><span data-field="total_loans"><?= formatCurrencyThousands($financials['total_loans']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['cash_due']): ?>
                        <tr class="clickable-row" data-field="cash_due" data-label="Cash Due">
                            <td>Cash Due</td>
                            <td class="text-right"><span data-field="cash_due"><?= formatCurrencyThousands($financials['cash_due']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['securities']): ?>
                        <tr class="clickable-row" data-field="securities" data-label="Securities">
                            <td>Securities</td>
                            <td class="text-right"><span data-field="securities"><?= formatCurrencyThousands($financials['securities']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['total_investments']): ?>
                        <tr class="clickable-row" data-field="total_investments" data-label="Total Investments">
                            <td>Total Investments</td>
                            <td class="text-right"><span data-field="total_investments"><?= formatCurrencyThousands($financials['total_investments']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['fed_funds_sold']): ?>
                        <tr class="clickable-row" data-field="fed_funds_sold" data-label="Fed Funds Sold">
                            <td>Fed Funds Sold</td>
                            <td class="text-right"><span data-field="fed_funds_sold"><?= formatCurrencyThousands($financials['fed_funds_sold']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['all_other_assets']): ?>
                        <tr class="clickable-row" data-field="all_other_assets" data-label="All Other Assets">
                            <td>All Other Assets</td>
                            <td class="text-right"><span data-field="all_other_assets"><?= formatCurrencyThousands($financials['all_other_assets']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="financial-table">
                <h3>Capital &amp; Liabilities</h3>
                <table>
                    <tbody>
                        <?php if ($financials['capital_stock']): ?>
                        <tr class="clickable-row" data-field="capital_stock" data-label="Capital Stock">
                            <td>Capital Stock</td>
                            <td class="text-right"><span data-field="capital_stock"><?= formatCurrencyThousands($financials['capital_stock']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['surplus']): ?>
                        <tr class="clickable-row" data-field="surplus" data-label="Surplus">
                            <td>Surplus</td>
                            <td class="text-right"><span data-field="surplus"><?= formatCurrencyThousands($financials['surplus']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['undivided_profits']): ?>
                        <tr class="clickable-row" data-field="undivided_profits" data-label="Undivided Profits">
                            <td>Undivided Profits</td>
                            <td class="text-right"><span data-field="undivided_profits"><?= formatCurrencyThousands($financials['undivided_profits']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['retained_earnings']): ?>
                        <tr class="clickable-row" data-field="retained_earnings" data-label="Retained Earnings">
                            <td>Retained Earnings</td>
                            <td class="text-right"><span data-field="retained_earnings"><?= formatCurrencyThousands($financials['retained_earnings']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['total_deposits']): ?>
                        <tr class="clickable-row" data-field="total_deposits" data-label="Total Deposits">
                            <td>Total Deposits</td>
                            <td class="text-right"><span data-field="total_deposits"><?= formatCurrencyThousands($financials['total_deposits']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['shares']): ?>
                        <tr class="clickable-row" data-field="shares" data-label="Shares">
                            <td>Shares</td>
                            <td class="text-right"><span data-field="shares"><?= formatCurrencyThousands($financials['shares']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($financials['net_income']): ?>
                        <tr class="total-row clickable-row" data-field="net_income" data-label="Net Income">
                            <td><strong>Net Income</strong></td>
                            <td class="text-right"><strong><span data-field="net_income"><?= formatCurrencyThousands($financials['net_income']) ?></span></strong></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Chart Container -->
        <div class="financial-chart-container" id="financial-chart-container" style="display: none;">
            <div class="chart-header">
                <h3 id="chart-title">Historical Data</h3>
                <button type="button" class="chart-close" id="chart-close">&times;</button>
            </div>
            <canvas id="financial-chart"></canvas>
        </div>
        
        <p class="figures-note" id="figures-note">
            <?php if (!$figIsCurrent): ?>
                <a href="<?= h($figBaseUrl) ?>" class="fig-current-link"
                   data-year="<?= h($currentYear) ?>"
                   data-season="<?= h($currentSeason) ?>">View current figures (<?= h(formatPublication($currentYear, $currentSeason)) ?>)</a>
            <?php endif; ?>
        </p>
    </section>
    
    <!-- Memberships -->
    <?php if (!empty($memberships)): ?>
    <section class="detail-section">
        <h2>Memberships</h2>
        <ul class="memberships-list">
            <?php foreach ($memberships as $org): ?>
                <li>
                    <a href="<?= buildUrl('membership.php', [
                        'state' => $currentState,
                        'code' => $org['code']
                    ]) ?>">
                        <?= h($org['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>
    
    <!-- City Branches -->
    <?php if (!empty($cityBranches)): ?>
    <section class="detail-section">
        <h2>Branches</h2>
        
        <div class="accordion">
            <div class="accordion-item">
                <button class="accordion-header" aria-expanded="true">
                    City Branches (<?= count($cityBranches) ?>)
                </button>
                <div class="accordion-content" style="display: block;">
                    <table class="branches-table">
                        <thead>
                            <tr>
                                <th>Address</th>
                                <th>City</th>
                                <th>Phone</th>
                                <th>Manager</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cityBranches as $branch): ?>
                            <tr>
                                <td><?= displayValue($branch['address']) ?></td>
                                <td><?= displayValue($branch['city']) ?>, <?= h($branch['state']) ?> <?= h($branch['zip']) ?></td>
                                <td><?= formatPhone($branch['phone']) ?: '—' ?></td>
                                <td><?= displayValue($branch['manager']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if (!empty($otherBranches)): ?>
            <div class="accordion-item">
                <button class="accordion-header" aria-expanded="true">
                    Other Branches (<?= count($otherBranches) ?>)
                </button>
                <div class="accordion-content" style="display: block;">
                    <table class="branches-table">
                        <thead>
                            <tr>
                                <th>Branch Name</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>Phone</th>
                                <th>Manager</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($otherBranches as $branch): ?>
                            <tr>
                                <td><?= displayValue($branch['branch_name']) ?></td>
                                <td><?= displayValue($branch['address']) ?></td>
                                <td><?= displayValue($branch['city']) ?>, <?= h($branch['state']) ?> <?= h($branch['zip']) ?></td>
                                <td><?= formatPhone($branch['phone']) ?: '—' ?></td>
                                <td><?= displayValue($branch['manager']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    
</div>

<?php include 'includes/footer.php'; ?>
