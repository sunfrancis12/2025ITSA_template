// 受檢者界面 JavaScript 功能
$(document).ready(function() {
    // 載入病患的檢查結果
    loadPatientResults();
    
    // 檢查是否需要修改密碼
    checkFirstLogin();
});

// 載入病患檢查結果
function loadPatientResults() {
    $('.loading').show();
    
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { action: 'get_patient_results' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayPatientResults(response.data);
            } else {
                showError('載入檢查結果失敗：' + response.message);
            }
            $('.loading').hide();
        },
        error: function() {
            showError('載入檢查結果時發生錯誤');
            $('.loading').hide();
        }
    });
}

// 顯示病患檢查結果
function displayPatientResults(results) {
    const container = $('#patient-results-container');
    
    if (results.length === 0) {
        container.html(`
            <div class="no-results">
                <div class="no-results-icon">📋</div>
                <h4>暫無檢查結果</h4>
                <p>您目前還沒有任何檢查結果，請聯繫醫檢人員進行檢查。</p>
            </div>
        `);
        return;
    }
    
    // 按醫檢項目分組顯示結果
    const groupedResults = groupResultsByItem(results);
    let html = '<div class="results-grid">';
    
    Object.keys(groupedResults).forEach(itemId => {
        const item = groupedResults[itemId];
        const latestResult = item.results[0]; // 假設已按時間排序
        const scoreClass = getScoreClass(latestResult.score);
        const scoreText = getScoreText(latestResult.score);
        
        html += `
            <div class="result-card">
                <div class="result-header">
                    <h4>${item.itemName}</h4>
                    <span class="result-date">${formatDateTime(latestResult.created_at)}</span>
                </div>
                <div class="result-score">
                    <span class="score-value ${scoreClass}">${latestResult.score}</span>
                    <span class="score-text">${scoreText}</span>
                </div>
                <div class="result-details">
                    <p><strong>檢測人員：</strong>${latestResult.technician_name}</p>
                    ${item.results.length > 1 ? `
                        <button class="btn btn-sm btn-info" onclick="showResultHistory('${itemId}', '${item.itemName}')">
                            查看歷史記錄 (${item.results.length})
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    // 添加統計資訊
    html += generateStatistics(results);
    
    container.html(html);
}

// 按醫檢項目分組
function groupResultsByItem(results) {
    const grouped = {};
    
    results.forEach(result => {
        if (!grouped[result.item_id]) {
            grouped[result.item_id] = {
                itemName: result.item_name,
                results: []
            };
        }
        grouped[result.item_id].results.push(result);
    });
    
    // 對每個項目的結果按時間排序（最新的在前）
    Object.keys(grouped).forEach(itemId => {
        grouped[itemId].results.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    });
    
    return grouped;
}

// 顯示結果歷史記錄
function showResultHistory(itemId, itemName) {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { 
            action: 'get_patient_results',
            item_id: itemId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const results = response.data.filter(r => r.item_id === itemId);
                showHistoryModal(itemName, results);
            } else {
                showError('載入歷史記錄失敗：' + response.message);
            }
        },
        error: function() {
            showError('載入歷史記錄時發生錯誤');
        }
    });
}

