<?php
/**
 * Chatbot Manager – Admin Page
 * OceanPearl Hotel · hotel_management
 *
 * Tabs: Thống kê | Quản lý Từ khóa | Lịch sử Chat | Cài đặt
 */

// Auth check is done by index.php / Router before including this file
?>

<div class="main-content chatbot-page" id="chatbot-manager-root">

<!-- ══════════════════════════════════════════
     Toast container
══════════════════════════════════════════ -->
<div class="bot-toast-area" id="botToastArea"></div>

<!-- ══════════════════════════════════════════
     Page Header
══════════════════════════════════════════ -->
<div class="chatbot-page-header">
    <div class="page-title">
        <div class="icon-wrap"><i class="fas fa-robot"></i></div>
        <div>
            <h1>Chat Tự Động Manager</h1>
            <p class="subtitle">Quản lý Chatbot • OceanPearl Hotel</p>
        </div>
    </div>

    <!-- Bot active toggle -->
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div id="botStatusBadge" class="info-chip">
            <span class="status-dot active" id="botStatusDot"></span>
            <span id="botStatusText">Đang tải...</span>
        </div>
        <label class="switch-wrap" title="Bật / Tắt Chat Tự Động">
            <input type="checkbox" id="botActiveToggle" onchange="toggleBotActive(this)">
            <span class="switch-slider"></span>
        </label>
        <button class="btn-bot-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Thêm Từ Khóa
        </button>
    </div>
</div>

<!-- ══════════════════════════════════════════
     Tabs
══════════════════════════════════════════ -->
<div class="chatbot-tabs" role="tablist">
    <button class="chatbot-tab-btn active" onclick="switchTab('analytics')" id="tab-analytics" aria-selected="true">
        <i class="fas fa-chart-line"></i> Thống Kê
    </button>
    <button class="chatbot-tab-btn" onclick="switchTab('keywords')" id="tab-keywords">
        <i class="fas fa-key"></i> Từ Khóa
    </button>
    <button class="chatbot-tab-btn" onclick="switchTab('history')" id="tab-history">
        <i class="fas fa-history"></i> Lịch Sử Chat
    </button>
    <button class="chatbot-tab-btn" onclick="switchTab('settings')" id="tab-settings">
        <i class="fas fa-sliders-h"></i> Cài Đặt
    </button>
</div>

<!-- ══════════════════════════════════════════
     TAB: Analytics
══════════════════════════════════════════ -->
<div class="chatbot-tab-panel active" id="panel-analytics">

    <!-- Stats row -->
    <div class="bot-stats-grid" id="statsGrid">
        <?php for($i=0;$i<5;$i++): ?>
        <div class="bot-stat-card">
            <div class="bot-stat-icon primary"><i class="fas fa-spinner fa-spin"></i></div>
            <div class="bot-stat-info"><h3>—</h3><p>Đang tải...</p></div>
        </div>
        <?php endfor; ?>
    </div>

    <div class="row g-3">

        <!-- Answer rate ring -->
        <div class="col-lg-4">
            <div class="bot-card">
                <div class="bot-card-header">
                    <h5><i class="fas fa-bullseye" style="color:var(--bot-primary)"></i> Tỉ lệ Trả lời</h5>
                </div>
                <div class="bot-card-body" id="answerRatePanel">
                    <div class="bot-empty"><i class="fas fa-spinner fa-spin"></i><p>Đang tải...</p></div>
                </div>
            </div>
        </div>

        <!-- Top keywords bar chart -->
        <div class="col-lg-8">
            <div class="bot-card">
                <div class="bot-card-header">
                    <h5><i class="fas fa-fire" style="color:var(--bot-primary)"></i> Từ Khóa Nổi Bật</h5>
                    <span class="info-chip">Top 8</span>
                </div>
                <div class="bot-card-body">
                    <div class="bot-progress-list" id="topKeywordsPanel">
                        <div class="bot-empty"><i class="fas fa-spinner fa-spin"></i><p>Đang tải...</p></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily chart -->
        <div class="col-12">
            <div class="bot-card">
                <div class="bot-card-header">
                    <h5><i class="fas fa-calendar-alt" style="color:var(--bot-primary)"></i> 7 Ngày Gần Nhất</h5>
                </div>
                <div class="bot-card-body">
                    <div class="bot-chart-area" id="dailyChartPanel">
                        <!-- SVG gradient def (hidden) -->
                        <svg width="0" height="0" style="position:absolute">
                            <defs>
                                <linearGradient id="botGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%"   stop-color="#D4A373"/>
                                    <stop offset="100%" stop-color="#B8895A"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="bot-empty"><i class="fas fa-spinner fa-spin"></i></div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /row -->
