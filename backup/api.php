<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

// 設定錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new Database();
    $pdo = $db->connect();
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'login':
            login($pdo);
            break;
        case 'register':
            register($pdo);
            break;
        case 'change_password':
            changePassword($pdo);
            break;
        case 'logout':
            logout();
            break;
        case 'get_users':
            getUsers($pdo);
            break;
        case 'add_user':
            addUser($pdo);
            break;
        case 'update_user':
            updateUser($pdo);
            break;
        case 'delete_user':
            deleteUser($pdo);
            break;
        case 'get_medical_items':
            getMedicalItems($pdo);
            break;
        case 'add_medical_item':
            addMedicalItem($pdo);
            break;
        case 'update_medical_item':
            updateMedicalItem($pdo);
            break;
        case 'delete_medical_item':
            deleteMedicalItem($pdo);
            break;
        case 'get_test_results':
            getTestResults($pdo);
            break;
        case 'add_test_result':
            addTestResult($pdo);
            break;
        case 'update_test_result':
            updateTestResult($pdo);
            break;
        case 'delete_test_result':
            deleteTestResult($pdo);
            break;
        case 'get_patient_results':
            getPatientResults($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => '無效的操作']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '系統錯誤: ' . $e->getMessage()]);
}

// 登入功能
function login($pdo) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '帳號密碼不能為空']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
        echo json_encode([
            'success' => true, 
            'message' => '登入成功',
            'user' => [
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'role' => $user['role'],
                'first_login' => $user['first_login']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '帳號或密碼錯誤']);
    }
}

// 註冊功能
function register($pdo) {
    $user_id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'patient';
    
    if (empty($user_id) || empty($name) || empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }
    
    // 檢查帳號是否已存在
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR user_id = ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => '帳號或人員編號已存在']);
        return;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (user_id, name, username, password, role) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $name, $username, $hashed_password, $role])) {
        echo json_encode(['success' => true, 'message' => '註冊成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '註冊失敗']);
    }
}

// 修改密碼功能
function changePassword($pdo) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => '請先登入']);
        return;
    }
    
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => '密碼不能為空']);
        return;
    }
    
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => '兩次密碼輸入不一致']);
        return;
    }
    
    // 驗證密碼格式（12碼，英文大小寫與數字混合）
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{12}$/', $new_password)) {
        echo json_encode(['success' => false, 'message' => '密碼必須為12碼，包含英文大小寫與數字']);
        return;
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ?, first_login = FALSE WHERE user_id = ?");
    if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
        echo json_encode(['success' => true, 'message' => '密碼修改成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '密碼修改失敗']);
    }
}

// 登出功能
function logout() {
    session_destroy();
    echo json_encode(['success' => true, 'message' => '登出成功']);
}

// 取得使用者列表
function getUsers($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT user_id, name, username, role, created_at FROM users ORDER BY role, user_id");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $users]);
}

// 新增使用者
function addUser($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $user_id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $role = $_POST['role'] ?? 'patient';
    $default_password = 'Temp123456789'; // 預設密碼
    
    if (empty($user_id) || empty($name) || empty($username)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }
    
    // 檢查帳號是否已存在
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR user_id = ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => '帳號或人員編號已存在']);
        return;
    }
    
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (user_id, name, username, password, role) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $name, $username, $hashed_password, $role])) {
        echo json_encode(['success' => true, 'message' => '新增使用者成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '新增使用者失敗']);
    }
}

// 更新使用者
function updateUser($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    
    if (empty($id) || empty($name) || empty($username)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ? WHERE id = ?");
    if ($stmt->execute([$name, $username, $id])) {
        echo json_encode(['success' => true, 'message' => '更新使用者成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '更新使用者失敗']);
    }
}

// 刪除使用者
function deleteUser($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => '缺少使用者ID']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['success' => true, 'message' => '刪除使用者成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '刪除使用者失敗']);
    }
}

// 取得醫檢項目列表
function getMedicalItems($pdo) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => '請先登入']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM medical_items ORDER BY item_id");
    $stmt->execute();
    $items = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $items]);
}

