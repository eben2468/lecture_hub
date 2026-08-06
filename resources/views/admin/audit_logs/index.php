<?php
/**
 * Nadics LectureHub — Administrative Audit Logs View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'System Audit Trail',
    'page_description' => 'Monitor user activities, course creations, and audio stream sessions.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid py-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">System Audit Trail</h1>
            <p class="page-subtitle">Track and review user interactions and background service events.</p>
        </div>
    </div>

    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>IP Address</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-muted" style="white-space: nowrap;">
                                    <?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                                <td>
                                    <?php if (!empty($log['email'])): ?>
                                        <strong><?= e($log['first_name'] . ' ' . $log['last_name']) ?></strong><br>
                                        <span class="small text-muted"><?= e($log['email']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">System / Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 text-xs">
                                        <?= e(strtoupper(str_replace('_', ' ', $log['action']))) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($log['entity_type'])): ?>
                                        <span class="small text-muted"><?= e($log['entity_type']) ?> (ID: <?= (int)$log['entity_id'] ?>)</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= e($log['ip_address'] ?? '127.0.0.1') ?>
                                </td>
                                <td class="small">
                                    <?= e($log['description'] ?? 'No description provided.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <div class="mb-2" style="font-size:2.5rem;opacity:0.3;"><i class="fas fa-history"></i></div>
                                <p class="mb-0">No audit activity logs recorded yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
