<?php
/**
 * Nadics LectureHub — Quizzes Listing View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Online Quizzes',
    'page_description' => 'Assess your course knowledge with automated grading.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid py-4">
    <div class="page-header">
        <div>
            <h1 class="page-title">Online Quizzes</h1>
            <p class="page-subtitle"><?= ($userRole ?? 'student') === 'student' ? 'Showing published quizzes for your enrolled courses.' : 'Create, publish, and manage quizzes for your courses.' ?></p>
        </div>
        <?php if (in_array($userRole ?? 'student', ['lecturer', 'university_admin', 'super_admin'])): ?>
            <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                <i class="fas fa-plus me-1"></i> Create Quiz
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($selectedCourse)): ?>
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 border-0 shadow-sm" style="border-radius: var(--radius-lg); background: rgba(37, 99, 235, 0.1); color: var(--secondary-dark);">
            <div>
                <i class="fas fa-filter me-2"></i>
                Showing quizzes for course: <strong><?= e($selectedCourse['code']) ?> — <?= e($selectedCourse['title']) ?></strong>
            </div>
            <a href="<?= url('/quizzes') ?>" class="btn btn-sm btn-outline-primary" style="border-color: rgba(37, 99, 235, 0.3);">Clear Filter</a>
        </div>
    <?php endif; ?>

    <div class="slms-card">
        <div class="table-responsive">
            <table class="table-slms">
                <thead>
                    <tr>
                        <th>Quiz Title</th>
                        <th>Course</th>
                        <th>Duration</th>
                        <th>Questions</th>
                        <th>Passing Score</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($quizzes)): ?>
                        <?php foreach ($quizzes as $quiz): ?>
                            <?php 
                                $attempt = $attemptsMap[$quiz['id']] ?? null;
                                $isStaff = in_array($userRole ?? 'student', ['lecturer', 'university_admin', 'super_admin']);
                                
                                if ($isStaff) {
                                    $qStatus = $quiz['status'] ?? 'published';
                                    if ($qStatus === 'published') {
                                        $statusBadge = '<span class="badge bg-success-subtle text-success"><i class="fas fa-check-circle me-1"></i> Published</span>';
                                    } elseif ($qStatus === 'draft') {
                                        $statusBadge = '<span class="badge bg-warning-subtle text-warning" style="color:#d97706!important;background:rgba(245,158,11,0.15)!important;"><i class="fas fa-pen me-1"></i> Draft</span>';
                                    } else {
                                        $statusBadge = '<span class="badge bg-secondary-subtle text-secondary">Closed</span>';
                                    }
                                } else {
                                    if ($attempt) {
                                        $scorePct = ($attempt['score'] / $attempt['total_possible']) * 100;
                                        $statusBadge = $scorePct >= (int)$quiz['pass_score']
                                            ? '<span class="badge bg-success-subtle text-success">Passed (' . round($scorePct) . '%)</span>'
                                            : '<span class="badge bg-danger-subtle text-danger">Failed (' . round($scorePct) . '%)</span>';
                                    } else {
                                        $statusBadge = '<span class="badge bg-primary-subtle text-primary">Not Attempted</span>';
                                    }
                                }
                            ?>
                            <tr>
                                <td><strong><?= e($quiz['title']) ?></strong></td>
                                <td><span class="badge-slms badge-primary"><?= e($quiz['course_code']) ?></span></td>
                                <td><?= (int)$quiz['duration_minutes'] ?> mins</td>
                                <td><?= (int)$quiz['total_questions'] ?> questions</td>
                                <td><?= (int)$quiz['pass_score'] ?>%</td>
                                <td><?= $statusBadge ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($isStaff): ?>
                                            <a href="<?= url('/quizzes/' . $quiz['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-sliders-h me-1"></i> Manage
                                            </a>
                                            <form method="POST" action="<?= url('/quizzes/' . $quiz['id'] . '/publish') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <?php if (($quiz['status'] ?? 'published') === 'published'): ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Save as Draft">
                                                        <i class="fas fa-eye-slash me-1"></i> Unpublish
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-sm btn-success" title="Publish to Enrolled Students">
                                                        <i class="fas fa-paper-plane me-1"></i> Publish
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                            <form method="POST" action="<?= url('/quizzes/' . $quiz['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this quiz? All student attempts and questions will be permanently removed.');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Quiz">
                                                    <i class="fas fa-trash me-1"></i> Delete
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <?php if ($attempt): ?>
                                                <a href="<?= url('/quizzes/' . $quiz['id']) ?>" class="btn-slms btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i> View Attempt
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= url('/quizzes/' . $quiz['id']) ?>" class="btn-slms btn-sm btn-primary">
                                                    <i class="fas fa-play me-1"></i> Start Quiz
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div class="mb-2" style="font-size:2.5rem;opacity:0.3;"><i class="fas fa-question-circle"></i></div>
                                <p class="mb-0">No quizzes available.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Quiz Modal -->
<?php if (in_array($userRole ?? 'student', ['lecturer', 'university_admin', 'super_admin'])): ?>
<div class="modal fade" id="createQuizModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700"><i class="fas fa-plus me-2 text-primary"></i> Create Online Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('/quizzes') ?>">
                <?= csrf_field() ?>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-control-slms" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses ?? [] as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['code'] . ' — ' . $c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quiz Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control-slms" placeholder="e.g. Midterm revision quiz" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_minutes" class="form-control-slms" value="15" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Passing Score (%) <span class="text-danger">*</span></label>
                            <input type="number" name="pass_score" class="form-control-slms" value="60" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Publishing Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control-slms">
                                <option value="published">Publish Immediately</option>
                                <option value="draft">Save as Draft</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-700 m-0 text-primary">Quiz Questions (Multiple Choice)</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addQuestionBtn">
                            <i class="fas fa-plus-circle me-1"></i> Add Question
                        </button>
                    </div>

                    <div id="questionsContainer">
                        <div class="p-3 mb-4 rounded border question-block" data-idx="1" style="background: var(--bg-surface-alt);">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-700 text-secondary m-0">Question 1</h6>
                                <button type="button" class="btn-close remove-question-btn" style="display: none;"></button>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Question Text</label>
                                <input type="text" name="q1_text" class="form-control-slms" placeholder="Enter question description..." required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <input type="text" name="q1_opt_a" class="form-control-slms" placeholder="Option A" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="q1_opt_b" class="form-control-slms" placeholder="Option B" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="q1_opt_c" class="form-control-slms" placeholder="Option C" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="q1_opt_d" class="form-control-slms" placeholder="Option D" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-success">Correct Option Letter</label>
                                <select name="q1_correct" class="form-control-slms">
                                    <option value="a">Option A</option>
                                    <option value="b">Option B</option>
                                    <option value="c">Option C</option>
                                    <option value="d">Option D</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">
                        <i class="fas fa-check me-1"></i> Publish Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('questionsContainer');
    const addBtn = document.getElementById('addQuestionBtn');
    if (!addBtn || !container) return;

    let questionCount = 1;

    addBtn.addEventListener('click', () => {
        questionCount++;
        const block = document.createElement('div');
        block.className = 'p-3 mb-4 rounded border question-block';
        block.dataset.idx = questionCount;
        block.style.background = 'var(--bg-surface-alt)';
        block.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-700 text-secondary m-0">Question ${questionCount}</h6>
                <button type="button" class="btn-close remove-question-btn"></button>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Question Text</label>
                <input type="text" name="q${questionCount}_text" class="form-control-slms" placeholder="Enter question description..." required>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q${questionCount}_opt_a" class="form-control-slms" placeholder="Option A" required>
                </div>
                <div class="col-md-6">
                    <input type="text" name="q${questionCount}_opt_b" class="form-control-slms" placeholder="Option B" required>
                </div>
                <div class="col-md-6">
                    <input type="text" name="q${questionCount}_opt_c" class="form-control-slms" placeholder="Option C" required>
                </div>
                <div class="col-md-6">
                    <input type="text" name="q${questionCount}_opt_d" class="form-control-slms" placeholder="Option D" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label text-success">Correct Option Letter</label>
                <select name="q${questionCount}_correct" class="form-control-slms">
                    <option value="a">Option A</option>
                    <option value="b">Option B</option>
                    <option value="c">Option C</option>
                    <option value="d">Option D</option>
                </select>
            </div>
        `;
        container.appendChild(block);
        
        // Remove button handler
        block.querySelector('.remove-question-btn').addEventListener('click', () => {
            block.remove();
            reindexQuestions();
        });
    });

    function reindexQuestions() {
        const blocks = container.querySelectorAll('.question-block');
        questionCount = 0;
        blocks.forEach((b, i) => {
            questionCount = i + 1;
            b.dataset.idx = questionCount;
            b.querySelector('h6').textContent = `Question ${questionCount}`;
            b.querySelector('[name$="_text"]').name = `q${questionCount}_text`;
            b.querySelector('[name$="_opt_a"]').name = `q${questionCount}_opt_a`;
            b.querySelector('[name$="_opt_b"]').name = `q${questionCount}_opt_b`;
            b.querySelector('[name$="_opt_c"]').name = `q${questionCount}_opt_c`;
            b.querySelector('[name$="_opt_d"]').name = `q${questionCount}_opt_d`;
            b.querySelector('select').name = `q${questionCount}_correct`;
            
            // Show close button for all except the first one
            const closeBtn = b.querySelector('.remove-question-btn');
            if (closeBtn) {
                closeBtn.style.display = i === 0 ? 'none' : 'block';
            }
        });
    }
});
</script>
<?php endif; ?>
<?php $__view->endSection(); ?>
