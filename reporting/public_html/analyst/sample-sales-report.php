<?php
/**
 * Sales Performance Report Sample
 * This creates a realistic sales report with charts and detailed data tables
 */

$page_title = "Sales Performance Report";
require_once __DIR__ . '/../includes/header.php';

require_role(['analyst', 'super_admin']);

?>

<h1>📊 Sales Performance Report Template</h1>

<div class="card">
    <h2>How to Create a Sales Performance Report</h2>
    <p>Use this template as a guide for creating comprehensive sales analysis reports. Include monthly revenue trends, product performance, and regional breakdowns.</p>
</div>

<div class="card">
    <h2>Sample: Q1 2026 Sales Performance</h2>
    
    <h3>Performance Overview</h3>
    <p>
        <strong>Total Revenue:</strong> $245,300 | 
        <strong>Growth:</strong> +15% YoY | 
        <strong>Top Product:</strong> Premium Plan | 
        <strong>Conversion Rate:</strong> 3.8%
    </p>
    
    <h3>Monthly Revenue Breakdown</h3>
    
    <canvas id="revenueChart" style="max-width: 100%; height: 400px;"></canvas>
    
    <script>
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: ['January', 'February', 'March'],
            datasets: [{
                label: 'Revenue ($)',
                data: [75200, 82100, 88000],
                backgroundColor: ['#667eea', '#764ba2', '#f39c12'],
                borderColor: ['#667eea', '#764ba2', '#f39c12'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    </script>
    
    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Month</th>
                <th>Revenue</th>
                <th>Orders</th>
                <th>Avg Order Value</th>
                <th>Growth</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>January 2026</td>
                <td>$75,200</td>
                <td>1,240</td>
                <td>$60.65</td>
                <td>+5% vs Dec</td>
            </tr>
            <tr>
                <td>February 2026</td>
                <td>$82,100</td>
                <td>1,380</td>
                <td>$59.49</td>
                <td>+9.2% vs Jan</td>
            </tr>
            <tr>
                <td>March 2026</td>
                <td>$88,000</td>
                <td>1,510</td>
                <td>$58.28</td>
                <td>+7.2% vs Feb</td>
            </tr>
        </tbody>
    </table>
    
    <h3 style="margin-top: 30px;">Product Category Performance</h3>
    
    <canvas id="productChart" style="max-width: 100%; height: 300px;"></canvas>
    
    <script>
    const productCtx = document.getElementById('productChart').getContext('2d');
    new Chart(productCtx, {
        type: 'doughnut',
        data: {
            labels: ['Premium Plan', 'Enterprise Plan', 'Starter Plan', 'Custom Solutions'],
            datasets: [{
                data: [45200, 38900, 32100, 29100],
                backgroundColor: ['#667eea', '#764ba2', '#2ecc71', '#f39c12']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    </script>
    
    <h3 style="margin-top: 30px;">Regional Sales Performance</h3>
    
    <table>
        <thead>
            <tr>
                <th>Region</th>
                <th>Q1 Revenue</th>
                <th>Market Share</th>
                <th>Growth vs Q1 2025</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>North America</strong></td>
                <td>$147,180</td>
                <td>60%</td>
                <td>+18%</td>
                <td><span style="background: #2ecc71; color: white; padding: 3px 8px; border-radius: 3px;">Strong</span></td>
            </tr>
            <tr>
                <td><strong>Europe</strong></td>
                <td>$61,325</td>
                <td>25%</td>
                <td>+12%</td>
                <td><span style="background: #f39c12; color: white; padding: 3px 8px; border-radius: 3px;">Growing</span></td>
            </tr>
            <tr>
                <td><strong>Asia Pacific</strong></td>
                <td>$36,795</td>
                <td>15%</td>
                <td>+8%</td>
                <td><span style="background: #3498db; color: white; padding: 3px 8px; border-radius: 3px;">Expanding</span></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Key Performance Indicators</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
        <div style="background: #efe; padding: 15px; border-radius: 4px;">
            <h4 style="color: #3a3;">Conversion Rate</h4>
            <p style="font-size: 24px; font-weight: bold; color: #3a3;">3.8%</p>
            <p style="font-size: 12px; color: #666;">↑ 0.3% from previous month</p>
        </div>
        <div style="background: #eff; padding: 15px; border-radius: 4px;">
            <h4 style="color: #339;">Customer Lifetime Value</h4>
            <p style="font-size: 24px; font-weight: bold; color: #339;">$1,240</p>
            <p style="font-size: 12px; color: #666;">↑ $180 increase</p>
        </div>
        <div style="background: #fee; padding: 15px; border-radius: 4px;">
            <h4 style="color: #c33;">Churn Rate</h4>
            <p style="font-size: 24px; font-weight: bold; color: #c33;">2.1%</p>
            <p style="font-size: 12px; color: #666;">↓ 0.4% improvement</p>
        </div>
    </div>
</div>

<div class="card" style="background: #f9f9f9; border-left: 4px solid #667eea;">
    <h3>Next Steps</h3>
    <p>To create your own Sales Performance report:</p>
    <ol style="margin-left: 20px; margin-top: 10px;">
        <li>Go to <a href="create-report.php">Create Report</a></li>
        <li>Select "Sales Performance Dashboard" category</li>
        <li>Copy the HTML content from this template</li>
        <li>Customize with your own data and metrics</li>
        <li>Add your analysis in "Analyst Comments"</li>
        <li>Publish when ready</li>
    </ol>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
