<?php

class TestResultController extends BaseController {
    private $testResultModel;
    
    public function __construct($pdo) {
        $this->testResultModel = new TestResultModel($pdo);
    }
    
    // 取得所有檢查結果
    public function getTestResults() {
        $this->requireRole('technician');
        
        $results = $this->testResultModel->getAllWithDetails();
        ResponseHandler::success('取得檢查結果列表成功', $results);
    }
    
    // 新增檢查結果
    public function addTestResult() {
        $this->requireRole('technician');
        
        $resultData = [
            'patient_id' => $_POST['patient_id'] ?? '',
            'item_id' => $_POST['item_id'] ?? '',
            'score' => $_POST['score'] ?? '',
            'created_by' => $this->getCurrentUserId()
        ];
        
        // 驗證規則
        $rules = [
            'patient_id' => [
                'required' => true,
                'message' => '請選擇受檢者'
            ],
            'item_id' => [
                'required' => true,
                'message' => '請選擇醫檢項目'
            ],
            'score' => [
                'required' => true,
                'validator' => [Validator::class, 'validateScore']
            ]
        ];
        
        $errors = Validator::validate($rules, $resultData);
        
        if (!empty($errors)) {
            ResponseHandler::validationError(implode(', ', array_values($errors)));
        }
        
        // 檢查是否已有該受檢者的該項目結果
        if ($this->testResultModel->hasResult($resultData['patient_id'], $resultData['item_id'])) {
            ResponseHandler::error('該受檢者此項目已有結果，請使用更新功能');
        }
        
        // 建立檢查結果
        if ($this->testResultModel->create($resultData)) {
            ResponseHandler::success('新增檢查結果成功');
        } else {
            ResponseHandler::serverError('新增檢查結果失敗');
        }
    }
    
    // 更新檢查結果
    public function updateTestResult() {
        $this->requireRole('technician');
        
        $id = $_POST['id'] ?? '';
        $resultData = [
            'score' => $_POST['score'] ?? ''
        ];
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少結果ID');
        }
        
        // 驗證分數
        $scoreError = Validator::validateScore($resultData['score']);
        if ($scoreError) {
            ResponseHandler::validationError($scoreError);
        }
        
        // 檢查結果是否存在
        $existingResult = $this->testResultModel->getById($id);
        if (!$existingResult) {
            ResponseHandler::notFound('檢查結果不存在');
        }
        
        // 更新檢查結果
        if ($this->testResultModel->update($id, $resultData)) {
            ResponseHandler::success('更新檢查結果成功');
        } else {
            ResponseHandler::serverError('更新檢查結果失敗');
        }
    }
    
    // 刪除檢查結果
    public function deleteTestResult() {
        $this->requireRole('technician');
        
        $id = $_POST['id'] ?? '';
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少結果ID');
        }
        
        // 檢查結果是否存在
        $result = $this->testResultModel->getById($id);
        if (!$result) {
            ResponseHandler::notFound('檢查結果不存在');
        }
        
        // 刪除檢查結果
        if ($this->testResultModel->delete($id)) {
            ResponseHandler::success('刪除檢查結果成功');
        } else {
            ResponseHandler::serverError('刪除檢查結果失敗');
        }
    }
    
    // 取得受檢者個人檢查結果
    public function getPatientResults() {
        $this->requireRole('patient');
        
        $patientId = $this->getCurrentUserId();
        $results = $this->testResultModel->getByPatientId($patientId);
        
        ResponseHandler::success('取得個人檢查結果成功', $results);
    }
    
    // 取得特定受檢者的檢查結果（醫檢員功能）
    public function getPatientResultsByTechnician() {
        $this->requireRole('technician');
        
        $patientId = $_GET['patient_id'] ?? '';
        
        if (empty($patientId)) {
            ResponseHandler::validationError('請指定受檢者ID');
        }
        
        $results = $this->testResultModel->getByPatientId($patientId);
        ResponseHandler::success('取得受檢者檢查結果成功', $results);
    }
    
    // 取得檢查結果詳細資訊
    public function getTestResultById() {
        $this->requireRole('technician');
        
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            ResponseHandler::validationError('缺少結果ID');
        }
        
        $result = $this->testResultModel->getById($id);
        
        if (!$result) {
            ResponseHandler::notFound('檢查結果不存在');
        }
        
        ResponseHandler::success('取得檢查結果資訊成功', $result);
    }
    
    // 批量新增檢查結果
    public function batchAddTestResults() {
        $this->requireRole('technician');
        
        $results = json_decode($_POST['results'] ?? '[]', true);
        
        if (empty($results) || !is_array($results)) {
            ResponseHandler::validationError('請提供有效的檢查結果資料');
        }
        
        $createdBy = $this->getCurrentUserId();
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        $this->testResultModel->beginTransaction();
        
        try {
            foreach ($results as $index => $resultData) {
                // 補充建立者資訊
                $resultData['created_by'] = $createdBy;
                
                // 驗證每一筆資料
                $rules = [
                    'patient_id' => ['required' => true],
                    'item_id' => ['required' => true],
                    'score' => [
                        'required' => true,
                        'validator' => [Validator::class, 'validateScore']
                    ]
                ];
                
                $validationErrors = Validator::validate($rules, $resultData);
                
                if (!empty($validationErrors)) {
                    $errors[] = "第" . ($index + 1) . "筆：" . implode(', ', array_values($validationErrors));
                    $errorCount++;
                    continue;
                }
                
                // 檢查是否已有結果
                if ($this->testResultModel->hasResult($resultData['patient_id'], $resultData['item_id'])) {
                    $errors[] = "第" . ($index + 1) . "筆：該受檢者此項目已有結果";
                    $errorCount++;
                    continue;
                }
                
                // 建立結果
                if ($this->testResultModel->create($resultData)) {
                    $successCount++;
                } else {
                    $errors[] = "第" . ($index + 1) . "筆：建立失敗";
                    $errorCount++;
                }
            }
            
            if ($errorCount > 0 && $successCount === 0) {
                $this->testResultModel->rollBack();
                ResponseHandler::error('批量新增失敗：' . implode('; ', $errors));
            } else {
                $this->testResultModel->commit();
                $message = "批量新增完成：成功 {$successCount} 筆";
                if ($errorCount > 0) {
                    $message .= "，失敗 {$errorCount} 筆：" . implode('; ', $errors);
                }
                ResponseHandler::success($message, [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]);
            }
            
        } catch (Exception $e) {
            $this->testResultModel->rollBack();
            ResponseHandler::serverError('批量新增失敗：' . $e->getMessage());
        }
    }
    
    // 取得統計資訊
    public function getStatistics() {
        $this->requireRole('technician');
        
        $stats = $this->testResultModel->getStatistics();
        ResponseHandler::success('取得統計資訊成功', $stats);
    }
    
    // 取得受檢者統計資訊
    public function getPatientStatistics() {
        $userRole = $this->getCurrentUserRole();
        
        if ($userRole === 'patient') {
            $patientId = $this->getCurrentUserId();
        } elseif ($userRole === 'technician') {
            $patientId = $_GET['patient_id'] ?? '';
            if (empty($patientId)) {
                ResponseHandler::validationError('請指定受檢者ID');
            }
        } else {
            ResponseHandler::unauthorized();
        }
        
        $stats = $this->testResultModel->getPatientStatistics($patientId);
        ResponseHandler::success('取得受檢者統計資訊成功', $stats);
    }
}
?>