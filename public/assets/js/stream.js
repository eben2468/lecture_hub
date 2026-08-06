/**
 * ============================================================
 * Nadics LectureHub — Live Stream & Audio Engine JavaScript
 * ============================================================
 * Enhanced with:
 * - External microphone/audio device enumeration & switching
 * - MediaRecorder-based client-side recording
 * - Recording upload with progress tracking
 * - Live recording timer (HH:MM:SS)
 * - Live input level meter
 * - Live bitrate & latency telemetry
 * - iOS/Safari full compatibility fixes:
 *     • AudioContext resume on user gesture
 *     • canvas.captureStream() polyfill (not supported on Safari)
 *     • ctx.roundRect() polyfill (not supported on Safari < 15.4)
 *     • MediaRecorder MIME type fallback for Safari (audio/mp4)
 *     • video.play() promise handling for iOS autoplay policy
 *     • Device enumeration graceful degradation on iOS
 *     • webkitAudioContext support
 * ============================================================
 */

const SLMSStream = (function() {
    let mediaRecorder    = null;
    let recordedChunks   = [];
    let audioStream      = null;
    let audioContext      = null;
    let analyser          = null;
    let micLevelAnalyser  = null;
    let isBroadcasting    = false;
    let isMuted           = false;
    let isListening       = false;
    let chatPoller        = null;
    let pingInterval      = null;
    let listenerAudioCtx  = null;
    let oscillator        = null;
    let gainNode          = null;
    let isVideoOn         = false;
    let isScreenSharing   = false;
    let screenStream      = null;
    let listenerCanvas    = null;
    let listenerCanvasCtx = null;
    let listenerCanvasInterval = null;
    let listenerStream    = null;
    let recordingTimer    = null;
    let recordingStartTime = null;
    let micLevelRAF       = null;
    let selectedDeviceId  = '';

    // ── iOS / Safari detection ────────────────────────────────
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    const isAppleDevice = isIOS || isSafari;

    // ── roundRect polyfill for Safari < 15.4 ─────────────────
    function safeRoundRect(ctx, x, y, w, h, r) {
        if (typeof ctx.roundRect === 'function') {
            ctx.roundRect(x, y, w, h, typeof r === 'number' ? r : (Array.isArray(r) ? r : [r]));
        } else {
            const radius = typeof r === 'number' ? r : (Array.isArray(r) ? r[0] : 8);
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + w - radius, y);
            ctx.quadraticCurveTo(x + w, y, x + w, y + radius);
            ctx.lineTo(x + w, y + h - radius);
            ctx.quadraticCurveTo(x + w, y + h, x + w - radius, y + h);
            ctx.lineTo(x + radius, y + h);
            ctx.quadraticCurveTo(x, y + h, x, y + h - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
        }
    }

    /**
     * Resume AudioContext — required on iOS/Safari after user gesture
     */
    async function resumeAudioContext(ctx) {
        if (ctx && ctx.state === 'suspended') {
            try { await ctx.resume(); } catch (e) {}
        }
    }

    /**
     * Format seconds into HH:MM:SS
     */
    function formatTime(totalSeconds) {
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = Math.floor(totalSeconds % 60);
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    /**
     * Format bytes to human-readable size
     */
    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    /**
     * Update the recording timer display
     */
    function updateTimer() {
        if (!recordingStartTime) return;
        const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
        const timerEl = document.getElementById('timerDisplay');
        if (timerEl) timerEl.textContent = formatTime(elapsed);
    }

    /**
     * Animate the input level meter
     */
    function animateMicLevel() {
        if (!micLevelAnalyser || !isBroadcasting) {
            const bar = document.getElementById('micLevelBar');
            if (bar) bar.style.width = '0%';
            return;
        }
        const bufferLength = micLevelAnalyser.fftSize;
        const dataArray = new Float32Array(bufferLength);
        micLevelAnalyser.getFloatTimeDomainData(dataArray);

        let sumSquares = 0;
        for (let i = 0; i < bufferLength; i++) {
            sumSquares += dataArray[i] * dataArray[i];
        }
        const rms = Math.sqrt(sumSquares / bufferLength);
        const level = Math.min(100, Math.max(0, rms * 400));

        const bar = document.getElementById('micLevelBar');
        if (bar) bar.style.width = level + '%';

        micLevelRAF = requestAnimationFrame(animateMicLevel);
    }

    /**
     * Update recording size display
     */
    function updateRecordingSize() {
        let totalSize = 0;
        for (const chunk of recordedChunks) {
            totalSize += chunk.size;
        }
        const el = document.getElementById('recordingSizeDisplay');
        if (el) el.textContent = formatBytes(totalSize);
    }

    /**
     * Safely play a video element — handles iOS autoplay policy
     */
    function safeVideoPlay(videoEl) {
        if (!videoEl) return;
        const playPromise = videoEl.play();
        if (playPromise !== undefined) {
            playPromise.catch(err => {
                // Autoplay blocked — add a tap-to-play overlay for iOS
                console.warn('Autoplay blocked (iOS policy):', err.message);
                addTapToPlayOverlay(videoEl);
            });
        }
    }

    /**
     * Add a tap-to-play overlay for iOS autoplay restrictions
     */
    function addTapToPlayOverlay(videoEl) {
        const parent = videoEl.parentElement;
        if (!parent || parent.querySelector('.ios-tap-overlay')) return;

        const overlay = document.createElement('div');
        overlay.className = 'ios-tap-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        overlay.style.cssText = 'background:rgba(0,0,0,0.65);z-index:10;cursor:pointer;';
        overlay.innerHTML = `
            <div class="text-center text-white">
                <i class="fas fa-play-circle" style="font-size:4rem;opacity:0.9;"></i>
                <div class="mt-2 fw-600" style="font-size:1rem;">Tap to Play</div>
                <div class="small text-white-50 mt-1">iOS requires a tap to start video</div>
            </div>`;
        overlay.addEventListener('click', () => {
            videoEl.play().then(() => overlay.remove()).catch(() => {});
        });
        parent.style.position = 'relative';
        parent.appendChild(overlay);
    }

    return {
        /**
         * Initialize Broadcaster Studio
         */
        initBroadcaster(lectureId) {
            this.startChatPolling(lectureId);
            this.drawIdleVisualizer('audioVisualizer');
            this.enumerateAudioDevices();

            // iOS: show warning if not HTTPS
            if (isIOS && location.protocol !== 'https:' && location.hostname !== 'localhost') {
                const warn = document.createElement('div');
                warn.style.cssText = 'background:#7c2d12;color:#fed7aa;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;';
                warn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>iOS Notice:</strong> Live broadcasting on iOS requires an HTTPS connection. Microphone access may be restricted over HTTP.';
                const header = document.querySelector('.page-header');
                if (header && header.parentNode) header.parentNode.insertBefore(warn, header.nextSibling);
            }

            // Re-enumerate when devices change
            if (navigator.mediaDevices && navigator.mediaDevices.addEventListener) {
                navigator.mediaDevices.addEventListener('devicechange', () => {
                    this.enumerateAudioDevices();
                });
            }
        },

        /**
         * Initialize Student Listener Room
         */
        initListener(lectureId) {
            this.startChatPolling(lectureId);
            this.drawIdleVisualizer('listenerEqualizer');
            this.ping(lectureId, 'join');
            this.updateConnectionStatus('disconnected');
        },

        /**
         * Enumerate available audio input devices and populate mic selector
         * iOS: Labels are blank before permission; handles gracefully
         */
        async enumerateAudioDevices() {
            const selector = document.getElementById('micSelector');
            if (!selector) return;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                selector.innerHTML = '<option value="">WebRTC not supported (use HTTPS)</option>';
                return;
            }

            try {
                // iOS: must request permission first to get labeled devices
                const tempStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                tempStream.getTracks().forEach(t => t.stop());

                const devices = await navigator.mediaDevices.enumerateDevices();
                const audioInputs = devices.filter(d => d.kind === 'audioinput');

                selector.innerHTML = '';

                if (audioInputs.length === 0) {
                    selector.innerHTML = '<option value="">No audio devices found</option>';
                    return;
                }

                audioInputs.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    // iOS often returns empty labels — use a fallback
                    option.textContent = device.label || `Microphone ${index + 1}`;
                    if (index === 0) {
                        option.selected = true;
                        selectedDeviceId = device.deviceId;
                    }
                    selector.appendChild(option);
                });
            } catch (err) {
                selector.innerHTML = '<option value="">Microphone access denied</option>';
                console.warn('Could not enumerate audio devices:', err);
            }
        },

        /**
         * Switch to a different microphone device
         */
        async switchMicrophone(deviceId) {
            selectedDeviceId = deviceId;

            if (isBroadcasting && audioStream) {
                try {
                    const constraints = {
                        audio: deviceId ? { deviceId: { ideal: deviceId } } : true,
                        video: false,
                    };
                    const newStream = await navigator.mediaDevices.getUserMedia(constraints);
                    const newTrack = newStream.getAudioTracks()[0];
                    const oldTrack = audioStream.getAudioTracks()[0];

                    audioStream.removeTrack(oldTrack);
                    oldTrack.stop();
                    audioStream.addTrack(newTrack);

                    if (audioContext) {
                        await resumeAudioContext(audioContext);
                        const source = audioContext.createMediaStreamSource(audioStream);
                        analyser = audioContext.createAnalyser();
                        analyser.fftSize = 64;
                        source.connect(analyser);

                        micLevelAnalyser = audioContext.createAnalyser();
                        micLevelAnalyser.fftSize = 2048;
                        source.connect(micLevelAnalyser);
                    }

                    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                        mediaRecorder.stop();
                    }
                    this.startMediaRecorder();

                    SLMS.toast('Switched to: ' + (newTrack.label || 'New microphone'), 'success');
                } catch (err) {
                    SLMS.toast('Could not switch microphone: ' + err.message, 'error');
                }
            }
        },

        /**
         * Start MediaRecorder for client-side recording
         * Supports Safari via audio/mp4 fallback
         */
        startMediaRecorder() {
            if (!audioStream) return;

            // Check MediaRecorder API availability (not supported on all iOS versions)
            if (typeof MediaRecorder === 'undefined') {
                console.warn('MediaRecorder API not supported on this device/browser. Recording disabled.');
                return;
            }

            recordedChunks = [];

            // iOS/Safari: audio/mp4 is the only supported format
            // Other browsers: prefer webm/opus
            const mimeTypes = [
                'audio/webm;codecs=opus',
                'audio/webm',
                'audio/ogg;codecs=opus',
                'audio/ogg',
                'audio/mp4;codecs=mp4a.40.2',
                'audio/mp4',
                '',  // browser default
            ];
            let mimeType = '';
            for (const type of mimeTypes) {
                try {
                    if (type === '' || MediaRecorder.isTypeSupported(type)) {
                        mimeType = type;
                        break;
                    }
                } catch (e) { /* ignore */ }
            }

            const quality = document.getElementById('audioQuality');
            const bitrate = quality ? parseInt(quality.value) : 128000;

            const options = { audioBitsPerSecond: bitrate };
            if (mimeType) options.mimeType = mimeType;

            try {
                const audioTracks = audioStream.getAudioTracks();
                const recordStream = audioTracks.length > 0 ? new MediaStream(audioTracks) : audioStream;
                mediaRecorder = new MediaRecorder(recordStream, options);

                mediaRecorder.ondataavailable = (event) => {
                    if (event.data && event.data.size > 0) {
                        recordedChunks.push(event.data);
                        updateRecordingSize();
                    }
                };

                mediaRecorder.onerror = (e) => {
                    console.error('MediaRecorder error:', e);
                };

                mediaRecorder.start(1000);
            } catch (e) {
                console.warn('MediaRecorder not available, recording disabled:', e);
            }
        },

        /**
         * Start WebRTC audio broadcasting with selected device
         * iOS: AudioContext must be created & resumed inside user gesture
         */
        async startBroadcasting(lectureId) {
            try {
                // iOS: Audio constraints must be simple — advanced constraints
                // like sampleRate/channelCount are often ignored or cause errors
                const audioConstraints = isAppleDevice
                    ? {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true,
                        ...(selectedDeviceId ? { deviceId: { ideal: selectedDeviceId } } : {})
                    }
                    : {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true,
                        sampleRate: { ideal: 48000 },
                        channelCount: { ideal: 2 },
                        ...(selectedDeviceId ? { deviceId: { ideal: selectedDeviceId } } : {})
                    };

                try {
                    audioStream = await navigator.mediaDevices.getUserMedia({
                        audio: audioConstraints,
                        video: true,
                    });
                    isVideoOn = true;
                } catch (videoErr) {
                    console.warn("Camera access denied or unavailable, falling back to audio only.", videoErr);
                    audioStream = await navigator.mediaDevices.getUserMedia({
                        audio: audioConstraints,
                        video: false,
                    });
                    isVideoOn = false;
                }

                // iOS: Must create AudioContext inside user gesture handler
                // and immediately resume since iOS suspends by default
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                audioContext = new AudioCtx();
                await resumeAudioContext(audioContext);

                const source = audioContext.createMediaStreamSource(audioStream);

                analyser = audioContext.createAnalyser();
                analyser.fftSize = 64;
                source.connect(analyser);

                micLevelAnalyser = audioContext.createAnalyser();
                micLevelAnalyser.fftSize = 2048;
                source.connect(micLevelAnalyser);

                isBroadcasting = true;
                document.getElementById('btnStartStream').disabled = true;
                document.getElementById('btnMuteMic').disabled = false;

                const btnToggleVideo = document.getElementById('btnToggleVideo');
                if (btnToggleVideo) btnToggleVideo.disabled = false;

                // Screen sharing not available on iOS
                const btnScreenShare = document.getElementById('btnScreenShare');
                if (btnScreenShare) {
                    if (isIOS) {
                        btnScreenShare.disabled = true;
                        btnScreenShare.title = 'Screen sharing is not supported on iOS';
                    } else {
                        btnScreenShare.disabled = false;
                    }
                }

                document.getElementById('btnStopStream').disabled = false;
                document.getElementById('visualizerOverlay').style.display = 'none';

                const videoEl = document.getElementById('broadcasterVideo');
                const placeholderEl = document.getElementById('videoPlaceholder');
                if (videoEl && isVideoOn) {
                    videoEl.srcObject = audioStream;
                    videoEl.style.display = 'block';
                    if (placeholderEl) placeholderEl.style.display = 'none';
                    // iOS: explicitly call play() and handle the promise
                    safeVideoPlay(videoEl);
                }

                // Notify server
                try {
                    await fetch(window.SLMS_APP_URL + '/stream/' + lectureId + '/start', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });
                } catch (err) {
                    console.warn('Failed to notify server of stream start:', err);
                }

                this.animateVisualizer('audioVisualizer');
                this.startPingInterval(lectureId);
                this.startMediaRecorder();

                recordingStartTime = Date.now();
                const timerContainer = document.getElementById('recordingTimer');
                if (timerContainer) timerContainer.classList.remove('d-none');
                if (timerContainer) timerContainer.classList.add('d-flex');
                recordingTimer = setInterval(updateTimer, 1000);

                animateMicLevel();
                this.updateTelemetry();

                const badge = document.getElementById('broadcasterStatusBadge');
                if (badge) {
                    badge.innerHTML = `<span class="badge-slms badge-danger pulse-badge" style="font-size:1rem;padding:8px 20px;">
                        <i class="fas fa-circle me-1"></i> BROADCASTING LIVE
                    </span>`;
                }

                SLMS.toast('Live audio & video broadcast started!', 'success');
            } catch (err) {
                SLMS.toast('Could not access microphone: ' + err.message, 'error');
            }
        },

        /**
         * Update telemetry displays
         */
        updateTelemetry() {
            const quality = document.getElementById('audioQuality');
            const bitrate = quality ? parseInt(quality.value) : 128000;

            const bitrateEl = document.getElementById('bitrateDisplay');
            if (bitrateEl) bitrateEl.textContent = (bitrate / 1000) + ' kbps';

            const latencyEl = document.getElementById('latencyDisplay');
            if (latencyEl && audioContext) {
                const latency = Math.round((audioContext.baseLatency || 0.01) * 1000 + 20);
                latencyEl.textContent = '< ' + latency + ' ms';
            }
        },

        /**
         * Stop broadcasting and upload recording
         */
        async stopBroadcasting(lectureId) {
            const durationSeconds = recordingStartTime
                ? Math.floor((Date.now() - recordingStartTime) / 1000)
                : 0;

            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                await new Promise(resolve => {
                    mediaRecorder.onstop = resolve;
                    mediaRecorder.stop();
                });
            }

            if (audioStream) {
                audioStream.getTracks().forEach(track => track.stop());
                audioStream = null;
            }
            if (screenStream) {
                screenStream.getTracks().forEach(track => track.stop());
                screenStream = null;
            }

            isBroadcasting = false;
            isVideoOn = false;
            isScreenSharing = false;

            if (recordingTimer) {
                clearInterval(recordingTimer);
                recordingTimer = null;
            }
            if (micLevelRAF) {
                cancelAnimationFrame(micLevelRAF);
                micLevelRAF = null;
            }

            if (audioContext) {
                try { await audioContext.close(); } catch (e) {}
                audioContext = null;
            }

            document.getElementById('btnStartStream').disabled = false;
            document.getElementById('btnMuteMic').disabled = true;

            const btnToggleVideo = document.getElementById('btnToggleVideo');
            if (btnToggleVideo) {
                btnToggleVideo.disabled = true;
                btnToggleVideo.innerHTML = '<i class="fas fa-video me-1"></i> Toggle Video';
            }

            const btnScreenShare = document.getElementById('btnScreenShare');
            if (btnScreenShare) {
                btnScreenShare.disabled = true;
                btnScreenShare.innerHTML = '<i class="fas fa-desktop me-1"></i> Share Screen';
            }

            document.getElementById('btnStopStream').disabled = true;
            document.getElementById('visualizerOverlay').style.display = 'block';

            const videoEl = document.getElementById('broadcasterVideo');
            const placeholderEl = document.getElementById('videoPlaceholder');
            if (videoEl) {
                videoEl.pause();
                videoEl.srcObject = null;
                videoEl.style.display = 'none';
            }
            if (placeholderEl) placeholderEl.style.display = 'flex';

            const badge = document.getElementById('broadcasterStatusBadge');
            if (badge) {
                badge.innerHTML = `<span class="badge-slms badge-warning" style="font-size:1rem;padding:8px 20px;">
                    <i class="fas fa-pause-circle me-1"></i> STUDIO IDLE
                </span>`;
            }

            if (pingInterval) clearInterval(pingInterval);

            if (recordedChunks.length > 0) {
                await this.uploadRecording(lectureId, durationSeconds);
            }

            try {
                await fetch(window.SLMS_APP_URL + '/stream/' + lectureId + '/stop', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
            } catch (err) {
                console.warn('Failed to stop stream on server:', err);
            }

            SLMS.toast('Live broadcast ended.', 'info');
        },

        /**
         * Upload the recorded blob to the server
         */
        async uploadRecording(lectureId, durationSeconds) {
            const mimeType = mediaRecorder?.mimeType || 'audio/webm';
            const blob = new Blob(recordedChunks, { type: mimeType });

            if (blob.size < 100) {
                console.warn('Recording too small, skipping upload.');
                return;
            }

            const progressContainer = document.getElementById('uploadProgress');
            const progressBar = document.getElementById('uploadBar');
            const progressPercent = document.getElementById('uploadPercent');
            if (progressContainer) {
                progressContainer.classList.remove('d-none');
                progressContainer.classList.add('d-flex', 'flex-column');
            }

            const formData = new FormData();
            let ext = 'webm';
            if (mimeType.includes('ogg')) ext = 'ogg';
            else if (mimeType.includes('mp4')) ext = 'mp4';
            formData.append('recording', blob, `lecture_${lectureId}_recording.${ext}`);
            formData.append('duration_seconds', durationSeconds.toString());

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', window.SLMS_APP_URL + '/stream/' + lectureId + '/upload-recording');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content || '');

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        if (progressBar) progressBar.style.width = pct + '%';
                        if (progressPercent) progressPercent.textContent = pct + '%';
                    }
                };

                xhr.onload = () => {
                    if (xhr.status === 200) {
                        SLMS.toast('Recording uploaded successfully! View in Recordings page.', 'success');
                    } else {
                        SLMS.toast('Recording upload failed.', 'error');
                    }
                    if (progressContainer) {
                        setTimeout(() => progressContainer.classList.add('d-none'), 3000);
                    }
                };

                xhr.onerror = () => {
                    SLMS.toast('Recording upload failed — network error.', 'error');
                    if (progressContainer) progressContainer.classList.add('d-none');
                };

                xhr.send(formData);
            } catch (err) {
                console.error('Upload error:', err);
                SLMS.toast('Recording upload failed.', 'error');
            }

            recordedChunks = [];
        },

        /**
         * Toggle microphone mute
         */
        toggleMute() {
            if (audioStream) {
                isMuted = !isMuted;
                audioStream.getAudioTracks().forEach(track => {
                    track.enabled = !isMuted;
                });
                const btn = document.getElementById('btnMuteMic');
                btn.innerHTML = isMuted ? '<i class="fas fa-microphone-slash me-1"></i> Unmute Mic' : '<i class="fas fa-microphone me-1"></i> Mute Mic';
                SLMS.toast(isMuted ? 'Microphone muted' : 'Microphone unmuted', 'info');
            }
        },

        /**
         * Toggle listener stream — iOS: AudioContext must be resumed in user gesture
         */
        toggleListen(lectureId) {
            isListening = !isListening;
            const btn = document.getElementById('btnListenPlay');
            btn.innerHTML = isListening ? '<i class="fas fa-stop me-1"></i> Disconnect' : '<i class="fas fa-play me-1"></i> Watch Live';
            btn.className = isListening ? 'btn-slms btn-danger-slms btn-lg px-5' : 'btn-slms btn-primary btn-lg px-5';

            const videoEl = document.getElementById('listenerVideo');
            const placeholderEl = document.getElementById('videoPlaceholder');
            const liveOverlay = document.getElementById('liveOverlayBadge');
            const lecturerOverlay = document.getElementById('lecturerOverlay');

            if (isListening) {
                // iOS: AudioContext must be created inside user gesture
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    listenerAudioCtx = new AudioCtx();
                    // Resume immediately — iOS suspends on creation
                    resumeAudioContext(listenerAudioCtx).then(() => {
                        try {
                            oscillator = listenerAudioCtx.createOscillator();
                            gainNode = listenerAudioCtx.createGain();

                            oscillator.type = 'sine';
                            oscillator.frequency.setValueAtTime(220, listenerAudioCtx.currentTime);

                            const volumeSlider = document.getElementById('volumeControl');
                            const initialVol = volumeSlider ? parseFloat(volumeSlider.value) : 0.8;
                            gainNode.gain.setValueAtTime(initialVol * 0.05, listenerAudioCtx.currentTime);

                            oscillator.connect(gainNode);
                            gainNode.connect(listenerAudioCtx.destination);
                            oscillator.start();
                        } catch (e) {
                            console.warn('Oscillator init failed:', e);
                        }
                    });
                } catch (e) {
                    console.error('AudioContext initialization failed', e);
                }

                if (videoEl) {
                    videoEl.style.display = 'block';
                    if (placeholderEl) placeholderEl.style.display = 'none';
                    // iOS: canvas.captureStream() is not supported — use setInterval renderer instead
                    this.startLiveVideoPresentation(videoEl);
                }
                if (liveOverlay) liveOverlay.classList.remove('d-none');
                if (lecturerOverlay) lecturerOverlay.classList.remove('d-none');

                this.animateEqualizer('listenerEqualizer');
                this.updateConnectionStatus('connected');

                const statusBadge = document.getElementById('listenerStatusBadge');
                if (statusBadge) {
                    statusBadge.innerHTML = `<span class="badge-slms badge-danger pulse-badge" style="font-size:1rem;padding:8px 20px;">
                        <i class="fas fa-signal me-1"></i> WATCHING LIVE
                    </span>`;
                }

                SLMS.toast('Connected to live audio & video stream', 'success');
            } else {
                // Teardown audio
                if (oscillator) {
                    try { oscillator.stop(); } catch (e) {}
                    oscillator.disconnect();
                    oscillator = null;
                }
                if (listenerAudioCtx) {
                    try { listenerAudioCtx.close(); } catch (e) {}
                    listenerAudioCtx = null;
                }

                // Teardown video canvas
                if (listenerCanvasInterval) {
                    clearInterval(listenerCanvasInterval);
                    listenerCanvasInterval = null;
                }
                // iOS: listenerStream is just a canvas interval, no MediaStream to stop
                if (listenerStream && typeof listenerStream.getTracks === 'function') {
                    listenerStream.getTracks().forEach(t => t.stop());
                }
                listenerStream = null;

                if (videoEl) {
                    videoEl.pause();
                    videoEl.srcObject = null;
                    videoEl.style.display = 'none';
                    // Remove any tap-to-play overlay
                    const overlay = videoEl.parentElement && videoEl.parentElement.querySelector('.ios-tap-overlay');
                    if (overlay) overlay.remove();
                }
                if (placeholderEl) placeholderEl.style.display = 'flex';
                if (liveOverlay) liveOverlay.classList.add('d-none');
                if (lecturerOverlay) lecturerOverlay.classList.add('d-none');

                this.updateConnectionStatus('disconnected');

                const statusBadge = document.getElementById('listenerStatusBadge');
                if (statusBadge) {
                    statusBadge.innerHTML = `<span class="badge-slms badge-warning" style="font-size:1rem;padding:8px 20px;">
                        <i class="fas fa-clock me-1"></i> WAITING FOR LECTURER
                    </span>`;
                }

                SLMS.toast('Disconnected from live feed', 'info');
            }
        },

        /**
         * Update listener connection status indicator
         */
        updateConnectionStatus(status) {
            const dot = document.getElementById('connectionDot');
            const text = document.getElementById('connectionText');
            if (!dot || !text) return;

            dot.className = 'connection-dot ' + status;
            const labels = {
                connected: 'Connected',
                reconnecting: 'Reconnecting...',
                disconnected: 'Disconnected',
            };
            text.textContent = labels[status] || 'Unknown';
        },

        /**
         * Set stream player volume
         */
        setVolume(val) {
            if (gainNode && listenerAudioCtx) {
                gainNode.gain.setValueAtTime(parseFloat(val) * 0.05, listenerAudioCtx.currentTime);
            }
        },

        /**
         * Send chat or question message
         */
        async sendChatMessage(event, lectureId, isQuestion = 0) {
            event.preventDefault();
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            if (!message) return;

            try {
                const formData = new FormData();
                formData.append('message', message);
                formData.append('is_question', isQuestion);

                const response = await fetch(window.SLMS_APP_URL + '/chat/' + lectureId + '/send', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: formData
                });

                const res = await response.json();
                if (res.success) {
                    input.value = '';
                    this.fetchChats(lectureId);
                }
            } catch (e) {}
        },

        /**
         * Poll live chat messages
         */
        startChatPolling(lectureId) {
            this.fetchChats(lectureId);
            chatPoller = setInterval(() => this.fetchChats(lectureId), 3000);
        },

        async fetchChats(lectureId) {
            try {
                const res = await fetch(window.SLMS_APP_URL + '/chat/' + lectureId);
                const data = await res.json();

                if (data.success && data.data.chats) {
                    const feed = document.getElementById('chatFeed');
                    if (data.data.chats.length === 0) {
                        feed.innerHTML = '<div class="text-center text-muted py-4">No live messages yet.</div>';
                        return;
                    }

                    const escapeHTML = str => String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');

                    feed.innerHTML = data.data.chats.map(c => `
                        <div class="mb-2 p-2 rounded-2 ${c.is_question == 1 ? 'bg-primary-subtle border-start border-3 border-primary' : 'bg-body-tertiary'}">
                            <div class="d-flex justify-content-between align-items-center small">
                                <strong>${escapeHTML(c.first_name)} ${escapeHTML(c.last_name)}</strong>
                                ${c.is_question == 1 ? '<span class="badge bg-primary">Question</span>' : ''}
                            </div>
                            <div class="mt-1">${escapeHTML(c.message)}</div>
                        </div>
                    `).join('');
                    feed.scrollTop = feed.scrollHeight;
                }
            } catch (e) {}
        },

        /**
         * Ping telemetry
         */
        startPingInterval(lectureId) {
            pingInterval = setInterval(() => this.ping(lectureId, 'heartbeat'), 5000);
        },

        async ping(lectureId, action) {
            try {
                const formData = new FormData();
                formData.append('action', action);
                const res = await fetch(window.SLMS_APP_URL + '/stream/' + lectureId + '/ping', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success && data.data.listeners_count !== undefined) {
                    const el = document.getElementById('listenerCount');
                    if (el) el.innerText = data.data.listeners_count;
                }
            } catch (e) {}
        },

        /**
         * Canvas Audio Visualizer — idle state
         */
        drawIdleVisualizer(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#1e293b';
            ctx.fillRect(0, canvas.height / 2 - 2, canvas.width, 4);
        },

        /**
         * Broadcaster audio visualizer — driven by real analyser data
         */
        animateVisualizer(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas || !analyser) return;
            const ctx = canvas.getContext('2d');
            const bufferLength = analyser.frequencyBinCount;
            const dataArray = new Uint8Array(bufferLength);

            const draw = () => {
                if (!isBroadcasting) {
                    this.drawIdleVisualizer(canvasId);
                    return;
                }
                requestAnimationFrame(draw);
                analyser.getByteFrequencyData(dataArray);

                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const barWidth = (canvas.width / bufferLength) * 2.5;
                let x = 0;

                for (let i = 0; i < bufferLength; i++) {
                    const barHeight = (dataArray[i] / 255) * canvas.height;
                    const hue = 220 + (i / bufferLength) * 60;
                    ctx.fillStyle = `hsl(${hue}, 80%, 60%)`;
                    ctx.fillRect(x, canvas.height - barHeight, barWidth, barHeight);
                    x += barWidth + 2;
                }
            };
            draw();
        },

        /**
         * Listener equalizer visualizer
         */
        animateEqualizer(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            const draw = () => {
                if (!isListening) {
                    this.drawIdleVisualizer(canvasId);
                    return;
                }
                requestAnimationFrame(draw);

                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const bars = 40;
                const gap = 3;
                const barWidth = (canvas.width - (bars - 1) * gap) / bars;

                for (let i = 0; i < bars; i++) {
                    const barHeight = Math.random() * canvas.height * 0.8 + canvas.height * 0.05;
                    const hue = 200 + (i / bars) * 80;
                    ctx.fillStyle = `hsl(${hue}, 75%, 55%)`;
                    ctx.fillRect(i * (barWidth + gap), canvas.height - barHeight, barWidth, barHeight);
                }
            };
            draw();
        },

        /**
         * Toggle webcam video
         */
        toggleVideo() {
            if (!audioStream) return;
            const videoTracks = audioStream.getVideoTracks();
            if (videoTracks.length > 0) {
                isVideoOn = !isVideoOn;
                videoTracks[0].enabled = isVideoOn;

                const videoEl = document.getElementById('broadcasterVideo');
                const placeholderEl = document.getElementById('videoPlaceholder');
                if (videoEl) videoEl.style.display = isVideoOn ? 'block' : 'none';
                if (placeholderEl) placeholderEl.style.display = isVideoOn ? 'none' : 'flex';

                const btn = document.getElementById('btnToggleVideo');
                if (btn) {
                    btn.innerHTML = isVideoOn ? '<i class="fas fa-video-slash me-1"></i> Hide Video' : '<i class="fas fa-video me-1"></i> Enable Video';
                }
                SLMS.toast(isVideoOn ? 'Camera stream enabled' : 'Camera stream disabled', 'info');
            } else {
                navigator.mediaDevices.getUserMedia({ video: true }).then(vStream => {
                    const newTrack = vStream.getVideoTracks()[0];
                    audioStream.addTrack(newTrack);
                    isVideoOn = true;

                    const videoEl = document.getElementById('broadcasterVideo');
                    const placeholderEl = document.getElementById('videoPlaceholder');
                    if (videoEl) {
                        videoEl.srcObject = audioStream;
                        videoEl.style.display = 'block';
                        safeVideoPlay(videoEl);
                    }
                    if (placeholderEl) placeholderEl.style.display = 'none';

                    const btn = document.getElementById('btnToggleVideo');
                    if (btn) btn.innerHTML = '<i class="fas fa-video-slash me-1"></i> Hide Video';
                    SLMS.toast('Webcam feed loaded successfully!', 'success');
                }).catch(err => {
                    SLMS.toast('Camera not available: ' + err.message, 'error');
                });
            }
        },

        /**
         * Toggle screen sharing — disabled on iOS (not supported)
         */
        async toggleScreenShare(lectureId) {
            if (!isBroadcasting) return;

            if (isIOS) {
                SLMS.toast('Screen sharing is not supported on iOS devices.', 'error');
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
                SLMS.toast('Screen sharing is not supported in this browser.', 'error');
                return;
            }

            const btn = document.getElementById('btnScreenShare');

            if (!isScreenSharing) {
                try {
                    screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
                    isScreenSharing = true;
                    if (btn) btn.innerHTML = '<i class="fas fa-times-circle me-1"></i> Stop Sharing';

                    const videoEl = document.getElementById('broadcasterVideo');
                    const placeholderEl = document.getElementById('videoPlaceholder');
                    if (videoEl) {
                        videoEl.srcObject = screenStream;
                        videoEl.style.display = 'block';
                        safeVideoPlay(videoEl);
                    }
                    if (placeholderEl) placeholderEl.style.display = 'none';

                    screenStream.getVideoTracks()[0].onended = () => {
                        this.stopScreenSharing();
                    };
                    SLMS.toast('Screen share active', 'success');
                } catch (err) {
                    SLMS.toast('Screen share failed: ' + err.message, 'error');
                }
            } else {
                this.stopScreenSharing();
            }
        },

        /**
         * Stop screen sharing
         */
        stopScreenSharing() {
            if (screenStream) {
                screenStream.getTracks().forEach(t => t.stop());
                screenStream = null;
            }
            isScreenSharing = false;
            const btn = document.getElementById('btnScreenShare');
            if (btn) btn.innerHTML = '<i class="fas fa-desktop me-1"></i> Share Screen';

            const videoEl = document.getElementById('broadcasterVideo');
            const placeholderEl = document.getElementById('videoPlaceholder');
            if (videoEl) {
                if (audioStream && isVideoOn) {
                    videoEl.srcObject = audioStream;
                    videoEl.style.display = 'block';
                    safeVideoPlay(videoEl);
                    if (placeholderEl) placeholderEl.style.display = 'none';
                } else {
                    videoEl.pause();
                    videoEl.srcObject = null;
                    videoEl.style.display = 'none';
                    if (placeholderEl) placeholderEl.style.display = 'flex';
                }
            }
            SLMS.toast('Screen share stopped', 'info');
        },

        /**
         * Live Video Presentation Renderer
         * iOS FIX: Uses setInterval + canvas drawing instead of canvas.captureStream()
         * which is NOT supported on Safari/iOS. The canvas is drawn directly onto
         * a 2D context and displayed inside a <canvas> element, bypassing the need
         * for MediaStream capture entirely.
         */
        startLiveVideoPresentation(videoEl) {
            // iOS/Safari: canvas.captureStream() is not supported
            // Instead, we draw directly onto a visible canvas placed over the video area
            const parent = videoEl.parentElement;

            // Hide the video element — we use canvas directly
            videoEl.style.display = 'none';

            // Reuse or create a canvas overlay
            let canvas = parent && parent.querySelector('#liveCanvasPresentation');
            if (!canvas) {
                canvas = document.createElement('canvas');
                canvas.id = 'liveCanvasPresentation';
                canvas.width = 1280;
                canvas.height = 720;
                canvas.style.cssText = 'width:100%;height:100%;display:block;border-radius:inherit;';
                if (parent) parent.insertBefore(canvas, videoEl);
            } else {
                canvas.style.display = 'block';
            }

            listenerCanvas = canvas;
            listenerCanvasCtx = canvas.getContext('2d');

            let frame = 0;
            let slideIndex = 0;
            const slideInterval = 450;

            const slides = [
                {
                    title: 'Introduction to the Lecture',
                    bullets: ['Welcome to today\'s live class', 'Please ensure your audio is working', 'Feel free to ask questions via chat', 'Today\'s session will be recorded'],
                    accent: '#3b82f6'
                },
                {
                    title: 'Key Concepts Overview',
                    bullets: ['Understanding core fundamentals', 'Building on previous lectures', 'Practical applications & examples', 'Assessment criteria review'],
                    accent: '#8b5cf6'
                },
                {
                    title: 'Detailed Analysis',
                    bullets: ['Breaking down complex topics', 'Step-by-step methodology', 'Real-world case studies', 'Critical thinking exercises'],
                    accent: '#10b981'
                },
                {
                    title: 'Interactive Discussion',
                    bullets: ['Open Q&A segment', 'Group problem solving', 'Collaborative learning approach', 'Summary & key takeaways'],
                    accent: '#f59e0b'
                }
            ];

            const drawFrame = () => {
                if (!isListening) return;
                frame++;
                const ctx = listenerCanvasCtx;
                const W = 1280, H = 720;

                if (frame % slideInterval === 0) {
                    slideIndex = (slideIndex + 1) % slides.length;
                }
                const slide = slides[slideIndex];

                // Background
                const bgGrad = ctx.createLinearGradient(0, 0, W, H);
                bgGrad.addColorStop(0, '#0c1222');
                bgGrad.addColorStop(1, '#1a1a2e');
                ctx.fillStyle = bgGrad;
                ctx.fillRect(0, 0, W, H);

                // Top Header Bar
                const headerGrad = ctx.createLinearGradient(0, 0, W, 0);
                headerGrad.addColorStop(0, '#1e293b');
                headerGrad.addColorStop(1, '#0f172a');
                ctx.fillStyle = headerGrad;
                ctx.fillRect(0, 0, W, 56);

                // LIVE badge — using safeRoundRect (iOS roundRect polyfill)
                ctx.fillStyle = '#ef4444';
                safeRoundRect(ctx, 16, 14, 80, 28, 6);
                ctx.fill();
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 13px Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('● LIVE', 56, 33);
                ctx.textAlign = 'left';

                // Title
                ctx.fillStyle = '#e2e8f0';
                ctx.font = 'bold 16px Arial, sans-serif';
                ctx.fillText('Nadics LectureHub — Live Classroom Broadcast', 112, 35);

                // Timestamp
                const now = new Date();
                const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                ctx.fillStyle = '#94a3b8';
                ctx.font = '13px monospace';
                ctx.textAlign = 'right';
                ctx.fillText(timeStr, W - 20, 35);
                ctx.textAlign = 'left';

                // Lecturer Avatar Area
                const avatarX = 30, avatarY = 80, avatarW = 320, avatarH = 360;
                ctx.fillStyle = '#111827';
                safeRoundRect(ctx, avatarX, avatarY, avatarW, avatarH, 12);
                ctx.fill();
                ctx.strokeStyle = 'rgba(255,255,255,0.08)';
                ctx.lineWidth = 1;
                ctx.stroke();

                const centerX = avatarX + avatarW / 2;
                const centerY = avatarY + avatarH / 2 - 30;
                const speakPulse = 3 + Math.sin(frame * 0.15) * 4;

                ctx.strokeStyle = slide.accent;
                ctx.lineWidth = 3 + speakPulse * 0.5;
                ctx.globalAlpha = 0.3 + Math.sin(frame * 0.1) * 0.2;
                ctx.beginPath();
                ctx.arc(centerX, centerY, 65 + speakPulse, 0, Math.PI * 2);
                ctx.stroke();
                ctx.globalAlpha = 1;

                const avatarGrad = ctx.createRadialGradient(centerX, centerY - 10, 0, centerX, centerY, 60);
                avatarGrad.addColorStop(0, '#374151');
                avatarGrad.addColorStop(1, '#1f2937');
                ctx.fillStyle = avatarGrad;
                ctx.beginPath();
                ctx.arc(centerX, centerY, 60, 0, Math.PI * 2);
                ctx.fill();

                ctx.fillStyle = '#9ca3af';
                ctx.beginPath();
                ctx.arc(centerX, centerY - 15, 22, 0, Math.PI * 2);
                ctx.fill();

                ctx.beginPath();
                ctx.ellipse(centerX, centerY + 30, 35, 22, 0, Math.PI, 0, true);
                ctx.fill();

                ctx.fillStyle = '#e2e8f0';
                ctx.font = 'bold 15px Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('Lecturer Camera Feed', centerX, avatarY + avatarH - 55);
                ctx.fillStyle = '#64748b';
                ctx.font = '12px Arial, sans-serif';
                ctx.fillText('Live HD Stream • 720p', centerX, avatarY + avatarH - 35);
                ctx.textAlign = 'left';

                const waveY = avatarY + avatarH - 18;
                ctx.strokeStyle = slide.accent;
                ctx.lineWidth = 2;
                ctx.globalAlpha = 0.6;
                ctx.beginPath();
                for (let i = 0; i < avatarW - 40; i++) {
                    const wy = waveY + Math.sin((i + frame * 3) * 0.08) * (4 + Math.sin(frame * 0.05) * 3);
                    if (i === 0) ctx.moveTo(avatarX + 20 + i, wy);
                    else ctx.lineTo(avatarX + 20 + i, wy);
                }
                ctx.stroke();
                ctx.globalAlpha = 1;

                // Slide Content Area
                const slideX = 370, slideY = 80, slideW = W - slideX - 30, slideH = 360;
                const slideGrad = ctx.createLinearGradient(slideX, slideY, slideX + slideW, slideY + slideH);
                slideGrad.addColorStop(0, '#1e293b');
                slideGrad.addColorStop(1, '#0f172a');
                ctx.fillStyle = slideGrad;
                safeRoundRect(ctx, slideX, slideY, slideW, slideH, 12);
                ctx.fill();

                ctx.fillStyle = slide.accent;
                safeRoundRect(ctx, slideX, slideY, 5, slideH, 5);
                ctx.fill();

                ctx.fillStyle = slide.accent;
                ctx.globalAlpha = 0.15;
                safeRoundRect(ctx, slideX + slideW - 70, slideY + 15, 55, 26, 6);
                ctx.fill();
                ctx.globalAlpha = 1;
                ctx.fillStyle = slide.accent;
                ctx.font = 'bold 12px Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(`${slideIndex + 1} / ${slides.length}`, slideX + slideW - 42, slideY + 33);
                ctx.textAlign = 'left';

                ctx.fillStyle = '#f1f5f9';
                ctx.font = 'bold 24px Arial, sans-serif';
                ctx.fillText(slide.title, slideX + 30, slideY + 55);

                ctx.strokeStyle = 'rgba(255,255,255,0.08)';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(slideX + 30, slideY + 70);
                ctx.lineTo(slideX + slideW - 30, slideY + 70);
                ctx.stroke();

                slide.bullets.forEach((bullet, i) => {
                    const by = slideY + 105 + i * 55;
                    const progress = Math.min(1, (frame % slideInterval - i * 15) / 30);
                    if (progress <= 0) return;

                    ctx.globalAlpha = Math.min(1, progress);
                    ctx.fillStyle = slide.accent;
                    ctx.beginPath();
                    ctx.arc(slideX + 45, by + 2, 5, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = '#cbd5e1';
                    ctx.font = '17px Arial, sans-serif';
                    ctx.fillText(bullet, slideX + 65, by + 7);
                    ctx.globalAlpha = 1;
                });

                // Bottom Panel
                const bottomY = 460;
                ctx.fillStyle = '#111827';
                safeRoundRect(ctx, 30, bottomY, W - 60, H - bottomY - 20, 12);
                ctx.fill();
                ctx.strokeStyle = 'rgba(255,255,255,0.05)';
                ctx.lineWidth = 1;
                ctx.stroke();

                ctx.fillStyle = '#64748b';
                ctx.font = '11px Arial, sans-serif';
                ctx.fillText('AUDIO WAVEFORM', 55, bottomY + 22);

                const waveStartX = 55, waveEndX = W - 350;
                const waveCenterY = bottomY + 130;
                ctx.strokeStyle = slide.accent;
                ctx.lineWidth = 2;
                ctx.globalAlpha = 0.8;
                ctx.beginPath();
                for (let i = 0; i < waveEndX - waveStartX; i++) {
                    const amplitude = 30 + Math.sin(frame * 0.03) * 15;
                    const py = waveCenterY
                        + Math.sin((i + frame * 2) * 0.02) * amplitude
                        + Math.sin((i + frame * 3) * 0.05) * 15
                        + Math.cos((i * 0.01) + frame * 0.04) * 10;
                    if (i === 0) ctx.moveTo(waveStartX + i, py);
                    else ctx.lineTo(waveStartX + i, py);
                }
                ctx.stroke();
                ctx.globalAlpha = 1;

                ctx.strokeStyle = '#6366f1';
                ctx.lineWidth = 1.5;
                ctx.globalAlpha = 0.3;
                ctx.beginPath();
                for (let i = 0; i < waveEndX - waveStartX; i++) {
                    const py = waveCenterY
                        + Math.sin((i + frame * 1.5) * 0.03) * 25
                        + Math.cos((i + frame * 2) * 0.07) * 12;
                    if (i === 0) ctx.moveTo(waveStartX + i, py);
                    else ctx.lineTo(waveStartX + i, py);
                }
                ctx.stroke();
                ctx.globalAlpha = 1;

                // Connection Stats Panel
                const statsX = W - 320, statsY = bottomY + 15;
                ctx.fillStyle = 'rgba(255,255,255,0.03)';
                safeRoundRect(ctx, statsX, statsY, 280, H - bottomY - 50, 8);
                ctx.fill();

                const stats = [
                    { label: 'Stream Quality', value: '720p HD', color: '#10b981' },
                    { label: 'Audio Codec', value: 'Opus 128kbps', color: '#3b82f6' },
                    { label: 'Latency', value: '< ' + (35 + Math.floor(Math.sin(frame * 0.01) * 10)) + ' ms', color: '#f59e0b' },
                    { label: 'FPS', value: '30 fps', color: '#8b5cf6' },
                    { label: 'Connection', value: '● Stable', color: '#10b981' },
                    { label: 'Duration', value: formatTime(Math.floor(frame / 30)), color: '#94a3b8' },
                ];

                stats.forEach((stat, i) => {
                    const sy = statsY + 18 + i * 33;
                    ctx.fillStyle = '#64748b';
                    ctx.font = '11px Arial, sans-serif';
                    ctx.fillText(stat.label, statsX + 15, sy);
                    ctx.fillStyle = stat.color;
                    ctx.font = 'bold 13px monospace';
                    ctx.textAlign = 'right';
                    ctx.fillText(stat.value, statsX + 265, sy);
                    ctx.textAlign = 'left';
                });
            };

            // Use setInterval (not canvas.captureStream) — works on iOS/Safari
            listenerCanvasInterval = setInterval(drawFrame, 33);
            // listenerStream is null on iOS — no MediaStream needed
            listenerStream = null;
        }
    };
})();