</div><!-- /panel-analytics -->

<!-- ══════════════════════════════════════════
     TAB: Keywords
══════════════════════════════════════════ -->
<div class="chatbot-tab-panel" id="panel-keywords">

    <div class="bot-card">
        <div class="bot-card-header">
            <h5><i class="fas fa-list" style="color:var(--bot-primary)"></i> Danh Sách Từ Khóa</h5>
            <span class="info-chip" id="kwCount">0 từ khóa</span>
        </div>
        <div class="bot-card-body">

            <!-- Toolbar -->
            <div class="bot-toolbar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="kwSearch" placeholder="Tìm từ khóa..." oninput="debounceLoadKeywords()">
                </div>
                <select id="kwCategory" onchange="loadKeywords()">
                    <option value="">Tất cả danh mục</option>
                    <option value="Phòng">🛏️ Phòng</option>
                    <option value="Giờ Giấc">⏰ Giờ Giấc</option>
                    <option value="Đặt Phòng">📅 Đặt Phòng</option>
                    <option value="Địa Chỉ">📍 Địa Chỉ</option>
                    <option value="Dịch Vụ">🛎️ Dịch Vụ</option>
                    <option value="Liên Hệ">📞 Liên Hệ</option>
                    <option value="Chính Sách">📋 Chính Sách</option>
                    <option value="Chào Hỏi">👋 Chào Hỏi</option>
                    <option value="General">📦 General</option>
                </select>
                <button class="btn-bot-outline" onclick="loadKeywords()">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="btn-bot-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
            </div>

            <!-- Table -->
            <div class="bot-table-wrap">
                <table class="bot-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Từ khoá</th>
                            <th>Câu trả lời</th>
                            <th>Danh mục</th>
                            <th style="width:70px">Kích hoạt</th>
                            <th style="width:80px; text-align:right">Lượt khớp</th>
                            <th style="width:130px; text-align:center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="kwTableBody">
                        <tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="bot-pagination" id="kwPagination"></div>

        </div>
    </div>

</div><!-- /panel-keywords -->

<!-- ══════════════════════════════════════════
     TAB: History
══════════════════════════════════════════ -->
<div class="chatbot-tab-panel" id="panel-history">

    <div class="bot-card">
        <div class="bot-card-header">
            <h5><i class="fas fa-comments" style="color:var(--bot-primary)"></i> Lịch Sử Chat</h5>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn-bot-outline" id="unknownFilterBtn" onclick="toggleUnknownFilter()" title="Lọc chỉ câu hỏi không hiểu">
                    <i class="fas fa-question-circle"></i> Không hiểu
                </button>
                <button class="btn-bot-danger" onclick="confirmClearHistory()">
                    <i class="fas fa-trash"></i> Xóa tất cả
                </button>
            </div>
        </div>
        <div class="bot-card-body">

            <div class="bot-toolbar">
                <span class="info-chip" id="histTotal">0 tin nhắn</span>
            </div>

            <div id="historyList">
                <div class="bot-empty"><i class="fas fa-spinner fa-spin"></i><p>Đang tải...</p></div>
            </div>

            <div class="bot-pagination" id="histPagination"></div>

        </div>
    </div>

</div><!-- /panel-history -->

<!-- ══════════════════════════════════════════
     TAB: Settings
