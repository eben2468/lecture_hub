<?php
/**
 * Nadics LectureHub — AI Transcript & Summary View
 */
$__view->layout('layouts.app', [
    'page_title'       => $page_title ?? 'AI Transcript',
    'page_description' => $page_description ?? 'AI Speech-to-Text Transcript',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid py-4">
    <!-- Header banner -->
    <div class="slms-card p-4 mb-4" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color: white; border-radius: 16px;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="badge bg-purple px-3 py-1 mb-2" style="background: #8B5CF6;"><i class="fas fa-robot me-1"></i> WHISPER AI TRANSCRIPT ENGINE</span>
                <h2 style="color: white; font-weight: 800; font-size: 1.75rem;" class="mb-1"><?= e($lecture['title'] ?? 'Lecture') ?></h2>
                <p style="color: #94A3B8; font-size: 0.95rem;" class="mb-0"><?= e($lecture['course_code'] ?? '') ?> — <?= e($lecture['course_title'] ?? '') ?></p>
            </div>
            <a href="<?= url('/lectures/' . ($lecture['id'] ?? 1)) ?>" class="btn-slms btn-outline-slms" style="color: white; border: 1px solid rgba(255,255,255,0.2);">
                <i class="fas fa-arrow-left me-1"></i> Back to Lecture
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Transcript Text & Key Takeaways -->
        <div class="col-lg-8">
            <!-- Key Takeaways -->
            <div class="slms-card p-4 mb-4">
                <h4 style="font-weight: 700; color: var(--primary);" class="mb-3"><i class="fas fa-list-check text-accent me-2"></i> Key Takeaways & Summary</h4>
                <div style="background: var(--bg-surface-alt); border-radius: 12px; padding: 20px; font-size: 0.95rem; line-height: 1.8; color: var(--text-primary); white-space: pre-line;">
                    <?= e($transcript['summary'] ?? '') ?>
                </div>
            </div>

            <!-- Full Transcript -->
            <div class="slms-card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 style="font-weight: 700; color: var(--primary);" class="mb-0"><i class="fas fa-file-alt text-primary me-2"></i> Full Audio Speech-to-Text Transcript</h4>
                    <span class="badge bg-success-subtle text-success px-3 py-1"><i class="fas fa-check-circle me-1"></i> <?= e($transcript['word_count'] ?? 0) ?> Words</span>
                </div>
                <div style="background: var(--bg-surface-alt); border-radius: 12px; padding: 24px; font-size: 1rem; line-height: 1.8; color: var(--text-primary);">
                    <?= e($transcript['full_text'] ?? '') ?>
                </div>
            </div>
        </div>

        <!-- AI Flashcards & Revision Quizzes -->
        <div class="col-lg-4">
            <div class="slms-card p-4">
                <h4 style="font-weight: 700; color: var(--primary);" class="mb-3"><i class="fas fa-layer-group text-warning me-2"></i> AI Revision Flashcards</h4>
                
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($flashcards as $idx => $card): ?>
                        <div class="p-3 rounded border" style="background: var(--bg-surface-alt);">
                            <span class="badge bg-warning text-dark mb-2">Card #<?= $idx + 1 ?></span>
                            <div style="font-weight: 600; color: var(--primary); font-size: 0.95rem;" class="mb-2">
                                <?= e($card['question']) ?>
                            </div>
                            <div style="font-size: 0.88rem; color: var(--text-secondary); background: white; padding: 10px; border-radius: 8px; border-left: 3px solid var(--warning);">
                                <strong>Answer:</strong> <?= e($card['answer']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
