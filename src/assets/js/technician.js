// 醫檢員界面 JavaScript 功能
$(document).ready(function() {
    // 預設顯示人員管理標籤
    showTab('users');
    
    // 載入初始資料
    loadUsers();
    loadMedicalItems();
    loadTestResults();
    
    // 設定表單提交事件
    setupFormHandlers();
});

// 標籤切換功能
function showTab(tabName) {
    // 隱藏所有標籤內容
    $('.tab-content').hide();
    $('.tab-button').removeClass('active');
    
    // 顯示選中的標籤
    $(`#${tabName}-tab`).show();
    $(`.tab-button:contains('${getTabTitle(tabName)}')`).addClass('active');
    
    // 根據標籤載入對應資料
    switch(tabName) {
        case 'users':
            loadUsers();
            break;
        case 'medical-items':
            loadMedicalItems();
            break;
        case 'test-results':
            loadTestResults();
            break;
    }
}

function getTabTitle(tabName) {
    const titles = {
        'users': '人員管理',
        'medical-items': '醫檢項目管理',
        'test-results': '檢查結果管理'
    };
    return titles[tabName] || '';
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

// === 人員管理功能 ===
function loadUsers() {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { action: 'get_users' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayUsers(response.data);
            } else {
                showError('載入人員資料失敗：' + response.message);
            }
        },
        error: function() {
            showError('載入人員資料時發生錯誤');
        }
    });
}

function displayUsers(users) {
    const tbody = $('#users-table tbody');
    tbody.empty();
    
    users.forEach(user => {
        const row = $(`
            <tr>
                <td>${user.user_id}</td>
                <td>${user.name}</td>
                <td>${user.username}</td>
                <td><span class="role-badge role-${user.role}">${user.role === 'technician' ? '醫檢員' : '受檢者'}</span></td>
                <td>${user.first_login ? '是' : '否'}</td>
                <td>${formatDateTime(user.created_at)}</td>
                <td class="actions">
                    <button class="btn btn-sm btn-info" onclick="viewUserDetail('${user.id}')">查看</button>
                    <button class="btn btn-sm btn-primary" onclick="editUser('${user.id}')">編輯</button>
                    <button class="btn btn-sm btn-warning" onclick="resetUserPassword('${user.id}', '${user.name}')">重設密碼</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser('${user.id}', '${user.name}')">刪除</button>
                </td>
            </tr>
        `);
        tbody.append(row);
    });
}

function showAddUserModal() {
    $('#userModalTitle').text('新增使用者');
    $('#userForm')[0].reset();
    $('#user_edit_id').val('');
    $('#user_id').prop('readonly', false);
    $('#password_group small').text('必填');
    $('#user_password').prop('required', true);
    showModal('userModal');
}

function viewUserDetail(userId) {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { 
            action: 'get_user_by_id',
            id: userId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const user = response.data;
                $('#detailModalTitle').text('使用者詳細資訊');
                $('#detailModalBody').html(`
                    <div class="detail-info">
                        <div class="detail-section">
                            <h4>基本資訊</h4>
                            <div class="detail-row">
                                <label>人員編號：</label>
                                <span>${user.user_id}</span>
                            </div>
                            <div class="detail-row">
                                <label>姓名：</label>
                                <span>${user.name}</span>
                            </div>
                            <div class="detail-row">
                                <label>帳號：</label>
                                <span>${user.username}</span>
                            </div>
                            <div class="detail-row">
                                <label>角色：</label>
                                <span class="role-badge role-${user.role}">${user.role === 'technician' ? '醫檢員' : '受檢者'}</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4>帳戶狀態</h4>
                            <div class="detail-row">
                                <label>首次登入：</label>
                                <span class="${user.first_login ? 'status-yes' : 'status-no'}">${user.first_login ? '是' : '否'}</span>
                            </div>
                            <div class="detail-row">
                                <label>帳戶狀態：</label>
                                <span class="status-active">正常</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4>時間資訊</h4>
                            <div class="detail-row">
                                <label>建立時間：</label>
                                <span>${formatDateTime(user.created_at)}</span>
                            </div>
                            <div class="detail-row">
                                <label>更新時間：</label>
                                <span>${formatDateTime(user.updated_at)}</span>
                            </div>
                        </div>
                    </div>
                `);
                showModal('detailModal');
            } else {
                showError('載入使用者詳細資訊失敗：' + response.message);
            }
        },
        error: function() {
            showError('載入使用者詳細資訊時發生錯誤');
        }
    });
}