══════════════════════════════════════════ -->
<div class="chatbot-tab-panel" id="panel-settings">

    <div class="bot-card">
        <div class="bot-card-header">
            <h5><i class="fas fa-cog" style="color:var(--bot-primary)"></i> Cài đặt Bot</h5>
            <button class="btn-bot-primary" onclick="saveAllSettings()">
                <i class="fas fa-save"></i> Lưu tất cả
            </button>
        </div>
        <div class="bot-card-body">

            <div class="bot-settings-grid">
                <div class="form-group">
                    <label><i class="fas fa-robot"></i> Tên Bot</label>
                    <input type="text" id="setting_bot_name" placeholder="LuxBot">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Hotline</label>
                    <input type="text" id="setting_hotline" placeholder="+84.244.243.434">
                </div>
                <div class="form-group" style="grid-column: 1/-1">
                    <label><i class="fas fa-hand-wave"></i> Lời chào</label>
                    <textarea id="setting_welcome_message" placeholder="Tin nhắn chào khi khách mở chatbot..." rows="3"></textarea>
                </div>
                <div class="form-group" style="grid-column: 1/-1">
                    <label><i class="fas fa-question"></i> Câu trả lời khi không hiểu</label>
                    <textarea id="setting_unknown_message" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-palette"></i> Màu chính (hex)</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="color" id="setting_primary_color_pick" style="width:46px;height:42px;padding:2px;border-radius:8px;border:1.5px solid #e8ddd0;cursor:pointer" value="#D4A373" oninput="document.getElementById('setting_primary_color').value=this.value">
                        <input type="text" id="setting_primary_color" placeholder="#D4A373" style="flex:1" oninput="syncColorPicker()">
                    </div>
                </div>
            </div>

            <hr style="border-color: var(--bot-border); margin: 20px 0;">

            <!-- Toggle rows -->
            <div class="bot-toggle-row">
                <div>
                    <div class="bot-toggle-label">Kích hoạt Bot</div>
                    <div class="bot-toggle-desc">Bật/Tắt chatbot trên toàn trang web</div>
                </div>
                <label class="switch-wrap">
                    <input type="checkbox" id="setting_is_active">
                    <span class="switch-slider"></span>
                </label>
            </div>

        </div>
    </div>

</div><!-- /panel-settings -->

</div><!-- /chatbot-page -->

<!-- ══════════════════════════════════════════
     Modal: Add / Edit Keyword
══════════════════════════════════════════ -->
<div class="bot-modal-overlay" id="kwModal" onclick="closeKwModalOnBg(event)">
    <div class="bot-modal" role="dialog" aria-modal="true" aria-labelledby="kwModalTitle">
        <div class="bot-modal-header">
            <h4 id="kwModalTitle"><i class="fas fa-key" style="color:var(--bot-primary)"></i> <span id="kwModalTitleText">Thêm Từ Khóa</span></h4>
            <button class="btn-modal-close" onclick="closeKwModal()" aria-label="Đóng"><i class="fas fa-times"></i></button>
        </div>

        <input type="hidden" id="kwModalId">

        <div class="form-group">
            <label for="kwModalKeyword">Từ khóa <span style="color:red">*</span></label>
            <input type="text" id="kwModalKeyword" placeholder="vd: giá phòng, check-in, spa...">
        </div>

        <div class="form-group">
            <label for="kwModalResponse">Câu trả lời của Bot <span style="color:red">*</span></label>
            <textarea id="kwModalResponse" rows="5" placeholder="Nhập nội dung bot sẽ trả lời..."></textarea>
        </div>

        <div class="form-group">
            <label for="kwModalCategory">Danh mục</label>
            <select id="kwModalCategory">
                <option value="General">📦 General</option>
                <option value="Phòng">🛏️ Phòng</option>
                <option value="Giờ Giấc">⏰ Giờ Giấc</option>
                <option value="Đặt Phòng">📅 Đặt Phòng</option>
                <option value="Địa Chỉ">📍 Địa Chỉ</option>
                <option value="Dịch Vụ">🛎️ Dịch Vụ</option>
                <option value="Liên Hệ">📞 Liên Hệ</option>
                <option value="Chính Sách">📋 Chính Sách</option>
                <option value="Chào Hỏi">👋 Chào Hỏi</option>
            </select>
        </div>

        <div class="form-group" id="kwModalActiveRow" style="display:none">
            <label>Trạng thái</label>
            <div class="d-flex align-items-center gap-3">
                <label class="switch-wrap">
                    <input type="checkbox" id="kwModalActive" checked>
                    <span class="switch-slider"></span>
                </label>
                <span style="font-size:0.85rem" id="kwModalActiveLabel">Đang kích hoạt</span>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-3">
            <button class="btn-bot-outline" onclick="closeKwModal()">Hủy</button>
            <button class="btn-bot-primary" onclick="saveKeyword()" id="kwModalSaveBtn">
                <i class="fas fa-save"></i> Lưu
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     JavaScript
══════════════════════════════════════════ -->
<script>
/* ── Config ── */
const CHATBOT_API = '/My-Web-Hotel/admin/api/chatbot.php';
const HIST_LIMIT  = 20;
const KW_PER_PAGE = 15;

