<?php
// 基礎控制器類，提供通用功能
abstract class BaseController {
    
    // 檢查登入狀態
    protected function requireLogin() {
        try {
            SessionManager::requireLogin();
        } catch (Exception $e) {
            ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }
    
    // 檢查角色權限
    protected function requireRole($requiredRole) {
        try {
            SessionManager::requireRole($requiredRole);
        } catch (Exception $e) {
            ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }
    
    // 取得當前使用者ID
    protected function getCurrentUserId() {
        return SessionManager::getCurrentUserId();
    }
    
    // 取得當前使用者角色
    protected function getCurrentUserRole() {
        return SessionManager::getCurrentUserRole();
    }
    
    // 取得當前使用者名稱
    protected function getCurrentUserName() {
        return SessionManager::getCurrentUserName();
    }
    
    // 檢查是否已登入
    protected function isLoggedIn() {
        return SessionManager::isLoggedIn();
    }
}
?>