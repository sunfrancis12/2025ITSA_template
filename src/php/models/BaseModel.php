<?php
// 基礎模型類
abstract class BaseModel {
    protected $pdo;
    protected $table;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // 執行查詢
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    // 取得所有記錄
    public function getAll($orderBy = 'id') {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
    
    // 根據ID取得單一記錄
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    // 刪除記錄
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    // 檢查記錄是否存在
    public function exists($field, $value, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$field} = ?";
        $params = [$value];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn() > 0;
    }
    
    // 開始交易
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    // 提交交易
    public function commit() {
        return $this->pdo->commit();
    }
    
    // 回滾交易
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}
?>