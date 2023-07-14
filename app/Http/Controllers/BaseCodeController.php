<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use DB;
use Schema;
// use Jenssegers\Agent\Agent;

class BaseCodeController extends Controller
{
    //

    public function fStoredProcedure(){
        // DB::statement('SET @output ="" ');
        // $output = DB::statement('EXEC usp_ValidateUSer @PhoneNumber="?"', ['0920330308']);
        $results = DB::select('EXEC usp_ValidateUSer @PhoneNumber=?', ['0920330308']);
        // $outputValue = $output;
        return $results;
    }

    public function fGenderBaseCode(Request $request){
        $input = $request->all();
        switch ($input['type']) {
            case 'mysql':
                $tables = DB::select('SHOW TABLES');
                break;

            case 'mssql':
                $tables = DB::select("SELECT name FROM sysobjects WHERE xtype = 'U'");
                break;
        }
        // $tables = DB::select('SHOW TABLES');
        // $tables = DB::select("SELECT name FROM sysobjects WHERE xtype = 'U'");
        $aService = [];
        foreach ($tables as $table) {
            foreach ($table as $key => $value)
                $array = [
                    'controller' => ucfirst($value),
                    'model' => $value
                ];
                array_push($aService,$array);
        }

        //產生的controller model 在public底下
        $adminRoute = '';
        $apiRoute = '';
        foreach ($aService as $key => $value) {
           $controller = File::get('baseController.txt');
           //建立Controller
           $controllerName = $value['controller'];
           $code = str_replace('Base',$controllerName,$controller);

            //取得表Id
            switch ($input['type']) {
                case 'mysql':
                    $PrimaryKey = DB::select("SHOW KEYS FROM {$controllerName} WHERE Key_name = 'PRIMARY'");
                    $sPrimaryKey = $PrimaryKey[0]->Column_name;
                    break;

                case 'mssql':
                    $PrimaryKey = DB::select("SELECT
                                column_name as PRIMARYKEYCOLUMN
                            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS TC

                            INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KU
                                ON TC.CONSTRAINT_TYPE = 'PRIMARY KEY'
                                AND TC.CONSTRAINT_NAME = KU.CONSTRAINT_NAME
                                AND KU.table_name='" . $controllerName . "'");
                    $sPrimaryKey = $PrimaryKey[0]->PRIMARYKEYCOLUMN;
                    break;
            }


            //拼接input的欄位上去
            $oTable = Schema::getColumnListing($controllerName);

            //新增 或是更新的資料
            $sTempDataStr = "\$aData = array(
                                 ";
            //select的資料
            $sTempSelect = '';
            //selete的where條件
            $sTempSelectWhere = '';
            $sTempSelectWhereNotLike = '';
            //檢查不能為空的資料
            $sTempCheckStr = "";


            $number = 0;
            foreach ($oTable as $key2 => $value2) {

                if($value2 != $sPrimaryKey && !in_array($value2,['created_at','updated_at','creator'
                ,'ipOfCreator','lastUpdater','ipOfLastUpdater','isRemoved','removeTime',
                'remover','ipOfRemover','memo']) ){
                    $sTempDataStr .=  " '" . $value2 . "' => " . "\$input['" . $value2 . "']," . "\r\n";
                }


                //拼接select *的欄位
                if($number < 5){
                    $sTempSelect .= "'" . $value2 . "',";
                }else{
                    $sTempSelect .= "'" . $value2 . "\r\n". "',";
                    $number = ($number >= 5) ? 0 : $number;
                }
                $number += $number;



                //檢查不能為空的資料
                if ($value2 != $sPrimaryKey && !in_array($value2,['created_at','updated_at','creator'
                ,'ipOfCreator','lastUpdater','ipOfLastUpdater','isRemoved','removeTime',
                'remover','ipOfRemover','memo'])) {
                    $sTempCheckStr .= "if(!isset(\$input['" . $value2 . "'])) " ." return ['result' => false, 'msg' => '" . $value2 . "不能為空'];" . "\r\n";

                }

                //select的where條件
                if(!in_array($value2,['created_at','updated_at','creator'
                ,'ipOfCreator','lastUpdater','ipOfLastUpdater','isRemoved','removeTime',
                'remover','ipOfRemover','memo'])){
                    $sTempSelectWhere .= "if(!empty(\$input['" . $value2 . "'])) " . "  \$sql->where('". $value2. "','like','%'.\$input['". $value2."'].'%');" . "\r\n";
                    $sTempSelectWhereNotLike .= "if(!empty(\$input['" . $value2 . "'])) " . "  \$sql->where('". $value2. "',\$input['". $value2."']);" . "\r\n";
                }

            }

            //取得model的int類型欄位
            switch ($input['type']) {
                case 'mysql':
                    $aModelColum = DB::select("SHOW KEYS FROM {$controllerName} WHERE Key_name = 'PRIMARY'");
                    break;

                case 'mssql':
                    $aModelColum = DB::select("SELECT
                                                TABLE_NAME,
                                                COLUMN_NAME,
                                                DATA_TYPE,
                                                CHARACTER_MAXIMUM_LENGTH,
                                                IS_NULLABLE,
                                                *
                                            FROM
                                                INFORMATION_SCHEMA.COLUMNS
                                            WHERE
                                                TABLE_NAME = '{$controllerName}'");
                    break;
            }

            //model 的intger類型
            $sTempModel = '';
            // return $aModelColum;

            switch ($input['type']) {
                case 'mysql':
                    break;

                case 'mssql':
                    foreach ($aModelColum as $key3 => $value3) {
                        if ($value3->COLUMN_NAME != $sPrimaryKey){
                            if (in_array($value3->DATA_TYPE, array('int', 'bit', 'float', 'real', 'bigint', 'tinyint'))) {
                                $sTempModel .=  " '" . $value3->COLUMN_NAME . "' => " . "'integer'," . "\r\n";
                            }
                        }

                    }
                    break;
            }

            // if ($value2 != $sPrimaryKey) {
            //     $sTempModel .=  " '" . $value2 . "' => " . "'integer'," . "\r\n";
            // }


            $sTempDataStr .= ' );';
            $sTempSelect = substr($sTempSelect,0,-1);

            $code = str_replace('sPrimaryKey_replace', $sPrimaryKey, $code);
            $code = str_replace('sTempSelectWhere_replace', $sTempSelectWhere, $code);
            $code = str_replace('sTempSelectWhereNotLike_replace', $sTempSelectWhereNotLike, $code);
            $code = str_replace('sTempCheckStr_replace', $sTempCheckStr, $code);
            $code = str_replace('sTempSelect_replace', $sTempSelect, $code);
            $code = str_replace('sTempDataStr_replace', $sTempDataStr, $code);

           File::put("code/app/Http/Controller/Admin/{$controllerName}Controller.php",$code);
            $code = str_replace('Admin', 'Api', $code);
           File::put("code/app/Http/Controller/Api/{$controllerName}Controller.php", $code);

           //拼接 route
           $adminRoute .=  "\r\n"." // {$controllerName} 管理
                Route::prefix('{$controllerName}')->group(function(){
                    Route::get('list', [App\Http\Controllers\Admin\\{$controllerName}Controller::class, 'fList']); //列表
                    Route::get('/data', [App\Http\Controllers\Admin\\{$controllerName}Controller::class, 'fSelectData']); //下拉資料
                    Route::get('/{id}', [App\Http\Controllers\Admin\\{$controllerName}Controller::class, 'fGetID']); //取單一
                    Route::post('/create', [App\Http\Controllers\Admin\\{$controllerName}Controller::class, 'fAdd']); //新增
                    Route::put('/{id}/update', [App\Http\Controllers\Admin\\{$controllerName}Controller::class, 'fUpdate']); //更新
                    Route::delete('/{id}/delete', [App\Http\Controllers\Admin\\{$controllerName}Controller::class, 'fDelete']); //刪除
                    Route::put('/{id}/enable', [App\Http\Controllers\Admin\\{$controllerName}Controller::class, 'fEnable']); //啟用停用
                });";
            $apiRoute .= "\r\n"." // {$controllerName} 頁面
                Route::prefix('{$controllerName}')->group(function(){
                    Route::get('list', [App\Http\Controllers\Api\\{$controllerName}Controller::class, 'fList']); //列表
                    Route::get('/data', [App\Http\Controllers\Api\\{$controllerName}Controller::class, 'fSelectData']); //下拉資料
                    Route::get('/{id}', [App\Http\Controllers\Api\\{$controllerName}Controller::class, 'fGetID']); //取單一
                    Route::post('/create', [App\Http\Controllers\Api\\{$controllerName}Controller::class, 'fAdd']); //新增
                    Route::put('/{id}/update', [App\Http\Controllers\Api\\{$controllerName}Controller::class, 'fUpdate']); //更新
                    Route::delete('/{id}/delete', [App\Http\Controllers\Api\\{$controllerName}Controller::class, 'fDelete']); //刪除
                    Route::put('/{id}/enable', [App\Http\Controllers\Api\\{$controllerName}Controller::class, 'fEnable']); //啟用停用
                });";

           //建立Model
           $model = File::get('model.txt');
           $modelName = $value['model'];
           $code = str_replace('sTempId_replace', $sPrimaryKey,$model);
            $code = str_replace('sTempModel_replace', $sTempModel, $code);
            $code = str_replace('Base', $controllerName, $code);
        //    $code = str_replace('base',strtolower($modelName),$code);
           $code = str_replace('tablebase',$modelName,$code);
           File::put("code/app/Models/{$controllerName}.php",$code);

        }

        //建立 admin api route
        $route = File::get('route.txt');
        $route .= "Route::prefix('admin')->group(function(){
                    //登入
                    Route::post('login', [App\Http\Controllers\Admin\LoginController::class, 'fLogin']);
                    //取得驗證碼
                    Route::get('GetCaptcha', [App\Http\Controllers\Admin\LoginController::class, 'pictureBack']);
                    Route::middleware(['adminToken'])->group(function(){
                        //修改密碼
                        Route::post('/PostChangePassword', [App\Http\Controllers\Admin\LoginController::class, 'changePassword']);
                        {$adminRoute}
                    });

                });Route::prefix('api')->group(function(){

                    //登入
                    Route::post('login', [App\Http\Controllers\Api\LoginController::class, 'fLogin']);
                    //取得驗證碼
                    Route::get('GetCaptcha', [App\Http\Controllers\Api\LoginController::class, 'pictureBack']);
                    Route::middleware(['apiToken'])->group(function(){
                        //修改密碼
                        Route::post('/PostChangePassword', [App\Http\Controllers\Api\LoginController::class, 'changePassword']);
                        {$apiRoute}
                    });

                });";
        File::put("code/api.php", $route);


        //建立 admin api 登入controller
        $loginCode = File::get('loginController.txt');
        //建立Controller
        $controllerName = 'Login';
        $userCode = str_replace('Base', 'User', $loginCode);
        $accountCode = str_replace('Base', 'Account', $loginCode);
        File::put("code/app/Http/Controller/Api/{$controllerName}Controller.php", $userCode);
        File::put("code/app/Http/Controller/Admin/{$controllerName}Controller.php", $accountCode);

        return 'success';
    }


