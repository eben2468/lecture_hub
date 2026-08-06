<?php
/**
 * Nadics LectureHub — Notifications View
 * Full-featured notification center with read/unread, type-based icons,
 * mark-as-read, and empty state.
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Notifications',
    'page_description' => 'View all your academic and system notifications.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-bell text-warning me-2"></i> Notifications</h1>
            <p class="page-subtitle">Stay up to date with your academic activities and system alerts.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($notifications)): ?>
                <button class="btn-slms btn-ghost" onclick="markAllRead()" id="btnMarkAllRead">
                    <i class="fas fa-check-double me-1"></i> Mark All Read
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 mb-4 flex-wrap" id="notifFilterTabs">
        <button class="btn-slms btn-sm btn-primary notif-filter-btn active" data-filter="all">
            <i class="fas fa-inbox me-1"></i> All
            <span class="ms-1 badge bg-light text-dark" style="font-size:10px;"><?= count($notifications ?? []) ?></span>
        </button>
        <button class="btn-slms btn-sm btn-ghost notif-filter-btn" data-filter="unread">
            <i class="fas fa-circle me-1" style="font-size:8px; color:#3b82f6;"></i> Unread
        </button>
        <button class="btn-slms btn-sm btn-ghost notif-filter-btn" data-filter="lecture">
            <i class="fas fa-broadcast-tower me-1"></i> Lectures
        </button>
        <button class="btn-slms btn-sm btn-ghost notif-filter-btn" data-filter="assignment">
            <i class="fas fa-tasks me-1"></i> Assignments
        </button>
        <button class="btn-slms btn-sm btn-ghost notif-filter-btn" data-filter="system">
            <i class="fas fa-cog me-1"></i> System
        </button>
    </div>

    <!-- Notification List -->
    <div class="slms-card" id="notifListCard">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $i => $notif):
                $type = $notif['type'] ?? 'info';
                $isRead = !empty($notif['is_read']);
                $icon = $notif['icon'] ?? 'fas fa-bell';
                $link = $notif['link'] ?? null;
                
                // Map type to icon colors
                $iconStyles = [
                    'info'       => ['bg' => 'rgba(99,102,241,0.12)', 'color' => '#6366f1'],
                    'lecture'    => ['bg' => 'rgba(239,68,68,0.12)',   'color' => '#ef4444'],
                    'assignment' => ['bg' => 'rgba(245,158,11,0.12)', 'color' => '#f59e0b'],
                    'material'   => ['bg' => 'rgba(16,185,129,0.12)', 'color' => '#10b981'],
                    'system'     => ['bg' => 'rgba(100,116,139,0.12)','color' => '#64748b'],
                    'success'    => ['bg' => 'rgba(16,185,129,0.12)', 'color' => '#10b981'],
                    'warning'    => ['bg' => 'rgba(245,158,11,0.12)', 'color' => '#f59e0b'],
                    'danger'     => ['bg' => 'rgba(239,68,68,0.12)',  'color' => '#ef4444'],
                ];
                $style = $iconStyles[$type] ?? $iconStyles['info'];
                
                // Time formatting
                $timeAgo = '';
                if (!empty($notif['created_at'])) {
                    $diff = time() - strtotime($notif['created_at']);
                    if ($diff < 60) $timeAgo = 'Just now';
                    elseif ($diff < 3600) $timeAgo = floor($diff / 60) . ' min ago';
                    elseif ($diff < 86400) $timeAgo = floor($diff / 3600) . ' hr ago';
                    elseif ($diff < 604800) $timeAgo = floor($diff / 86400) . ' days ago';
                    else $timeAgo = date('M d, Y', strtotime($notif['created_at']));
                }
            ?>
                <div class="notif-row d-flex align-items-start gap-3 p-4 <?= !$isRead ? 'notif-unread' : '' ?>"
                     data-notif-id="<?= $notif['id'] ?>"
                     data-notif-type="<?= e($type) ?>"
                     style="border-bottom:1px solid var(--border); cursor:pointer; transition: all 0.2s;"
                     onclick="handleNotifClick(this, <?= $notif['id'] ?>, '<?= e($link ?? '') ?>')">
                    <!-- Icon -->
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-circle"
                         style="width:48px; height:48px; background:<?= $style['bg'] ?>; color:<?= $style['color'] ?>; font-size:1.1rem;">
                        <i class="<?= e($icon) ?>"></i>
                    </div>
                    <!-- Content -->
                    <div class="flex-fill min-width-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-600" style="font-size:0.95rem;"><?= e($notif['title'] ?? 'Notification') ?></span>
                            <?php if (!$isRead): ?>
                                <span class="notif-unread-dot" style="width:8px; height:8px; background:#3b82f6; border-radius:50%; display:inline-block; flex-shrink:0;"></span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted" style="font-size:0.85rem; line-height:1.5;"><?= e($notif['message'] ?? '') ?></div>
                        <div class="d-flex align-items-center gap-3 mt-2">
                            <span class="text-muted" style="font-size:0.75rem;">
                                <i class="fas fa-clock me-1"></i><?= $timeAgo ?>
                            </span>
                            <span class="badge" style="font-size:0.65rem; padding:2px 8px; border-radius:10px; background:<?= $style['bg'] ?>; color:<?= $style['color'] ?>;">
                                <?= ucfirst($type) ?>
                            </span>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
                        <?php if (!$isRead): ?>
                            <button class="btn btn-sm border-0 text-muted" onclick="event.stopPropagation(); markOneRead(this, <?= $notif['id'] ?>)" title="Mark as read" style="background:transparent;">
                                <i class="fas fa-check"></i>
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-sm border-0 text-muted" onclick="event.stopPropagation(); deleteNotif(this, <?= $notif['id'] ?>)" title="Delete" style="background:transparent;">
                            <i class="fas fa-trash-alt" style="font-size:0.75rem;"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
                <div class="mb-3" style="font-size:4rem; opacity:0.15;">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h5 class="fw-700 mb-2">No notifications yet</h5>
                <p class="text-muted mb-0" style="max-width:400px; margin:0 auto;">
                    You're all caught up! Notifications about live lectures, assignments, and system updates will appear here.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .notif-row:hover {
        background: rgba(99, 102, 241, 0.03) !important;
    }
    .notif-unread {
        background: rgba(59, 130, 246, 0.04);
        border-left: 3px solid #3b82f6 !important;
    }
    .notif-filter-btn {
        transition: all 0.2s;
    }
    .notif-filter-btn.active {
        box-shadow: 0 2px 8px rgba(99,102,241,0.25);
    }
    .notif-row.fade-out {
        opacity: 0;
        transform: translateX(30px);
        transition: all 0.3s ease;
    }
</style>

<script>
// Filter tabs
document.querySelectorAll('.notif-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.notif-filter-btn').forEach(b => {
            b.classList.remove('active');
            b.classList.add('btn-ghost');
            b.classList.remove('btn-primary');
        });
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-ghost');

        const filter = this.dataset.filter;
        document.querySelectorAll('.notif-row').forEach(row => {
            if (filter === 'all') {
                row.style.display = 'flex';
            } else if (filter === 'unread') {
                row.style.display = row.classList.contains('notif-unread') ? 'flex' : 'none';
            } else {
                row.style.display = row.dataset.notifType === filter ? 'flex' : 'none';
            }
        });
    });
});

// Click notification to navigate
function handleNotifClick(el, id, link) {
    // Mark as read visually
    el.classList.remove('notif-unread');
    const dot = el.querySelector('.notif-unread-dot');
    if (dot) dot.remove();

    // Mark as read on server
    fetch(window.SLMS_APP_URL + '/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).catch(() => {});

    // Navigate if link exists
    if (link) {
        window.location.href = link;
    }
}

// Mark single notification as read
function markOneRead(btn, id) {
    const row = btn.closest('.notif-row');
    row.classList.remove('notif-unread');
    const dot = row.querySelector('.notif-unread-dot');
    if (dot) dot.remove();
    btn.remove();

    fetch(window.SLMS_APP_URL + '/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).catch(() => {});

    if (typeof SLMS !== 'undefined') SLMS.toast('Notification marked as read', 'success');
}

// Delete notification
function deleteNotif(btn, id) {
    const row = btn.closest('.notif-row');
    row.classList.add('fade-out');
    setTimeout(() => row.remove(), 300);

    fetch(window.SLMS_APP_URL + '/notifications/' + id + '/delete', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).catch(() => {});

    if (typeof SLMS !== 'undefined') SLMS.toast('Notification removed', 'info');
}

// Mark all as read
function markAllRead() {
    document.querySelectorAll('.notif-row').forEach(row => {
        row.classList.remove('notif-unread');
        const dot = row.querySelector('.notif-unread-dot');
        if (dot) dot.remove();
        const checkBtn = row.querySelector('.fa-check')?.closest('button');
        if (checkBtn) checkBtn.remove();
    });

    fetch(window.SLMS_APP_URL + '/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).catch(() => {});

    if (typeof SLMS !== 'undefined') SLMS.toast('All notifications marked as read', 'success');
}
</script>
<?php $__view->endSection(); ?>