let kwPage       = 0;
let histPage     = 0;
let histUnknown  = false;
let allKeywords  = [];
let kwDebounce   = null;

/* ══════════════════════════════════════════
   Initialise
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    loadAnalytics();
    loadKeywords();
    loadHistory();
    loadSettings();
});

/* ══════════════════════════════════════════
   Tab Switcher
══════════════════════════════════════════ */
function switchTab(name) {
    document.querySelectorAll('.chatbot-tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.chatbot-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(`panel-${name}`).classList.add('active');
    document.getElementById(`tab-${name}`).classList.add('active');
}

/* ══════════════════════════════════════════
   Toast
══════════════════════════════════════════ */
function showToast(msg, type = 'success') {
    const area = document.getElementById('botToastArea');
    const toast = document.createElement('div');
    const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
    toast.className = `bot-toast ${type}`;
    toast.innerHTML = `<i class="fas fa-${icons[type] || 'info-circle'}"></i> ${escHtml(msg)}`;
    area.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.4s'; setTimeout(() => toast.remove(), 400); }, 3000);
}

/* ══════════════════════════════════════════
   API Helper
══════════════════════════════════════════ */
async function apiPost(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    const res = await fetch(CHATBOT_API, { method: 'POST', body: fd });
    return res.json();
}

async function apiGet(params) {
    const qs = new URLSearchParams(params).toString();
    const res = await fetch(`${CHATBOT_API}?${qs}`);
    return res.json();
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ══════════════════════════════════════════
   ANALYTICS
══════════════════════════════════════════ */
async function loadAnalytics() {
    try {
        const d = await apiGet({ action: 'get_analytics' });
        if (!d.success) { showToast('Không tải được thống kê', 'error'); return; }

        renderStats(d);
        renderAnswerRate(d.answer_rate, d.total_messages, d.unknown_count);
        renderTopKeywords(d.top_keywords, d.total_messages);
        renderDailyChart(d.daily_stats);

        // Update header bot status
        updateBotStatusUI(null); // will be set by settings
    } catch(e) {
        console.error(e);
    }
}

function renderStats(d) {
    const cfg = [
        { icon:'comments',        cls:'primary', val: d.total_sessions,  label:'Cuộc hội thoại' },
        { icon:'comment-dots',    cls:'info',    val: d.total_messages,  label:'Tổng tin nhắn' },
        { icon:'check-double',    cls:'success', val: d.total_messages - d.unknown_count, label:'Trả lời được' },
        { icon:'question-circle', cls:'danger',  val: d.unknown_count,   label:'Chưa hiểu' },
        { icon:'key',             cls:'warning', val: d.total_keywords,  label:'Từ khóa đang dùng' },
    ];
    document.getElementById('statsGrid').innerHTML = cfg.map(c => `
        <div class="bot-stat-card">
            <div class="bot-stat-icon ${c.cls}"><i class="fas fa-${c.icon}"></i></div>
            <div class="bot-stat-info">
                <h3>${Number(c.val).toLocaleString('vi-VN')}</h3>
                <p>${escHtml(c.label)}</p>
            </div>
        </div>
    `).join('');
}

function renderAnswerRate(rate, total, unknown) {
    const r = 45; // radius
    const circ = 2 * Math.PI * r;
    const offset = circ - (rate / 100) * circ;

    document.getElementById('answerRatePanel').innerHTML = `
        <div class="answer-rate-wrap">
            <div class="rate-ring">
                <svg width="110" height="110" viewBox="0 0 110 110">
                    <circle class="ring-bg"   cx="55" cy="55" r="${r}"/>
                    <circle class="ring-fill" cx="55" cy="55" r="${r}"
                        stroke-dasharray="${circ}"
                        stroke-dashoffset="${offset}"/>
                </svg>
                <div class="rate-ring-label">
                    <span class="value">${rate}%</span>
                    <span class="label">Trả lời</span>
                </div>
            </div>
            <div class="rate-legend">
                <div class="rate-legend-item">
                    <span class="rate-legend-dot" style="background:linear-gradient(135deg,#D4A373,#B8895A)"></span>
                    Trả lời được: ${(total - unknown).toLocaleString('vi-VN')}
                </div>
                <div class="rate-legend-item">
                    <span class="rate-legend-dot" style="background:linear-gradient(135deg,#f05060,#c0392b)"></span>
                    Không hiểu: ${Number(unknown).toLocaleString('vi-VN')}
                </div>
                <div class="rate-legend-item">
                    <span class="rate-legend-dot" style="background:#ddd"></span>
                    Tổng: ${Number(total).toLocaleString('vi-VN')}
                </div>
            </div>
        </div>
    `;
}

function renderTopKeywords(keywords, totalMessages) {
    if (!keywords || !keywords.length) {
        document.getElementById('topKeywordsPanel').innerHTML = '<div class="bot-empty"><i class="fas fa-inbox"></i><p>Chưa có dữ liệu</p></div>';
        return;
    }
    const top8 = keywords.slice(0, 8);
    const max  = Math.max(...top8.map(k => +k.match_count), 1);

    document.getElementById('topKeywordsPanel').innerHTML = top8.map(k => {
        const pct = Math.round((+k.match_count / max) * 100);
        const abs = totalMessages > 0 ? Math.round((+k.match_count / totalMessages) * 100) : 0;
        return `
            <div class="bot-progress-item">
                <div class="bot-progress-label">
                    <span>${escHtml(k.keyword)}</span>
                    <span><strong>${k.match_count}</strong> lần · ${abs}%</span>
                </div>
                <div class="bot-progress-track">
                    <div class="bot-progress-fill" style="width:${pct}%"></div>
                </div>
            </div>`;
    }).join('');
}

function renderDailyChart(stats) {
    const panel = document.getElementById('dailyChartPanel');
    if (!stats || !stats.length) {
        panel.innerHTML = '<div class="bot-empty"><i class="fas fa-chart-bar"></i><p>Chưa có dữ liệu 7 ngày</p></div>';
        return;
    }
    const max = Math.max(...stats.map(s => +s.total), 1);
    const maxH = 160; // px

    panel.innerHTML = `
        <svg width="0" height="0" style="position:absolute">
            <defs>
                <linearGradient id="botGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#D4A373"/>
                    <stop offset="100%" stop-color="#B8895A"/>
                </linearGradient>
            </defs>
        </svg>
        ${stats.map(s => {
            const h = Math.max(Math.round((+s.total / max) * maxH), 4);
            const dt = new Date(s.date);
            const label = (dt.getDate()) + '/' + (dt.getMonth()+1);
            return `
                <div class="bot-bar-wrap">
                    <div class="bot-bar" style="height:${h}px" data-value="${s.total}"></div>
                    <span class="bot-bar-label">${label}</span>
                </div>`;
        }).join('')}
    `;
}

/* ══════════════════════════════════════════
   KEYWORDS
══════════════════════════════════════════ */
let kwSearchTimeout;
function debounceLoadKeywords() {
    clearTimeout(kwSearchTimeout);
    kwSearchTimeout = setTimeout(() => { kwPage = 0; loadKeywords(); }, 380);
}

async function loadKeywords() {
    const search   = document.getElementById('kwSearch').value.trim();
    const category = document.getElementById('kwCategory').value;

    const d = await apiGet({ action: 'get_keywords', search, category });
    if (!d.success) { showToast('Lỗi tải từ khóa', 'error'); return; }

    allKeywords = d.keywords || [];
    renderKeywordsTable(allKeywords);
    document.getElementById('kwCount').textContent = `${allKeywords.length} từ khóa`;
}

function renderKeywordsTable(keywords) {
    const start = kwPage * KW_PER_PAGE;
    const slice = keywords.slice(start, start + KW_PER_PAGE);
    const tbody = document.getElementById('kwTableBody');

    if (!keywords.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5">
            <div class="bot-empty"><i class="fas fa-inbox"></i><p>Chưa có từ khóa nào</p></div></td></tr>`;
        document.getElementById('kwPagination').innerHTML = '';
        return;
    }

    tbody.innerHTML = slice.map((k, i) => `
        <tr>
            <td style="color:var(--bot-text-muted);font-size:0.8rem">${start + i + 1}</td>
            <td><span class="keyword-chip"><i class="fas fa-tag"></i> ${escHtml(k.keyword)}</span></td>
            <td style="max-width:280px">
                <div style="font-size:0.82rem;line-height:1.4;white-space:pre-wrap;max-height:60px;overflow:hidden;cursor:pointer"
                     title="${escHtml(k.response)}">${escHtml(k.response.substring(0,120))}${k.response.length>120?'…':''}</div>
            </td>
            <td><span class="category-badge">${escHtml(k.category)}</span></td>
            <td>
                <div class="status-toggle">
                    <span class="status-dot ${k.is_active=='1'?'active':'inactive'}"></span>
                    ${k.is_active=='1'?'Bật':'Tắt'}
                </div>
            </td>
            <td style="text-align:right;font-weight:700;color:var(--bot-primary)">${Number(k.match_count).toLocaleString()}</td>
            <td>
                <div class="btn-actions justify-content-center">
                    <button class="btn-bot-edit" onclick="openEditModal(${k.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn-bot-danger" onclick="deleteKeyword(${k.id}, '${escHtml(k.keyword).replace(/'/g,"\\'")}')"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination('kwPagination', Math.ceil(keywords.length / KW_PER_PAGE), kwPage, p => { kwPage = p; renderKeywordsTable(allKeywords); });
}

/* ── Modal helpers ── */
function openAddModal() {
    document.getElementById('kwModalId').value       = '';
    document.getElementById('kwModalKeyword').value  = '';
    document.getElementById('kwModalResponse').value = '';
    document.getElementById('kwModalCategory').value = 'General';
    document.getElementById('kwModalActive').checked = true;
    document.getElementById('kwModalActiveRow').style.display = 'none';
    document.getElementById('kwModalTitleText').textContent   = 'Thêm Từ Khóa';
    document.getElementById('kwModal').classList.add('open');
    document.getElementById('kwModalKeyword').focus();
}

function openEditModal(id) {
    const kw = allKeywords.find(k => +k.id === +id);
    if (!kw) return showToast('Không tìm thấy từ khóa', 'error');

    document.getElementById('kwModalId').value             = kw.id;
    document.getElementById('kwModalKeyword').value        = kw.keyword;
    document.getElementById('kwModalResponse').value       = kw.response;
    document.getElementById('kwModalCategory').value       = kw.category;
    document.getElementById('kwModalActive').checked       = kw.is_active == '1';
    document.getElementById('kwModalActiveRow').style.display = '';
    updateActiveLabel();
    document.getElementById('kwModalTitleText').textContent = 'Chỉnh sửa Từ Khóa';
    document.getElementById('kwModal').classList.add('open');
}

function updateActiveLabel() {
    const checked = document.getElementById('kwModalActive').checked;
    document.getElementById('kwModalActiveLabel').textContent = checked ? 'Đang kích hoạt' : 'Đang tắt';
}

document.getElementById('kwModalActive')?.addEventListener('change', updateActiveLabel);

function closeKwModal() { document.getElementById('kwModal').classList.remove('open'); }
function closeKwModalOnBg(e) { if (e.target.id === 'kwModal') closeKwModal(); }

async function saveKeyword() {
    const id       = document.getElementById('kwModalId').value;
    const keyword  = document.getElementById('kwModalKeyword').value.trim();
    const response = document.getElementById('kwModalResponse').value.trim();
    const category = document.getElementById('kwModalCategory').value;
    const isActive = document.getElementById('kwModalActive').checked ? 1 : 0;

    if (!keyword || !response) return showToast('Vui lòng điền đầy đủ thông tin', 'error');

    const btn = document.getElementById('kwModalSaveBtn');
    btn.disabled = true;

    const action = id ? 'update_keyword' : 'add_keyword';
    const data   = { action, keyword, response, category };
    if (id) { data.id = id; data.is_active = isActive; }

    const d = await apiPost(data);
    btn.disabled = false;

    if (d.success) {
        showToast(d.message || 'Lưu thành công!', 'success');
        closeKwModal();
        loadKeywords();
        loadAnalytics();
    } else {
        showToast(d.message || 'Có lỗi xảy ra', 'error');
    }
}

async function deleteKeyword(id, name) {
    if (!confirm(`Xóa từ khóa "${name}"?\nThao tác này không thể hoàn tác.`)) return;
    const d = await apiPost({ action: 'delete_keyword', id });
    if (d.success) {
        showToast('Đã xóa từ khóa', 'success');
        loadKeywords(); loadAnalytics();
    } else {
        showToast(d.message || 'Lỗi xóa từ khóa', 'error');
    }
}

/* ══════════════════════════════════════════
   HISTORY
══════════════════════════════════════════ */
async function loadHistory() {
    const d = await apiGet({
        action: 'get_history',
        limit: HIST_LIMIT,
        offset: histPage * HIST_LIMIT,
        only_unknown: histUnknown ? 1 : 0
    });

    if (!d.success) return;

    const list = document.getElementById('historyList');
    document.getElementById('histTotal').textContent = `${d.total} tin nhắn`;

    if (!d.history || !d.history.length) {
        list.innerHTML = '<div class="bot-empty"><i class="fas fa-comment-slash"></i><p>Chưa có lịch sử chat</p></div>';
        document.getElementById('histPagination').innerHTML = '';
        return;
    }

    list.innerHTML = d.history.map(h => {
        const dt = new Date(h.created_at);
        const ts = `${dt.getDate()}/${dt.getMonth()+1}/${dt.getFullYear()} ${dt.getHours()}:${String(dt.getMinutes()).padStart(2,'0')}`;
        const isUnk = h.is_unknown == '1';
        return `
            <div class="history-item">
                <div class="history-icon user"><i class="fas fa-user"></i></div>
                <div class="history-content" style="flex:1">
                    <h6>
                        Khách hỏi
                        ${isUnk ? '<span class="badge-unknown"><i class="fas fa-question"></i> Không hiểu</span>' : ''}
                        ${h.matched_keyword ? `<span class="keyword-chip ms-2" style="font-size:0.72rem"><i class="fas fa-tag"></i> ${escHtml(h.matched_keyword)}</span>` : ''}
                    </h6>
                    <p><strong>Hỏi:</strong> ${escHtml(h.user_message)}</p>
                    <p style="color:var(--bot-primary-dark)"><strong>Bot:</strong> ${escHtml(h.bot_response.substring(0, 160))}${h.bot_response.length>160?'…':''}</p>
                    <small><i class="fas fa-clock"></i> ${ts} · Session: ${escHtml(h.session_id.substring(0,12))}... · IP: ${escHtml(h.ip_address||'—')}</small>
                </div>
            </div>`;
    }).join('');

    const totalPages = Math.ceil(d.total / HIST_LIMIT);
    renderPagination('histPagination', totalPages, histPage, p => { histPage = p; loadHistory(); });
}

function toggleUnknownFilter() {
    histUnknown = !histUnknown;
    histPage = 0;
    const btn = document.getElementById('unknownFilterBtn');
    if (histUnknown) {
        btn.classList.add('btn-bot-primary');
        btn.classList.remove('btn-bot-outline');
    } else {
        btn.classList.add('btn-bot-outline');
        btn.classList.remove('btn-bot-primary');
    }
    loadHistory();
}

async function confirmClearHistory() {
    if (!confirm('Xóa TOÀN BỘ lịch sử chat?\nĐây là thao tác không thể hoàn tác!')) return;
    const d = await apiPost({ action: 'clear_history' });
    if (d.success) {
        showToast('Đã xóa lịch sử', 'success');
        loadHistory(); loadAnalytics();
    } else {
        showToast('Lỗi xóa lịch sử', 'error');
    }
}

/* ══════════════════════════════════════════
   SETTINGS
══════════════════════════════════════════ */
async function loadSettings() {
    const d = await apiGet({ action: 'get_settings' });
    if (!d.success) return;
    const s = d.settings || {};

    document.getElementById('setting_bot_name').value        = s.bot_name        || '';
    document.getElementById('setting_hotline').value         = s.hotline         || '';
    document.getElementById('setting_welcome_message').value = s.welcome_message || '';
    document.getElementById('setting_unknown_message').value = s.unknown_message || '';
    document.getElementById('setting_primary_color').value   = s.primary_color   || '#D4A373';
    document.getElementById('setting_primary_color_pick').value = s.primary_color || '#D4A373';
    document.getElementById('setting_is_active').checked     = s.is_active === '1';

    updateBotStatusUI(s.is_active === '1');
    document.getElementById('botActiveToggle').checked = s.is_active === '1';
}

async function saveAllSettings() {
    const settings = {
        bot_name:        document.getElementById('setting_bot_name').value.trim(),
        hotline:         document.getElementById('setting_hotline').value.trim(),
        welcome_message: document.getElementById('setting_welcome_message').value.trim(),
        unknown_message: document.getElementById('setting_unknown_message').value.trim(),
        primary_color:   document.getElementById('setting_primary_color').value.trim(),
        is_active:       document.getElementById('setting_is_active').checked ? '1' : '0',
    };

    let ok = true;
    for (const [key, value] of Object.entries(settings)) {
        const d = await apiPost({ action: 'update_setting', key, value });
        if (!d.success) { ok = false; showToast(`Lỗi lưu ${key}`, 'error'); }
    }
    if (ok) {
        showToast('Đã lưu tất cả cài đặt!', 'success');
        updateBotStatusUI(document.getElementById('setting_is_active').checked);
    }
}

async function toggleBotActive(checkbox) {
    const d = await apiPost({ action: 'update_setting', key: 'is_active', value: checkbox.checked ? '1' : '0' });
    if (d.success) {
        showToast(checkbox.checked ? 'Bot đã được bật' : 'Bot đã tắt', checkbox.checked ? 'success' : 'info');
        updateBotStatusUI(checkbox.checked);
        document.getElementById('setting_is_active').checked = checkbox.checked;
    } else {
        checkbox.checked = !checkbox.checked; // revert
        showToast('Lỗi cập nhật', 'error');
    }
}

function updateBotStatusUI(active) {
    const dot  = document.getElementById('botStatusDot');
    const txt  = document.getElementById('botStatusText');
    if (active === null) return; // skip initial call
    dot.className = `status-dot ${active ? 'active' : 'inactive'}`;
    txt.textContent = active ? 'Bot đang hoạt động' : 'Bot đang tắt';
}

function syncColorPicker() {
    const val = document.getElementById('setting_primary_color').value;
    if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
        document.getElementById('setting_primary_color_pick').value = val;
    }
}

/* ══════════════════════════════════════════
   Pagination helper
══════════════════════════════════════════ */
function renderPagination(containerId, totalPages, currentPage, onPageClick) {
    const el = document.getElementById(containerId);
    if (totalPages <= 1) { el.innerHTML = ''; return; }

    let html = '';
    const start = Math.max(0, currentPage - 2);
    const end   = Math.min(totalPages - 1, currentPage + 2);

    if (currentPage > 0)
        html += `<button class="bot-page-btn" onclick="(${onPageClick})(${currentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;

    for (let i = start; i <= end; i++) {
        html += `<button class="bot-page-btn${i===currentPage?' active':''}" onclick="(${onPageClick})(${i})">${i + 1}</button>`;
    }

    if (currentPage < totalPages - 1)
        html += `<button class="bot-page-btn" onclick="(${onPageClick})(${currentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;

    el.innerHTML = html;
}

/* ══════════════════════════════════════════
   Keyboard shortcuts
══════════════════════════════════════════ */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeKwModal();
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter' && document.getElementById('kwModal').classList.contains('open')) {
        e.preventDefault(); saveKeyword();
    }
});
</script>
