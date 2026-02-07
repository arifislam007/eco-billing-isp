<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('admin/routers/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Add Router
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="routersTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Router Name</th>
                        <th>IP Address</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routers as $router): ?>
                        <tr>
                            <td><?= $router['id'] ?></td>
                            <td><?= $router['router_name'] ?></td>
                            <td><?= $router['ip_address'] ?></td>
                            <td>
                                <span class="badge bg-<?= $router['router_type'] == 'mikrotik' ? 'info' : ($router['router_type'] == 'ubiquiti' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($router['router_type']) ?>
                                </span>
                            </td>
                            <td><?= $router['location'] ?? '-' ?></td>
                            <td>
                                <span class="badge-status status-<?= $router['status'] ?>">
                                    <?= ucfirst($router['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('admin/routers/edit/' . $router['id']) ?>" class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="deleteRouter(<?= $router['id'] ?>)" title="Delete">
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
                Are you sure you want to delete this router?
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

function deleteRouter(id) {
    deleteId = id;
    deleteModal.show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (deleteId) {
        window.location.href = '<?= base_url('admin/routers/delete/') ?>' + deleteId;
    }
});

$(document).ready(function() {
    $('#routersTable').DataTable({
        responsive: true,
        dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ routers',
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
