<?php
/**
 * Nadics LectureHub — System Settings View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'System Settings',
    'page_description' => 'Configure platform parameters, security, and integration keys.',
]);

$settingsMap = [];
if (!empty($settings)) {
    foreach ($settings as $s) {
        $settingsMap[$s['setting_key']] = $s['setting_value'];
    }
}

$streamBitrate = $settingsMap['stream_bitrate'] ?? '1200';
$audioQuality = $settingsMap['audio_quality'] ?? 'high';
$aiModel = $settingsMap['ai_model'] ?? 'Gemini 1.5 Pro';
$aiMaxTokens = $settingsMap['ai_max_tokens'] ?? '2048';
$academicYear = $settingsMap['academic_year'] ?? '2026/2027';
$selfReg = $settingsMap['allow_student_self_registration'] ?? '1';
?>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Global Platform Settings</h1>
            <p class="page-subtitle">Configure enterprise application parameters, streaming server keys, and AI configuration.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="slms-card p-4 mb-4">
                <h4 class="mb-4"><i class="fas fa-sliders-h text-primary me-2"></i> Application & Core Service Settings</h4>
                <form method="POST" action="<?= url('/admin/settings') ?>">
                    <?= csrf_field() ?>

                    <!-- Section A: Streaming Parameters -->
                    <h5 class="fw-700 text-secondary mb-3 mt-2"><i class="fas fa-broadcast-tower me-2"></i> Media Streaming Engine</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 form-group">
                            <label class="form-label small fw-600">Default Stream Bitrate (kbps)</label>
                            <input type="number" name="stream_bitrate" class="form-control-slms" value="<?= e($streamBitrate) ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label small fw-600">Audio Stream Quality Profile</label>
                            <select name="audio_quality" class="form-select" required>
                                <option value="low" <?= $audioQuality === 'low' ? 'selected' : '' ?>>Low Bandwidth (32 kbps)</option>
                                <option value="medium" <?= $audioQuality === 'medium' ? 'selected' : '' ?>>Standard Profile (64 kbps)</option>
                                <option value="high" <?= $audioQuality === 'high' ? 'selected' : '' ?>>High Fidelity (128 kbps)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Section B: AI Services -->
                    <h5 class="fw-700 text-secondary mb-3"><i class="fas fa-robot me-2"></i> AI Classroom Processing</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 form-group">
                            <label class="form-label small fw-600">Active LLM Model</label>
                            <select name="ai_model" class="form-select" required>
                                <option value="Gemini 1.5 Pro" <?= $aiModel === 'Gemini 1.5 Pro' ? 'selected' : '' ?>>Gemini 1.5 Pro (Enterprise)</option>
                                <option value="Gemini 1.5 Flash" <?= $aiModel === 'Gemini 1.5 Flash' ? 'selected' : '' ?>>Gemini 1.5 Flash (Performance)</option>
                                <option value="GPT-4o-mini" <?= $aiModel === 'GPT-4o-mini' ? 'selected' : '' ?>>GPT-4o Mini (Cost-Optimized)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label small fw-600">Max Response Length (Tokens)</label>
                            <input type="number" name="ai_max_tokens" class="form-control-slms" value="<?= e($aiMaxTokens) ?>" required>
                        </div>
                    </div>

                    <!-- Section C: Global Enrollment Parameters -->
                    <h5 class="fw-700 text-secondary mb-3"><i class="fas fa-user-shield me-2"></i> Roster & Registration</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 form-group">
                            <label class="form-label small fw-600">Active Academic Year</label>
                            <input type="text" name="academic_year" class="form-control-slms" value="<?= e($academicYear) ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label small fw-600">Allow Student Registration</label>
                            <select name="allow_student_self_registration" class="form-select" required>
                                <option value="1" <?= $selfReg === '1' ? 'selected' : '' ?>>Enabled (Open Enrollment)</option>
                                <option value="0" <?= $selfReg === '0' ? 'selected' : '' ?>>Disabled (Admin Upload Only)</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-slms btn-primary px-4 py-2 mt-2">
                        <i class="fas fa-save me-1"></i> Save Platform Settings
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="slms-card p-4">
                <h5 class="mb-3"><i class="fas fa-server text-accent me-2"></i> Server Environment</h5>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        <span>PHP Version</span>
                        <strong><?= PHP_VERSION ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        <span>Web Server</span>
                        <strong><?= e($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        <span>Database Driver</span>
                        <strong>MariaDB / PDO</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        <span>Session Driver</span>
                        <strong>Secure Cookie / File</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__view->endSection(); ?>
