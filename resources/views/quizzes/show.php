<?php
/**
 * Nadics LectureHub — Quiz Active Attempt & Results View
 */
$__view->layout('layouts.app', [
    'page_title'       => $quiz['title'] ?? 'Take Quiz',
    'page_description' => 'Online multiple-choice assessment.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <?php 
        $isStaff = in_array($userRole ?? 'student', ['lecturer', 'university_admin', 'super_admin']); 
        $quizStatus = $quiz['status'] ?? 'published';
    ?>
    <div class="slms-card p-4 mb-4" style="background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%); color: white; border-radius: 16px;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-white text-primary px-3 py-1 fw-700"><?= e($quiz['course_code']) ?> — <?= e($quiz['course_title']) ?></span>
                    <?php if ($isStaff): ?>
                        <?php if ($quizStatus === 'published'): ?>
                            <span class="badge bg-success text-white px-3 py-1 fw-700"><i class="fas fa-check-circle me-1"></i> Published</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark px-3 py-1 fw-700"><i class="fas fa-pen me-1"></i> Draft</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <h2 style="color: white; font-weight: 800; font-size: 1.75rem;" class="mb-1"><?= e($quiz['title']) ?></h2>
                <p class="mb-0 opacity-90 small">Passing Score: <?= (int)$quiz['pass_score'] ?>% | Duration: <?= (int)$quiz['duration_minutes'] ?> Minutes</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if ($isStaff): ?>
                    <form method="POST" action="<?= url('/quizzes/' . $quiz['id'] . '/publish') ?>" class="m-0">
                        <?= csrf_field() ?>
                        <?php if ($quizStatus === 'published'): ?>
                            <button type="submit" class="btn btn-outline-light">
                                <i class="fas fa-eye-slash me-1"></i> Unpublish (Save as Draft)
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-success font-weight-bold">
                                <i class="fas fa-paper-plane me-1"></i> Publish to Enrolled Students
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
                <a href="<?= url('/quizzes') ?>" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Back to Quizzes
                </a>
            </div>
        </div>
    </div>

    <?php if ($isStaff && $quizStatus === 'draft'): ?>
        <div class="alert alert-warning d-flex align-items-center justify-content-between mb-4 shadow-sm" style="border-radius: 12px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #92400E;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle fs-4 text-warning me-2"></i>
                <div>
                    <strong>This quiz is currently in Draft Mode.</strong>
                    <div class="small opacity-90">Enrolled students in <strong><?= e($quiz['course_code']) ?></strong> cannot see or attempt this quiz until it is published.</div>
                </div>
            </div>
            <form method="POST" action="<?= url('/quizzes/' . $quiz['id'] . '/publish') ?>" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning btn-sm font-weight-bold text-dark px-3 py-2">
                    <i class="fas fa-paper-plane me-1"></i> Publish Now
                </button>
            </form>
        </div>
    <?php endif; ?>

    <?php if (in_array($userRole, ['lecturer', 'university_admin', 'super_admin'])): ?>
        <div class="row g-4">
            <!-- Left Column: Manage Questions -->
            <div class="col-lg-7">
                <!-- Current Questions List -->
                <div class="slms-card p-4 mb-4">
                    <h4 class="fw-700 mb-4"><i class="fas fa-list text-primary me-2"></i> Current Questions (<?= count($questions) ?>)</h4>
                    
                    <?php if (!empty($questions)): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($questions as $idx => $q): ?>
                                <div class="p-3 rounded border" style="background: var(--bg-surface-alt);">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="fw-600">Q<?= $idx + 1 ?>: <?= e($q['question_text']) ?></div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary-subtle text-secondary"><?= (int)$q['points'] ?> Points</span>
                                            <form method="POST" action="<?= url('/quizzes/' . $quiz['id'] . '/questions/' . $q['id'] . '/delete') ?>" class="m-0" onsubmit="return confirm('Remove this question from the quiz?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Remove Question">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-2 small">
                                        <?php foreach ($q['options_json'] ?? [] as $letter => $txt): ?>
                                            <?php $isCorrect = strtolower($letter) === strtolower($q['correct_answer']); ?>
                                            <div class="col-md-6">
                                                <div class="p-2 rounded border <?= $isCorrect ? 'bg-success-subtle border-success text-success fw-700' : 'bg-light' ?>" style="background: <?= $isCorrect ? 'rgba(16, 185, 129, 0.15)' : 'var(--bg-surface)' ?>;">
                                                    <strong><?= strtoupper($letter) ?>:</strong> <?= e($txt) ?>
                                                    <?php if ($isCorrect): ?><i class="fas fa-check-circle text-success ms-1"></i><?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle mb-2" style="font-size:2rem;opacity:0.3;"></i>
                            <p class="mb-0">No questions added yet. Use the form below to add one!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Add Question Form with Plus Icon -->
                <div class="slms-card p-4">
                    <h4 class="fw-700 mb-4 text-success d-flex align-items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Add Question to Quiz
                    </h4>
                    <form method="POST" action="<?= url('/quizzes/' . $quiz['id'] . '/questions') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group mb-3">
                            <label class="form-label">Question Text <span class="text-danger">*</span></label>
                            <input type="text" name="question_text" class="form-control-slms" placeholder="e.g. What is a binary search tree?" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 form-group">
                                <label class="form-label">Option A <span class="text-danger">*</span></label>
                                <input type="text" name="opt_a" class="form-control-slms" placeholder="Option A text" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Option B <span class="text-danger">*</span></label>
                                <input type="text" name="opt_b" class="form-control-slms" placeholder="Option B text" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Option C <span class="text-danger">*</span></label>
                                <input type="text" name="opt_c" class="form-control-slms" placeholder="Option C text" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Option D <span class="text-danger">*</span></label>
                                <input type="text" name="opt_d" class="form-control-slms" placeholder="Option D text" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 form-group">
                                <label class="form-label text-success">Correct Option Letter <span class="text-danger">*</span></label>
                                <select name="correct" class="form-control-slms" required>
                                    <option value="a">Option A</option>
                                    <option value="b">Option B</option>
                                    <option value="c">Option C</option>
                                    <option value="d">Option D</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Points <span class="text-danger">*</span></label>
                                <input type="number" name="points" class="form-control-slms" value="10" min="1" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-slms btn-primary px-4 py-2 mt-2">
                            <i class="fas fa-plus me-1"></i> Add Question
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Student Attempts (Received Quizzes) -->
            <div class="col-lg-5">
                <div class="slms-card p-4">
                    <h4 class="fw-700 mb-4"><i class="fas fa-clipboard-check text-primary me-2"></i> Received Quizzes (<?= count($attempts) ?>)</h4>
                    
                    <?php if (!empty($attempts)): ?>
                        <div class="table-responsive">
                            <table class="table-slms">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Score</th>
                                        <th>Result</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attempts as $att): ?>
                                        <?php
                                            $pct = $att['total_possible'] > 0 ? ($att['score'] / $att['total_possible']) * 100 : 0;
                                            $passed = $pct >= (int)$quiz['pass_score'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-600"><?= e($att['first_name'] . ' ' . $att['last_name']) ?></div>
                                                <div class="small text-muted"><?= e($att['matric_staff_id']) ?></div>
                                            </td>
                                            <td><strong><?= (float)$att['score'] ?></strong>/<?= (float)$att['total_possible'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= $passed ? 'success' : 'danger' ?>-subtle text-<?= $passed ? 'success' : 'danger' ?>">
                                                    <?= round($pct) ?>% (<?= $passed ? 'Passed' : 'Failed' ?>)
                                                </span>
                                            </td>
                                            <td class="small text-muted"><?= date('M d, H:i', strtotime($att['completed_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <div class="mb-2" style="font-size:2.5rem;opacity:0.3;"><i class="fas fa-user-edit"></i></div>
                            <p class="mb-0">No students have attempted this quiz yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- If already attempted, show results -->
        <?php if ($attempt): ?>
            <?php
                $scorePct = ($attempt['score'] / $attempt['total_possible']) * 100;
                $passed = $scorePct >= (int)$quiz['pass_score'];
                $answers = json_decode($attempt['answers_json'], true) ?? [];
            ?>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="slms-card p-4 text-center">
                        <div style="width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 15px; background: <?= $passed ? '#D1FAE5; color: #10B981;' : '#FEE2E2; color: #EF4444;' ?>">
                            <i class="<?= $passed ? 'fas fa-check-circle' : 'fas fa-times-circle' ?>"></i>
                        </div>
                        <h3><?= $passed ? 'Quiz Passed!' : 'Quiz Failed' ?></h3>
                        <p class="text-muted">You completed this quiz on <?= date('M d, Y', strtotime($attempt['completed_at'])) ?>.</p>
                        
                        <div class="my-4">
                            <div class="display-4 fw-800" style="color: <?= $passed ? 'var(--success)' : 'var(--danger)' ?>"><?= round($scorePct) ?>%</div>
                            <span class="text-muted"><?= (float)$attempt['score'] ?> / <?= (float)$attempt['total_possible'] ?> Points</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="slms-card p-4">
                        <h4 class="fw-700 mb-4"><i class="fas fa-list text-primary me-2"></i> Question Breakdown</h4>
                        
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($questions as $idx => $q): ?>
                                <?php 
                                    $studentAnsKey = strtolower(trim($answers[$q['id']] ?? ''));
                                    $correctAnsKey = strtolower(trim($q['correct_answer']));
                                    $optionsMap = is_array($q['options_json']) ? $q['options_json'] : (json_decode($q['options_json'] ?? '[]', true) ?? []);
                                    
                                    $studentAnsText = $optionsMap[$studentAnsKey] ?? '';
                                    $correctAnsText = $optionsMap[$correctAnsKey] ?? '';
                                    $isCorrect = ($studentAnsKey === $correctAnsKey);
                                ?>
                                <div class="p-3 rounded border" style="background: var(--bg-surface-alt);">
                                    <div class="fw-600 mb-2">Q<?= $idx + 1 ?>: <?= e($q['question_text']) ?></div>
                                    <div class="small d-flex flex-column gap-1">
                                        <div>
                                            <span class="text-muted">Your Answer:</span> 
                                            <strong class="<?= $isCorrect ? 'text-success' : 'text-danger' ?>">
                                                <?= $studentAnsKey ? 'Option ' . strtoupper($studentAnsKey) . ($studentAnsText ? ' (' . e($studentAnsText) . ')' : '') : '(No answer)' ?>
                                            </strong>
                                            <?php if ($isCorrect): ?>
                                                <i class="fas fa-check-circle text-success ms-1"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-danger ms-1"></i>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!$isCorrect): ?>
                                            <div>
                                                <span class="text-muted">Correct Answer:</span> 
                                                <strong class="text-success">Option <?= strtoupper($correctAnsKey) ?><?= $correctAnsText ? ' (' . e($correctAnsText) . ')' : '' ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Active quiz taking form -->
        <?php else: ?>
            <form method="POST" action="<?= url('/quizzes/' . $quiz['id'] . '/submit') ?>">
                <?= csrf_field() ?>
                <div class="row g-4">
                    <div class="col-lg-9">
                        <div class="d-flex flex-column gap-4">
                            <?php foreach ($questions as $idx => $q): ?>
                                <div class="slms-card p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                        <h5 class="fw-700 m-0">Question <?= $idx + 1 ?></h5>
                                        <span class="badge bg-secondary-subtle text-secondary"><?= (int)$q['points'] ?> Points</span>
                                    </div>
                                    <p class="mb-4 fw-600" style="font-size: 1.1rem;"><?= e($q['question_text']) ?></p>

                                    <?php if ($q['question_type'] === 'multiple_choice'): ?>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($q['options_json'] ?? [] as $optVal => $optText): ?>
                                                <label class="d-flex align-items-center gap-3 p-3 rounded border cursor-pointer hover-bg" style="background: var(--bg-surface-alt);">
                                                    <input type="radio" name="question_<?= $q['id'] ?>" value="<?= e($optVal) ?>" required>
                                                    <span><?= e($optText) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($q['question_type'] === 'true_false'): ?>
                                        <div class="d-flex gap-3">
                                            <label class="flex-fill d-flex align-items-center gap-3 p-3 rounded border cursor-pointer hover-bg" style="background: var(--bg-surface-alt);">
                                                <input type="radio" name="question_<?= $q['id'] ?>" value="true" required>
                                                <span>True</span>
                                            </label>
                                            <label class="flex-fill d-flex align-items-center gap-3 p-3 rounded border cursor-pointer hover-bg" style="background: var(--bg-surface-alt);">
                                                <input type="radio" name="question_<?= $q['id'] ?>" value="false" required>
                                                <span>False</span>
                                            </label>
                                        </div>
                                    <?php else: ?>
                                        <input type="text" name="question_<?= $q['id'] ?>" class="form-control-slms" placeholder="Type your answer here..." required>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="slms-card p-4 position-sticky" style="top: 24px;">
                            <h4 class="fw-700 mb-3"><i class="fas fa-clock text-primary me-2"></i> Quiz Timer</h4>
                            <div class="display-6 text-center fw-800 my-3 text-primary" id="timerDisplay">15:00</div>
                            <p class="small text-muted text-center">Remaining time. Do not refresh or exit the page during the active attempt.</p>
                            
                            <button type="submit" class="btn-slms btn-primary w-100 py-3 mt-4" style="background: #1E3A8A; border-color: #1E3A8A;">
                                <i class="fas fa-check-double me-1"></i> Submit Assessment
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <script>
                // Simple Timer
                let durationSeconds = <?= (int)$quiz['duration_minutes'] * 60 ?>;
                const display = document.getElementById('timerDisplay');
                
                const timer = setInterval(() => {
                    let minutes = Math.floor(durationSeconds / 60);
                    let seconds = durationSeconds % 60;
                    
                    minutes = minutes < 10 ? '0' + minutes : minutes;
                    seconds = seconds < 10 ? '0' + seconds : seconds;
                    
                    display.textContent = minutes + ':' + seconds;
                    
                    if (--durationSeconds < 0) {
                        clearInterval(timer);
                        alert('Time is up! Submitting your answers automatically.');
                        document.querySelector('form').submit();
                    }
                }, 1000);
            </script>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php $__view->endSection(); ?>