function editUser(userId) {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { 
            action: 'get_user_by_id',
            id: userId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const user = response.data;
                $('#userModalTitle').text('編輯使用者');
                $('#user_edit_id').val(user.id);
                $('#user_id').val(user.user_id).prop('readonly', true);
                $('#user_name').val(user.name);
                $('#user_username').val(user.username);
                $('#user_role').val(user.role);
                $('#user_password').val('').prop('required', false);
                $('#password_group small').text('留空則不修改');
                showModal('userModal');
            } else {
                showError('載入使用者資料失敗：' + response.message);
            }
        },
        error: function() {
            showError('載入使用者資料時發生錯誤');
        }
    });
}

function resetUserPassword(userId, userName) {
    $('#reset_user_id').val(userId);
    $('#resetPasswordModal h3').text(`重設密碼 - ${userName}`);
    $('#resetPasswordForm')[0].reset();
    showModal('resetPasswordModal');
}

function deleteUser(userId, userName) {
    showDeleteConfirm(`確定要刪除使用者「${userName}」嗎？`, () => {
        $.ajax({
            url: '../php/api.php',
            type: 'POST',
            data: { 
                action: 'delete_user',
                id: userId 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess('使用者刪除成功');
                    loadUsers();
                    closeModal('deleteModal');
                } else {
                    showError('刪除失敗：' + response.message);
                }
            },
            error: function() {
                showError('刪除時發生錯誤');
            }
        });
    });
}

// === 醫檢項目管理功能 ===
function loadMedicalItems() {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { action: 'get_medical_items' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayMedicalItems(response.data);
                // 同時更新選項列表
                updateMedicalItemOptions(response.data);
            } else {
                console.error('API Error:', response);
                showError('載入醫檢項目失敗：' + (response.message || '未知錯誤'));
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            let errorMsg = '載入醫檢項目時發生錯誤';
            if (xhr.status === 401) {
                errorMsg = '請重新登入';
                setTimeout(() => {
                    window.location.href = '../index.php';
                }, 2000);
            } else if (xhr.status === 403) {
                errorMsg = '權限不足';
            } else if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg += '：' + (response.message || response.error || xhr.statusText);
                } catch (e) {
                    errorMsg += '：' + xhr.statusText;
                }
            }
            showError(errorMsg);
        }
    });
}

function displayMedicalItems(items) {
    const tbody = $('#medical-items-table tbody');
    tbody.empty();
    
    items.forEach(item => {
        const row = $(`
            <tr>
                <td>${item.item_id}</td>
                <td>${item.name}</td>
                <td class="description-cell">${item.description || '無'}</td>
                <td>${formatDateTime(item.created_at)}</td>
                <td class="actions">
                    <button class="btn btn-sm btn-info" onclick="viewMedicalItemDetail('${item.id}')">查看</button>
                    <button class="btn btn-sm btn-primary" onclick="editMedicalItem('${item.id}')">編輯</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteMedicalItem('${item.id}', '${item.name}')">刪除</button>
                </td>
            </tr>
        `);
        tbody.append(row);
    });
}

function showAddMedicalItemModal() {
    $('#medicalItemModalTitle').text('新增醫檢項目');
    $('#medicalItemForm')[0].reset();
    $('#medical_item_edit_id').val('');
    $('#item_id').prop('readonly', false);
    showModal('medicalItemModal');
}

function editMedicalItem(itemId) {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { 
            action: 'get_medical_item_by_id',
            id: itemId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const item = response.data;
                $('#medicalItemModalTitle').text('編輯醫檢項目');
                $('#medical_item_edit_id').val(item.id);
                $('#item_id').val(item.item_id).prop('readonly', true);
                $('#item_name').val(item.name);
                $('#item_description').val(item.description);
                showModal('medicalItemModal');
            } else {
                showError('載入醫檢項目失敗：' + response.message);
            }
        },
        error: function() {
            showError('載入醫檢項目時發生錯誤');
        }
    });
}

