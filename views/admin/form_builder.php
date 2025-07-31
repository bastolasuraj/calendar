<?php require_once 'views/templates/header.php'; ?>

<!-- Page Header -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-ui-checks me-2"></i>Dynamic Form Builder
                </h1>
                <p class="text-muted mb-0">Create and customize booking forms with drag-and-drop interface</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-1"></i>Import Form
                </button>
                <button type="button" class="btn btn-primary" id="createNewFormBtn">
                    <i class="bi bi-plus-circle me-1"></i>Create New Form
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form Configurations Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>Form Configurations
                </h5>
            </div>
            <div class="card-body">
                <?php if (iterator_count($configurations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Form Name</th>
                                <th>Fields</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($configurations as $config): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($config['name']) ?></strong>
                                        <?php if ($config['active']): ?>
                                            <span class="badge bg-success ms-2">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= count($config['fields']) ?> fields
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($config['active']): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-pause-circle me-1"></i>Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $config['created_at']->toDateTime()->format('M d, Y g:i A') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary"
                                                    onclick="editForm('<?= $config['_id'] ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info"
                                                    onclick="previewForm('<?= $config['_id'] ?>')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="cloneForm('<?= $config['_id'] ?>')">
                                                <i class="bi bi-files"></i>
                                            </button>
                                            <?php if (!$config['active']): ?>
                                                <button type="button" class="btn btn-outline-success"
                                                        onclick="activateForm('<?= $config['_id'] ?>')">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="<?= BASE_PATH ?>/admin/form_builder/export/<?= $config['_id'] ?>">
                                                            <i class="bi bi-download me-2"></i>Export
                                                        </a>
                                                    </li>
                                                    <?php if (!$config['active']): ?>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#"
                                                               onclick="deleteForm('<?= $config['_id'] ?>')">
                                                                <i class="bi bi-trash me-2"></i>Delete
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-ui-checks fs-1 text-muted d-block mb-3"></i>
                        <h5 class="text-muted">No Form Configurations</h5>
                        <p class="text-muted mb-4">Create your first dynamic form to get started.</p>
                        <button type="button" class="btn btn-primary" id="createFirstFormBtn">
                            <i class="bi bi-plus-circle me-1"></i>Create Your First Form
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Form Builder Modal -->
<div class="modal fade" id="formBuilderModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-ui-checks me-2"></i>Form Builder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Builder panels here (left palette, center canvas, right properties) -->
                <!-- ... -->
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>Form Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>Import Form Configuration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_PATH ?>/admin/form_builder/import" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Select Form Configuration File</label>
                        <input type="file" class="form-control" id="importFile" name="import_file" accept=".json" required>
                        <div class="form-text">Select a JSON file exported from the form builder.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import Form</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?= BASE_PATH ?>/views/assets/js/form-builder.js"></script>

<?php require_once 'views/templates/footer.php'; ?>
