<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('admin/packages/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Add Package
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="packagesTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Package Name</th>
                        <th>Type</th>
                        <th>Speed</th>
                        <th>Price</th>
                        <th>RADIUS Group</th>
                        <th>Valid Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $package): ?>
                        <tr>
                            <td><?= $package['id'] ?></td>
                            <td><?= $package['package_name'] ?></td>
                            <td>
                                <span class="badge bg-<?= $package['package_type'] == 'prepaid' ? 'primary' : ($package['package_type'] == 'postpaid' ? 'info' : ($package['package_type'] == 'hotspot' ? 'warning' : 'success')) ?>">
                                    <?= ucfirst($package['package_type']) ?>
                                </span>
                            </td>
                            <td><?= $package['download_speed'] ?>M / <?= $package['upload_speed'] ?>M</td>
                            <td>৳<?= number_format($package['price'], 2) ?></td>
                            <td><?= $package['radius_group'] ?? '-' ?></td>
                            <td><?= $package['valid_days'] ?> days</td>
                            <td>
                                <span class="badge-status status-<?= $package['status'] ?>">
                                    <?= ucfirst($package['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('admin/packages/edit/' . $package['id']) ?>" class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="deletePackage(<?= $package['id'] ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this package?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

function deletePackage(id) {
    deleteId = id;
    deleteModal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (deleteId) {
        window.location.href = '<?= base_url('admin/packages/delete/') ?>' + deleteId;
    }
});

$(document).ready(function() {
    $('#packagesTable').DataTable({
        responsive: true,
        dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ packages',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