function deleteMedicalItem(itemId, itemName) {
    showDeleteConfirm(`確定要刪除醫檢項目「${itemName}」嗎？`, () => {
        $.ajax({
            url: '../php/api.php',
            type: 'POST',
            data: { 
                action: 'delete_medical_item',
                id: itemId 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess('醫檢項目刪除成功');
                    loadMedicalItems();
                    closeModal('deleteModal');
                } else {
                    showError('刪除失敗：' + response.message);
                }
            },
            error: function() {
                showError('刪除時發生錯誤');
            }
        });
    });
}

function viewMedicalItemDetail(itemId) {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { 
            action: 'get_medical_item_by_id',
            id: itemId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const item = response.data;
                $('#detailModalTitle').text('醫檢項目詳細資訊');
                $('#detailModalBody').html(`
                    <div class="detail-info">
                        <div class="detail-row">
                            <label>項目編號：</label>
                            <span>${item.item_id}</span>
                        </div>
                        <div class="detail-row">
                            <label>項目名稱：</label>
                            <span>${item.name}</span>
                        </div>
                        <div class="detail-row">
                            <label>項目說明：</label>
                            <span>${item.description || '無'}</span>
                        </div>
                        <div class="detail-row">
                            <label>建立時間：</label>
                            <span>${formatDateTime(item.created_at)}</span>
                        </div>
                        <div class="detail-row">
                            <label>更新時間：</label>
                            <span>${formatDateTime(item.updated_at)}</span>
                        </div>
                    </div>
                `);
                showModal('detailModal');
            } else {
                showError('載入詳細資訊失敗：' + response.message);
            }
        },
        error: function() {
            showError('載入詳細資訊時發生錯誤');
        }
    });
}

// === 檢查結果管理功能 ===
function loadTestResults() {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { action: 'get_test_results' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayTestResults(response.data);
            } else {
                showError('載入檢查結果失敗：' + response.message);
            }
        },
        error: function() {
            showError('載入檢查結果時發生錯誤');
        }
    });
}

function displayTestResults(results) {
    const tbody = $('#test-results-table tbody');
    tbody.empty();
    
    results.forEach(result => {
        const scoreClass = getScoreClass(result.score);
        const scoreText = getScoreText(result.score);
        
        const row = $(`
            <tr>
                <td>${result.patient_name} (${result.patient_id})</td>
                <td>${result.item_name} (${result.item_id})</td>
                <td><span class="score-badge ${scoreClass}">${result.score} - ${scoreText}</span></td>
                <td>${result.technician_name} (${result.created_by})</td>
                <td>${formatDateTime(result.created_at)}</td>
                <td class="actions">
                    <button class="btn btn-sm btn-info" onclick="viewTestResultDetail('${result.id}')">查看</button>
                    <button class="btn btn-sm btn-primary" onclick="editTestResult('${result.id}')">編輯</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteTestResult('${result.id}', '${result.patient_name}', '${result.item_name}')">刪除</button>
                </td>
            </tr>
        `);
        tbody.append(row);
    });
}

function showAddTestResultModal() {
    $('#testResultModalTitle').text('新增檢查結果');
    $('#testResultForm')[0].reset();
    $('#test_result_edit_id').val('');
    loadPatientOptions();
    loadMedicalItemOptions();
    showModal('testResultModal');
}

function editTestResult(resultId) {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { 
            action: 'get_test_result_by_id',
            id: resultId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const result = response.data;
                $('#testResultModalTitle').text('編輯檢查結果');
                $('#test_result_edit_id').val(result.id);
                $('#score_input').val(result.score);
                
                loadPatientOptions(() => {
                    $('#patient_select').val(result.patient_id);
                });
                
                loadMedicalItemOptions(() => {
                    $('#medical_item_select').val(result.item_id);
                });
                
                showModal('testResultModal');
            } else {
                showError('載入檢查結果失敗：' + response.message);
            }
        },
        error: function() {
            showError('載入檢查結果時發生錯誤');
        }
    });
}

