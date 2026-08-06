<?php
/**
 * Nadics LectureHub — Student Course Enrollments View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Enrollments — ' . ($course['code'] ?? ''),
    'page_description' => 'Manage student course enrollments.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <a href="<?= url('/courses') ?>" class="text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Courses</a>
            <h1 class="page-title mt-2">Course Enrollments</h1>
            <p class="page-subtitle"><?= e($course['code']) ?> — <?= e($course['title']) ?></p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Enrolled Students List -->
        <div class="col-lg-8">
            <div class="slms-card p-4">
                <h5 class="fw-700 mb-3"><i class="fas fa-user-graduates text-primary me-2"></i> Enrolled Students</h5>

                <?php if (!empty($enrollments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Enrolled At</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $enroll): ?>
                                    <tr>
                                        <td><strong><?= e($enroll['first_name'] . ' ' . $enroll['last_name']) ?></strong></td>
                                        <td><?= e($enroll['email']) ?></td>
                                        <td><?= date('M d, Y', strtotime($enroll['enrolled_at'])) ?></td>
                                        <td>
                                            <span class="badge-slms badge-success">
                                                <?= ucfirst($enroll['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="<?= url('/enrollments/' . $enroll['enrollment_id'] . '/drop') ?>" onsubmit="return confirm('Are you sure you want to remove this student from the course?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn-slms btn-sm btn-outline-slms btn-danger-hover">
                                                    <i class="fas fa-user-minus me-1"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <div class="mb-3" style="font-size:3rem;opacity:0.3;"><i class="fas fa-user-slash"></i></div>
                        <h5 class="fw-600">No students enrolled yet</h5>
                        <p class="small mb-0">Use the enrollment panel on the right to add students to this course.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Enroll New Student Form -->
        <div class="col-lg-4">
            <div class="slms-card p-4">
                <h5 class="fw-700 mb-3"><i class="fas fa-plus-circle text-accent me-2"></i> Enroll Student</h5>

                <?php if (!empty($availableStudents)): ?>
                    <form method="POST" action="<?= url('/courses/' . $course['id'] . '/enroll') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="student_id" class="form-label small fw-600">Select Student</label>
                            <select name="student_id" id="student_id" class="form-select" required>
                                <option value="" disabled selected>Choose a student...</option>
                                <?php foreach ($availableStudents as $student): ?>
                                    <option value="<?= $student['id'] ?>">
                                        <?= e($student['first_name'] . ' ' . $student['last_name']) ?> (<?= e($student['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-slms btn-primary w-100 py-2">
                            <i class="fas fa-user-plus me-1"></i> Enroll in Course
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4 text-muted bg-light rounded-3 p-3">
                        <i class="fas fa-check-circle text-success mb-2" style="font-size:2rem;"></i>
                        <p class="small mb-0">All registered students in your institution are currently enrolled in this course.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bulk Enroll via CSV Card -->
            <div class="slms-card p-4 mt-4">
                <h5 class="fw-700 mb-3"><i class="fas fa-file-upload text-accent me-2"></i> Bulk Enroll (CSV)</h5>
                
                <form method="POST" action="<?= url('/courses/' . $course['id'] . '/enroll/bulk') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="csv_file" class="form-label small fw-600">Upload CSV File</label>
                        <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                        <div class="form-text" style="font-size: 11px;">
                            CSV file should contain a header line, with student **Email** or **Matric ID** in the first column.
                        </div>
                    </div>
                    <button type="submit" class="btn-slms btn-primary w-100 py-2">
                        <i class="fas fa-upload me-1"></i> Upload & Enroll
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
