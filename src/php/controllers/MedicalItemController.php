<?php

class MedicalItemController extends BaseController {
    private $medicalItemModel;
    
    public function __construct($pdo) {
        $this->medicalItemModel = new MedicalItemModel($pdo);
    }
    
    // 取得所有醫檢項目
    public function getMedicalItems() {
        $this->requireLogin();
        
        $items = $this->medicalItemModel->getAll();
        ResponseHandler::success('取得醫檢項目列表成功', $items);
    }
    
    // 新增醫檢項目
    public function addMedicalItem() {
        $this->requireRole('technician');
        
        $itemData = [
            'item_id' => $_POST['item_id'] ?? '',
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];
        
        // 驗證規則
        $rules = [
            'item_id' => [
                'required' => true,
                'validator' => [Validator::class, 'validateItemId']
            ],
            'name' => [
                'required' => true,
                'validator' => function($value) {
                    if (empty($value)) {
                        return '項目名稱不能為空';
                    }
                    if (strlen($value) > 100) {
                        return '項目名稱長度不能超過100字符';
                    }
                    return null;
                }
            ],
            'description' => [
                'validator' => function($value) {
                    if (strlen($value) > 500) {
                        return '項目說明長度不能超過500字符';
                    }
                    return null;
                }
            ]
        ];
        
        $errors = Validator::validate($rules, $itemData);
        
        if (!empty($errors)) {
            ResponseHandler::validationError(implode(', ', array_values($errors)));
        }
        
        // 檢查項目ID是否已存在
        if ($this->medicalItemModel->itemIdExists($itemData['item_id'])) {
            ResponseHandler::error('項目編號已存在');
        }
        
        // 建立醫檢項目
        if ($this->medicalItemModel->create($itemData)) {
            ResponseHandler::success('新增醫檢項目成功');
        } else {
            ResponseHandler::serverError('新增醫檢項目失敗');
        }
    }
    
    // 更新醫檢項目
    public function updateMedicalItem() {
        $this->requireRole('technician');
        
        $id = $_POST['id'] ?? '';
        $itemData = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少項目ID');
        }
        
        // 驗證規則
        $rules = [
            'name' => [
                'required' => true,
                'validator' => function($value) {
                    if (empty($value)) {
                        return '項目名稱不能為空';
                    }
                    if (strlen($value) > 100) {
                        return '項目名稱長度不能超過100字符';
                    }
                    return null;
                }
            ],
            'description' => [
                'validator' => function($value) {
                    if (strlen($value) > 500) {
                        return '項目說明長度不能超過500字符';
                    }
                    return null;
                }
            ]
        ];
        
        $errors = Validator::validate($rules, $itemData);
        
        if (!empty($errors)) {
            ResponseHandler::validationError(implode(', ', array_values($errors)));
        }
        
        // 檢查項目是否存在
        $existingItem = $this->medicalItemModel->getById($id);
        if (!$existingItem) {
            ResponseHandler::notFound('醫檢項目不存在');
        }
        
        // 更新醫檢項目
        if ($this->medicalItemModel->update($id, $itemData)) {
            ResponseHandler::success('更新醫檢項目成功');
        } else {
            ResponseHandler::serverError('更新醫檢項目失敗');
        }
    }
    
    // 刪除醫檢項目
    public function deleteMedicalItem() {
        $this->requireRole('technician');
        
        $id = $_POST['id'] ?? '';
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少項目ID');
        }
        
        // 檢查項目是否存在
        $item = $this->medicalItemModel->getById($id);
        if (!$item) {
            ResponseHandler::notFound('醫檢項目不存在');
        }
        
        // 檢查項目是否被使用
        if ($this->medicalItemModel->isInUse($item['item_id'])) {
            ResponseHandler::error('此項目已有檢查結果，無法刪除');
        }
        
        // 刪除醫檢項目
        if ($this->medicalItemModel->delete($id)) {
            ResponseHandler::success('刪除醫檢項目成功');
        } else {
            ResponseHandler::serverError('刪除醫檢項目失敗');
        }
    }
    
    // 取得醫檢項目詳細資訊
    public function getMedicalItemById() {
        $this->requireLogin();
        
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少項目ID');
        }
        
        $item = $this->medicalItemModel->getById($id);
        
        if (!$item) {
            ResponseHandler::notFound('醫檢項目不存在');
        }
        
        ResponseHandler::success('取得醫檢項目資訊成功', $item);
    }
    
    // 取得醫檢項目選項（用於下拉選單）
    public function getMedicalItemOptions() {
        $this->requireRole('technician');
        
        $options = $this->medicalItemModel->getSelectOptions();
        ResponseHandler::success('取得醫檢項目選項成功', $options);
    }
    
    // 搜尋醫檢項目
    public function searchMedicalItems() {
        $this->requireLogin();
        
        $keyword = $_GET['keyword'] ?? '';
        
        if (empty($keyword)) {
            ResponseHandler::validationError('請輸入搜尋關鍵字');
        }
        
        $results = $this->medicalItemModel->search($keyword);
        ResponseHandler::success('搜尋完成', $results);
    }
    
    // 檢查項目ID是否可用
    public function checkItemIdAvailable() {
        $this->requireRole('technician');
        
        $itemId = $_GET['item_id'] ?? '';
        $excludeId = $_GET['exclude_id'] ?? null;
        
        if (empty($itemId)) {
            ResponseHandler::validationError('請提供項目ID');
        }
        
        // 驗證ID格式
        $error = Validator::validateItemId($itemId);
        if ($error) {
            ResponseHandler::validationError($error);
        }
        
        $exists = $this->medicalItemModel->itemIdExists($itemId, $excludeId);
        
        ResponseHandler::success('檢查完成', [
            'item_id' => $itemId,
            'available' => !$exists
        ]);
    }
}
?>