function viewTestResultDetail(resultId) {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { 
            action: 'get_test_result_by_id',
            id: resultId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const result = response.data;
                const scoreClass = getScoreClass(result.score);
                const scoreText = getScoreText(result.score);
                
                $('#detailModalTitle').text('檢查結果詳細資訊');
                $('#detailModalBody').html(`
                    <div class="detail-info">
                        <div class="detail-section">
                            <h4>受檢者資訊</h4>
                            <div class="detail-row">
                                <label>病患編號：</label>
                                <span>${result.patient_id}</span>
                            </div>
                            <div class="detail-row">
                                <label>病患姓名：</label>
                                <span>${result.patient_name}</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4>檢查項目資訊</h4>
                            <div class="detail-row">
                                <label>項目編號：</label>
                                <span>${result.item_id}</span>
                            </div>
                            <div class="detail-row">
                                <label>項目名稱：</label>
                                <span>${result.item_name}</span>
                            </div>
                            <div class="detail-row">
                                <label>項目說明：</label>
                                <span>${result.item_description || '無'}</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4>檢查結果</h4>
                            <div class="detail-row">
                                <label>度量分數：</label>
                                <span class="score-badge ${scoreClass}">${result.score} - ${scoreText}</span>
                            </div>
                            <div class="detail-row">
                                <label>檢查日期：</label>
                                <span>${formatDateTime(result.created_at)}</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4>操作資訊</h4>
                            <div class="detail-row">
                                <label>執行醫檢員：</label>
                                <span>${result.technician_name} (${result.created_by})</span>
                            </div>
                            <div class="detail-row">
                                <label>建立時間：</label>
                                <span>${formatDateTime(result.created_at)}</span>
                            </div>
                            <div class="detail-row">
                                <label>更新時間：</label>
                                <span>${formatDateTime(result.updated_at)}</span>
                            </div>
                        </div>
                    </div>
                `);
                showModal('detailModal');
            } else {
                showError('載入詳細資訊失敗：' + response.message);
            }
        },
        error: function() {
            showError('載入詳細資訊時發生錯誤');
        }
    });
}

function deleteTestResult(resultId, patientName, itemName) {
    showDeleteConfirm(`確定要刪除「${patientName}」的「${itemName}」檢查結果嗎？`, () => {
        $.ajax({
            url: '../php/api.php',
            type: 'POST',
            data: { 
                action: 'delete_test_result',
                id: resultId 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess('檢查結果刪除成功');
                    loadTestResults();
                    closeModal('deleteModal');
                } else {
                    showError('刪除失敗：' + response.message);
                }
            },
            error: function() {
                showError('刪除時發生錯誤');
            }
        });
    });
}

function showBatchTestResultModal() {
    $('#batchTestResultForm')[0].reset();
    loadPatientOptions(() => {}, '#batch_patient_select');
    loadMedicalItemsForBatch();
    showModal('batchTestResultModal');
}

function loadMedicalItemsForBatch() {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { action: 'get_medical_items' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const container = $('#batchItemsList');
                container.empty();
                
                response.data.forEach(item => {
                    const itemDiv = $(`
                        <div class="batch-item">
                            <label>
                                <input type="checkbox" name="items[]" value="${item.item_id}">
                                ${item.name} (${item.item_id})
                            </label>
                            <input type="number" name="scores[${item.item_id}]" min="1" max="10" step="1" 
                                   placeholder="分數" disabled class="score-input">
                        </div>
                    `);
                    container.append(itemDiv);
                });
                
                // 設定核取方塊事件
                $('input[name="items[]"]').change(function() {
                    const scoreInput = $(this).closest('.batch-item').find('.score-input');
                    scoreInput.prop('disabled', !this.checked);
                    if (!this.checked) scoreInput.val('');
                });
            }
        }
    });
}

// === 輔助功能 ===
function loadPatientOptions(callback = null, selector = '#patient_select') {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { action: 'get_patient_options' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const select = $(selector);
                select.find('option:not(:first)').remove();
                
                response.data.forEach(patient => {
                    select.append(`<option value="${patient.user_id}">${patient.name} (${patient.user_id})</option>`);
                });
                
                if (callback) callback();
            }
        }
    });
}

function loadMedicalItemOptions(callback = null, selector = '#medical_item_select') {
    $.ajax({
        url: '../php/api.php',
        type: 'POST',
        data: { action: 'get_medical_item_options' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const select = $(selector);
                select.find('option:not(:first)').remove();
                
                response.data.forEach(item => {
                    select.append(`<option value="${item.item_id}">${item.name} (${item.item_id})</option>`);
                });
                
                if (callback) callback();
            }
        }
    });
}

function updateMedicalItemOptions(items) {
    const selects = ['#medical_item_select'];
    selects.forEach(selector => {
        const select = $(selector);
        if (select.length > 0) {
            select.find('option:not(:first)').remove();
            items.forEach(item => {
                select.append(`<option value="${item.item_id}">${item.name} (${item.item_id})</option>`);
            });
        }
    });
}

