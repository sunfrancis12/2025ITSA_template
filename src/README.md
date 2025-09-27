# 醫檢系統

## 系統簡介
這是一個基於 Docker 的醫檢系統，使用 Apache + PHP + MariaDB 架構，實現醫檢員與受檢者的管理功能。

## 技術架構
- **前端：** HTML5、CSS3、JavaScript、jQuery
- **後端：** PHP 8.1 with PDO（MVC 架構）
- **資料庫：** MariaDB 10.9
- **伺服器：** Apache 2.4
- **容器化：** Docker & Docker Compose
- **編碼：** UTF-8

## 系統架構特點
- **模組化設計：** MVC 分層架構，代碼清晰易維護
- **自動載入：** 使用 PSR-4 自動載入機制
- **統一響應：** 標準化的 API 響應格式
- **安全防護：** SQL 注入防護、XSS 防護、權限控制
- **資料驗證：** 前後端雙重驗證機制

## 檔案結構
```
src/
├── docker-compose.yml      # Docker 配置檔案
├── Dockerfile             # Docker 映像檔配置
├── init.sql              # 資料庫初始化檔案
├── team021.html          # 主要網頁檔案
├── test_accounts.md      # 測試帳號資訊
├── README.md            # 系統說明文件
├── API_REFACTOR.md      # API 模組化重構說明
├── config/
│   └── database.php     # 資料庫連線配置
├── css/
│   └── team021.css      # 樣式表
├── js/
│   └── team021.js       # JavaScript 程式
└── php/
    ├── api.php          # API 入口點（路由器）
    ├── models/          # 資料模型層
    │   ├── BaseModel.php    # 基礎模型類
    │   ├── UserModel.php    # 使用者模型
    │   ├── MedicalItemModel.php  # 醫檢項目模型
    │   └── TestResultModel.php   # 檢查結果模型
    ├── controllers/     # 控制器層
    │   ├── AuthController.php    # 認證控制器
    │   ├── UserController.php    # 使用者控制器
    │   ├── MedicalItemController.php  # 醫檢項目控制器
    │   └── TestResultController.php   # 檢查結果控制器
    └── utils/          # 工具類
        ├── ResponseHandler.php  # 響應處理器
        ├── Validator.php       # 驗證器
        └── SessionManager.php  # 會話管理器
```

## 安裝與啟動

### 系統需求
- Docker
- Docker Compose

### 啟動步驟
1. 在專案根目錄（p3-ans）執行：
   ```bash
   docker-compose up -d
   ```

2. 等待容器啟動完成後，開啟瀏覽器訪問：
   ```
   http://localhost
   ```

### 停止系統
```bash
docker-compose down
```

### 重新建置
```bash
docker-compose down
docker-compose up --build -d
```

## 資料庫結構

### 主要資料表
1. **users** - 人員資料表
   - 儲存醫檢員與受檢者資訊
   - 包含編號、姓名、帳號、密碼、角色等欄位

2. **medical_items** - 醫檢項目資料表
   - 儲存醫檢項目資訊
   - 包含項目編號、名稱、說明等欄位

3. **test_results** - 檢查結果資料表
   - 儲存檢查結果資訊
   - 包含受檢者、項目、分數、建立人等欄位
   - 建立唯一約束防止重複記錄

## 系統功能

### 醫檢員功能
1. **帳號管理**
   - 註冊新帳號
   - 登入系統

2. **人員管理**
   - 新增、編輯、刪除使用者
   - 查看所有人員列表

3. **醫檢項目管理**
   - 新增、編輯、刪除醫檢項目
   - 查看所有項目列表

4. **檢查結果管理**
   - 新增、編輯、刪除檢查結果
   - 查看所有結果列表
   - 確保同一受檢者同一項目不重複

### 受檢者功能
1. **登入管理**
   - 使用醫檢員提供的帳號登入
   - 首次登入強制修改密碼

2. **密碼驗證**
   - 密碼必須為12碼
   - 包含英文大小寫與數字

3. **個人結果查詢**
   - 查看個人所有醫檢項目及分數
   - 單一頁面顯示所有結果

## 測試資料
系統已預先建立測試資料：
- 3位醫檢員
- 5位受檢者
- 5項醫檢項目
- 完整的檢查結果資料

詳細測試帳號請參考 `test_accounts.md` 檔案。

## 安全特性
1. **密碼加密**：使用 PHP password_hash() 函數
2. **SQL 注入防護**：使用 PDO 預處理語句
3. **會話管理**：PHP Session 管理使用者狀態
4. **權限控制**：區分醫檢員與受檢者權限
5. **輸入驗證**：前後端雙重驗證

## 資料正規化
資料庫設計遵循正規化原則：
- 第一正規化：所有欄位皆為原子值
- 第二正規化：消除部分依賴
- 第三正規化：消除傳遞依賴
- 建立適當的外鍵關聯

## 響應式設計
網頁採用響應式設計，支援：
- 桌面電腦
- 平板電腦
- 手機裝置

## 瀏覽器支援
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## 疑難排解

### 常見問題
1. **無法訪問網頁**
   - 檢查 Docker 是否正常運行
   - 確認端口 80 未被占用

2. **資料庫連線失敗**
   - 等待 MariaDB 容器完全啟動
   - 檢查 docker-compose logs db

3. **中文顯示亂碼**
   - 所有檔案均使用 UTF-8 編碼
   - 資料庫字符集設定為 utf8mb4

### 查看容器狀態
```bash
docker-compose ps
```

### 查看日誌
```bash
docker-compose logs web
docker-compose logs db
```

## 開發者資訊
- 開發語言：PHP 8.1、JavaScript ES6、CSS3、HTML5
- 框架：jQuery 3.6.0
- 資料庫：MariaDB 10.9
- 開發時間：2025年9月
- 編碼標準：UTF-8