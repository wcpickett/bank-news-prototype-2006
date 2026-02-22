<?php
/**
 * Bank Detail Page Template
 * 
 * Expects these variables from router:
 *   $pdo, $currentState, $currentYear, $currentSeason
 *   $institution, $cityBranches, $otherBranches, $memberships
 *   $availablePublications, $bankNo
 */

include 'includes/header.php';
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="<?= buildUrl('search.php', ['state' => $currentState, 'year' => $currentYear, 'season' => $currentSeason]) ?>">Search</a>
        <span class="separator">/</span>
        <span class="current"><?= h($institution['name']) ?></span>
    </nav>
    <h1><?= h($institution['name']) ?></h1>
    <p class="institution-type"><?= h($institution['type_name']) ?></p>
</div>

<?php
// Publication selector - scoped to this institution's available publications
$baseUrl = 'bank.php';
$extraParams = ['id' => $bankNo];
include 'includes/publication-select.php';
?>

<div class="publication-label-bar">
    Viewing: <?= h(formatPublication($currentYear, $currentSeason)) ?> | <?= h($currentState) ?>
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
    
    <!-- Financial Snapshot -->
    <section class="detail-section">
        <h2>Financial Snapshot</h2>
        <p class="table-note">All figures in thousands of dollars</p>
        
        <div class="financial-tables">
            <div class="financial-table">
                <h3>Assets</h3>
                <table>
                    <tbody>
                        <?php if ($institution['net_loans']): ?>
                        <tr>
                            <td>Net Loans</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['net_loans']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['total_loans']): ?>
                        <tr>
                            <td>Total Loans</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['total_loans']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['cash_due']): ?>
                        <tr>
                            <td>Cash Due</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['cash_due']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['securities']): ?>
                        <tr>
                            <td>Securities</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['securities']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['total_investments']): ?>
                        <tr>
                            <td>Total Investments</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['total_investments']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['fed_funds_sold']): ?>
                        <tr>
                            <td>Fed Funds Sold</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['fed_funds_sold']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['all_other_assets']): ?>
                        <tr>
                            <td>All Other Assets</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['all_other_assets']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td><strong>Total Assets</strong></td>
                            <td class="text-right"><strong><?= formatCurrencyThousands($institution['total_assets']) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="financial-table">
                <h3>Capital &amp; Liabilities</h3>
                <table>
                    <tbody>
                        <?php if ($institution['capital_stock']): ?>
                        <tr>
                            <td>Capital Stock</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['capital_stock']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['surplus']): ?>
                        <tr>
                            <td>Surplus</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['surplus']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['undivided_profits']): ?>
                        <tr>
                            <td>Undivided Profits</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['undivided_profits']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['retained_earnings']): ?>
                        <tr>
                            <td>Retained Earnings</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['retained_earnings']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['total_deposits']): ?>
                        <tr>
                            <td>Total Deposits</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['total_deposits']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['shares']): ?>
                        <tr>
                            <td>Shares</td>
                            <td class="text-right"><?= formatCurrencyThousands($institution['shares']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($institution['net_income']): ?>
                        <tr class="total-row">
                            <td><strong>Net Income</strong></td>
                            <td class="text-right"><strong><?= formatCurrencyThousands($institution['net_income']) ?></strong></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
                        'code' => $org['code'],
                        'year' => $currentYear,
                        'season' => $currentSeason
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
