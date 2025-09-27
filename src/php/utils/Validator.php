<?php
// 驗證工具類
class Validator {
    
    // 驗證必填欄位
    public static function required($fields) {
        $errors = [];
        
        foreach ($fields as $field => $value) {
            if (empty($value) && $value !== '0') {
                $errors[] = "{$field} 不能為空";
            }
        }
        
        return $errors;
    }
    
    // 驗證密碼格式
    public static function validatePassword($password) {
        // 密碼必須為12碼，包含英文大小寫與數字
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{12}$/', $password)) {
            return '密碼必須為12碼，包含英文大小寫與數字';
        }
        return null;
    }
    
    // 驗證使用者ID格式
    public static function validateUserId($userId, $role = null) {
        if (empty($userId)) {
            return '使用者ID不能為空';
        }
        
        // 根據角色驗證ID格式
        if ($role === 'technician' && !preg_match('/^T\d{3}$/', $userId)) {
            return '醫檢員ID格式應為 T001 格式';
        }
        
        if ($role === 'patient' && !preg_match('/^P\d{3}$/', $userId)) {
            return '受檢者ID格式應為 P001 格式';
        }
        
        return null;
    }
    
    // 驗證醫檢項目ID格式
    public static function validateItemId($itemId) {
        if (empty($itemId)) {
            return '醫檢項目ID不能為空';
        }
        
        if (!preg_match('/^MI\d{3}$/', $itemId)) {
            return '醫檢項目ID格式應為 MI001 格式';
        }
        
        return null;
    }
    
    // 驗證分數範圍
    public static function validateScore($score) {
        if (!is_numeric($score)) {
            return '分數必須為數字';
        }
        
        $score = (int)$score;
        if ($score < 1 || $score > 10) {
            return '分數必須在1到10之間';
        }
        
        return null;
    }
    
    // 驗證帳號格式
    public static function validateUsername($username) {
        if (empty($username)) {
            return '帳號不能為空';
        }
        
        if (strlen($username) < 3 || strlen($username) > 50) {
            return '帳號長度必須在3到50字符之間';
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return '帳號只能包含英文、數字和下底線';
        }
        
        return null;
    }
    
    // 驗證姓名格式
    public static function validateName($name) {
        if (empty($name)) {
            return '姓名不能為空';
        }
        
        if (strlen($name) > 100) {
            return '姓名長度不能超過100字符';
        }
        
        return null;
    }
    
    // 驗證角色
    public static function validateRole($role) {
        $validRoles = ['technician', 'patient'];
        
        if (!in_array($role, $validRoles)) {
            return '無效的角色類型';
        }
        
        return null;
    }
    
    // 統一驗證器
    public static function validate($rules, $data) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // 檢查必填
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = $rule['message'] ?? "{$field} 為必填欄位";
                continue;
            }
            
            // 如果值為空且非必填，跳過其他驗證
            if (empty($value) && !isset($rule['required'])) {
                continue;
            }
            
            // 執行自定義驗證
            if (isset($rule['validator']) && is_callable($rule['validator'])) {
                $error = $rule['validator']($value);
                if ($error) {
                    $errors[$field] = $error;
                }
            }
        }
        
        return $errors;
    }
}
?>