<?php
// 響應處理工具類
class ResponseHandler {
    
    // 發送成功響應
    public static function success($message, $data = null) {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 發送錯誤響應
    public static function error($message, $code = 400) {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code
        ];
        
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 發送未授權響應
    public static function unauthorized($message = '權限不足') {
        self::error($message, 401);
    }
    
    // 發送未找到響應
    public static function notFound($message = '資源不存在') {
        self::error($message, 404);
    }
    
    // 發送驗證失敗響應
    public static function validationError($message) {
        self::error($message, 422);
    }
    
    // 發送伺服器錯誤響應
    public static function serverError($message = '伺服器錯誤') {
        self::error($message, 500);
    }
}
?>