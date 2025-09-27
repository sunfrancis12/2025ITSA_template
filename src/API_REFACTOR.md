# API 模組化重構說明

## 重構內容

### 1. 檔案結構
```
php/
├── api.php              # 主要 API 入口點（路由器）
├── models/              # 資料模型層
│   ├── BaseModel.php    # 基礎模型類
│   ├── UserModel.php    # 使用者模型
│   ├── MedicalItemModel.php  # 醫檢項目模型
│   └── TestResultModel.php   # 檢查結果模型
├── controllers/         # 控制器層
│   ├── AuthController.php    # 認證控制器
│   ├── UserController.php    # 使用者控制器
│   ├── MedicalItemController.php  # 醫檢項目控制器
│   └── TestResultController.php   # 檢查結果控制器
└── utils/              # 工具類
    ├── ResponseHandler.php  # 響應處理器
    ├── Validator.php       # 驗證器
    └── SessionManager.php  # 會話管理器
```

### 2. 架構優勢

#### MVC 分離
- **Model（模型）**：負責資料存取和業務邏輯
- **Controller（控制器）**：負責處理請求和協調模型與視圖
- **View（視圖）**：前端 JavaScript 處理響應

#### 模組化優勢
1. **可維護性**：每個功能模組獨立，易於維護
2. **可重用性**：模型和工具類可重複使用
3. **可測試性**：每個類別可單獨測試
4. **可擴展性**：新增功能只需添加新的控制器和模型
5. **代碼品質**：統一的錯誤處理和響應格式

#### 安全性提升
1. **統一驗證**：Validator 類統一處理輸入驗證
2. **權限控制**：SessionManager 統一處理權限檢查
3. **錯誤處理**：ResponseHandler 統一處理錯誤響應
4. **SQL 注入防護**：所有查詢使用 PDO 預處理語句

### 3. 新增功能

#### 批量操作
- 批量新增檢查結果
- 重設使用者密碼

#### 增強驗證
- 使用者ID格式驗證（T001, P001, MI001）
- 密碼複雜度驗證
- 輸入長度限制

#### 統計功能
- 整體統計資訊
- 受檢者個人統計

#### 搜尋功能
- 醫檢項目搜尋
- ID 可用性檢查

### 4. API 端點

#### 認證相關
- `login` - 使用者登入
- `register` - 使用者註冊
- `change_password` - 修改密碼
- `logout` - 登出
- `check_login_status` - 檢查登入狀態
- `get_current_user` - 取得當前使用者資訊

#### 使用者管理
- `get_users` - 取得使用者列表
- `add_user` - 新增使用者
- `update_user` - 更新使用者
- `delete_user` - 刪除使用者
- `get_user_by_id` - 取得使用者詳細資訊
- `get_patient_options` - 取得受檢者選項
- `reset_user_password` - 重設使用者密碼

#### 醫檢項目管理
- `get_medical_items` - 取得醫檢項目列表
- `add_medical_item` - 新增醫檢項目
- `update_medical_item` - 更新醫檢項目
- `delete_medical_item` - 刪除醫檢項目
- `get_medical_item_by_id` - 取得醫檢項目詳細資訊
- `get_medical_item_options` - 取得醫檢項目選項
- `search_medical_items` - 搜尋醫檢項目
- `check_item_id_available` - 檢查項目ID可用性

#### 檢查結果管理
- `get_test_results` - 取得檢查結果列表
- `add_test_result` - 新增檢查結果
- `update_test_result` - 更新檢查結果
- `delete_test_result` - 刪除檢查結果
- `get_patient_results` - 取得受檢者個人結果
- `get_patient_results_by_technician` - 醫檢員查看受檢者結果
- `get_test_result_by_id` - 取得檢查結果詳細資訊
- `batch_add_test_results` - 批量新增檢查結果
- `get_statistics` - 取得統計資訊
- `get_patient_statistics` - 取得受檢者統計

### 5. 自動載入機制
使用 `spl_autoload_register` 實現類別自動載入，無需手動 require 每個檔案。

### 6. 錯誤處理
- 統一的錯誤響應格式
- 詳細的錯誤日誌記錄
- 生產環境的錯誤隱藏

### 7. 向後相容性
現有的前端 JavaScript 代碼無需修改，API 端點保持相同。

## 使用方法

所有 API 調用仍然通過 `php/api.php` 進行，使用方式不變：

```javascript
$.ajax({
    url: 'php/api.php',
    type: 'POST',
    data: { action: 'login', username: 'user', password: 'pass' },
    success: function(response) {
        // 處理響應
    }
});
```

## 開發指引

### 新增功能
1. 在對應的 Model 中新增資料存取方法
2. 在對應的 Controller 中新增處理方法
3. 在 `api.php` 的路由中新增端點

### 新增驗證規則
在 `Validator.php` 中新增驗證方法，然後在 Controller 中使用。

### 自定義響應
使用 `ResponseHandler` 類的方法來發送統一格式的響應。