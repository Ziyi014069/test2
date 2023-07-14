<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//package
use App\Packages\Common;
use App\Packages\Jwt;
use App\Packages\AdminLog;

//Model
use App\Models\MenuFunction;
use App\Models\Role;

//Laravel
use DB;
use Exception;
use Log;

class RoleController extends Controller
{
    private $Common;
    private $AdminLog;

    public function __construct()
    {
        // parent::__construct();
        $this->Common = new Common;
        $this->AdminLog = new AdminLog;
    }

    //列表
    public function fList(Request $request)
    {
        $input = $request->all();

        $sql = Role::select('roleId','roleName','authorizedFunctionIds','isEnabled', 'created_at', 'creator','updated_at',
                            'lastUpdater', 'isRemoved','remover', 'memo' );

        //分頁
        $page = isset($input['page']) ? $input['page'] : 1;
        $pageSize = isset($input['limit']) ? $input['limit'] : 10;
        $first = $pageSize * ($page - 1);

        $total = $sql->count();
        $data = $sql->orderBy('roleId', 'ASC')->skip($first)->take($pageSize)->get();

        return ['total' => $total, 'data' => $data];
    }

    //取單一
    public function fGetId($id)
    {
        $Role = Role::where('roleId', $id)->where('isRemoved', 0)->first();
        if (!$Role) return  ['result' => false, 'msg' => '帳號不存在'];
        return ['data' => $Role];
    }

    //新增
    public function fAdd(Request $request)
    {

        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if(empty($input['roleName'])){
            return ['result' => false, 'msg' => '名稱不能為空'];
        }else if(empty($input['authorizedFunctionIds'])){
            return ['result' => false, 'msg' => '權限不能為空'];
        }
        //檢查
        if (!in_array($input['isEnabled'], array('0', 1))) {
            return ['result' => false, 'msg' => '狀態格式錯誤'];
        }

        //角色權限 檢查
        $aMenuFunction = MenuFunction::select('menuFunctionId')->get();
        $authority = [];
        foreach ($aMenuFunction as $key => $value) {
            $authority[] = $value['menuFunctionId'];
        }
        $aAuthorizedFunctionIds = explode(',',$input['authorizedFunctionIds']);

        foreach ($aAuthorizedFunctionIds as $key => $value) {
            if(!in_array($value, $authority)){
                return ['result' => false, 'msg' => '權限資料錯誤'];
            }
        }

        $aData = array(
            'roleName' => $input['roleName'],
            'authorizedFunctionIds' => $input['authorizedFunctionIds'],
            'isEnabled' => $input['isEnabled'],
            'memo' => $input['memo']
        );
        $aData = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            Role::create($aData);

            $this->AdminLog->fCreate('角色管理','新增',null,[],$aData,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '新增成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '新增失敗'];
        }

    }

    //更新
    public function fUpdate(Request $request, $id)
    {

        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (empty($input['roleName'])) {
            return ['result' => false, 'msg' => '名稱不能為空'];
        } else if (empty($input['authorizedFunctionIds'])) {
            return ['result' => false, 'msg' => '權限不能為空'];
        }
        if (!in_array($input['isEnabled'], array('0', 1))) {
            return ['result' => false, 'msg' => '狀態格式錯誤'];
        }

        //角色存在
        $oRole = Role::select('roleId')->where('roleId', $id)->first();
        if (empty($oRole)) {
            return ['result' => false, 'msg' => '角色不存在'];
        }

        //角色權限 檢查
        $aMenuFunction = MenuFunction::select('menuFunctionId')->get();
        $authority = [];
        foreach ($aMenuFunction as $key => $value) {
            $authority[] = $value['menuFunctionId'];
        }
        $aAuthorizedFunctionIds = explode(',', $input['authorizedFunctionIds']);

        foreach ($aAuthorizedFunctionIds as $key => $value) {
            if (!in_array($value, $authority)) {
                return ['result' => false, 'msg' => '權限資料錯誤'];
            }
        }


        $aData = array(
            'roleName' => $input['roleName'],
            'authorizedFunctionIds' => $input['authorizedFunctionIds'],
            'isEnabled' => $input['isEnabled'],
            'memo' => $input['memo']
        );

        $aData = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            $oRole = Role::where('roleId', $id)->get();
            Role::where('roleId', $id)->update($aData);

            $this->AdminLog->fCreate('角色管理','更新',$id,$oRole,$aData,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '更新成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '更新失敗'];
        }
    }

    //刪除
    public function fDelete(Request $request, $id)
    {
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        $oRole =  Role::select('roleId')->where('roleId', $id)->where('isRemoved', 0)->first();
        if (!$oRole) return ['result' => false, 'msg' => '角色不存在'];

        DB::beginTransaction();
        try {

            $aData = [];
            $aData = $this->Common->fLaravelDeleteDate($aData, $token['name']);

            $oRole = Role::where('roleId', $id)->get();
            Role::where('roleId', $id)->update($aData);

            $this->AdminLog->fCreate('角色管理','刪除',$id,$oRole,$aData,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '刪除成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '刪除失敗'];
        }
    }

    //啟用 停用
    public function fEnable(Request $request, $id)
    {
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        $oRole =  Role::select('roleId', 'isEnabled')->where('roleId', $id)->first();
        if (!$oRole) return ['result' => false, 'msg' => '角色不存在'];


        DB::beginTransaction();
        try {

            $aData = [
                'isEnabled' => ($oRole->isEnabled == 1) ? 0 : 1
            ];
            $aData = $this->Common->fLaravelUpdateDate($aData, $token['name']);

            $oRole = Role::where('roleId', $id)->get();
            Role::where('roleId', $id)->update($aData);

            $this->AdminLog->fCreate('角色管理','啟用停用',$id,$oRole,$aData,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '更新成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '更新失敗'];
        }
    }

    //取得角色下拉資料
    public function fRoleData(){
        $data = Role::select(
            'roleId',
            'roleName'
        )->where('isEnabled',1)->where('isRemoved',0)->orderBy('roleId', 'ASC')->get();


        return ['result' => true, 'msg' => 'success', 'data'=> $data];
    }

}