// === 表單處理 ===
function setupFormHandlers() {
    // 使用者表單
    $('#userForm').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const action = $('#user_edit_id').val() ? 'update_user' : 'add_user';
        formData.append('action', action);
        
        // 密碼驗證
        const password = $('#user_password').val();
        if (action === 'add_user' && !password) {
            showError('新增使用者時密碼為必填');
            return;
        }
        
        $.ajax({
            url: '../php/api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(action === 'add_user' ? '使用者新增成功' : '使用者更新成功');
                    closeModal('userModal');
                    loadUsers();
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('操作時發生錯誤');
            }
        });
    });
    
    // 醫檢項目表單
    $('#medicalItemForm').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const action = $('#medical_item_edit_id').val() ? 'update_medical_item' : 'add_medical_item';
        formData.append('action', action);
        
        $.ajax({
            url: '../php/api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(action === 'add_medical_item' ? '醫檢項目新增成功' : '醫檢項目更新成功');
                    closeModal('medicalItemModal');
                    loadMedicalItems();
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('操作時發生錯誤');
            }
        });
    });
    
    // 檢查結果表單
    $('#testResultForm').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const action = $('#test_result_edit_id').val() ? 'update_test_result' : 'add_test_result';
        formData.append('action', action);
        
        $.ajax({
            url: '../php/api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(action === 'add_test_result' ? '檢查結果新增成功' : '檢查結果更新成功');
                    closeModal('testResultModal');
                    loadTestResults();
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('操作時發生錯誤');
            }
        });
    });
    
    // 重設密碼表單
    $('#resetPasswordForm').submit(function(e) {
        e.preventDefault();
        
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        
        if (newPassword !== confirmPassword) {
            showError('新密碼與確認密碼不一致');
            return;
        }
        
        // 密碼規則驗證
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{12,}$/;
        if (!passwordRegex.test(newPassword)) {
            showError('密碼必須包含至少12個字元，且包含英文大小寫與數字');
            return;
        }
        
        const formData = new FormData(this);
        formData.append('action', 'reset_user_password');
        
        $.ajax({
            url: '../php/api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess('密碼重設成功');
                    closeModal('resetPasswordModal');
                } else {
                    showError('重設失敗：' + response.message);
                }
            },
            error: function() {
                showError('重設時發生錯誤');
            }
        });
    });
    
    // 批量新增表單
    $('#batchTestResultForm').submit(function(e) {
        e.preventDefault();
        
        const patientId = $('#batch_patient_select').val();
        const checkedItems = $('input[name="items[]"]:checked');
        
        if (checkedItems.length === 0) {
            showError('請至少選擇一個醫檢項目');
            return;
        }
        
        const batchData = [];
        checkedItems.each(function() {
            const itemId = $(this).val();
            const score = $(`input[name="scores[${itemId}]"]`).val();
            
            if (!score || score < 1 || score > 10) {
                showError(`請為「${$(this).parent().text()}」輸入有效分數 (1-10)`);
                return false;
            }
            
            batchData.push({
                patient_id: patientId,
                item_id: itemId,
                score: parseInt(score)
            });
        });
        
        if (batchData.length === 0) return;
        
        $.ajax({
            url: '../php/api.php',
            type: 'POST',
            data: { 
                action: 'batch_add_test_results',
                results: JSON.stringify(batchData)
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(`批量新增成功，共新增 ${batchData.length} 筆記錄`);
                    closeModal('batchTestResultModal');
                    loadTestResults();
                } else {
                    showError('批量新增失敗：' + response.message);
                }
            },
            error: function() {
                showError('批量新增時發生錯誤');
            }
        });
    });
}

// === 彈出視窗管理 ===
function showModal(modalId) {
    $(`#${modalId}`).css('display', 'flex');
    $('body').addClass('modal-open');
}

function closeModal(modalId) {
    $(`#${modalId}`).hide();
    $('body').removeClass('modal-open');
}

function showDeleteConfirm(message, callback) {
    $('#deleteMessage').text(message);
    $('#confirmDeleteBtn').off('click').on('click', callback);
    showModal('deleteModal');
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

// === 視窗關閉事件 ===
$(window).click(function(event) {
    if ($(event.target).hasClass('modal')) {
        $(event.target).hide();
        $('body').removeClass('modal-open');
    }
});