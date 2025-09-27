<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel {
    protected $table = 'users';
    
    // 根據帳號取得使用者
    public function getByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ?";
        $stmt = $this->query($sql, [$username]);
        return $stmt->fetch();
    }
    
    // 建立新使用者
    public function create($userData) {
        $sql = "INSERT INTO {$this->table} (user_id, name, username, password, role, first_login) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [
            $userData['user_id'],
            $userData['name'],
            $userData['username'],
            $userData['password'],
            $userData['role'] ?? 'patient',
            $userData['first_login'] ?? true
        ];
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    // 更新使用者資料
    public function update($id, $userData) {
        $sql = "UPDATE {$this->table} SET name = ?, username = ? WHERE id = ?";
        $params = [
            $userData['name'],
            $userData['username'],
            $id
        ];
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    // 更新密碼
    public function updatePassword($userId, $hashedPassword) {
        $sql = "UPDATE {$this->table} SET password = ?, first_login = FALSE WHERE user_id = ?";
        $stmt = $this->query($sql, [$hashedPassword, $userId]);
        return $stmt->rowCount() > 0;
    }
    
    // 檢查使用者名稱或使用者ID是否存在
    public function checkUsernameOrUserIdExists($username, $userId, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE (username = ? OR user_id = ?)";
        $params = [$username, $userId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn() > 0;
    }
    
    // 取得所有使用者（排除密碼）
    public function getAllPublic() {
        $sql = "SELECT id, user_id, name, username, role, created_at FROM {$this->table} ORDER BY role, user_id";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
    
    // 取得所有受檢者
    public function getAllPatients() {
        $sql = "SELECT user_id, name FROM {$this->table} WHERE role = 'patient' ORDER BY user_id";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
    
    // 驗證使用者權限
    public function hasPermission($userId, $requiredRole) {
        $sql = "SELECT role FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->query($sql, [$userId]);
        $user = $stmt->fetch();
        
        if (!$user) return false;
        
        return $user['role'] === $requiredRole;
    }
    
    // 刪除使用者相關的測試結果
    public function deleteRelatedTestResults($userId) {
        $sql = "DELETE FROM test_results WHERE patient_id = ? OR created_by = ?";
        $stmt = $this->query($sql, [$userId, $userId]);
        return $stmt->rowCount();
    }
    
    // 更新使用者密碼並設為首次登入
    public function resetPassword($id, $hashedPassword) {
        $sql = "UPDATE users SET password = ?, first_login = TRUE WHERE id = ?";
        $stmt = $this->query($sql, [$hashedPassword, $id]);
        return $stmt->rowCount() > 0;
    }
}
?>