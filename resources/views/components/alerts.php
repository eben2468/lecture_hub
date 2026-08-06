<?php
/**
 * Nadics LectureHub — Alerts Component
 * Displays server-side validation errors inline.
 */
$errors = $errors ?? [];
?>

<?php if (!empty($errors) && is_array($errors)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:var(--radius-lg);border-left:4px solid var(--danger);margin-bottom:var(--space-5);">
    <div class="d-flex align-items-start gap-2">
        <i class="fas fa-exclamation-circle mt-1" style="color:var(--danger);"></i>
        <div>
            <strong style="font-size:var(--text-sm);">Please fix the following errors:</strong>
            <ul class="mb-0 mt-2" style="font-size:var(--text-sm);padding-left:18px;">
                <?php foreach ($errors as $field => $messages): ?>
                    <?php if (is_array($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><?= e($messages) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
