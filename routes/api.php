<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Route::get('/fGetTest', [App\Http\Controllers\BaseCodeController::class, 'fGetTest']);
// Route::post('/fImportExcel', [App\Http\Controllers\ExcelController::class, 'fImport']);
//test stored procedure
// Route::post('/fStoredProcedure', [App\Http\Controllers\BaseCodeController::class, 'fStoredProcedure']);
//產生基本crud
Route::post('/fGenderBaseCode', [App\Http\Controllers\BaseCodeController::class, 'fGenderBaseCode']);

//產生後台 route
Route::post('/fGenderAllRouteForAdmin', [App\Http\Controllers\BaseCodeController::class, 'fGenderAllRouteForAdmin']);
//產生後台 route 單一
Route::post('/fGenderAllRouteForAdmin/{name}', [App\Http\Controllers\BaseCodeController::class, 'fGenderAllRouteForAdminByOne']);

Route::prefix('admin')->group(function () {
    //登入
    Route::post('login', [App\Http\Controllers\Admin\LoginController::class, 'fLogin']);
    //取得驗證碼
    Route::get('GetCaptcha', [App\Http\Controllers\Admin\LoginController::class, 'pictureBack']);

    //檔案上傳
    Route::post('file/upload', [App\Http\Controllers\Admin\FileController::class, 'fUpload']); //上傳檔案

    Route::middleware(['adminToken'])->group(function () {
        //修改密碼
        Route::post('/PostChangePassword', [App\Http\Controllers\Admin\LoginController::class, 'changePassword']);

        // Account 管理
        Route::prefix('account')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\AccountController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\AccountController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\AccountController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\AccountController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\AccountController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\AccountController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\AccountController::class, 'fEnable']); //啟用停用
            Route::put('/{id}/changePassword', [App\Http\Controllers\Admin\AccountController::class, 'fChangePassword']); //更新密碼
        });
        // Admin_log 管理
        Route::prefix('admin_log')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Admin_logController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Admin_logController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Admin_logController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Admin_logController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Admin_logController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Admin_logController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Admin_logController::class, 'fEnable']); //啟用停用
        });
        // Attrs 管理
        Route::prefix('attrs')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\AttrsController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\AttrsController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\AttrsController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\AttrsController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\AttrsController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\AttrsController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\AttrsController::class, 'fEnable']); //啟用停用
        });
        // Books 管理
        Route::prefix('books')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\BooksController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\BooksController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\BooksController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\BooksController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\BooksController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\BooksController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\BooksController::class, 'fEnable']); //啟用停用
        });
        // Books_attrs_mapping 管理
        Route::prefix('books_attrs_mapping')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Books_attrs_mappingController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Books_attrs_mappingController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Books_attrs_mappingController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Books_attrs_mappingController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Books_attrs_mappingController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Books_attrs_mappingController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Books_attrs_mappingController::class, 'fEnable']); //啟用停用
        });
        // Books_teacher_mapping 管理
        Route::prefix('books_teacher_mapping')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Books_teacher_mappingController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Books_teacher_mappingController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Books_teacher_mappingController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Books_teacher_mappingController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Books_teacher_mappingController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Books_teacher_mappingController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Books_teacher_mappingController::class, 'fEnable']); //啟用停用
        });
        // Books_units 管理
        Route::prefix('books_units')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Books_unitsController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Books_unitsController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Books_unitsController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Books_unitsController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Books_unitsController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Books_unitsController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Books_unitsController::class, 'fEnable']); //啟用停用
        });
        // Books_units_cards 管理
        Route::prefix('books_units_cards')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Books_units_cardsController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Books_units_cardsController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Books_units_cardsController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Books_units_cardsController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Books_units_cardsController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Books_units_cardsController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Books_units_cardsController::class, 'fEnable']); //啟用停用
        });
        // Books_units_cards_topics 管理
        Route::prefix('books_units_cards_topics')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Books_units_cards_topicsController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Books_units_cards_topicsController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Books_units_cards_topicsController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Books_units_cards_topicsController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Books_units_cards_topicsController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Books_units_cards_topicsController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Books_units_cards_topicsController::class, 'fEnable']); //啟用停用
        });
        // Code_config 管理
        Route::prefix('code_config')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Code_configController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Code_configController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Code_configController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Code_configController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Code_configController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Code_configController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Code_configController::class, 'fEnable']); //啟用停用
        });
        // Menu_function 管理
        Route::prefix('menuFunction')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\MenuFunctionController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\MenuFunctionController::class, 'fSelectData']); //下拉資料
            Route::post('/create', [App\Http\Controllers\Admin\MenuFunctionController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\MenuFunctionController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\MenuFunctionController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\MenuFunctionController::class, 'fEnable']); //啟用停用
            Route::get('tree', [App\Http\Controllers\Admin\MenuFunctionController::class, 'fTree']); //角色權限樹狀
        });
        // Role 管理
        Route::prefix('role')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\RoleController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\RoleController::class, 'fSelectData']); //下拉資料
            // Route::get('/{id}', [App\Http\Controllers\Admin\RoleController::class, 'fGetID']); //取單一
            Route::get('/data', [App\Http\Controllers\Admin\RoleController::class, 'fRoleData']); //角色下拉資料
            Route::post('/create', [App\Http\Controllers\Admin\RoleController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\RoleController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\RoleController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\RoleController::class, 'fEnable']); //啟用停用
        });
        // Teacher 管理
        Route::prefix('teacher')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\TeacherController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\TeacherController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\TeacherController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\TeacherController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\TeacherController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\TeacherController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\TeacherController::class, 'fEnable']); //啟用停用
        });
        // Coin 管理
        Route::prefix('coin')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\CoinController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\CoinController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\CoinController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\CoinController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\CoinController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\CoinController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\CoinController::class, 'fEnable']); //啟用停用
        });
        // Ebbinghaus_config 管理
        Route::prefix('ebbinghaus_config')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Ebbinghaus_configController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Ebbinghaus_configController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Ebbinghaus_configController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Ebbinghaus_configController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Ebbinghaus_configController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Ebbinghaus_configController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Ebbinghaus_configController::class, 'fEnable']); //啟用停用
        });
        // Experience_config 管理
        Route::prefix('experience_config')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Experience_configController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Experience_configController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Experience_configController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Experience_configController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Experience_configController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Experience_configController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Experience_configController::class, 'fEnable']); //啟用停用
        });
        // News 管理
        Route::prefix('news')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\NewsController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\NewsController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\NewsController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\NewsController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\NewsController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\NewsController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\NewsController::class, 'fEnable']); //啟用停用
        });

        // Class 管理
        Route::prefix('class')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Grade_classController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Grade_classController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Grade_classController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Grade_classController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Grade_classController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Grade_classController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Grade_classController::class, 'fEnable']); //啟用停用
        });
        // Class_teacher 管理
        Route::prefix('class_teacher')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\Class_teacherController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\Class_teacherController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\Class_teacherController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\Class_teacherController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\Class_teacherController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\Class_teacherController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\Class_teacherController::class, 'fEnable']); //啟用停用
        });
        // Student 管理
        Route::prefix('student')->group(function () {
            Route::get('list', [App\Http\Controllers\Admin\StudentController::class, 'fList']); //列表
            Route::get('/data', [App\Http\Controllers\Admin\StudentController::class, 'fSelectData']); //下拉資料
            Route::get('/{id}', [App\Http\Controllers\Admin\StudentController::class, 'fGetID']); //取單一
            Route::post('/create', [App\Http\Controllers\Admin\StudentController::class, 'fAdd']); //新增
            Route::put('/{id}/update', [App\Http\Controllers\Admin\StudentController::class, 'fUpdate']); //更新
            Route::delete('/{id}/delete', [App\Http\Controllers\Admin\StudentController::class, 'fDelete']); //刪除
            Route::put('/{id}/enable', [App\Http\Controllers\Admin\StudentController::class, 'fEnable']); //啟用停用
            Route::put('/{id}/changePassword', [App\Http\Controllers\Admin\StudentController::class, 'fChangePassword']); //更改密碼
        });
    });
});
