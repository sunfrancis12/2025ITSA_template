<?php
// 會話管理工具類
class SessionManager {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // 設置會話資料
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    // 取得會話資料
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    // 移除會話資料
    public static function remove($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }
    
    // 檢查會話是否存在
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    // 清除所有會話資料
    public static function clear() {
        self::start();
        $_SESSION = [];
    }
    
    // 銷毀會話
    public static function destroy() {
        self::start();
        session_destroy();
    }
    
    // 檢查使用者是否已登入
    public static function isLoggedIn() {
        return self::has('user_id') && self::has('role');
    }
    
    // 取得當前使用者ID
    public static function getCurrentUserId() {
        return self::get('user_id');
    }
    
    // 取得當前使用者角色
    public static function getCurrentUserRole() {
        return self::get('role');
    }
    
    // 取得當前使用者名稱
    public static function getCurrentUserName() {
        return self::get('name');
    }
    
    // 設置使用者登入資訊
    public static function setUserSession($user) {
        self::set('user_id', $user['user_id']);
        self::set('username', $user['username']);
        self::set('name', $user['name']);
        self::set('role', $user['role']);
        self::set('login_time', time());
    }
    
    // 檢查使用者權限
    public static function checkPermission($requiredRole) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $userRole = self::getCurrentUserRole();
        
        // 如果要求醫檢員權限
        if ($requiredRole === 'technician') {
            return $userRole === 'technician';
        }
        
        // 如果要求受檢者權限
        if ($requiredRole === 'patient') {
            return $userRole === 'patient';
        }
        
        return true;
    }
    
    // 要求登入
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            throw new Exception('請先登入', 401);
        }
    }
    
    // 要求特定角色權限
    public static function requireRole($requiredRole) {
        self::requireLogin();
        
        if (!self::checkPermission($requiredRole)) {
            throw new Exception('權限不足', 403);
        }
    }
    
    // 產生 CSRF Token
    public static function generateCSRFToken() {
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        return $token;
    }
    
    // 驗證 CSRF Token
    public static function validateCSRFToken($token) {
        $sessionToken = self::get('csrf_token');
        return hash_equals($sessionToken, $token);
    }
    
    // 取得會話資訊
    public static function getSessionInfo() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'user_id' => self::getCurrentUserId(),
            'username' => self::get('username'),
            'name' => self::getCurrentUserName(),
            'role' => self::getCurrentUserRole(),
            'login_time' => self::get('login_time')
        ];
    }
}
?>