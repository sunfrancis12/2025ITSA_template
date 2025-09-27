<?php

class UserController extends BaseController {
    private $userModel;
    
    public function __construct($pdo) {
        $this->userModel = new UserModel($pdo);
    }
    
    // 取得所有使用者
    public function getUsers() {
        $this->requireRole('technician');
        
        $users = $this->userModel->getAllPublic();
        ResponseHandler::success('取得使用者列表成功', $users);
    }
    
    // 新增使用者
    public function addUser() {
        $this->requireRole('technician');
        
        $userData = [
            'user_id' => $_POST['user_id'] ?? '',
            'name' => $_POST['name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'role' => $_POST['role'] ?? 'patient'
        ];
        
        // 驗證規則
        $rules = [
            'user_id' => [
                'required' => true,
                'validator' => function($value) use ($userData) {
                    return Validator::validateUserId($value, $userData['role']);
                }
            ],
            'name' => [
                'required' => true,
                'validator' => [Validator::class, 'validateName']
            ],
            'username' => [
                'required' => true,
                'validator' => [Validator::class, 'validateUsername']
            ],
            'role' => [
                'required' => true,
                'validator' => [Validator::class, 'validateRole']
            ]
        ];
        
        $errors = Validator::validate($rules, $userData);
        
        if (!empty($errors)) {
            ResponseHandler::validationError(implode(', ', array_values($errors)));
        }
        
        // 檢查帳號是否已存在
        if ($this->userModel->checkUsernameOrUserIdExists($userData['username'], $userData['user_id'])) {
            ResponseHandler::error('帳號或人員編號已存在');
        }
        
        // 設置預設密碼
        $defaultPassword = 'Temp123456789';
        $userData['password'] = password_hash($defaultPassword, PASSWORD_DEFAULT);
        $userData['first_login'] = ($userData['role'] === 'patient');
        
        // 建立使用者
        if ($this->userModel->create($userData)) {
            ResponseHandler::success('新增使用者成功');
        } else {
            ResponseHandler::serverError('新增使用者失敗');
        }
    }
    
    // 更新使用者
    public function updateUser() {
        $this->requireRole('technician');
        
        $id = $_POST['id'] ?? '';
        $userData = [
            'name' => $_POST['name'] ?? '',
            'username' => $_POST['username'] ?? ''
        ];
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少使用者ID');
        }
        
        // 驗證規則
        $rules = [
            'name' => [
                'required' => true,
                'validator' => [Validator::class, 'validateName']
            ],
            'username' => [
                'required' => true,
                'validator' => [Validator::class, 'validateUsername']
            ]
        ];
        
        $errors = Validator::validate($rules, $userData);
        
        if (!empty($errors)) {
            ResponseHandler::validationError(implode(', ', array_values($errors)));
        }
        
        // 檢查使用者是否存在
        $existingUser = $this->userModel->getById($id);
        if (!$existingUser) {
            ResponseHandler::notFound('使用者不存在');
        }
        
        // 檢查帳號是否被其他使用者使用
        if ($this->userModel->exists('username', $userData['username'], $id)) {
            ResponseHandler::error('帳號已被其他使用者使用');
        }
        
        // 更新使用者
        if ($this->userModel->update($id, $userData)) {
            ResponseHandler::success('更新使用者成功');
        } else {
            ResponseHandler::serverError('更新使用者失敗');
        }
    }
    
    // 刪除使用者
    public function deleteUser() {
        $this->requireRole('technician');
        
        $id = $_POST['id'] ?? '';
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少使用者ID');
        }
        
        // 檢查使用者是否存在
        $user = $this->userModel->getById($id);
        if (!$user) {
            ResponseHandler::notFound('使用者不存在');
        }
        
        // 不允許刪除自己
        $currentUserId = $this->getCurrentUserId();
        if ($user['user_id'] === $currentUserId) {
            ResponseHandler::error('不能刪除自己的帳號');
        }
        
        // 開始交易
        $this->userModel->beginTransaction();
        
        try {
            // 刪除相關的檢查結果
            $this->userModel->deleteRelatedTestResults($user['user_id']);
            
            // 刪除使用者
            if ($this->userModel->delete($id)) {
                $this->userModel->commit();
                ResponseHandler::success('刪除使用者成功');
            } else {
                $this->userModel->rollBack();
                ResponseHandler::serverError('刪除使用者失敗');
            }
            
        } catch (Exception $e) {
            $this->userModel->rollBack();
            ResponseHandler::serverError('刪除使用者失敗: ' . $e->getMessage());
        }
    }
    
    // 取得使用者詳細資訊
    public function getUserById() {
        $this->requireRole('technician');
        
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少使用者ID');
        }
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            ResponseHandler::notFound('使用者不存在');
        }
        
        // 移除敏感資訊
        unset($user['password']);
        
        ResponseHandler::success('取得使用者資訊成功', $user);
    }
    
    // 取得受檢者選項（用於下拉選單）
    public function getPatientOptions() {
        $this->requireRole('technician');
        
        $patients = $this->userModel->getAllPatients();
        ResponseHandler::success('取得受檢者選項成功', $patients);
    }
    
    // 重設使用者密碼
    public function resetUserPassword() {
        $this->requireRole('technician');
        
        $id = $_POST['id'] ?? '';
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少使用者ID');
        }
        
        $user = $this->userModel->getById($id);
        if (!$user) {
            ResponseHandler::notFound('使用者不存在');
        }
        
        // 重設為預設密碼
        $defaultPassword = 'Temp123456789';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        // 更新密碼並設為首次登入
        if ($this->userModel->resetPassword($id, $hashedPassword)) {
            ResponseHandler::success('密碼重設成功，預設密碼為: ' . $defaultPassword);
        } else {
            ResponseHandler::serverError('密碼重設失敗');
        }
    }
}
?>