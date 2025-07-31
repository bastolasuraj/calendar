<?php require_once 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">
        <i class="fas fa-users me-2"></i>User Management
    </h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="fas fa-user-plus me-1"></i>Add New User
    </button>
</div>

<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>System Users
        </h5>
    </div>
    <div class="card-body">
        <?php if (iterator_count($allUsers) === 0): ?>
            <div class="text-center py-4">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted">No users found</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allUsers as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['name'] ?? 'Unknown'); ?></strong>
                                        <?php if ((string)$user['_id'] === $_SESSION['user_id']): ?>
                                            <span class="badge bg-info ms-2">You</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php
                                echo $user['role'] === 'super_admin' ? 'danger' :
                                    ($user['role'] === 'admin' ? 'warning' : 'info');
                                ?>">
                                    <?php
                                    $roleNames = [
                                        'super_admin' => 'Super Admin',
                                        'admin' => 'Administrator',
                                        'manager' => 'Manager'
                                    ];
                                    echo $roleNames[$user['role']] ?? 'Unknown';
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo ($user['active'] ?? true) ? 'success' : 'secondary'; ?>">
                                    <?php echo ($user['active'] ?? true) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (isset($user['last_login'])): ?>
                                    <small class="text-muted">
                                        <?php echo $user['last_login']->toDateTime()->format('M j, Y g:i A'); ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">Never</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((string)$user['_id'] !== $_SESSION['user_id']): ?>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary"
                                                onclick="editUser('<?php echo (string)$user['_id']; ?>', '<?php echo htmlspecialchars($user['role']); ?>')"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUserModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['active'] ?? true): ?>
                                            <button class="btn btn-outline-warning"
                                                    onclick="deactivateUser('<?php echo (string)$user['_id']; ?>')"
                                                    title="Deactivate User">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <small class="text-muted">Current User</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Create New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/admin/createUser" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="createName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="createName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="createEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="createEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="createPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="createPassword" name="password" required minlength="8">
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                    <div class="mb-3">
                        <label for="createRole" class="form-label">Role</label>
                        <select class="form-select" id="createRole" name="role" required>
                            <option value="">Select Role</option>
                            <?php if ($currentUser['role'] === 'super_admin'): ?>
                                <option value="super_admin">Super Administrator</option>
                            <?php endif; ?>
                            <option value="admin">Administrator</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="fas fa-user-edit me-2"></i>Edit User Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/admin/updateUserRole" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="editUserId" name="user_id">
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role" required>
                            <?php if ($currentUser['role'] === 'super_admin'): ?>
                                <option value="super_admin">Super Administrator</option>
                            <?php endif; ?>
                            <option value="admin">Administrator</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Changing a user's role will affect their access permissions immediately.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editUser(userId, currentRole) {
        document.getElementById('editUserId').value = userId;
        document.getElementById('editRole').value = currentRole;
    }

    function deactivateUser(userId) {
        if (confirm('Are you sure you want to deactivate this user? They will lose access to the system.')) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo BASE_PATH; ?>/admin/deactivateUser'; // Fixed this URL

            const userIdInput = document.createElement('input');
            userIdInput.type = 'hidden';
            userIdInput.name = 'user_id';
            userIdInput.value = userId;

            form.appendChild(userIdInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 3000);
                }
            });
        });
    });
</script>

<style>
    .avatar-md {
        width: 40px;
        height: 40px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75rem;
    }

    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
    }

    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
</style>

<?php require_once 'views/templates/footer.php'; ?>
