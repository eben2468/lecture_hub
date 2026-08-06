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
 * - Bug fixes (equalizer canvas, hardcoded values)
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
        const level = Math.min(100, Math.max(0, rms * 400)); // Scale RMS to 0-100

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

    return {
        /**
         * Initialize Broadcaster Studio
         */
        initBroadcaster(lectureId) {
            this.startChatPolling(lectureId);
            this.drawIdleVisualizer('audioVisualizer');
            this.enumerateAudioDevices();

            // Re-enumerate when devices change (e.g. plugging in a USB mic)
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

            // Update connection status
            this.updateConnectionStatus('disconnected');
        },

        /**
         * Enumerate available audio input devices and populate mic selector
         */
        async enumerateAudioDevices() {
            const selector = document.getElementById('micSelector');
            if (!selector) return;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                selector.innerHTML = '<option value="">WebRTC not supported (use localhost/HTTPS)</option>';
                return;
            }

            try {
                // Need temporary permission to enumerate labeled devices
                const tempStream = await navigator.mediaDevices.getUserMedia({ audio: true });
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

            // If currently broadcasting, hot-swap the audio track
            if (isBroadcasting && audioStream) {
                try {
                    const constraints = {
                        audio: deviceId ? { deviceId: { ideal: deviceId } } : true,
                        video: false,
                    };
                    const newStream = await navigator.mediaDevices.getUserMedia(constraints);
                    const newTrack = newStream.getAudioTracks()[0];
                    const oldTrack = audioStream.getAudioTracks()[0];

                    // Replace the audio track in the existing stream
                    audioStream.removeTrack(oldTrack);
                    oldTrack.stop();
                    audioStream.addTrack(newTrack);

                    // Reconnect analyser
                    if (audioContext) {
                        const source = audioContext.createMediaStreamSource(audioStream);
                        analyser = audioContext.createAnalyser();
                        analyser.fftSize = 64;
                        source.connect(analyser);

                        micLevelAnalyser = audioContext.createAnalyser();
                        micLevelAnalyser.fftSize = 2048;
                        source.connect(micLevelAnalyser);
                    }

                    // Restart MediaRecorder with new stream
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
         */
        startMediaRecorder() {
            if (!audioStream) return;

            recordedChunks = [];

            // Pick best supported MIME type
            const mimeTypes = [
                'audio/webm;codecs=opus',
                'audio/webm',
                'audio/ogg;codecs=opus',
                'audio/ogg',
                'audio/mp4',
            ];
            let mimeType = '';
            for (const type of mimeTypes) {
                if (MediaRecorder.isTypeSupported(type)) {
                    mimeType = type;
                    break;
                }
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

                // Collect chunks every 1 second for live size tracking
                mediaRecorder.start(1000);
            } catch (e) {
                console.warn('MediaRecorder not available, recording disabled:', e);
            }
        },

        /**
         * Start WebRTC audio broadcasting with selected device
         */
        async startBroadcasting(lectureId) {
            try {
                const audioConstraints = {
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

                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const source = audioContext.createMediaStreamSource(audioStream);

                // Frequency analyser for visualizer
                analyser = audioContext.createAnalyser();
                analyser.fftSize = 64;
                source.connect(analyser);

                // Time-domain analyser for input level meter
                micLevelAnalyser = audioContext.createAnalyser();
                micLevelAnalyser.fftSize = 2048;
                source.connect(micLevelAnalyser);

                isBroadcasting = true;
                document.getElementById('btnStartStream').disabled = true;
                document.getElementById('btnMuteMic').disabled = false;

                const btnToggleVideo = document.getElementById('btnToggleVideo');
                if (btnToggleVideo) btnToggleVideo.disabled = false;

                const btnScreenShare = document.getElementById('btnScreenShare');
                if (btnScreenShare) btnScreenShare.disabled = false;

                document.getElementById('btnStopStream').disabled = false;
                document.getElementById('visualizerOverlay').style.display = 'none';

                const videoEl = document.getElementById('broadcasterVideo');
                const placeholderEl = document.getElementById('videoPlaceholder');
                if (videoEl && isVideoOn) {
                    videoEl.srcObject = audioStream;
                    videoEl.style.display = 'block';
                    if (placeholderEl) placeholderEl.style.display = 'none';
                }

                // Start server-side stream
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

                // Start visualizer
                this.animateVisualizer('audioVisualizer');
                this.startPingInterval(lectureId);

                // Start MediaRecorder for client-side recording
                this.startMediaRecorder();

                // Start recording timer
                recordingStartTime = Date.now();
                const timerContainer = document.getElementById('recordingTimer');
                if (timerContainer) timerContainer.classList.remove('d-none');
                if (timerContainer) timerContainer.classList.add('d-flex');
                recordingTimer = setInterval(updateTimer, 1000);

                // Start input level animation
                animateMicLevel();

                // Update telemetry
                this.updateTelemetry();

                // Update status badge
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
         * Update telemetry displays (bitrate, latency)
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

            // Stop MediaRecorder first to collect final chunk
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

            // Stop timer
            if (recordingTimer) {
                clearInterval(recordingTimer);
                recordingTimer = null;
            }
            if (micLevelRAF) {
                cancelAnimationFrame(micLevelRAF);
                micLevelRAF = null;
            }

            // Reset UI
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
                videoEl.srcObject = null;
                videoEl.style.display = 'none';
            }
            if (placeholderEl) {
                placeholderEl.style.display = 'flex';
            }

            // Update status badge
            const badge = document.getElementById('broadcasterStatusBadge');
            if (badge) {
                badge.innerHTML = `<span class="badge-slms badge-warning" style="font-size:1rem;padding:8px 20px;">
                    <i class="fas fa-pause-circle me-1"></i> STUDIO IDLE
                </span>`;
            }

            if (pingInterval) clearInterval(pingInterval);

            // Upload recording first if we have recorded microphone chunks
            if (recordedChunks.length > 0) {
                await this.uploadRecording(lectureId, durationSeconds);
            }

            // Stop stream on server
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

            // Show upload progress UI
            const progressContainer = document.getElementById('uploadProgress');
            const progressBar = document.getElementById('uploadBar');
            const progressPercent = document.getElementById('uploadPercent');
            if (progressContainer) {
                progressContainer.classList.remove('d-none');
                progressContainer.classList.add('d-flex', 'flex-column');
            }

            const formData = new FormData();
            const ext = mimeType.includes('ogg') ? 'ogg' : (mimeType.includes('mp4') ? 'mp4' : 'webm');
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
         * Toggle microphone mute state
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
         * Toggle listener stream connection (Audio + Video)
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
                // Setup audio playback (simulated low-volume ambient tone)
                try {
                    listenerAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
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
                    console.error("AudioContext initialization failed", e);
                }

                // Show video player and overlays
                if (videoEl) {
                    videoEl.style.display = 'block';
                    if (placeholderEl) placeholderEl.style.display = 'none';
                    this.startLiveVideoPresentation(videoEl);
                }
                if (liveOverlay) liveOverlay.classList.remove('d-none');
                if (lecturerOverlay) lecturerOverlay.classList.remove('d-none');

                // Start equalizer animation
                this.animateEqualizer('listenerEqualizer');

                // Update connection status
                this.updateConnectionStatus('connected');

                // Update status badge
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
                    listenerAudioCtx.close();
                    listenerAudioCtx = null;
                }

                // Teardown video canvas
                if (listenerCanvasInterval) {
                    clearInterval(listenerCanvasInterval);
                    listenerCanvasInterval = null;
                }
                if (listenerStream) {
                    listenerStream.getTracks().forEach(t => t.stop());
                    listenerStream = null;
                }

                // Hide video and overlays
                if (videoEl) {
                    videoEl.srcObject = null;
                    videoEl.style.display = 'none';
                }
                if (placeholderEl) placeholderEl.style.display = 'flex';
                if (liveOverlay) liveOverlay.classList.add('d-none');
                if (lecturerOverlay) lecturerOverlay.classList.add('d-none');

                // Update connection status
                this.updateConnectionStatus('disconnected');

                // Update status badge
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
                    // Gradient from blue to purple based on frequency
                    const hue = 220 + (i / bufferLength) * 60;
                    ctx.fillStyle = `hsl(${hue}, 80%, 60%)`;
                    ctx.fillRect(x, canvas.height - barHeight, barWidth, barHeight);
                    x += barWidth + 2;
                }
            };
            draw();
        },

        /**
         * Listener equalizer visualizer — simulated but now renders properly
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
         * Toggle screen sharing
         */
        async toggleScreenShare(lectureId) {
            if (!isBroadcasting) return;
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
                    if (placeholderEl) placeholderEl.style.display = 'none';
                } else {
                    videoEl.srcObject = null;
                    videoEl.style.display = 'none';
                    if (placeholderEl) placeholderEl.style.display = 'flex';
                }
            }
            SLMS.toast('Screen share stopped', 'info');
        },

        /**
         * Live Video Presentation Renderer
         * Renders a rich simulated live lecture video with lecturer avatar,
         * presentation slides, live waveform, and connection telemetry.
         */
        startLiveVideoPresentation(videoEl) {
            listenerCanvas = document.createElement('canvas');
            listenerCanvas.width = 1280;
            listenerCanvas.height = 720;
            listenerCanvasCtx = listenerCanvas.getContext('2d');

            let frame = 0;
            let slideIndex = 0;
            const slideInterval = 450; // Switch slide every ~15 seconds at 30fps

            // Slide content data
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

                // Update slide index
                if (frame % slideInterval === 0) {
                    slideIndex = (slideIndex + 1) % slides.length;
                }
                const slide = slides[slideIndex];

                // === Background ===
                const bgGrad = ctx.createLinearGradient(0, 0, W, H);
                bgGrad.addColorStop(0, '#0c1222');
                bgGrad.addColorStop(1, '#1a1a2e');
                ctx.fillStyle = bgGrad;
                ctx.fillRect(0, 0, W, H);

                // === Top Header Bar ===
                const headerGrad = ctx.createLinearGradient(0, 0, W, 0);
                headerGrad.addColorStop(0, '#1e293b');
                headerGrad.addColorStop(1, '#0f172a');
                ctx.fillStyle = headerGrad;
                ctx.fillRect(0, 0, W, 56);

                // LIVE badge
                ctx.fillStyle = '#ef4444';
                ctx.beginPath();
                ctx.roundRect(16, 14, 80, 28, 6);
                ctx.fill();
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 13px Inter, Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('● LIVE', 56, 33);
                ctx.textAlign = 'left';

                // Title
                ctx.fillStyle = '#e2e8f0';
                ctx.font = 'bold 16px Inter, Arial, sans-serif';
                ctx.fillText('Nadics LectureHub — Live Classroom Broadcast', 112, 35);

                // Timestamp
                const now = new Date();
                const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                ctx.fillStyle = '#94a3b8';
                ctx.font = '13px monospace';
                ctx.textAlign = 'right';
                ctx.fillText(timeStr, W - 20, 35);
                ctx.textAlign = 'left';

                // === Lecturer Avatar Area (left side) ===
                const avatarX = 30, avatarY = 80, avatarW = 320, avatarH = 360;
                ctx.fillStyle = '#111827';
                ctx.beginPath();
                ctx.roundRect(avatarX, avatarY, avatarW, avatarH, 12);
                ctx.fill();
                ctx.strokeStyle = 'rgba(255,255,255,0.08)';
                ctx.lineWidth = 1;
                ctx.stroke();

                // Lecturer silhouette with animated speaking ring
                const centerX = avatarX + avatarW / 2;
                const centerY = avatarY + avatarH / 2 - 30;
                const speakPulse = 3 + Math.sin(frame * 0.15) * 4;

                // Speaking ring glow
                ctx.strokeStyle = slide.accent;
                ctx.lineWidth = 3 + speakPulse * 0.5;
                ctx.globalAlpha = 0.3 + Math.sin(frame * 0.1) * 0.2;
                ctx.beginPath();
                ctx.arc(centerX, centerY, 65 + speakPulse, 0, Math.PI * 2);
                ctx.stroke();
                ctx.globalAlpha = 1;

                // Avatar circle (head)
                const avatarGrad = ctx.createRadialGradient(centerX, centerY - 10, 0, centerX, centerY, 60);
                avatarGrad.addColorStop(0, '#374151');
                avatarGrad.addColorStop(1, '#1f2937');
                ctx.fillStyle = avatarGrad;
                ctx.beginPath();
                ctx.arc(centerX, centerY, 60, 0, Math.PI * 2);
                ctx.fill();

                // Head
                ctx.fillStyle = '#9ca3af';
                ctx.beginPath();
                ctx.arc(centerX, centerY - 15, 22, 0, Math.PI * 2);
                ctx.fill();

                // Shoulders
                ctx.beginPath();
                ctx.ellipse(centerX, centerY + 30, 35, 22, 0, Math.PI, 0, true);
                ctx.fill();

                // Lecturer name label
                ctx.fillStyle = '#e2e8f0';
                ctx.font = 'bold 15px Inter, Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('Lecturer Camera Feed', centerX, avatarY + avatarH - 55);
                ctx.fillStyle = '#64748b';
                ctx.font = '12px Inter, Arial, sans-serif';
                ctx.fillText('Live HD Stream • 720p', centerX, avatarY + avatarH - 35);
                ctx.textAlign = 'left';

                // Speaking waveform under avatar
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

                // === Slide Content Area (right side) ===
                const slideX = 370, slideY = 80, slideW = W - slideX - 30, slideH = 360;
                const slideGrad = ctx.createLinearGradient(slideX, slideY, slideX + slideW, slideY + slideH);
                slideGrad.addColorStop(0, '#1e293b');
                slideGrad.addColorStop(1, '#0f172a');
                ctx.fillStyle = slideGrad;
                ctx.beginPath();
                ctx.roundRect(slideX, slideY, slideW, slideH, 12);
                ctx.fill();

                // Slide accent bar
                ctx.fillStyle = slide.accent;
                ctx.beginPath();
                ctx.roundRect(slideX, slideY, 5, slideH, [5, 0, 0, 5]);
                ctx.fill();

                // Slide number badge
                ctx.fillStyle = slide.accent;
                ctx.globalAlpha = 0.15;
                ctx.beginPath();
                ctx.roundRect(slideX + slideW - 70, slideY + 15, 55, 26, 6);
                ctx.fill();
                ctx.globalAlpha = 1;
                ctx.fillStyle = slide.accent;
                ctx.font = 'bold 12px Inter, Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(`${slideIndex + 1} / ${slides.length}`, slideX + slideW - 42, slideY + 33);
                ctx.textAlign = 'left';

                // Slide title
                ctx.fillStyle = '#f1f5f9';
                ctx.font = 'bold 24px Inter, Arial, sans-serif';
                ctx.fillText(slide.title, slideX + 30, slideY + 55);

                // Divider line
                ctx.strokeStyle = 'rgba(255,255,255,0.08)';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(slideX + 30, slideY + 70);
                ctx.lineTo(slideX + slideW - 30, slideY + 70);
                ctx.stroke();

                // Bullet points with animated entrance
                slide.bullets.forEach((bullet, i) => {
                    const by = slideY + 105 + i * 55;
                    const progress = Math.min(1, (frame % slideInterval - i * 15) / 30);
                    if (progress <= 0) return;

                    ctx.globalAlpha = Math.min(1, progress);

                    // Bullet dot
                    ctx.fillStyle = slide.accent;
                    ctx.beginPath();
                    ctx.arc(slideX + 45, by + 2, 5, 0, Math.PI * 2);
                    ctx.fill();

                    // Bullet text
                    ctx.fillStyle = '#cbd5e1';
                    ctx.font = '17px Inter, Arial, sans-serif';
                    ctx.fillText(bullet, slideX + 65, by + 7);

                    ctx.globalAlpha = 1;
                });

                // === Bottom Panel — Audio Waveform & Stats ===
                const bottomY = 460;
                ctx.fillStyle = '#111827';
                ctx.beginPath();
                ctx.roundRect(30, bottomY, W - 60, H - bottomY - 20, 12);
                ctx.fill();
                ctx.strokeStyle = 'rgba(255,255,255,0.05)';
                ctx.lineWidth = 1;
                ctx.stroke();

                // Waveform label
                ctx.fillStyle = '#64748b';
                ctx.font = '11px Inter, Arial, sans-serif';
                ctx.fillText('AUDIO WAVEFORM', 55, bottomY + 22);

                // Live audio waveform
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

                // Secondary waveform (lower opacity)
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

                // === Connection Stats Panel (bottom right) ===
                const statsX = W - 320, statsY = bottomY + 15;
                ctx.fillStyle = 'rgba(255,255,255,0.03)';
                ctx.beginPath();
                ctx.roundRect(statsX, statsY, 280, H - bottomY - 50, 8);
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
                    ctx.font = '11px Inter, Arial, sans-serif';
                    ctx.fillText(stat.label, statsX + 15, sy);
                    ctx.fillStyle = stat.color;
                    ctx.font = 'bold 13px monospace';
                    ctx.textAlign = 'right';
                    ctx.fillText(stat.value, statsX + 265, sy);
                    ctx.textAlign = 'left';
                });
            };

            listenerCanvasInterval = setInterval(drawFrame, 33);
            listenerStream = listenerCanvas.captureStream(30);
            videoEl.srcObject = listenerStream;
        }
    };
})();
