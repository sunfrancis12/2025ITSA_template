<?php
// 受檢者頁面
session_start();

// 檢查登入狀態和權限
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../index.php');
    exit();
}

include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="header">
        <h1>醫檢系統 - 受檢者介面</h1>
        <div class="user-info">
            歡迎，<span id="patient-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <button class="btn btn-logout" onclick="logout()">登出</button>
        </div>
    </div>

    <div class="patient-results">
        <h3>我的檢查結果</h3>
        <div id="patient-results-container">
            <div class="loading">載入中...</div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="../assets/js/patient.js"></script>
</body>
</html>