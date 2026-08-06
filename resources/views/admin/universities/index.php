<?php
/**
 * Nadics LectureHub — University Management View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'University Management',
    'page_description' => 'Onboard and manage higher education institutions.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">University Directory</h1>
            <p class="page-subtitle">Onboard new universities, configure domain bindings, and govern status.</p>
        </div>
        <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#addUniversityModal">
            <i class="fas fa-plus-circle me-1"></i> Onboard University
        </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="slms-card mb-4 p-3">
        <form method="GET" action="<?= url('/admin/universities') ?>" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group-slms">
                    <i class="fas fa-search input-icon"></i>
                    <input type="text" name="search" class="form-control-slms" placeholder="Search by name or code..." value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-control-slms">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn-slms btn-primary flex-fill"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="<?= url('/admin/universities') ?>" class="btn-slms btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    <!-- Universities Table -->
    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>University Name</th>
                        <th>Domain</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($universities)): ?>
                        <?php foreach ($universities as $univ): ?>
                            <tr>
                                <td><span class="badge-slms badge-primary"><?= e($univ['code']) ?></span></td>
                                <td><strong class="text-primary"><?= e($univ['name']) ?></strong></td>
                                <td><code><?= e($univ['domain'] ?: 'N/A') ?></code></td>
                                <td><?= e(trim(($univ['city'] ?? '') . ', ' . ($univ['state'] ?? ''))) ?></td>
                                <td>
                                    <?php if ($univ['status'] === 'active'): ?>
                                        <span class="badge-slms badge-success">Active</span>
                                    <?php elseif ($univ['status'] === 'suspended'): ?>
                                        <span class="badge-slms badge-danger">Suspended</span>
                                    <?php else: ?>
                                        <span class="badge-slms badge-warning">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn-slms btn-sm btn-outline-slms"><i class="fas fa-edit"></i> Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No universities found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Onboard University Modal -->
<div class="modal fade" id="addUniversityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700">Onboard New University</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('/admin/universities') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label" for="name">University Full Name</label>
                        <input type="text" id="name" name="name" class="form-control-slms" placeholder="e.g. University of Lagos" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="code">Abbreviation Code</label>
                            <input type="text" id="code" name="code" class="form-control-slms" placeholder="e.g. UNILAG" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="domain">Domain Name</label>
                            <input type="text" id="domain" name="domain" class="form-control-slms" placeholder="e.g. unilag.edu.ng">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="city">City / Campus</label>
                            <input type="text" id="city" name="city" class="form-control-slms" placeholder="e.g. Akoka, Lagos">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="status">Initial Status</label>
                            <select id="status" name="status" class="form-control-slms" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">Onboard Institution</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