    public function fGenderAllRouteForAdmin(Request $request){

        $input = $request->all();
        switch ($input['type']) {
            case 'mysql':
                $tables = DB::select('SHOW TABLES');
                break;

            case 'mssql':
                $tables = DB::select("SELECT name FROM sysobjects WHERE xtype = 'U'");
                break;
        }
        // $tables = DB::select('SHOW TABLES');
        // $tables = DB::select("SELECT name FROM sysobjects WHERE xtype = 'U'");
        $aService = [];
        foreach ($tables as $table) {
            foreach ($table as $key => $value)
                $array = [
                    'controller' => ucfirst($value),
                    'model' => $value
                ];
                array_push($aService,$array);
        }

        //產生的controller model 在public底下
        $adminRoute ='<!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta http-equiv="X-UA-Compatible" content="IE=edge">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Document</title>
                        </head>
                        <body><pre style="word-wrap: break-word; white-space: pre-wrap;">

                        ';
        $apiRoute = '';
        foreach ($aService as $key => $value) {
           $controller = File::get('baseController.txt');
           //建立Controller
           $controllerName = $value['controller'];
           $code = str_replace('Base',$controllerName,$controller);

           //拼接 route
           $adminRoute .=  "\r\n"." // {$controllerName} 管理
                export const apiGet{$controllerName}List = (query: string) =>
                    apiInstance.get("."`\${prefix}/{$controllerName}/list?\${query}`".");
                export const apiGet{$controllerName}Detail = (id: number) =>
                    apiInstance.post(`\${prefix}/{$controllerName}/\${id}`);
                export const apiAdd{$controllerName} = (data: object) =>
                    apiInstance.post(`\${prefix}/{$controllerName}/create`, data);
                export const apiEdit{$controllerName} = (id: number, data: object) =>
                    apiInstance.put(`\${prefix}/{$controllerName}/\${id}/update`, data);
                export const apiDelete{$controllerName} = (id: number) =>
                    apiInstance.delete(`\${prefix}/{$controllerName}/\${id}/delete`);
                export const apiToggleEnable{$controllerName} = (id: number) =>
                    apiInstance.put(`\${prefix}/{$controllerName}/\${id}/enable`);";



        }
        $adminRoute .= '</pre></body>
                        </html>';
        File::put("code/route.ts",$adminRoute);

        return asset('').'code/route.ts';
    }

