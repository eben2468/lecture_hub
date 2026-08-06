<?php
/**
 * Nadics LectureHub — Course Materials Index View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Course Materials',
    'page_description' => 'Upload, browse, and download academic resources.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Course Materials Library</h1>
            <p class="page-subtitle">Access lecture notes, slides, past questions, and supplementary resources.</p>
        </div>
        <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
            <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#uploadMaterialModal">
                <i class="fas fa-cloud-upload-alt me-1"></i> Upload Material
            </button>
        <?php endif; ?>
    </div>

    <!-- Materials Table -->
    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Course</th>
                        <th>Uploaded By</th>
                        <th>Size</th>
                        <th>Downloads</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($materials)): ?>
                        <?php foreach ($materials as $mat): ?>
                            <?php
                                $ext = strtolower(pathinfo($mat['file_path'], PATHINFO_EXTENSION));
                                $iconClass = match($ext) {
                                    'pdf'              => 'fas fa-file-pdf text-danger',
                                    'doc', 'docx'      => 'fas fa-file-word text-primary',
                                    'ppt', 'pptx'      => 'fas fa-file-powerpoint text-warning',
                                    'xls', 'xlsx'      => 'fas fa-file-excel text-success',
                                    'jpg', 'jpeg', 'png', 'webp' => 'fas fa-file-image text-info',
                                    'zip'              => 'fas fa-file-archive text-secondary',
                                    default            => 'fas fa-file text-muted',
                                };
                                $sizeFormatted = $mat['file_size'] >= 1048576
                                    ? number_format($mat['file_size'] / 1048576, 1) . ' MB'
                                    : number_format($mat['file_size'] / 1024) . ' KB';
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="<?= $iconClass ?>" style="font-size:1.4rem;"></i>
                                        <div>
                                            <strong class="text-primary"><?= e($mat['title']) ?></strong>
                                            <?php if ($mat['description']): ?>
                                                <div class="text-muted small" style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                    <?= e($mat['description']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge-slms badge-primary"><?= e($mat['course_code']) ?></span></td>
                                <td class="small"><?= e($mat['uploader_first_name'] . ' ' . $mat['uploader_last_name']) ?></td>
                                <td class="small"><?= $sizeFormatted ?></td>
                                <td>
                                    <span class="badge-slms badge-info"><?= (int)$mat['download_count'] ?></span>
                                </td>
                                <td class="small text-muted"><?= date('M d, Y', strtotime($mat['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= url('/materials/' . $mat['id'] . '/preview') ?>" target="_blank" class="btn-slms btn-sm btn-outline-primary" title="Preview Document">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= url('/materials/' . $mat['id'] . '/download') ?>" class="btn-slms btn-sm btn-primary" title="Download File">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
                                            <form method="POST" action="<?= url('/materials/' . $mat['id'] . '/delete') ?>" onsubmit="return confirm('Delete this material?');" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn-slms btn-sm btn-danger-slms" title="Delete Material">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div class="mb-2" style="font-size:2.5rem;opacity:0.3;"><i class="fas fa-folder-open"></i></div>
                                <p class="mb-0">No course materials available yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Material Modal -->
<?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
<div class="modal fade" id="uploadMaterialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700"><i class="fas fa-cloud-upload-alt me-2"></i> Upload Course Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('/materials') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-control-slms" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['code'] . ' — ' . $c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Material Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control-slms" placeholder="e.g. Week 5 — Sorting Algorithms" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control-slms" rows="2" placeholder="Brief description of this resource..."></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Link to Lecture (Optional)</label>
                        <select name="lecture_id" class="form-control-slms">
                            <option value="">No specific lecture</option>
                            <?php foreach ($lectures ?? [] as $l): ?>
                                <option value="<?= $l['id'] ?>"><?= e($l['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" name="material_file" class="form-control-slms" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.zip,.txt" required>
                        <div class="form-text small">Accepted: PDF, Word, PowerPoint, Excel, Images, ZIP. Max: 100MB.</div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">
                        <i class="fas fa-upload me-1"></i> Upload Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__view->endSection(); ?>
