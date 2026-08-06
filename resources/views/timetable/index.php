<?php
/**
 * Nadics LectureHub — Timetable View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'Academic Timetable',
    'page_description' => 'Interactive weekly lecture schedules, time slots, and venues.',
]);
?>

<style>
@media print {
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .slms-sidebar, .slms-navbar, .slms-footer, .page-header, .filters-card, .btn-slms, .nav-section-title {
        display: none !important;
    }
    .slms-main {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .slms-card {
        border: none !important;
        box-shadow: none !important;
    }
    #timetable-grid-container {
        display: block !important;
    }
    #timetable-cards-container {
        display: none !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
}
</style>

<?php $__view->section('content'); ?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">Academic Class Timetable</h1>
            <p class="page-subtitle">Weekly lecture schedules, lecture hall venues, and live broadcast shortcuts.</p>
        </div>
        <?php if (in_array($user_role, ['lecturer', 'admin', 'super_admin', 'university_admin'])): ?>
            <a href="<?= url('/lectures/create') ?>" class="btn-slms btn-primary">
                <i class="fas fa-calendar-plus me-1"></i> Schedule New Class
            </a>
        <?php endif; ?>
    </div>

    <!-- Day Selector Tabs -->
    <div class="d-flex align-items-center gap-2 mb-4 overflow-auto pb-2">
        <a href="<?= url('/timetable') ?>" class="btn-slms btn-sm <?= $selectedDay === 'all' ? 'btn-primary' : 'btn-ghost' ?>">
            <i class="fas fa-th me-1"></i> All Days
        </a>
        <?php foreach ($days as $day): ?>
            <a href="<?= url('/timetable?day=' . strtolower($day)) ?>" class="btn-slms btn-sm <?= $selectedDay === strtolower($day) ? 'btn-primary' : 'btn-ghost' ?>">
                <?= e($day) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Filters & Action Bar -->
    <div class="slms-card mb-4 p-3 filters-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 flex-grow-1" style="max-width: 600px;">
                <div class="input-group-slms flex-grow-1">
                    <i class="fas fa-search input-icon"></i>
                    <input type="text" id="timetable-search" class="form-control-slms" placeholder="Search course code, title, or lecturer...">
                </div>
                <select id="timetable-venue-filter" class="form-control-slms" style="max-width: 180px;">
                    <option value="">All Venues</option>
                    <option value="virtual">Virtual Studio</option>
                    <?php
                    $venues = [];
                    foreach ($timetableGrid as $day => $slots) {
                        foreach ($slots as $lec) {
                            if ($lec['hall_name']) {
                                $venues[$lec['hall_name']] = true;
                            }
                        }
                    }
                    foreach (array_keys($venues) as $venue):
                    ?>
                        <option value="<?= e(strtolower($venue)) ?>"><?= e($venue) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button id="btn-view-cards" class="btn-slms btn-sm btn-primary"><i class="fas fa-th-large me-1"></i> List View</button>
                <button id="btn-view-grid" class="btn-slms btn-sm btn-ghost"><i class="fas fa-table me-1"></i> Matrix Grid</button>
                <button onclick="exportICS()" class="btn-slms btn-sm btn-outline-slms text-secondary"><i class="fas fa-calendar-alt me-1"></i> Export .ics</button>
                <button onclick="window.print()" class="btn-slms btn-sm btn-outline-slms text-secondary"><i class="fas fa-file-pdf me-1"></i> Print / PDF</button>
            </div>
        </div>
    </div>

    <!-- Timetable Card List (Default View) -->
    <div id="timetable-cards-container" class="row g-4">
        <?php foreach ($days as $day): ?>
            <?php 
            if ($selectedDay !== 'all' && $selectedDay !== strtolower($day)) continue;
            $dayLectures = $timetableGrid[$day] ?? [];
            ?>
            <div class="<?= $selectedDay === 'all' ? 'col-lg-6 col-xl-4' : 'col-12' ?> timetable-day-column">
                <div class="slms-card h-100">
                    <div class="card-header bg-primary-subtle text-primary border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="fw-700 m-0"><i class="fas fa-calendar-day me-2"></i> <?= e($day) ?></h5>
                        <span class="badge bg-primary text-white rounded-pill"><?= count($dayLectures) ?> Classes</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="timetable-no-match-msg text-center text-muted py-4 d-none">
                            <i class="fas fa-search mb-2" style="font-size:2rem;opacity:0.3;"></i>
                            <p class="small m-0">No matching classes found.</p>
                        </div>
                        <?php if (!empty($dayLectures)): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($dayLectures as $lec): ?>
                                    <?php 
                                    $start = strtotime($lec['scheduled_start']);
                                    $end = strtotime($lec['scheduled_end']);
                                    $isLive = strtolower($lec['status'] ?? '') === 'live' || !empty($lec['is_live']);
                                    ?>
                                    <div class="p-3 rounded-3 border bg-card-slms hover-shadow transition-all timetable-card-item" 
                                         style="border-left: 4px solid var(--primary-color)!important;"
                                         data-id="<?= e($lec['id']) ?>"
                                         data-start="<?= e($lec['scheduled_start']) ?>"
                                         data-end="<?= e($lec['scheduled_end']) ?>"
                                         data-search-content="<?= e(strtolower($lec['course_code'] . ' ' . $lec['course_title'] . ' ' . $lec['lecturer_first_name'] . ' ' . $lec['lecturer_last_name'])) ?>"
                                         data-venue="<?= e(strtolower($lec['hall_name'] ?: 'virtual')) ?>"
                                         data-venue-raw="<?= e($lec['hall_name'] ? ($lec['hall_name'] . ($lec['hall_building'] ? ' (' . $lec['hall_building'] . ')' : '')) : 'Virtual Studio') ?>">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge-slms badge-primary"><?= e($lec['course_code']) ?></span>
                                            <span class="small fw-700 text-secondary">
                                                <i class="fas fa-clock me-1 text-muted"></i>
                                                <?= date('g:i A', $start) ?> - <?= date('g:i A', $end) ?>
                                            </span>
                                        </div>
                                        <h6 class="fw-700 mb-1 text-dark"><?= e($lec['course_title']) ?></h6>
                                        <p class="small text-muted mb-2">
                                            <i class="fas fa-chalkboard-teacher me-1"></i>
                                            <?= e($lec['lecturer_first_name'] . ' ' . $lec['lecturer_last_name']) ?>
                                        </p>
                                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                            <span class="small text-muted">
                                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                <?= e($lec['hall_name'] ? ($lec['hall_name'] . ($lec['hall_building'] ? ' (' . $lec['hall_building'] . ')' : '')) : 'Virtual Studio') ?>
                                            </span>
                                            <?php if ($isLive): ?>
                                                <a href="<?= url('/lectures/' . $lec['id']) ?>" class="btn btn-sm btn-danger animate-pulse">
                                                    <i class="fas fa-play-circle me-1"></i> Watch Live
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= url('/lectures/' . $lec['id']) ?>" class="btn-slms btn-sm btn-ghost">
                                                    View Details <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times mb-2" style="font-size:2rem;opacity:0.3;"></i>
                                <p class="small m-0">No classes scheduled for <?= e($day) ?>.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Matrix Grid View (Hidden by default) -->
    <div id="timetable-grid-container" class="slms-card d-none">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle mb-0" style="min-width: 900px; border-color: var(--border-color);">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 16%;" class="fw-700 text-secondary">Time Slot</th>
                        <?php foreach ($days as $day): ?>
                            <th style="width: 14%;" class="fw-700 text-primary"><?= e($day) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $timeSlots = [
                        '08:00 - 10:00' => ['start' => '08:00:00', 'end' => '10:00:00', 'label' => '08:00 AM - 10:00 AM'],
                        '10:00 - 12:00' => ['start' => '10:00:00', 'end' => '12:00:00', 'label' => '10:00 AM - 12:00 PM'],
                        '12:00 - 14:00' => ['start' => '12:00:00', 'end' => '14:00:00', 'label' => '12:00 PM - 02:00 PM'],
                        '14:00 - 16:00' => ['start' => '14:00:00', 'end' => '16:00:00', 'label' => '02:00 PM - 04:00 PM'],
                        '16:00 - 18:00' => ['start' => '16:00:00', 'end' => '18:00:00', 'label' => '04:00 PM - 06:00 PM'],
                    ];
                    
                    foreach ($timeSlots as $key => $slot):
                    ?>
                        <tr>
                            <td class="fw-700 text-secondary" style="font-size: var(--text-sm); background: var(--bg-hover);">
                                <i class="fas fa-clock me-1 text-muted"></i><?= $slot['label'] ?>
                            </td>
                            <?php 
                            foreach ($days as $day): 
                                $dayLectures = $timetableGrid[$day] ?? [];
                                $matchingLec = null;
                                
                                foreach ($dayLectures as $lec) {
                                    $lecTime = date('H:i:s', strtotime($lec['scheduled_start']));
                                    if ($lecTime >= $slot['start'] && $lecTime < $slot['end']) {
                                        $matchingLec = $lec;
                                        break;
                                    }
                                }
                            ?>
                                <td class="timetable-cell" data-day="<?= strtolower($day) ?>" style="height: 100px;">
                                    <?php if ($matchingLec): ?>
                                        <?php 
                                        $isLive = strtolower($matchingLec['status'] ?? '') === 'live' || !empty($matchingLec['is_live']);
                                        ?>
                                        <div class="p-2 rounded border text-start timetable-matrix-item <?= $isLive ? 'border-danger bg-danger-subtle text-danger' : 'border-primary bg-primary-subtle text-primary' ?>" 
                                             data-search-content="<?= e(strtolower($matchingLec['course_code'] . ' ' . $matchingLec['course_title'] . ' ' . $matchingLec['lecturer_first_name'] . ' ' . $matchingLec['lecturer_last_name'])) ?>"
                                             data-venue="<?= e(strtolower($matchingLec['hall_name'] ?: 'virtual')) ?>">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="badge bg-primary text-white" style="font-size: 9px; padding: 2px 4px;"><?= e($matchingLec['course_code']) ?></span>
                                                <?php if ($isLive): ?>
                                                    <span class="badge bg-danger text-white animate-pulse" style="font-size: 8px; padding: 2px 4px;">LIVE</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="fw-700 text-dark text-truncate" style="font-size: 11px;" title="<?= e($matchingLec['course_title']) ?>"><?= e($matchingLec['course_title']) ?></div>
                                            <div class="text-muted text-truncate" style="font-size: 10px;"><?= e($matchingLec['lecturer_first_name'] . ' ' . $matchingLec['lecturer_last_name']) ?></div>
                                            <div class="mt-1 d-flex align-items-center justify-content-between border-top pt-1" style="font-size: 9px;">
                                                <span><i class="fas fa-map-marker-alt text-danger me-1"></i><?= e($matchingLec['hall_name'] ?: 'Virtual') ?></span>
                                                <a href="<?= url('/lectures/' . $matchingLec['id']) ?>" class="text-secondary fw-700">View <i class="fas fa-chevron-right" style="font-size: 7px;"></i></a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="py-3 text-muted" style="font-size: 11px; opacity: 0.15;">
                                            <i class="fas fa-plus-circle"></i> Free
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('timetable-search');
    const venueFilter = document.getElementById('timetable-venue-filter');
    const btnViewCards = document.getElementById('btn-view-cards');
    const btnViewGrid = document.getElementById('btn-view-grid');
    const cardsContainer = document.getElementById('timetable-cards-container');
    const gridContainer = document.getElementById('timetable-grid-container');

    // === View Toggling ===
    if (btnViewCards && btnViewGrid && cardsContainer && gridContainer) {
        btnViewCards.addEventListener('click', function() {
            btnViewCards.classList.replace('btn-ghost', 'btn-primary');
            btnViewGrid.classList.replace('btn-primary', 'btn-ghost');
            cardsContainer.classList.remove('d-none');
            gridContainer.classList.add('d-none');
        });

        btnViewGrid.addEventListener('click', function() {
            btnViewGrid.classList.replace('btn-ghost', 'btn-primary');
            btnViewCards.classList.replace('btn-primary', 'btn-ghost');
            gridContainer.classList.remove('d-none');
            cardsContainer.classList.add('d-none');
        });
    }

    // === Search & Venue Filters ===
    function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        const selectedVenue = venueFilter.value.toLowerCase();

        // 1. Filter Card View
        document.querySelectorAll('.timetable-card-item').forEach(card => {
            const searchContent = card.getAttribute('data-search-content');
            const venue = card.getAttribute('data-venue');
            
            const matchesSearch = !query || searchContent.includes(query);
            const matchesVenue = !selectedVenue || venue.includes(selectedVenue);

            if (matchesSearch && matchesVenue) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Hide empty day columns/cards if no items match
        document.querySelectorAll('.timetable-day-column').forEach(column => {
            const visibleItems = column.querySelectorAll('.timetable-card-item[style="display: block;"]').length;
            const allItems = column.querySelectorAll('.timetable-card-item').length;
            const noMatchMsg = column.querySelector('.timetable-no-match-msg');

            if (allItems > 0) {
                if (visibleItems === 0) {
                    column.querySelectorAll('.timetable-card-item').forEach(el => el.style.display = 'none');
                    if (noMatchMsg) noMatchMsg.classList.remove('d-none');
                } else {
                    column.querySelectorAll('.timetable-card-item').forEach(el => el.style.display = '');
                    if (noMatchMsg) noMatchMsg.classList.add('d-none');
                }
            }
        });

        // 2. Filter Grid View
        document.querySelectorAll('.timetable-matrix-item').forEach(item => {
            const searchContent = item.getAttribute('data-search-content');
            const venue = item.getAttribute('data-venue');

            const matchesSearch = !query || searchContent.includes(query);
            const matchesVenue = !selectedVenue || venue.includes(selectedVenue);

            if (matchesSearch && matchesVenue) {
                item.style.opacity = '1';
                item.style.filter = 'none';
            } else {
                item.style.opacity = '0.15';
                item.style.filter = 'grayscale(1)';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (venueFilter) venueFilter.addEventListener('change', applyFilters);
});

// === Export iCal (.ics) ===
function exportICS() {
    let icsContent = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Nadics Solutions//LectureHub//EN\nCALSCALE:GREGORIAN\nMETHOD:PUBLISH\n";
    
    document.querySelectorAll('.timetable-card-item').forEach(item => {
        const title = item.querySelector('h6').textContent.trim();
        const code = item.querySelector('.badge-slms').textContent.trim();
        const lecturer = item.querySelector('.text-muted').textContent.trim();
        const startRaw = item.getAttribute('data-start');
        const endRaw = item.getAttribute('data-end');
        const venue = item.getAttribute('data-venue-raw') || 'Virtual Studio';
        
        const formatICSDate = (dateStr) => {
            const date = new Date(dateStr);
            return date.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
        };

        const dtstart = formatICSDate(startRaw);
        const dtend = formatICSDate(endRaw);
        const uid = 'lec_' + item.getAttribute('data-id') + '@nadicslecturehub.com';

        icsContent += "BEGIN:VEVENT\n";
        icsContent += `UID:${uid}\n`;
        icsContent += `DTSTART:${dtstart}\n`;
        icsContent += `DTEND:${dtend}\n`;
        icsContent += `SUMMARY:${code} - ${title}\n`;
        icsContent += `DESCRIPTION:Lecturer: ${lecturer}\n`;
        icsContent += `LOCATION:${venue}\n`;
        icsContent += "END:VEVENT\n";
    });

    icsContent += "END:VCALENDAR";

    const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'academic_timetable.ics';
    link.click();
}
</script>
<?php $__view->endSection(); ?>
