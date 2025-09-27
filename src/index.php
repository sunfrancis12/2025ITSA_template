<?php
// 醫檢系統首頁
session_start();

// 檢查是否已登入，如果是則重定向到相應頁面
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'technician') {
        header('Location: pages/technician.php');
    } else {
        header('Location: pages/patient.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>醫檢系統 - 首頁</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/debug.js"></script>
    <script src="assets/js/system-init.js"></script>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <h1>醫檢系統</h1>
            <p class="subtitle">Medical Testing System</p>
        </header>

        <!-- 登入畫面 -->
        <div id="login-container" class="auth-container">
            <div class="auth-box">
                <h2>系統登入</h2>
                <form id="login-form">
                    <div class="form-group">
                        <label for="username">帳號：</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">密碼：</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">登入</button>
                        <button type="button" class="btn btn-secondary" onclick="showRegister()">註冊</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 註冊畫面 -->
        <div id="register-container" class="auth-container" style="display: none;">
            <div class="auth-box">
                <h2>使用者註冊</h2>
                <form id="register-form">
                    <div class="form-group">
                        <label for="reg-user-id">人員編號：</label>
                        <input type="text" id="reg-user-id" name="user_id" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-name">姓名：</label>
                        <input type="text" id="reg-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-username">帳號：</label>
                        <input type="text" id="reg-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-password">密碼：</label>
                        <input type="password" id="reg-password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-role">角色：</label>
                        <select id="reg-role" name="role" required>
                            <option value="technician">醫檢員</option>
                            <option value="patient" selected>受檢者</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">註冊</button>
                        <button type="button" class="btn btn-secondary" onclick="showLogin()">返回登入</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 修改密碼畫面 -->
        <div id="change-password-container" class="auth-container" style="display: none;">
            <div class="auth-box">
                <h2>首次登入 - 必須修改密碼</h2>
                <p class="password-rule">密碼規則：12碼，包含英文大小寫與數字</p>
                <form id="change-password-form">
                    <div class="form-group">
                        <label for="new-password">新密碼：</label>
                        <input type="password" id="new-password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">確認密碼：</label>
                        <input type="password" id="confirm-password" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">修改密碼</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <p>&copy; 2025 醫檢系統. All rights reserved.</p>
    </footer>

    <script src="assets/js/auth.js"></script>
</body>
</html>