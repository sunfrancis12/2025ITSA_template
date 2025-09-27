<?php

class AuthController extends BaseController {
    private $userModel;
    
    public function __construct($pdo) {
        $this->userModel = new UserModel($pdo);
    }
    
    // 使用者登入
    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // 驗證輸入
        $errors = Validator::required([
            '帳號' => $username,
            '密碼' => $password
        ]);
        
        if (!empty($errors)) {
            ResponseHandler::validationError(implode(', ', $errors));
        }
        
        // 查詢使用者
        $user = $this->userModel->getByUsername($username);
        
        if (!$user || !password_verify($password, $user['password'])) {
            ResponseHandler::error('帳號或密碼錯誤', 401);
        }
        
        // 設置會話
        SessionManager::setUserSession($user);
        
        ResponseHandler::success('登入成功', [
            'user' => [
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'role' => $user['role'],
                'first_login' => (bool)$user['first_login']
            ]
        ]);
    }
    
    // 使用者註冊
    public function register() {
        $userData = [
            'user_id' => $_POST['user_id'] ?? '',
            'name' => $_POST['name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
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
            'password' => [
                'required' => true,
                'validator' => function($value) {
                    if (strlen($value) < 6) {
                        return '密碼至少需要6個字符';
                    }
                    return null;
                }
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
        
        // 加密密碼
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        $userData['first_login'] = ($userData['role'] === 'patient');
        
        // 建立使用者
        if ($this->userModel->create($userData)) {
            ResponseHandler::success('註冊成功');
        } else {
            ResponseHandler::serverError('註冊失敗');
        }
    }
    
    // 修改密碼
    public function changePassword() {
        $this->requireLogin();
        
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // 基本驗證
        if (empty($newPassword) || empty($confirmPassword)) {
            ResponseHandler::validationError('密碼不能為空');
        }
        
        if ($newPassword !== $confirmPassword) {
            ResponseHandler::validationError('兩次密碼輸入不一致');
        }
        
        // 密碼格式驗證
        $passwordError = Validator::validatePassword($newPassword);
        if ($passwordError) {
            ResponseHandler::validationError($passwordError);
        }
        
        // 更新密碼
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $userId = $this->getCurrentUserId();
        
        if ($this->userModel->updatePassword($userId, $hashedPassword)) {
            ResponseHandler::success('密碼修改成功');
        } else {
            ResponseHandler::serverError('密碼修改失敗');
        }
    }
    
    // 登出
    public function logout() {
        SessionManager::destroy();
        ResponseHandler::success('登出成功');
    }
    
    // 取得當前使用者資訊
    public function getCurrentUser() {
        $this->requireLogin();
        
        $sessionInfo = SessionManager::getSessionInfo();
        ResponseHandler::success('取得使用者資訊成功', $sessionInfo);
    }
    
    // 檢查登入狀態
    public function checkLoginStatus() {
        if ($this->isLoggedIn()) {
            $sessionInfo = SessionManager::getSessionInfo();
            ResponseHandler::success('已登入', $sessionInfo);
        } else {
            ResponseHandler::error('未登入', 401);
        }
    }
}
?>