// 顯示歷史記錄彈出視窗
function showHistoryModal(itemName, results) {
    let historyHtml = `
        <div class="modal" id="historyModal" style="display: flex;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>${itemName} - 歷史記錄</h3>
                    <span class="close" onclick="closeHistoryModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="history-timeline">
    `;
    
    results.forEach((result, index) => {
        const scoreClass = getScoreClass(result.score);
        const scoreText = getScoreText(result.score);
        
        historyHtml += `
            <div class="timeline-item ${index === 0 ? 'latest' : ''}">
                <div class="timeline-date">${formatDateTime(result.created_at)}</div>
                <div class="timeline-content">
                    <div class="timeline-score">
                        <span class="score-badge ${scoreClass}">${result.score} - ${scoreText}</span>
                    </div>
                    <div class="timeline-technician">
                        檢測人員：${result.technician_name}
                    </div>
                    ${index === 0 ? '<div class="latest-badge">最新</div>' : ''}
                </div>
            </div>
        `;
    });
    
    historyHtml += `
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeHistoryModal()">關閉</button>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(historyHtml);
    $('body').addClass('modal-open');
}

// 關閉歷史記錄彈出視窗
function closeHistoryModal() {
    $('#historyModal').remove();
    $('body').removeClass('modal-open');
}

// 生成統計資訊
function generateStatistics(results) {
    if (results.length === 0) return '';
    
    const stats = {
        total: results.length,
        excellent: results.filter(r => r.score >= 8).length,
        good: results.filter(r => r.score >= 6 && r.score < 8).length,
        needsImprovement: results.filter(r => r.score < 6).length
    };
    
    const averageScore = (results.reduce((sum, r) => sum + r.score, 0) / results.length).toFixed(1);
    
    return `
        <div class="statistics-section">
            <h4>檢查統計</h4>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">${stats.total}</div>
                    <div class="stat-label">總檢查次數</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${averageScore}</div>
                    <div class="stat-label">平均分數</div>
                </div>
                <div class="stat-card excellent">
                    <div class="stat-value">${stats.excellent}</div>
                    <div class="stat-label">優良</div>
                </div>
                <div class="stat-card good">
                    <div class="stat-value">${stats.good}</div>
                    <div class="stat-label">良好</div>
                </div>
                <div class="stat-card needs-improvement">
                    <div class="stat-value">${stats.needsImprovement}</div>
                    <div class="stat-label">需改善</div>
                </div>
            </div>
        </div>
    `;
}

// 檢查首次登入
function checkFirstLogin() {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { action: 'get_current_user' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.first_login) {
                showFirstLoginModal();
            }
        }
    });
}

// 顯示首次登入密碼修改提示
function showFirstLoginModal() {
    const modalHtml = `
        <div class="modal" id="firstLoginModal" style="display: flex;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>首次登入</h3>
                </div>
                <div class="modal-body">
                    <p>為了您的帳號安全，首次登入需要修改密碼。</p>
                    <form id="firstLoginChangePasswordForm">
                        <div class="form-group">
                            <label for="first_new_password">新密碼：</label>
                            <input type="password" id="first_new_password" name="new_password" required>
                            <small class="form-help">至少12個字元，包含英文大小寫與數字</small>
                        </div>
                        <div class="form-group">
                            <label for="first_confirm_password">確認密碼：</label>
                            <input type="password" id="first_confirm_password" name="confirm_password" required>
                        </div>
                    </form>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-primary" onclick="submitFirstLoginPassword()">確認修改</button>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHtml);
    $('body').addClass('modal-open');
}

// 提交首次登入密碼修改
function submitFirstLoginPassword() {
    const newPassword = $('#first_new_password').val();
    const confirmPassword = $('#first_confirm_password').val();
    
    // 密碼驗證
    if (newPassword !== confirmPassword) {
        showError('新密碼與確認密碼不一致');
        return;
    }
    
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{12,}$/;
    if (!passwordRegex.test(newPassword)) {
        showError('密碼必須包含至少12個字元，且包含英文大小寫與數字');
        return;
    }
    
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { 
            action: 'change_password',
            current_password: '', // 首次登入不需要舊密碼
            new_password: newPassword,
            confirm_password: confirmPassword,
            first_login: true
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showSuccess('密碼修改成功');
                $('#firstLoginModal').remove();
                $('body').removeClass('modal-open');
            } else {
                showError('密碼修改失敗：' + response.message);
            }
        },
        error: function() {
            showError('密碼修改時發生錯誤');
        }
    });
}

// 登出功能
function logout() {
    if (confirm('確定要登出嗎？')) {
        $.ajax({
            url: '../php/api.php',
            type: 'POST',
            data: { action: 'logout' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess('登出成功，即將跳轉至首頁');
                    setTimeout(() => {
                        window.location.href = '../index.php';
                    }, 1500);
                } else {
                    showError('登出失敗：' + response.message);
                }
            },
            error: function() {
                showError('登出時發生錯誤');
            }
        });
    }
}

// === 工具函數 ===
function formatDateTime(dateTimeString) {
    if (!dateTimeString) return '-';
    const date = new Date(dateTimeString);
    return date.toLocaleString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getScoreClass(score) {
    if (score >= 8) return 'excellent';
    if (score >= 6) return 'good';
    return 'needs-improvement';
}

function getScoreText(score) {
    if (score >= 8) return '優良';
    if (score >= 6) return '良好';
    return '需改善';
}

// === 訊息顯示 ===
function showSuccess(message) {
    // 移除現有訊息
    $('.message').remove();
    
    const messageDiv = $(`
        <div class="message success">
            <i class="icon success-icon">✓</i>
            <span>${message}</span>
            <button class="close-message" onclick="$(this).parent().remove()">×</button>
        </div>
    `);
    
    $('body').prepend(messageDiv);
    
    // 3秒後自動移除
    setTimeout(() => {
        messageDiv.fadeOut(() => messageDiv.remove());
    }, 3000);
}

function showError(message) {
    // 移除現有訊息
    $('.message').remove();
    
    const messageDiv = $(`
        <div class="message error">
            <i class="icon error-icon">✕</i>
            <span>${message}</span>
            <button class="close-message" onclick="$(this).parent().remove()">×</button>
        </div>
    `);
    
    $('body').prepend(messageDiv);
    
    // 5秒後自動移除
    setTimeout(() => {
        messageDiv.fadeOut(() => messageDiv.remove());
    }, 5000);
}

// === 視窗關閉事件 ===
$(window).click(function(event) {
    if ($(event.target).hasClass('modal')) {
        $(event.target).remove();
        $('body').removeClass('modal-open');
    }
});