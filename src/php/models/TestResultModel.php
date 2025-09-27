<?php
require_once 'BaseModel.php';

class TestResultModel extends BaseModel {
    protected $table = 'test_results';
    
    // 建立新檢查結果
    public function create($resultData) {
        $sql = "INSERT INTO {$this->table} (patient_id, item_id, score, created_by) VALUES (?, ?, ?, ?)";
        $params = [
            $resultData['patient_id'],
            $resultData['item_id'],
            $resultData['score'],
            $resultData['created_by']
        ];
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    // 更新檢查結果
    public function update($id, $resultData) {
        $sql = "UPDATE {$this->table} SET score = ? WHERE id = ?";
        $params = [
            $resultData['score'],
            $id
        ];
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    // 取得所有檢查結果（含關聯資料）
    public function getAllWithDetails() {
        $sql = "
            SELECT 
                tr.*,
                u.name as patient_name,
                mi.name as item_name,
                creator.name as creator_name
            FROM {$this->table} tr
            JOIN users u ON tr.patient_id = u.user_id
            JOIN medical_items mi ON tr.item_id = mi.item_id
            JOIN users creator ON tr.created_by = creator.user_id
            ORDER BY tr.patient_id, tr.item_id
        ";
        
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
    
    // 取得特定受檢者的檢查結果
    public function getByPatientId($patientId) {
        $sql = "
            SELECT 
                tr.*,
                mi.name as item_name,
                mi.description as item_description
            FROM {$this->table} tr
            JOIN medical_items mi ON tr.item_id = mi.item_id
            WHERE tr.patient_id = ?
            ORDER BY tr.item_id
        ";
        
        $stmt = $this->query($sql, [$patientId]);
        return $stmt->fetchAll();
    }
    
    // 檢查受檢者是否已有此項目的結果
    public function hasResult($patientId, $itemId, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE patient_id = ? AND item_id = ?";
        $params = [$patientId, $itemId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn() > 0;
    }
    
    // 取得統計資訊
    public function getStatistics() {
        $sql = "
            SELECT 
                COUNT(*) as total_results,
                AVG(score) as average_score,
                MIN(score) as min_score,
                MAX(score) as max_score,
                COUNT(DISTINCT patient_id) as total_patients,
                COUNT(DISTINCT item_id) as total_items
            FROM {$this->table}
        ";
        
        $stmt = $this->query($sql);
        return $stmt->fetch();
    }
    
    // 根據受檢者取得結果統計
    public function getPatientStatistics($patientId) {
        $sql = "
            SELECT 
                COUNT(*) as total_tests,
                AVG(score) as average_score,
                MIN(score) as min_score,
                MAX(score) as max_score
            FROM {$this->table}
            WHERE patient_id = ?
        ";
        
        $stmt = $this->query($sql, [$patientId]);
        return $stmt->fetch();
    }
    
    // 驗證分數範圍
    public function isValidScore($score) {
        return is_numeric($score) && $score >= 1 && $score <= 10;
    }
}
?>