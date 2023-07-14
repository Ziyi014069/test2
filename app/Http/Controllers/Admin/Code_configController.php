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
use App\Models\Code_config;


class Code_configController extends Controller
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

        $sql = Code_config::select('id', 'kind', 'kindName', 'name', 'code', 'value', 'isEnabled', 'created_at', 'updated_at', 'creator', 'ipOfCreator', 'lastUpdater', 'ipOfLastUpdater', 'isRemoved', 'removeTime', 'remover', 'ipOfRemover', 'memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['id']))   $sql->where('id', 'like', '%' . $input['id'] . '%');
        if (!empty($input['kind']))   $sql->where('kind', 'like', '%' . $input['kind'] . '%');
        if (!empty($input['kindName']))   $sql->where('kindName', 'like', '%' . $input['kindName'] . '%');
        if (!empty($input['name']))   $sql->where('name', 'like', '%' . $input['name'] . '%');
        if (!empty($input['code']))   $sql->where('code', 'like', '%' . $input['code'] . '%');
        if (!empty($input['value']))   $sql->where('value', 'like', '%' . $input['value'] . '%');
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', 'like', '%' . $input['isEnabled'] . '%');


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
        if (!isset($input['kind']))  return ['result' => false, 'msg' => 'kind不能為空'];
        if (!isset($input['kindName']))  return ['result' => false, 'msg' => 'kindName不能為空'];
        if (!isset($input['name']))  return ['result' => false, 'msg' => 'name不能為空'];
        if (!isset($input['code']))  return ['result' => false, 'msg' => 'code不能為空'];
        if (!isset($input['value']))  return ['result' => false, 'msg' => 'value不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        $aData = array(
            'kind' => $input['kind'],
            'kindName' => $input['kindName'],
            'name' => $input['name'],
            'code' => $input['code'],
            'value' => $input['value'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Code_config::create($data);

            //建立log
            $this->AdminLog->fCreate('Code_config管理', '新增', null, [], $aData, $token['name']);

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
     * @param $id Code_configID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Code_config::where('id', '=', $id);


        //select條件
        if (!empty($input['id']))   $sql->where('id', $input['id']);
        if (!empty($input['kind']))   $sql->where('kind', $input['kind']);
        if (!empty($input['kindName']))   $sql->where('kindName', $input['kindName']);
        if (!empty($input['name']))   $sql->where('name', $input['name']);
        if (!empty($input['code']))   $sql->where('code', $input['code']);
        if (!empty($input['value']))   $sql->where('value', $input['value']);
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', $input['isEnabled']);


        $aCode_config = $sql->get();

        if (!empty($aCode_config)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aCode_config];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  Code_configID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['kind']))  return ['result' => false, 'msg' => 'kind不能為空'];
        if (!isset($input['kindName']))  return ['result' => false, 'msg' => 'kindName不能為空'];
        if (!isset($input['name']))  return ['result' => false, 'msg' => 'name不能為空'];
        if (!isset($input['code']))  return ['result' => false, 'msg' => 'code不能為空'];
        if (!isset($input['value']))  return ['result' => false, 'msg' => 'value不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        //檢查 Code_config 是否存在
        $checIinput = [];
        $aCode_config = $this->fGetID($id, $checIinput);
        if (!$aCode_config['result']) {
            return ['result' => false, 'msg' => '資料不存在'];
        }

        $aData = array(
            'kind' => $input['kind'],
            'kindName' => $input['kindName'],
            'name' => $input['name'],
            'code' => $input['code'],
            'value' => $input['value'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Code_config::where('id', $id)->get();
            //更新
            Code_config::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Code_config管理', '更新', $id, $oOldData[0], $data, $token['name']);

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

        $iCode_config = Code_config::where('id', $id)->count();
        if ($iCode_config != 1) {
            return ['result' => false, 'msg' => 'Code_config不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Code_config::where('id', $id)->get();
            //更新
            Code_config::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Code_config管理', '刪除', $id, $oOldData[0], $data, $token['name']);

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

        $oCode_config = Code_config::where('id', $id)->first();
        if (empty($oCode_config)) {
            return ['result' => false, 'msg' => 'Code_config不存在'];
        }

        $aData = [
            'isEnabled' => $oCode_config->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Code_config::where('id', $id)->get();
            //更新
            Code_config::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Code_config管理', '啟用/停用', $id, $oOldData[0], $data, $token['name']);

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

        $data = Code_config::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
