<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//Laravel
use Exception;
use Log;
use Hash;
use DB;
//package
use App\Packages\Common;
use App\Packages\Jwt;
use App\Packages\AdminLog;

//Model
use App\Models\Admin_log;


class Admin_logController extends Controller
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

        $sql = Admin_log::select('id', 'page', 'operate', 'operateId', 'dataBeforeModification', 'dataAfterModification', 'created_at', 'updated_at', 'creator', 'ipOfCreator', 'lastUpdater', 'ipOfLastUpdater', 'isRemoved', 'removeTime', 'remover', 'ipOfRemover');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['id']))   $sql->where('id', 'like', '%' . $input['id'] . '%');
        if (!empty($input['page']))   $sql->where('page', 'like', '%' . $input['page'] . '%');
        if (!empty($input['operate']))   $sql->where('operate', 'like', '%' . $input['operate'] . '%');
        if (!empty($input['operateId']))   $sql->where('operateId', 'like', '%' . $input['operateId'] . '%');
        if (!empty($input['dataBeforeModification']))   $sql->where('dataBeforeModification', 'like', '%' . $input['dataBeforeModification'] . '%');
        if (!empty($input['dataAfterModification']))   $sql->where('dataAfterModification', 'like', '%' . $input['dataAfterModification'] . '%');


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('id', 'desc')->skip($first)->take($pageSize)->get();

        return ['total' => $total, 'data' => $data];
    }

    /**
     * 新增
     *
     * @param
     *
     * @return {array
     */
    public function fAdd(Request $request)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['page']))  return ['result' => false, 'msg' => 'page不能為空'];
        if (!isset($input['operate']))  return ['result' => false, 'msg' => 'operate不能為空'];
        if (!isset($input['operateId']))  return ['result' => false, 'msg' => 'operateId不能為空'];
        if (!isset($input['dataBeforeModification']))  return ['result' => false, 'msg' => 'dataBeforeModification不能為空'];
        if (!isset($input['dataAfterModification']))  return ['result' => false, 'msg' => 'dataAfterModification不能為空'];


        $aData = array(
            'page' => $input['page'],
            'operate' => $input['operate'],
            'operateId' => $input['operateId'],
            'dataBeforeModification' => $input['dataBeforeModification'],
            'dataAfterModification' => $input['dataAfterModification'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Admin_log::create($data);

            //建立log
            $this->AdminLog->fCreate('Admin_log管理', '新增', null, [], $aData, $token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '新增成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '新增失敗'];
        }
    }


    /**
     * 取單一
     *
     * @param $id Admin_logID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Admin_log::where('id', '=', $id);


        //select條件
        if (!empty($input['id']))   $sql->where('id', $input['id']);
        if (!empty($input['page']))   $sql->where('page', $input['page']);
        if (!empty($input['operate']))   $sql->where('operate', $input['operate']);
        if (!empty($input['operateId']))   $sql->where('operateId', $input['operateId']);
        if (!empty($input['dataBeforeModification']))   $sql->where('dataBeforeModification', $input['dataBeforeModification']);
        if (!empty($input['dataAfterModification']))   $sql->where('dataAfterModification', $input['dataAfterModification']);


        $aAdmin_log = $sql->get();

        if (!empty($aAdmin_log)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aAdmin_log];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  Admin_logID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['page']))  return ['result' => false, 'msg' => 'page不能為空'];
        if (!isset($input['operate']))  return ['result' => false, 'msg' => 'operate不能為空'];
        if (!isset($input['operateId']))  return ['result' => false, 'msg' => 'operateId不能為空'];
        if (!isset($input['dataBeforeModification']))  return ['result' => false, 'msg' => 'dataBeforeModification不能為空'];
        if (!isset($input['dataAfterModification']))  return ['result' => false, 'msg' => 'dataAfterModification不能為空'];


        //檢查 Admin_log 是否存在
        $checIinput = [];
        $aAdmin_log = $this->fGetID($id, $checIinput);
        if (!$aAdmin_log['result']) {
            return ['result' => false, 'msg' => '資料不存在'];
        }

        $aData = array(
            'page' => $input['page'],
            'operate' => $input['operate'],
            'operateId' => $input['operateId'],
            'dataBeforeModification' => $input['dataBeforeModification'],
            'dataAfterModification' => $input['dataAfterModification'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Admin_log::where('id', $id)->get();
            //更新
            Admin_log::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Admin_log管理', '更新', $id, $oOldData[0], $data, $token['name']);

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

        $iAdmin_log = Admin_log::where('id', $id)->count();
        if ($iAdmin_log != 1) {
            return ['result' => false, 'msg' => 'Admin_log不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Admin_log::where('id', $id)->get();
            //更新
            Admin_log::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Admin_log管理', '刪除', $id, $oOldData[0], $data, $token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '刪除成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '刪除失敗'];
        }
    }

    //啟用 /停用
    public function fEnable(Request $request, $id)
    {

        $token = Jwt::verifyToken($request->bearerToken());

        $oAdmin_log = Admin_log::where('id', $id)->first();
        if (empty($oAdmin_log)) {
            return ['result' => false, 'msg' => 'Admin_log不存在'];
        }

        $aData = [
            'isEnabled' => $oAdmin_log->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Admin_log::where('id', $id)->get();
            //更新
            Admin_log::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Admin_log管理', '啟用/停用', $id, $oOldData[0], $data, $token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '更新成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '更新失敗'];
        }
    }

    //取得下拉資料
    public function fSelectData()
    {

        $data = Admin_log::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