    public function fGenderAllRouteForAdminByOne($controllerName){

        //拼接 route
        $adminRoute ='<!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta http-equiv="X-UA-Compatible" content="IE=edge">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Document</title>
                        </head>
                        <body><pre style="word-wrap: break-word; white-space: pre-wrap;">

                        ';
        $adminRoute .=  "\r\n"." // {$controllerName} 管理
                export const apiGet{$controllerName}List = (query: string) =>
                    apiInstance.get("."`\${prefix}/{$controllerName}/list?\${query}`".");
                export const apiGet{$controllerName}Detail = (id: number) =>
                    apiInstance.post(`\${prefix}/{$controllerName}/\${id}`);
                export const apiAdd{$controllerName} = (data: object) =>
                    apiInstance.post(`\${prefix}/{$controllerName}/create`, data);
                export const apiEdit{$controllerName} = (id: number, data: object) =>
                    apiInstance.put(`\${prefix}/{$controllerName}/\${id}/update`, data);
                export const apiDelete{$controllerName} = (id: number) =>
                    apiInstance.delete(`\${prefix}/{$controllerName}/\${id}/delete`);
                export const apiToggleEnable{$controllerName} = (id: number) =>
                    apiInstance.put(`\${prefix}/{$controllerName}/\${id}/enable`);";
        $adminRoute .= '</pre></body>
                        </html>';
        File::put("code/routeByOne.ts",$adminRoute);

        return asset('').'code/routeByOne.ts';
    }


    public function fGetTest(){
        // $ip = $_SERVER['REMOTE_ADDR'];

        // $agent = new Agent();
        // $os = $agent->platform();
        // $browser = $agent->browser();
        // $bVersion = $agent->version($browser);

        // $platform = $agent->platform();
        // $pVersion = $agent->version($platform);

        // return response()->json([
        //     'ip' => $ip,
        //     'os' => $os,
        //     'browser' => $browser.' '.$bVersion,
        //     'platform' => $platform.' '.$pVersion,
        //     // 'version' => $version,
        // ]);
    }
}
