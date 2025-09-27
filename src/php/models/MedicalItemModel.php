<?php
require_once 'BaseModel.php';

class MedicalItemModel extends BaseModel {
    protected $table = 'medical_items';
    
    // 建立新醫檢項目
    public function create($itemData) {
        $sql = "INSERT INTO {$this->table} (item_id, name, description) VALUES (?, ?, ?)";
        $params = [
            $itemData['item_id'],
            $itemData['name'],
            $itemData['description'] ?? ''
        ];
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    // 更新醫檢項目
    public function update($id, $itemData) {
        $sql = "UPDATE {$this->table} SET name = ?, description = ? WHERE id = ?";
        $params = [
            $itemData['name'],
            $itemData['description'] ?? '',
            $id
        ];
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    // 根據項目ID取得項目
    public function getByItemId($itemId) {
        $sql = "SELECT * FROM {$this->table} WHERE item_id = ?";
        $stmt = $this->query($sql, [$itemId]);
        return $stmt->fetch();
    }
    
    // 檢查項目ID是否存在
    public function itemIdExists($itemId, $excludeId = null) {
        return $this->exists('item_id', $itemId, $excludeId);
    }
    
    // 取得所有醫檢項目（按項目ID排序）
    public function getAll($orderBy = 'item_id') {
        return parent::getAll($orderBy);
    }
    
    // 取得醫檢項目選項（用於下拉選單）
    public function getSelectOptions() {
        $sql = "SELECT item_id, name FROM {$this->table} ORDER BY item_id";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
    
    // 檢查項目是否被使用（有檢查結果）
    public function isInUse($itemId) {
        $sql = "SELECT COUNT(*) FROM test_results WHERE item_id = ?";
        $stmt = $this->query($sql, [$itemId]);
        return $stmt->fetchColumn() > 0;
    }
    
    // 搜尋醫檢項目
    public function search($keyword) {
        $sql = "SELECT * FROM medical_items WHERE name LIKE ? OR description LIKE ? OR item_id LIKE ? ORDER BY item_id";
        $searchTerm = "%{$keyword}%";
        $stmt = $this->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
?>