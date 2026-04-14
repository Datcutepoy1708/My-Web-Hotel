<!-- ======================================================
     Chat Tự Động – Chatbot Widget for Client
     OceanPearl Hotel · include trong client/includes/footer.php
     ====================================================== -->
<link rel="stylesheet" href="/My-Web-Hotel/client/assets/css/chatbot-widget.css?v=<?php echo time(); ?>">

<!-- ── Widget HTML ── -->
<div id="luxbot-widget">

    <div id="luxbot-box" role="dialog" aria-label="Chat Tự Động" aria-live="polite">

        <!-- Header -->
        <div class="luxbot-header">
            <div class="luxbot-avatar">Bot</div>
            <div class="luxbot-header-info">
                <h4 id="luxbot-name">Chat Tự Động</h4>
                <span><span class="luxbot-status-dot"></span> Đang trực tuyến</span>
            </div>
            <button class="luxbot-close-btn" onclick="toggleLuxBot()" aria-label="Đóng chat">
                X
            </button>
        </div>

        <!-- Messages -->
        <div id="luxbot-messages"></div>

        <!-- Quick replies -->
        <div class="luxbot-quick-replies" id="luxbot-quick">
            <button class="luxbot-quick-btn" onclick="sendQuick('Giá phòng')">Giá phòng</button>
            <button class="luxbot-quick-btn" onclick="sendQuick('Check-in')">Check-in</button>
            <button class="luxbot-quick-btn" onclick="sendQuick('Dịch vụ')">Dịch vụ</button>
            <button class="luxbot-quick-btn" onclick="sendQuick('Địa chỉ')">Địa chỉ</button>
        </div>

        <!-- Input -->
        <div class="luxbot-input-area">
            <input type="text" id="luxbot-input" placeholder="Nhập tin nhắn..." maxlength="500"
                   onkeydown="if(event.key==='Enter') sendLuxBotMsg()">
            <button id="luxbot-send" onclick="sendLuxBotMsg()" aria-label="Gửi tin nhắn">
                Gửi
            </button>
        </div>
    </div>

    <!-- Toggle button -->
    <button id="luxbot-toggle" onclick="toggleLuxBot()" aria-label="Mở chatbot hỗ trợ" title="Chat Tự Động">
        <span class="toggle-icon" id="luxbot-icon">Chat</span>
        <div id="luxbot-unread"></div>
    </button>

</div>

