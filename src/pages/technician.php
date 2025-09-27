<?php
// 醫檢員頁面
session_start();

// 檢查登入狀態和權限
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header('Location: ../index.php');
    exit();
}

include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="header">
        <h1>醫檢系統 - 醫檢員介面</h1>
        <div class="user-info">
            歡迎，<span id="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <button class="btn btn-logout" onclick="logout()">登出</button>
        </div>
    </div>

    <div class="tab-container">
        <div class="tabs">
            <button class="tab-button active" onclick="showTab('users')">人員管理</button>
            <button class="tab-button" onclick="showTab('medical-items')">醫檢項目管理</button>
            <button class="tab-button" onclick="showTab('test-results')">檢查結果管理</button>
        </div>

        <!-- 人員管理標籤 -->
        <div id="users-tab" class="tab-content active">
            <h3>人員管理</h3>
            <button class="btn btn-primary" onclick="showAddUserModal()">新增人員</button>
            <div class="table-container">
                <table id="users-table" class="data-table">
                    <thead>
                        <tr>
                            <th>人員編號</th>
                            <th>姓名</th>
                            <th>帳號</th>
                            <th>角色</th>
                            <th>首次登入</th>
                            <th>建立時間</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 醫檢項目管理標籤 -->
        <div id="medical-items-tab" class="tab-content">
            <h3>醫檢項目管理</h3>
            <button class="btn btn-primary" onclick="showAddMedicalItemModal()">新增醫檢項目</button>
            <div class="table-container">
                <table id="medical-items-table" class="data-table">
                    <thead>
                        <tr>
                            <th>項目編號</th>
                            <th>項目名稱</th>
                            <th>說明</th>
                            <th>建立時間</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 檢查結果管理標籤 -->
        <div id="test-results-tab" class="tab-content">
            <h3>檢查結果管理</h3>
            <div class="button-group">
                <button class="btn btn-primary" onclick="showAddTestResultModal()">新增檢查結果</button>
                <button class="btn btn-secondary" onclick="showBatchTestResultModal()">批量新增</button>
            </div>
            <div class="table-container">
                <table id="test-results-table" class="data-table">
                    <thead>
                        <tr>
                            <th>受檢者</th>
                            <th>醫檢項目</th>
                            <th>度量分數</th>
                            <th>建立人員</th>
                            <th>建立時間</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/modals.php'; ?>
<?php include '../includes/footer.php'; ?>

<script src="../assets/js/technician.js"></script>
</body>
</html>