/**
 * Nadics LectureHub — Real-Time Live Broadcast Poller & Listener Alert Engine
 * 
 * Periodically polls for active studio broadcasts matching enrolled student courses.
 * Triggers animated floating live banners and toast alerts when a lecturer goes live.
 */

(function () {
    'use strict';

    let lastActiveBroadcasts = [];

    function pollActiveBroadcasts() {
        if (!window.SLMS_APP_URL) return;

        fetch(window.SLMS_APP_URL + '/stream/active-broadcasts', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.active_broadcasts)) {
                handleBroadcasts(data.active_broadcasts);
            }
        })
        .catch(err => {
            // Silent catch for background polling
        });
    }

    function handleBroadcasts(broadcasts) {
        const liveContainer = document.getElementById('live-broadcast-alert-container');

        if (broadcasts.length === 0) {
            if (liveContainer) liveContainer.innerHTML = '';
            return;
        }

        broadcasts.forEach(broadcast => {
            const isNew = !lastActiveBroadcasts.some(b => b.lecture_id === broadcast.lecture_id);

            // Show floating toast alert if a lecturer just went live while student is browsing
            if (isNew) {
                showLiveToast(broadcast);
            }
        });

        lastActiveBroadcasts = broadcasts;
    }

    function showLiveToast(broadcast) {
        let toastEl = document.getElementById('live-stream-floating-toast');
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.id = 'live-stream-floating-toast';
            toastEl.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 420px;
                background: linear-gradient(135deg, #1e293b, #0f172a);
                border: 2px solid #ef4444;
                border-radius: 16px;
                color: #ffffff;
                box-shadow: 0 10px 30px rgba(239, 68, 68, 0.4);
                padding: 16px 20px;
                animation: slideInRight 0.4s ease-out;
            `;
            document.body.appendChild(toastEl);
        }

        const listenerUrl = window.SLMS_APP_URL + '/stream/listener/' + broadcast.lecture_id;

        toastEl.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <span style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                    <span style="width: 8px; height: 8px; background: white; border-radius: 50%; display: inline-block; animation: pulse 1s infinite;"></span>
                    LIVE STUDIO BROADCAST
                </span>
                <button onclick="document.getElementById('live-stream-floating-toast').remove()" style="background: transparent; border: none; color: #94a3b8; font-size: 16px; cursor: pointer;">&times;</button>
            </div>
            <h6 style="margin: 0 0 4px 0; font-weight: 700; color: #f8fafc; font-size: 15px;">${escapeHtml(broadcast.course_code)} — ${escapeHtml(broadcast.lecture_title)}</h6>
            <p style="margin: 0 0 12px 0; font-size: 12px; color: #cbd5e1;">Lecturer <strong>${escapeHtml(broadcast.lecturer_first_name)} ${escapeHtml(broadcast.lecturer_last_name)}</strong> is streaming live audio & video right now!</p>
            <a href="${listenerUrl}" style="display: inline-block; width: 100%; text-align: center; background: #ef4444; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 13px; transition: all 0.2s;">
                <i class="fas fa-play-circle" style="margin-right: 6px;"></i> Watch Live (Audio + Video)
            </a>
        `;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    // Start polling every 8 seconds
    document.addEventListener('DOMContentLoaded', function () {
        pollActiveBroadcasts();
        setInterval(pollActiveBroadcasts, 8000);
    });
})();
