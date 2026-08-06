<?php
/**
 * Nadics LectureHub — Attendance & QR Code View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Attendance Management',
    'page_description' => 'Track and manage lecture attendance records and QR verification.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Attendance Records</h1>
            <p class="page-subtitle">Track and verify lecture attendance across your courses.</p>
        </div>
        <?php if (in_array($user_role, ['student'])): ?>
            <button class="btn-slms btn-success-slms" onclick="openQrScanner()">
                <i class="fas fa-camera me-1"></i> Scan QR Code
            </button>
        <?php else: ?>
            <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#generateQrModal">
                <i class="fas fa-qrcode me-1"></i> Generate QR Code
            </button>
        <?php endif; ?>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid mb-4" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card stat-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value"><?= count($records) ?></div>
                    <div class="stat-label">Total Records</div>
                </div>
                <div class="stat-icon icon-success"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="stat-card stat-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value">94%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
                <div class="stat-icon icon-primary"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value"><?= count(array_unique(array_column($records, 'lecture_id'))) ?></div>
                    <div class="stat-label">Lectures Attended</div>
                </div>
                <div class="stat-icon icon-info"><i class="fas fa-calendar-check"></i></div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="slms-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="m-0 fw-700"><i class="fas fa-list-check text-secondary me-2"></i>
                <?= in_array($user_role, ['lecturer','admin','university_admin','super_admin']) ? 'All Attendance Records' : 'My Attendance Records' ?>
            </h5>
            <a href="<?= url('/lectures') ?>" class="btn-slms btn-sm btn-ghost">
                <i class="fas fa-arrow-left me-1"></i> Back to Lectures
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-slms">
                    <thead>
                        <tr>
                            <?php if (in_array($user_role, ['lecturer','admin','university_admin','super_admin'])): ?>
                                <th>Student</th>
                                <th>Staff / Matric ID</th>
                            <?php endif; ?>
                            <th>Course</th>
                            <th>Lecture Session</th>
                            <th>Date / Time</th>
                            <th>Verification</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $rec): ?>
                                <tr>
                                    <?php if (in_array($user_role, ['lecturer','admin','university_admin','super_admin'])): ?>
                                        <td><strong><?= e($rec['first_name'] . ' ' . $rec['last_name']) ?></strong></td>
                                        <td><code><?= e($rec['matric_staff_id'] ?: 'N/A') ?></code></td>
                                    <?php endif; ?>
                                    <td><span class="badge-slms badge-primary"><?= e($rec['course_code'] ?? 'N/A') ?></span></td>
                                    <td><?= e($rec['lecture_title'] ?? 'N/A') ?></td>
                                    <td class="text-muted small"><?= e(date('M d Y, g:i A', strtotime($rec['verified_at'] ?? $rec['created_at'] ?? 'now'))) ?></td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info"><i class="fas fa-qrcode me-1"></i> QR Scan</span>
                                    </td>
                                    <td><span class="badge-slms badge-success"><i class="fas fa-check me-1"></i> Present</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="stat-icon icon-primary mx-auto mb-3" style="width:64px;height:64px;font-size:1.6rem;">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <h5 class="fw-700">No attendance records yet</h5>
                                    <?php if ($user_role === 'student'): ?>
                                        <p class="text-muted mb-3">Scan the QR code during live lectures to mark your attendance.</p>
                                        <button class="btn-slms btn-primary" onclick="openQrScanner()">
                                            <i class="fas fa-camera me-1"></i> Scan QR Code Now
                                        </button>
                                    <?php else: ?>
                                        <p class="text-muted mb-3">Attendance will appear here when students scan QR codes during live lectures.</p>
                                        <button class="btn-slms btn-primary" data-bs-toggle="modal" data-bs-target="#generateQrModal">
                                            <i class="fas fa-qrcode me-1"></i> Generate Attendance QR Code
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Generate QR Modal (Staff) -->
<?php if (in_array($user_role, ['lecturer','admin','university_admin','super_admin'])): ?>
<div class="modal fade" id="generateQrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-xl);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-700"><i class="fas fa-qrcode me-2 text-primary"></i> Generate Attendance QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="generateQrForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Lecture Session <span class="text-danger">*</span></label>
                        <select name="lecture_id" class="form-control-slms" required>
                            <option value="">Select Lecture</option>
                            <?php foreach ($lectures ?? [] as $lec): ?>
                                <option value="<?= $lec['id'] ?>"><?= e($lec['course_code']) ?> — <?= e($lec['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">QR Expiration Window <span class="text-danger">*</span></label>
                        <select name="duration_minutes" class="form-control-slms" required>
                            <option value="15">15 Minutes</option>
                            <option value="30" selected>30 Minutes</option>
                            <option value="60">1 Hour</option>
                            <option value="120">2 Hours</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-slms btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-slms btn-primary">
                        <i class="fas fa-magic me-1"></i> Generate Live QR Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Active QR Code Display Modal (Staff) -->
<div class="modal fade" id="activeQrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4" style="border-radius: var(--radius-xl);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-success-subtle text-success px-3 py-1 fw-700"><i class="fas fa-signal me-1"></i> Live Session Active</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <h4 class="fw-800 text-primary mb-1" id="qrModalTitle">Lecture Attendance QR</h4>
            <p class="text-muted small mb-3" id="qrModalCourse">Scan code to mark attendance</p>

            <div class="p-3 bg-white rounded border d-inline-block mx-auto mb-3 shadow-sm" style="border-radius: 16px!important;">
                <img id="qrCodeImage" src="" alt="QR Code" style="width: 240px; height: 240px; object-fit: contain;">
            </div>

            <div class="mb-3">
                <span class="text-muted small">Verification Token Code:</span>
                <div class="fw-700 fs-5 text-dark" id="qrCodeText" style="letter-spacing: 1px;">-</div>
            </div>

            <div class="alert alert-warning py-2 mb-3 small d-flex align-items-center justify-content-center gap-2" style="border-radius: 8px;">
                <i class="fas fa-clock text-warning"></i>
                <span>Expires in <strong id="qrTimer">30:00</strong></span>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm flex-fill" id="copyQrUrlBtn">
                    <i class="fas fa-copy me-1"></i> Copy Link
                </button>
                <button type="button" class="btn btn-primary btn-sm flex-fill" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print QR Card
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Student QR Scanner / Entry Modal -->
<div class="modal fade" id="studentQrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4" style="border-radius: var(--radius-xl);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="modal-title fw-700 m-0"><i class="fas fa-qrcode me-2 text-primary"></i> Mark Lecture Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="verifyQrForm">
                <?= csrf_field() ?>
                <div style="width:140px;height:140px;background:rgba(37, 99, 235, 0.1);border:2px dashed #3B82F6;border-radius:20px;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;font-size:3.5rem;color:#2563EB;">
                    <i class="fas fa-camera"></i>
                </div>
                <p class="text-muted small mb-4">Enter or paste the QR verification token code displayed on your lecturer's studio screen.</p>
                
                <div class="form-group mb-4">
                    <label class="form-label text-start w-100">QR Code / Verification Token <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control-slms text-center fw-700" placeholder="e.g. QR_ATT_78_a9f1b2" style="font-size: 1.1rem; letter-spacing: 1px;" required>
                </div>

                <button type="submit" class="btn-slms btn-primary w-100 py-3" style="font-size: 1rem;">
                    <i class="fas fa-check-circle me-1"></i> Verify & Mark Present
                </button>
            </form>
        </div>
    </div>
</div>

<script>
let qrCountdownInterval = null;

function openQrScanner() {
    const modal = new bootstrap.Modal(document.getElementById('studentQrModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    // Lecturer QR Code Generation AJAX Form
    const genForm = document.getElementById('generateQrForm');
    if (genForm) {
        genForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(genForm);
            
            try {
                const response = await fetch('<?= url("/attendance/generate-qr") ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const res = await response.json();
                
                if (res.success) {
                    // Hide creation modal
                    bootstrap.Modal.getInstance(document.getElementById('generateQrModal')).hide();
                    
                    // Update Active QR Display Modal
                    const data = res.data;
                    document.getElementById('qrModalTitle').textContent = data.lecture_title;
                    document.getElementById('qrModalCourse').textContent = data.course_code + ' — Attendance Session';
                    document.getElementById('qrCodeText').textContent = data.qr_code_hash;
                    
                    // Generate high resolution QR code image via serverless QR API
                    const qrImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(data.verify_url);
                    document.getElementById('qrCodeImage').src = qrImgUrl;
                    
                    // Setup Copy URL
                    document.getElementById('copyQrUrlBtn').onclick = () => {
                        navigator.clipboard.writeText(data.verify_url);
                        alert('Attendance verification URL copied to clipboard!');
                    };
                    
                    // Start Countdown Timer
                    startQrTimer(new Date(data.expires_at).getTime());
                    
                    // Show Active QR Modal
                    const activeModal = new bootstrap.Modal(document.getElementById('activeQrModal'));
                    activeModal.show();
                } else {
                    alert(res.message || 'Failed to generate QR Code.');
                }
            } catch (err) {
                alert('Error generating QR Code.');
            }
        });
    }

    // Student Verification AJAX Form
    const verifyForm = document.getElementById('verifyQrForm');
    if (verifyForm) {
        verifyForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(verifyForm);
            
            try {
                const response = await fetch('<?= url("/attendance/verify") ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const res = await response.json();
                
                if (res.success) {
                    alert('🎉 ' + res.message);
                    window.location.reload();
                } else {
                    alert('❌ ' + (res.message || 'Verification failed.'));
                }
            } catch (err) {
                alert('Error verifying attendance QR code.');
            }
        });
    }
});

function startQrTimer(expireTimeMs) {
    if (qrCountdownInterval) clearInterval(qrCountdownInterval);
    
    const updateDisplay = () => {
        const diff = expireTimeMs - new Date().getTime();
        if (diff <= 0) {
            clearInterval(qrCountdownInterval);
            document.getElementById('qrTimer').textContent = 'EXPIRED';
            return;
        }
        const mins = Math.floor(diff / 60000);
        const secs = Math.floor((diff % 60000) / 1000);
        document.getElementById('qrTimer').textContent = 
            (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
    };
    
    updateDisplay();
    qrCountdownInterval = setInterval(updateDisplay, 1000);
}
</script>
<?php $__view->endSection(); ?>