<script>
/* ── LuxBot Client JS ── */
(function() {
    'use strict';

    const API     = '/My-Web-Hotel/admin/api/chatbot.php';
    const SESSION = 'luxbot_' + Math.random().toString(36).substring(2, 10);
    let   isOpen  = false;
    let   isFirstOpen = true;
    let   unreadCount = 0;
    let   isSending   = false;

    /* ── Init ── */
    loadBotConfig();

    /* ── Toggle ── */
    window.toggleLuxBot = function() {
        isOpen = !isOpen;
        const box  = document.getElementById('luxbot-box');
        const icon = document.getElementById('luxbot-icon');

        if (isOpen) {
            box.classList.add('open');
            icon.textContent = 'X';
            clearUnread();
            if (isFirstOpen) { isFirstOpen = false; showWelcome(); }
            setTimeout(() => document.getElementById('luxbot-input').focus(), 320);
        } else {
            box.classList.remove('open');
            icon.textContent = 'Chat';
        }
    };

    /* ── Load bot config ── */
    async function loadBotConfig() {
        try {
            const res = await fetch(`${API}?action=get_settings`);
            const d   = await res.json();
            if (!d.success) return;
            const s = d.settings || {};
            if (s.bot_name) document.getElementById('luxbot-name').textContent = s.bot_name;
            // Auto-greet after 3s if bot is active & not opened
            if (s.is_active === '1' && !isOpen) {
                setTimeout(() => { if (!isOpen) showUnread(); }, 3000);
            }
            if (s.is_active !== '1') {
                // Hide widget
                document.getElementById('luxbot-widget').style.display = 'none';
            }
        } catch(e) { /* silent */ }
    }

    /* ── Welcome message ── */
    function showWelcome() {
        // Use a quick API call for the welcome message
        fetch(`${API}?action=get_settings`)
            .then(r => r.json())
            .then(d => {
                const msg = d.success && d.settings.welcome_message
                    ? d.settings.welcome_message
                    : 'Chào Anh/Chị! Em là LuxBot – Lễ tân ảo của OceanPearl Hotel. Em có thể giúp gì cho mình ạ?';
                appendBotMsg(msg);
            })
            .catch(() => appendBotMsg('Chào Anh/Chị! Em là LuxBot, em có thể giúp gì cho mình ạ?'));
    }

    /* ── Unread badge ── */
    function showUnread() {
        unreadCount = 1;
        const badge = document.getElementById('luxbot-unread');
        badge.style.display = 'flex';
        badge.textContent  = '1';
    }

    function clearUnread() {
        unreadCount = 0;
        document.getElementById('luxbot-unread').style.display = 'none';
    }

    /* ── Quick reply ── */
    window.sendQuick = function(text) {
        document.getElementById('luxbot-input').value = text;
        sendLuxBotMsg();
    };

    /* ── Send message ── */
    window.sendLuxBotMsg = async function() {
        if (isSending) return;
        const input = document.getElementById('luxbot-input');
        const msg   = input.value.trim();
        if (!msg) return;

        input.value = '';
        isSending   = true;
        document.getElementById('luxbot-send').disabled = true;

        // Hide quick replies after first send
        document.getElementById('luxbot-quick').style.display = 'none';

        appendUserMsg(msg);
        const typingEl = appendTyping();

        try {
            const fd = new FormData();
            fd.append('action',     'chat');
            fd.append('message',    msg);
            fd.append('session_id', SESSION);

            const res = await fetch(API, { method: 'POST', body: fd });
            const d   = await res.json();

            removeTyping(typingEl);
            const reply = d.success ? d.bot_reply : 'Dạ, em đang gặp sự cố kỹ thuật ạ Anh/Chị vui lòng gọi hotline nhé!';
            appendBotMsg(reply);
        } catch(e) {
            removeTyping(typingEl);
            appendBotMsg('Dạ, kết nối bị gián đoạn ạ Vui lòng thử lại hoặc gọi hotline!');
        }

        isSending = false;
        document.getElementById('luxbot-send').disabled = false;
        input.focus();
    };

    /* ── Append helpers ── */
    function appendUserMsg(text) {
        const msgs = document.getElementById('luxbot-messages');
        const el   = document.createElement('div');
        el.className = 'luxbot-msg user';
        el.innerHTML = `<div class="luxbot-bubble">${escHtml(text)}</div>`;
        msgs.appendChild(el);
        scrollToBottom();
    }

    function appendBotMsg(text) {
        const msgs = document.getElementById('luxbot-messages');
        const el   = document.createElement('div');
        el.className = 'luxbot-msg bot';
        el.innerHTML = `
            <div class="luxbot-msg-avatar">Bot</div>
            <div class="luxbot-bubble">${text}</div>`;
        msgs.appendChild(el);
        scrollToBottom();
    }

    function appendTyping() {
        const msgs = document.getElementById('luxbot-messages');
        const el   = document.createElement('div');
        el.className = 'luxbot-msg bot';
        el.innerHTML = `
            <div class="luxbot-msg-avatar">Bot</div>
            <div class="luxbot-typing">
                <div class="luxbot-typing-dot"></div>
                <div class="luxbot-typing-dot"></div>
                <div class="luxbot-typing-dot"></div>
            </div>`;
        msgs.appendChild(el);
        scrollToBottom();
        return el;
    }

    function removeTyping(el) {
        if (el && el.parentNode) el.parentNode.removeChild(el);
    }

    function scrollToBottom() {
        const msgs = document.getElementById('luxbot-messages');
        setTimeout(() => msgs.scrollTop = msgs.scrollHeight, 50);
    }

    function escHtml(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>
