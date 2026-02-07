<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Active Users</h6>
                        <h2 class="mt-2"><?= $active_customers ?? 0 ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Online Now</h6>
                        <h2 class="mt-2"><?= $online_now ?? 0 ?></h2>
                    </div>
                    <i class="fas fa-wifi fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Expired Today</h6>
                        <h2 class="mt-2"><?= $expired_today ?? 0 ?></h2>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Revenue Today</h6>
                        <h2 class="mt-2">৳<?= number_format($revenue_today ?? 0, 2) ?></h2>
                    </div>
                    <i class="fas fa-money-bill fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Monthly Sales</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment Methods</h5>
            </div>
            <div class="card-body">
                <canvas id="paymentChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Recent Activities</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_activities)): ?>
                        <?php foreach ($recent_activities as $activity): ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($activity['created_at'])) ?></td>
                                <td><?= $activity['customer_id'] ?? 'System' ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $activity['action'])) ?></td>
                                <td><?= $activity['description'] ?? '-' ?></td>
                                <td><?= $activity['ip_address'] ?? '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No recent activities</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($monthly_sales['months'] ?? []) ?>,
            datasets: [{
                label: 'Sales (৳)',
                data: <?= json_encode($monthly_sales['sales'] ?? []) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($payment_methods['labels'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($payment_methods['data'] ?? []) ?>,
                backgroundColor: <?= json_encode($payment_methods['colors'] ?? []) ?>
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
