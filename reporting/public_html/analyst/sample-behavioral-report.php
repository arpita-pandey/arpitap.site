<?php
/**
 * Customer Behavior Analytics Report Sample
 * Demonstrates engagement metrics, user behavior patterns, and retention analysis
 */

$page_title = "Customer Behavior Report";
require_once __DIR__ . '/../includes/header.php';

require_role(['analyst', 'super_admin']);

?>

<h1>👥 Customer Behavior Analytics Template</h1>

<div class="card">
    <h2>About This Report</h2>
    <p>Track user engagement patterns, behavior trends, and retention metrics. This template shows how to present complex behavioral data with clear visualizations and actionable insights.</p>
</div>

<div class="card">
    <h2>Sample: Customer Engagement Analysis - Q1 2026</h2>
    
    <h3>Executive Summary</h3>
    <p>
        <strong>Active Users:</strong> 12,450 | 
        <strong>Daily Active Users:</strong> 8,320 | 
        <strong>Avg Session Duration:</strong> 12.5 min | 
        <strong>Retention Rate:</strong> 87%
    </p>
    
    <h3>User Engagement Trends</h3>
    
    <canvas id="engagementChart" style="max-width: 100%; height: 350px;"></canvas>
    
    <script>
    const engagementCtx = document.getElementById('engagementChart').getContext('2d');
    new Chart(engagementCtx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8', 'Week 9', 'Week 10', 'Week 11', 'Week 12', 'Week 13'],
            datasets: [
                {
                    label: 'Daily Active Users',
                    data: [7850, 8100, 8250, 8340, 8420, 8580, 8620, 8750, 8840, 8920, 9050, 9120, 8320],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Total Active Users',
                    data: [10200, 10450, 10680, 10920, 11150, 11380, 11580, 11820, 12050, 12280, 12380, 12420, 12450],
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
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
    
    <h3 style="margin-top: 30px;">Customer Retention Cohort Analysis</h3>
    
    <table>
        <thead>
            <tr>
                <th>Cohort</th>
                <th>Size</th>
                <th>Week 1</th>
                <th>Week 2</th>
                <th>Week 4</th>
                <th>Week 8</th>
                <th>Week 12</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>January Cohort</strong></td>
                <td>3,200</td>
                <td>100%</td>
                <td>94%</td>
                <td>85%</td>
                <td>78%</td>
                <td>72%</td>
            </tr>
            <tr>
                <td><strong>February Cohort</strong></td>
                <td>4,150</td>
                <td>100%</td>
                <td>96%</td>
                <td>88%</td>
                <td>81%</td>
                <td>-</td>
            </tr>
            <tr>
                <td><strong>March Cohort</strong></td>
                <td>4,100</td>
                <td>100%</td>
                <td>97%</td>
                <td>89%</td>
                <td>-</td>
                <td>-</td>
            </tr>
        </tbody>
    </table>
    
    <h3 style="margin-top: 30px;">Feature Usage Breakdown</h3>
    
    <canvas id="featureChart" style="max-width: 100%; height: 300px;"></canvas>
    
    <script>
    const featureCtx = document.getElementById('featureChart').getContext('2d');
    new Chart(featureCtx, {
        type: 'horizontalBar',
        type: 'bar',
        data: {
            labels: ['Dashboard', 'Reports', 'Export Data', 'Custom Filters', 'Sharing', 'Analytics'],
            datasets: [{
                label: 'Usage %',
                data: [95, 87, 72, 64, 58, 45],
                backgroundColor: '#667eea'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { beginAtZero: true, max: 100 }
            }
        }
    });
    </script>
    
    <h3 style="margin-top: 30px;">User Engagement Segments</h3>
    
    <table>
        <thead>
            <tr>
                <th>Segment</th>
                <th>Users</th>
                <th>Avg Sessions/Week</th>
                <th>Avg Duration</th>
                <th>Trend</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Power Users</strong></td>
                <td>2,450 (19.7%)</td>
                <td>8.5</td>
                <td>23.4 min</td>
                <td>↑ +12%</td>
                <td><span style="background: #2ecc71; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Growing</span></td>
            </tr>
            <tr>
                <td><strong>Regular Users</strong></td>
                <td>5,280 (42.4%)</td>
                <td>3.2</td>
                <td>10.1 min</td>
                <td>↑ +8%</td>
                <td><span style="background: #3498db; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Stable</span></td>
            </tr>
            <tr>
                <td><strong>Casual Users</strong></td>
                <td>3,150 (25.3%)</td>
                <td>0.8</td>
                <td>4.2 min</td>
                <td>↓ -3%</td>
                <td><span style="background: #f39c12; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">At Risk</span></td>
            </tr>
            <tr>
                <td><strong>Inactive</strong></td>
                <td>1,570 (12.6%)</td>
                <td>0</td>
                <td>-</td>
                <td>↓↓</td>
                <td><span style="background: #e74c3c; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Churned</span></td>
            </tr>
        </tbody>
    </table>
    
    <h3 style="margin-top: 30px;">Channel Attribution</h3>
    
    <canvas id="channelChart" style="max-width: 100%; height: 300px;"></canvas>
    
    <script>
    const channelCtx = document.getElementById('channelChart').getContext('2d');
    new Chart(channelCtx, {
        type: 'pie',
        data: {
            labels: ['Direct', 'Email', 'Organic Search', 'Paid Ads', 'Referral', 'Social'],
            datasets: [{
                data: [28, 24, 22, 15, 7, 4],
                backgroundColor: ['#667eea', '#764ba2', '#2ecc71', '#f39c12', '#3498db', '#e74c3c']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    </script>
</div>

<div class="card" style="background: #f9f9f9; border-left: 4px solid #667eea;">
    <h3>Implementation Tips</h3>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Use cohort analysis to track user retention over time</li>
        <li>Segment customers by engagement level for targeted actions</li>
        <li>Monitor feature adoption to identify popular vs underutilized features</li>
        <li>Analyze user behavior patterns to predict churn</li>
        <li>Track attribution channels to optimize marketing spend</li>
    </ul>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
