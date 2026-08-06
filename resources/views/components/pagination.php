<?php
/**
 * Nadics LectureHub — Pagination Component
 *
 * @param array $pagination Pagination data from QueryBuilder::paginate()
 * @param string $baseUrl   Base URL for page links
 */
$pagination = $pagination ?? [];
$baseUrl    = $baseUrl ?? '?';

if (empty($pagination) || ($pagination['last_page'] ?? 1) <= 1) {
    return;
}

$currentPage = $pagination['current_page'] ?? 1;
$lastPage    = $pagination['last_page'] ?? 1;
$total       = $pagination['total'] ?? 0;
$from        = $pagination['from'] ?? 0;
$to          = $pagination['to'] ?? 0;

// Build page range (show max 7 page links)
$start = max(1, $currentPage - 3);
$end   = min($lastPage, $currentPage + 3);
$separator = str_contains($baseUrl, '?') ? '&' : '?';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4">
    <div style="font-size:var(--text-sm);color:var(--text-muted);">
        Showing <strong><?= $from ?></strong> to <strong><?= $to ?></strong> of <strong><?= number_format($total) ?></strong> results
    </div>

    <nav aria-label="Pagination">
        <ul class="pagination mb-0" style="gap:4px;">
            <!-- Previous -->
            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $baseUrl . $separator ?>page=<?= $currentPage - 1 ?>" style="border-radius:var(--radius-md);border-color:var(--border-color);font-size:var(--text-sm);">
                    <i class="fas fa-chevron-left" style="font-size:10px;"></i>
                </a>
            </li>

            <!-- First page -->
            <?php if ($start > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl . $separator ?>page=1" style="border-radius:var(--radius-md);border-color:var(--border-color);font-size:var(--text-sm);">1</a>
                </li>
                <?php if ($start > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link" style="border:none;background:none;font-size:var(--text-sm);">...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Page numbers -->
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl . $separator ?>page=<?= $i ?>"
                       style="border-radius:var(--radius-md);font-size:var(--text-sm);<?= $i === $currentPage ? 'background:var(--secondary);border-color:var(--secondary);' : 'border-color:var(--border-color);' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Last page -->
            <?php if ($end < $lastPage): ?>
                <?php if ($end < $lastPage - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link" style="border:none;background:none;font-size:var(--text-sm);">...</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl . $separator ?>page=<?= $lastPage ?>" style="border-radius:var(--radius-md);border-color:var(--border-color);font-size:var(--text-sm);"><?= $lastPage ?></a>
                </li>
            <?php endif; ?>

            <!-- Next -->
            <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $baseUrl . $separator ?>page=<?= $currentPage + 1 ?>" style="border-radius:var(--radius-md);border-color:var(--border-color);font-size:var(--text-sm);">
                    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>
