<?php
// 模組化 API 入口檔案
header('Content-Type: application/json; charset=utf-8');

// 啟動 Session（必須在任何輸出之前）
session_start();

// 設定錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 1); // 暫時啟用錯誤顯示來調試

// 自動載入檔案
spl_autoload_register(function ($className) {
    $paths = [
        'controllers/',
        'models/',
        'utils/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// 載入必要檔案
require_once '../config/database.php';
require_once 'utils/ResponseHandler.php';
require_once 'utils/SessionManager.php';

try {
    // 初始化資料庫連接
    $db = new Database();
    $pdo = $db->connect();
    
    // 取得操作類型
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    if (empty($action)) {
        ResponseHandler::error('缺少操作類型參數');
    }
    
    // 路由處理
    routeRequest($action, $pdo);
    
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    ResponseHandler::serverError('資料庫連接失敗');
} catch (Exception $e) {
    error_log('System Error: ' . $e->getMessage());
    error_log('Error File: ' . $e->getFile());
    error_log('Error Line: ' . $e->getLine());
    error_log('Stack Trace: ' . $e->getTraceAsString());
    ResponseHandler::serverError('系統錯誤: ' . $e->getMessage() . ' (調試模式)');
}

// 路由處理函數
function routeRequest($action, $pdo) {
    // 認證相關路由
    if (in_array($action, ['login', 'register', 'change_password', 'logout', 'check_login_status', 'get_current_user'])) {
        $controller = new AuthController($pdo);
        switch ($action) {
            case 'login':
                $controller->login();
                break;
            case 'register':
                $controller->register();
                break;
            case 'change_password':
                $controller->changePassword();
                break;
            case 'logout':
                $controller->logout();
                break;
            case 'check_login_status':
                $controller->checkLoginStatus();
                break;
            case 'get_current_user':
                $controller->getCurrentUser();
                break;
        }
        return;
    }
    
    // 使用者管理路由
    if (in_array($action, ['get_users', 'add_user', 'update_user', 'delete_user', 'get_user_by_id', 'get_patient_options', 'reset_user_password'])) {
        $controller = new UserController($pdo);
        switch ($action) {
            case 'get_users':
                $controller->getUsers();
                break;
            case 'add_user':
                $controller->addUser();
                break;
            case 'update_user':
                $controller->updateUser();
                break;
            case 'delete_user':
                $controller->deleteUser();
                break;
            case 'get_user_by_id':
                $controller->getUserById();
                break;
            case 'get_patient_options':
                $controller->getPatientOptions();
                break;
            case 'reset_user_password':
                $controller->resetUserPassword();
                break;
        }
        return;
    }
    
    // 醫檢項目管理路由
    if (in_array($action, ['get_medical_items', 'add_medical_item', 'update_medical_item', 'delete_medical_item', 'get_medical_item_by_id', 'get_medical_item_options', 'search_medical_items', 'check_item_id_available'])) {
        $controller = new MedicalItemController($pdo);
        switch ($action) {
            case 'get_medical_items':
                $controller->getMedicalItems();
                break;
            case 'add_medical_item':
                $controller->addMedicalItem();
                break;
            case 'update_medical_item':
                $controller->updateMedicalItem();
                break;
            case 'delete_medical_item':
                $controller->deleteMedicalItem();
                break;
            case 'get_medical_item_by_id':
                $controller->getMedicalItemById();
                break;
            case 'get_medical_item_options':
                $controller->getMedicalItemOptions();
                break;
            case 'search_medical_items':
                $controller->searchMedicalItems();
                break;
            case 'check_item_id_available':
                $controller->checkItemIdAvailable();
                break;
        }
        return;
    }
    
    // 檢查結果管理路由
    if (in_array($action, ['get_test_results', 'add_test_result', 'update_test_result', 'delete_test_result', 'get_patient_results', 'get_patient_results_by_technician', 'get_test_result_by_id', 'batch_add_test_results', 'get_statistics', 'get_patient_statistics'])) {
        $controller = new TestResultController($pdo);
        switch ($action) {
            case 'get_test_results':
                $controller->getTestResults();
                break;
            case 'add_test_result':
                $controller->addTestResult();
                break;
            case 'update_test_result':
                $controller->updateTestResult();
                break;
            case 'delete_test_result':
                $controller->deleteTestResult();
                break;
            case 'get_patient_results':
                $controller->getPatientResults();
                break;
            case 'get_patient_results_by_technician':
                $controller->getPatientResultsByTechnician();
                break;
            case 'get_test_result_by_id':
                $controller->getTestResultById();
                break;
            case 'batch_add_test_results':
                $controller->batchAddTestResults();
                break;
            case 'get_statistics':
                $controller->getStatistics();
                break;
            case 'get_patient_statistics':
                $controller->getPatientStatistics();
                break;
        }
        return;
    }
    
    // 未知的操作
    ResponseHandler::error('無效的操作類型: ' . $action);
}

?>