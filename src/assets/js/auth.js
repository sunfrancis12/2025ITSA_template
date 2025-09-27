// 醫檢系統 JavaScript 程式（簡化版）

// 全域變數
let currentUser = null;

// 頁面載入完成後初始化
$(document).ready(function() {
    console.log('頁面載入完成');
    // 只綁定基本的表單事件和按鈕事件
    bindBasicEvents();
});

// 顯示錯誤信息
function showError(message, container = '#login-container') {
    // 移除舊的錯誤信息
    $('.error-message').remove();
    
    // 創建錯誤信息元素
    const errorDiv = $('<div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 4px;">' + message + '</div>');
    
    // 在指定容器的表單前插入錯誤信息
    $(container + ' form').before(errorDiv);
    
    // 3秒後自動隱藏錯誤信息
    setTimeout(function() {
        errorDiv.fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
}

// 顯示成功信息
function showSuccess(message, container = '#login-container') {
    // 移除舊的信息
    $('.success-message, .error-message').remove();
    
    // 創建成功信息元素
    const successDiv = $('<div class="success-message" style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 4px;">' + message + '</div>');
    
    // 在指定容器的表單前插入成功信息
    $(container + ' form').before(successDiv);
    
    // 3秒後自動隱藏成功信息
    setTimeout(function() {
        successDiv.fadeOut(500, function() {
            $(this).remove();
        });
    }, 3000);
}
function bindBasicEvents() {
    // 註冊按鈕事件
    $('button[onclick="showRegister()"]').off('click').on('click', function(e) {
        e.preventDefault();
        showRegister();
    });
    
    // 返回登入按鈕事件
    $('button[onclick="showLogin()"]').off('click').on('click', function(e) {
        e.preventDefault();
        showLogin();
    });
    
    // 登入表單提交
    $('#login-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        handleLogin();
    });
    
    // 註冊表單提交
    $('#register-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        handleRegister();
    });
    
    // 修改密碼表單提交
    $('#change-password-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        handleChangePassword();
    });
}

// 顯示登入畫面
function showLogin() {
    console.log('切換到登入畫面');
    $('#register-container, #change-password-container').hide();
    $('#login-container').show();
}

// 顯示註冊畫面
function showRegister() {
    console.log('切換到註冊畫面');
    $('#login-container, #change-password-container').hide();
    $('#register-container').show();
}

// 顯示修改密碼畫面
function showChangePassword() {
    console.log('切換到修改密碼畫面');
    $('#login-container, #register-container').hide();
    $('#change-password-container').show();
}

// 處理登入
function handleLogin() {
    const formData = new FormData($('#login-form')[0]);
    formData.append('action', 'login');
    
    console.log('開始登入請求...');
    
    $.ajax({
        url: 'php/api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        // 移除 dataType: 'json' 來手動處理響應
        success: function(response, status, xhr) {
            console.log('原始回應:', response);
            console.log('回應類型:', typeof response);
            
            try {
                // 如果是字符串，嘗試解析 JSON
                const jsonResponse = typeof response === 'string' ? JSON.parse(response) : response;
                console.log('解析後的回應:', jsonResponse);
                
                if (jsonResponse.success) {
                    if (jsonResponse.data && jsonResponse.data.require_password_change) {
                        showChangePassword();
                    } else {
                        // 重定向到相應的頁面
                        if (jsonResponse.data && jsonResponse.data.role === 'technician') {
                            window.location.href = 'pages/technician.php';
                        } else {
                            window.location.href = 'pages/patient.php';
                        }
                    }
                } else {
                    showError('登入失敗: ' + (jsonResponse.message || '未知錯誤'), '#login-container');
                }
            } catch (parseError) {
                console.error('JSON 解析錯誤:', parseError);
                console.error('原始響應內容:', response);
                showError('服務器響應格式錯誤。請查看瀏覽器控制台獲取詳情。', '#login-container');
            }
        },
        error: function(xhr, status, error) {
            console.error('登入錯誤詳情:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response Text:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            
            // 顯示更詳細的錯誤信息
            let errorMessage = '登入時發生錯誤';
            if (xhr.status === 404) {
                errorMessage = 'API 路徑不存在 (404)';
            } else if (xhr.status === 500) {
                errorMessage = '服務器內部錯誤 (500)';
            } else if (xhr.responseText) {
                errorMessage = '錯誤: ' + xhr.responseText.substring(0, 100);
            }
            
            showError(errorMessage + '. 請查看瀏覽器控制台獲取更多詳情。', '#login-container');
        }
    });
}

// 處理註冊
function handleRegister() {
    const formData = new FormData($('#register-form')[0]);
    formData.append('action', 'register');
    
    $.ajax({
        url: 'php/api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showSuccess('註冊成功！請使用您的帳號登入。', '#register-container');
                setTimeout(function() {
                    showLogin();
                }, 2000);
            } else {
                showError('註冊失敗: ' + response.message, '#register-container');
            }
        },
        error: function(xhr, status, error) {
            console.error('註冊錯誤:', error);
            showError('註冊時發生錯誤，請稍後再試', '#register-container');
        }
    });
}

// 處理修改密碼
function handleChangePassword() {
    const newPassword = $('#new-password').val();
    const confirmPassword = $('#confirm-password').val();
    
    // 密碼規則驗證
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{12,}$/;
    if (!passwordRegex.test(newPassword)) {
        showError('密碼必須包含至少12個字元，且包含英文大小寫與數字', '#change-password-container');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showError('新密碼與確認密碼不一致', '#change-password-container');
        return;
    }
    
    const formData = new FormData($('#change-password-form')[0]);
    formData.append('action', 'change_password');
    
    $.ajax({
        url: 'php/api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showSuccess('密碼修改成功！請重新登入。', '#change-password-container');
                setTimeout(() => {
                    showLogin();
                    $('#change-password-form')[0].reset();
                }, 1500);
            } else {
                showError('密碼修改失敗: ' + response.message, '#change-password-container');
            }
        },
        error: function(xhr, status, error) {
            console.error('密碼修改錯誤:', error);
            showError('密碼修改時發生錯誤，請稍後再試', '#change-password-container');
        }
    });
}