// 新增醫檢項目
function addMedicalItem($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $item_id = $_POST['item_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($item_id) || empty($name)) {
        echo json_encode(['success' => false, 'message' => '項目編號和名稱不能為空']);
        return;
    }
    
    // 檢查項目編號是否已存在
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM medical_items WHERE item_id = ?");
    $stmt->execute([$item_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => '項目編號已存在']);
        return;
    }
    
    $stmt = $pdo->prepare("INSERT INTO medical_items (item_id, name, description) VALUES (?, ?, ?)");
    if ($stmt->execute([$item_id, $name, $description])) {
        echo json_encode(['success' => true, 'message' => '新增醫檢項目成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '新增醫檢項目失敗']);
    }
}

// 更新醫檢項目
function updateMedicalItem($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($id) || empty($name)) {
        echo json_encode(['success' => false, 'message' => '項目ID和名稱不能為空']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE medical_items SET name = ?, description = ? WHERE id = ?");
    if ($stmt->execute([$name, $description, $id])) {
        echo json_encode(['success' => true, 'message' => '更新醫檢項目成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '更新醫檢項目失敗']);
    }
}

// 刪除醫檢項目
function deleteMedicalItem($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => '缺少項目ID']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM medical_items WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['success' => true, 'message' => '刪除醫檢項目成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '刪除醫檢項目失敗']);
    }
}

// 取得檢查結果列表
function getTestResults($pdo) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => '請先登入']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT tr.*, u.name as patient_name, mi.name as item_name, creator.name as creator_name
        FROM test_results tr
        JOIN users u ON tr.patient_id = u.user_id
        JOIN medical_items mi ON tr.item_id = mi.item_id
        JOIN users creator ON tr.created_by = creator.user_id
        ORDER BY tr.patient_id, tr.item_id
    ");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $results]);
}

// 新增檢查結果
function addTestResult($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $patient_id = $_POST['patient_id'] ?? '';
    $item_id = $_POST['item_id'] ?? '';
    $score = $_POST['score'] ?? '';
    
    if (empty($patient_id) || empty($item_id) || empty($score)) {
        echo json_encode(['success' => false, 'message' => '所有欄位都必須填寫']);
        return;
    }
    
    if ($score < 1 || $score > 10) {
        echo json_encode(['success' => false, 'message' => '分數必須在1到10之間']);
        return;
    }
    
    // 檢查是否已有該受檢者的該項目結果
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_results WHERE patient_id = ? AND item_id = ?");
    $stmt->execute([$patient_id, $item_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => '該受檢者此項目已有結果，請使用更新功能']);
        return;
    }
    
    $stmt = $pdo->prepare("INSERT INTO test_results (patient_id, item_id, score, created_by) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$patient_id, $item_id, $score, $_SESSION['user_id']])) {
        echo json_encode(['success' => true, 'message' => '新增檢查結果成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '新增檢查結果失敗']);
    }
}

// 更新檢查結果
function updateTestResult($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    $score = $_POST['score'] ?? '';
    
    if (empty($id) || empty($score)) {
        echo json_encode(['success' => false, 'message' => '結果ID和分數不能為空']);
        return;
    }
    
    if ($score < 1 || $score > 10) {
        echo json_encode(['success' => false, 'message' => '分數必須在1到10之間']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE test_results SET score = ? WHERE id = ?");
    if ($stmt->execute([$score, $id])) {
        echo json_encode(['success' => true, 'message' => '更新檢查結果成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '更新檢查結果失敗']);
    }
}

// 刪除檢查結果
function deleteTestResult($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => '缺少結果ID']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM test_results WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['success' => true, 'message' => '刪除檢查結果成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '刪除檢查結果失敗']);
    }
}

// 取得受檢者個人檢查結果
function getPatientResults($pdo) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT tr.*, mi.name as item_name, mi.description as item_description
        FROM test_results tr
        JOIN medical_items mi ON tr.item_id = mi.item_id
        WHERE tr.patient_id = ?
        ORDER BY tr.item_id
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $results = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $results]);
}
?>