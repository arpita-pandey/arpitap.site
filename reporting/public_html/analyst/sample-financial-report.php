<?php
/**
 * Financial Health Report Sample
 * Demonstrates budget vs actual, financial metrics, and forecast analysis
 */

$page_title = "Financial Health Report";
require_once __DIR__ . '/../includes/header.php';

require_role(['analyst', 'super_admin']);

?>

<h1>💰 Financial Health Report Template</h1>

<div class="card">
    <h2>About This Report</h2>
    <p>Monitor financial performance, budget variance, and health metrics. This template shows budget vs actual analysis, cash flow trends, and financial forecasting.</p>
</div>

<div class="card">
    <h2>Sample: Financial Health Score Report - Q1 2026</h2>
    
    <h3>Financial Overview</h3>
    <p>
        <strong>Total Revenue:</strong> $485,600 | 
        <strong>Total Expenses:</strong> $312,400 | 
        <strong>Operating Margin:</strong> 35.6% | 
        <strong>Cash Position:</strong> $1.24M
    </p>
    
    <h3>Budget vs Actual Performance</h3>
    
    <canvas id="budgetChart" style="max-width: 100%; height: 350px;"></canvas>
    
    <script>
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: ['January', 'February', 'March'],
            datasets: [
                {
                    label: 'Budget',
                    data: [160000, 160000, 160000],
                    backgroundColor: '#95a5a6'
                },
                {
                    label: 'Actual',
                    data: [185200, 165400, 135000],
                    backgroundColor: '#667eea'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    </script>
    
    <h3 style="margin-top: 30px;">Detailed Budget Analysis</h3>
    
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Budget</th>
                <th>Actual</th>
                <th>Variance</th>
                <th>% of Budget</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #efe;">
                <td><strong>Revenue</strong></td>
                <td>$460,000</td>
                <td>$485,600</td>
                <td style="color: #2ecc71;">+$25,600</td>
                <td>105.6%</td>
                <td><span style="background: #2ecc71; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">✓ Exceeded</span></td>
            </tr>
            <tr>
                <td><strong>Operating Expenses</strong></td>
                <td>$280,000</td>
                <td>$312,400</td>
                <td style="color: #e74c3c;">-$32,400</td>
                <td>111.6%</td>
                <td><span style="background: #f39c12; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">⚠ Over Budget</span></td>
            </tr>
            <tr>
                <td style="padding-left: 30px;">Personnel</td>
                <td>$150,000</td>
                <td>$165,200</td>
                <td style="color: #e74c3c;">-$15,200</td>
                <td>110.1%</td>
                <td><span style="background: #f39c12; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Over</span></td>
            </tr>
            <tr>
                <td style="padding-left: 30px;">Technology</td>
                <td>$80,000</td>
                <td>$82,100</td>
                <td style="color: #e74c3c;">-$2,100</td>
                <td>102.6%</td>
                <td><span style="background: #f39c12; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Over</span></td>
            </tr>
            <tr>
                <td style="padding-left: 30px;">Marketing</td>
                <td>$50,000</td>
                <td>$65,100</td>
                <td style="color: #e74c3c;">-$15,100</td>
                <td>130.2%</td>
                <td><span style="background: #e74c3c; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">⚠ Significantly Over</span></td>
            </tr>
        </tbody>
    </table>
    
    <h3 style="margin-top: 30px;">Cash Flow Analysis</h3>
    
    <canvas id="cashflowChart" style="max-width: 100%; height: 300px;"></canvas>
    
    <script>
    const cashflowCtx = document.getElementById('cashflowChart').getContext('2d');
    new Chart(cashflowCtx, {
        type: 'line',
        data: {
            labels: ['Jan 1', 'Jan 15', 'Feb 1', 'Feb 15', 'Mar 1', 'Mar 15', 'Mar 31'],
            datasets: [{
                label: 'Cash Position',
                data: [980000, 1080000, 1120000, 1180000, 1240000, 1210000, 1180000],
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.3,
                fill: true,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    </script>
    
    <h3 style="margin-top: 30px;">Key Financial Metrics</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
        <div style="background: #eff; padding: 15px; border-radius: 4px; border-left: 4px solid #339;">
            <h4 style="color: #339;">Gross Profit Margin</h4>
            <p style="font-size: 24px; font-weight: bold; color: #339;">64.3%</p>
            <p style="font-size: 12px; color: #666;">↑ 2.1% improvement</p>
        </div>
        <div style="background: #efe; padding: 15px; border-radius: 4px; border-left: 4px solid #3a3;">
            <h4 style="color: #3a3;">Operating Margin</h4>
            <p style="font-size: 24px; font-weight: bold; color: #3a3;">35.6%</p>
            <p style="font-size: 12px; color: #666;">↓ 1.8% vs Q1 2025</p>
        </div>
        <div style="background: #fef9e7; padding: 15px; border-radius: 4px; border-left: 4px solid #f39c12;">
            <h4 style="color: #f39c12;">Current Ratio</h4>
            <p style="font-size: 24px; font-weight: bold; color: #f39c12;">2.48</p>
            <p style="font-size: 12px; color: #666;">Strong liquidity position</p>
        </div>
        <div style="background: #fee; padding: 15px; border-radius: 4px; border-left: 4px solid #e74c3c;">
            <h4 style="color: #e74c3c;">Debt-to-Equity</h4>
            <p style="font-size: 24px; font-weight: bold; color: #e74c3c;">0.32</p>
            <p style="font-size: 12px; color: #666;">↓ Improving capital structure</p>
        </div>
    </div>
    
    <h3 style="margin-top: 30px;">Financial Forecast (Next 3 Months)</h3>
    
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Revenue Forecast</th>
                <th>Expense Forecast</th>
                <th>Expected Margin</th>
                <th>Confidence</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>April 2026</strong></td>
                <td>$510,000</td>
                <td>$305,000</td>
                <td>40.2%</td>
                <td><span style="color: #2ecc71;">█████████░ 90%</span></td>
            </tr>
            <tr>
                <td><strong>May 2026</strong></td>
                <td>$535,000</td>
                <td>$318,000</td>
                <td>40.6%</td>
                <td><span style="color: #f39c12;">████████░░ 80%</span></td>
            </tr>
            <tr>
                <td><strong>June 2026</strong></td>
                <td>$560,000</td>
                <td>$330,000</td>
                <td>41.1%</td>
                <td><span style="color: #f39c12;">███████░░░ 70%</span></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card" style="background: #f9f9f9; border-left: 4px solid #667eea;">
    <h3>Key Insights & Recommendations</h3>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li><strong>Positive:</strong> Revenue exceeded targets with strong cash position of $1.24M</li>
        <li><strong>Concern:</strong> Marketing expenses are 30% over budget - need to review spending and ROI</li>
        <li><strong>Action:</strong> Personnel costs rising - evaluate headcount planning and compensation structure</li>
        <li><strong>Forecast:</strong> Expected to maintain 40%+ margins in Q2 with disciplined expense management</li>
    </ul>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
