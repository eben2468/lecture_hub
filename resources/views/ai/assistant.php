<?php
/**
 * Nadics LectureHub — AI Study Assistant View
 */
$__view->layout('layouts.app', [
    'page_title'       => 'AI Study Assistant',
    'page_description' => 'Interactive EdTech Q&A assistant for students.',
]);
?>

<?php $__view->section('content'); ?>
<div class="container-fluid py-4">
    <!-- Header banner -->
    <div class="slms-card p-4 mb-4" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color: white; border-radius: 16px;">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(139, 92, 246, 0.2); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: #8B5CF6;">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <h2 style="color: white; font-weight: 800; font-size: 1.75rem;" class="mb-1">AI Study Assistant</h2>
                <p style="color: #94A3B8; font-size: 0.95rem;" class="mb-0">Ask questions about your lectures, retrieve summaries, and study smart.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Chat Area -->
        <div class="col-lg-8">
            <div class="slms-card d-flex flex-column" style="height: 600px; border-radius: 16px;">
                <!-- Chat Header / Context selector -->
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="background: var(--bg-surface-alt); border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success px-3 py-1"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> Assistant Active</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted mb-0 me-1">Course Context:</label>
                        <select id="courseContext" class="form-select form-select-sm" style="width: 200px; border-radius: 8px;">
                            <option value="">All Enrolled Courses</option>
                            <?php foreach ($courses ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['code'] . ' — ' . $c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Chat Messages Body -->
                <div class="flex-grow-1 p-4 overflow-y-auto" id="chatContainer" style="background: #F8FAFC;">
                    <!-- Welcome message -->
                    <div class="d-flex gap-3 mb-4">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #8B5CF6; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="p-3 rounded-3" style="background: white; max-width: 75%; box-shadow: 0 1px 3px rgba(0,0,0,0.05); color: #334155; line-height: 1.6;">
                            <strong>Hello! I am your AI Study Assistant.</strong><br>
                            How can I help you study today? You can ask me questions about AVL tree rotations, check active assignments, or retrieve summary notes from your recent lectures.
                        </div>
                    </div>
                </div>

                <!-- Message input area -->
                <div class="p-3 border-top" style="background: var(--bg-surface-alt); border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <!-- Quick Pill Prompts -->
                    <div class="d-flex gap-2 mb-3 overflow-x-auto pb-1" style="white-space: nowrap;">
                        <button class="btn btn-sm btn-outline-secondary px-3 py-1 text-xs" style="border-radius: 20px; font-size: 0.8rem;" onclick="sendQuickPrompt('Explain AVL Tree rotations')">
                            💡 Explain AVL Tree rotations
                        </button>
                        <button class="btn btn-sm btn-outline-secondary px-3 py-1 text-xs" style="border-radius: 20px; font-size: 0.8rem;" onclick="sendQuickPrompt('What are my upcoming assignment deadlines?')">
                            📝 Check assignment deadlines
                        </button>
                        <button class="btn btn-sm btn-outline-secondary px-3 py-1 text-xs" style="border-radius: 20px; font-size: 0.8rem;" onclick="sendQuickPrompt('Summarize the recent lecture')">
                            📊 Summarize recent lecture
                        </button>
                    </div>

                    <form id="chatForm" onsubmit="event.preventDefault(); submitChat();">
                        <div class="input-group">
                            <input type="text" id="chatMessage" class="form-control" placeholder="Ask a study question..." style="border-radius: 12px 0 0 12px; height: 50px;" required>
                            <button type="submit" class="btn btn-primary px-4" style="border-radius: 0 12px 12px 0; background: #8B5CF6; border-color: #8B5CF6;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Widgets -->
        <div class="col-lg-4">
            <div class="slms-card p-4 mb-4">
                <h4 style="font-weight: 700; color: var(--primary);" class="mb-3"><i class="fas fa-lightbulb text-warning me-2"></i> Study Tips</h4>
                <ul class="list-unstyled d-flex flex-column gap-3 mb-0" style="font-size: 0.9rem; color: var(--text-secondary);">
                    <li>
                        <i class="fas fa-check-circle text-success me-2"></i> Use the **Course Context** dropdown to narrow down AI query answers to specific modules.
                    </li>
                    <li>
                        <i class="fas fa-check-circle text-success me-2"></i> Review AI Flashcards at the end of each lecture transcript to reinforce your recall.
                    </li>
                    <li>
                        <i class="fas fa-check-circle text-success me-2"></i> Scanned attendance items populate your reports page immediately.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function appendMessage(sender, text, isAi = false) {
    const container = document.getElementById('chatContainer');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'd-flex gap-3 mb-4 ' + (isAi ? '' : 'justify-content-end');
    
    const iconHtml = isAi 
        ? `<div style="width: 36px; height: 36px; border-radius: 50%; background: #8B5CF6; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-robot"></i></div>`
        : `<div style="width: 36px; height: 36px; border-radius: 50%; background: #3B82F6; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-user"></i></div>`;

    const bubbleHtml = `<div class="p-3 rounded-3" style="background: ${isAi ? 'white' : '#8B5CF6'}; color: ${isAi ? '#334155' : 'white'}; max-width: 75%; box-shadow: 0 1px 3px rgba(0,0,0,0.05); line-height: 1.6;">
        ${text}
    </div>`;

    msgDiv.innerHTML = isAi ? (iconHtml + bubbleHtml) : (bubbleHtml + iconHtml);
    container.appendChild(msgDiv);
    container.scrollTop = container.scrollHeight;
}

function sendQuickPrompt(promptText) {
    document.getElementById('chatMessage').value = promptText;
    submitChat();
}

function submitChat() {
    const input = document.getElementById('chatMessage');
    const msg = input.value.trim();
    if (!msg) return;

    // Append user message
    appendMessage('You', msg, false);
    input.value = '';

    const courseId = document.getElementById('courseContext').value;

    // Send AJAX Request
    Ajax.post('<?= url('/ai-assistant/chat') ?>', {
        'message': msg,
        'course_id': courseId
    })
    .then(data => {
        if (data.reply) {
            appendMessage('AI', data.reply, true);
        } else if (data.message) {
            appendMessage('AI', 'Error: ' + data.message, true);
        } else if (data.error) {
            appendMessage('AI', 'Error: ' + data.error, true);
        }
    })
    .catch(err => {
        appendMessage('AI', 'Sorry, I encountered an issue: ' + (err.message || err.error || 'Unknown error'), true);
    });
}
</script>
<?php $__view->endSection(); ?>
