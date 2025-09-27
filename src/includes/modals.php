<!-- 使用者管理相關彈出視窗 -->
<!-- 新增/編輯使用者彈出視窗 -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="userModalTitle">新增使用者</h3>
            <span class="close" onclick="closeModal('userModal')">&times;</span>
        </div>
        <form id="userForm">
            <input type="hidden" id="user_edit_id" name="edit_id">
            <div class="form-group">
                <label for="user_id">人員編號：</label>
                <input type="text" id="user_id" name="user_id" required maxlength="20">
                <small class="form-help">格式：T001(醫檢員) 或 P001(病患)</small>
            </div>
            <div class="form-group">
                <label for="user_name">姓名：</label>
                <input type="text" id="user_name" name="name" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="user_username">帳號：</label>
                <input type="text" id="user_username" name="username" required maxlength="50">
            </div>
            <div class="form-group">
                <label for="user_role">角色：</label>
                <select id="user_role" name="role" required>
                    <option value="">請選擇角色</option>
                    <option value="technician">醫檢員</option>
                    <option value="patient">受檢者</option>
                </select>
            </div>
            <div class="form-group" id="password_group">
                <label for="user_password">密碼：</label>
                <input type="password" id="user_password" name="password">
                <small class="form-help">新增時必填，編輯時留空則不修改</small>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">儲存</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('userModal')">取消</button>
            </div>
        </form>
    </div>
</div>

<!-- 醫檢項目管理相關彈出視窗 -->
<!-- 新增/編輯醫檢項目彈出視窗 -->
<div id="medicalItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="medicalItemModalTitle">新增醫檢項目</h3>
            <span class="close" onclick="closeModal('medicalItemModal')">&times;</span>
        </div>
        <form id="medicalItemForm">
            <input type="hidden" id="medical_item_edit_id" name="edit_id">
            <div class="form-group">
                <label for="item_id">項目編號：</label>
                <input type="text" id="item_id" name="item_id" required maxlength="20">
                <small class="form-help">格式：MI001, MI002...</small>
            </div>
            <div class="form-group">
                <label for="item_name">項目名稱：</label>
                <input type="text" id="item_name" name="name" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="item_description">項目說明：</label>
                <textarea id="item_description" name="description" rows="4" placeholder="請輸入詳細說明..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">儲存</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('medicalItemModal')">取消</button>
            </div>
        </form>
    </div>
</div>

<!-- 檢查結果管理相關彈出視窗 -->
<!-- 新增/編輯檢查結果彈出視窗 -->
<div id="testResultModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="testResultModalTitle">新增檢查結果</h3>
            <span class="close" onclick="closeModal('testResultModal')">&times;</span>
        </div>
        <form id="testResultForm">
            <input type="hidden" id="test_result_edit_id" name="edit_id">
            <div class="form-group">
                <label for="patient_select">受檢者：</label>
                <select id="patient_select" name="patient_id" required>
                    <option value="">請選擇受檢者</option>
                </select>
            </div>
            <div class="form-group">
                <label for="medical_item_select">醫檢項目：</label>
                <select id="medical_item_select" name="item_id" required>
                    <option value="">請選擇醫檢項目</option>
                </select>
            </div>
            <div class="form-group">
                <label for="score_input">度量分數：</label>
                <input type="number" id="score_input" name="score" required min="1" max="10" step="1">
                <small class="form-help">範圍：1-10 (1-5: 需改善, 6-7: 良好, 8-10: 優良)</small>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">儲存</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('testResultModal')">取消</button>
            </div>
        </form>
    </div>
</div>

<!-- 刪除確認彈出視窗 -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>刪除確認</h3>
            <span class="close" onclick="closeModal('deleteModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p id="deleteMessage">確定要刪除此項目嗎？</p>
            <p class="warning">此操作無法復原！</p>
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">確認刪除</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">取消</button>
        </div>
    </div>
</div>

<!-- 密碼重設彈出視窗 -->
<div id="resetPasswordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>重設密碼</h3>
            <span class="close" onclick="closeModal('resetPasswordModal')">&times;</span>
        </div>
        <form id="resetPasswordForm">
            <input type="hidden" id="reset_user_id" name="user_id">
            <div class="form-group">
                <label for="new_password">新密碼：</label>
                <input type="password" id="new_password" name="new_password" required>
                <small class="form-help">至少12個字元，包含英文大小寫與數字</small>
            </div>
            <div class="form-group">
                <label for="confirm_password">確認密碼：</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">重設密碼</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('resetPasswordModal')">取消</button>
            </div>
        </form>
    </div>
</div>

<!-- 批量新增檢查結果彈出視窗 -->
<div id="batchTestResultModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>批量新增檢查結果</h3>
            <span class="close" onclick="closeModal('batchTestResultModal')">&times;</span>
        </div>
        <form id="batchTestResultForm">
            <div class="form-group">
                <label for="batch_patient_select">受檢者：</label>
                <select id="batch_patient_select" name="patient_id" required>
                    <option value="">請選擇受檢者</option>
                </select>
            </div>
            <div class="batch-items-container">
                <h4>醫檢項目與分數：</h4>
                <div id="batchItemsList">
                    <!-- 動態產生的項目列表 -->
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">批量儲存</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('batchTestResultModal')">取消</button>
            </div>
        </form>
    </div>
</div>

<!-- 詳細資訊檢視彈出視窗 -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="detailModalTitle">詳細資訊</h3>
            <span class="close" onclick="closeModal('detailModal')">&times;</span>
        </div>
        <div class="modal-body" id="detailModalBody">
            <!-- 動態內容 -->
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal('detailModal')">關閉</button>
        </div>
    </div>
</div>