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
use App\Models\Attrs;


class AttrsController extends Controller
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

        $sql = Attrs::select('attrsId', 'attrsName', 'attrsCode', 'isEnabled', 'created_at', 'updated_at', 'creator', 'ipOfCreator', 'lastUpdater', 'ipOfLastUpdater', 'isRemoved', 'removeTime', 'remover', 'ipOfRemover', 'memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['attrsId']))   $sql->where('attrsId', 'like', '%' . $input['attrsId'] . '%');
        if (!empty($input['attrsName']))   $sql->where('attrsName', 'like', '%' . $input['attrsName'] . '%');
        if (!empty($input['attrsCode']))   $sql->where('attrsCode', 'like', '%' . $input['attrsCode'] . '%');
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', 'like', '%' . $input['isEnabled'] . '%');


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('attrsId', 'desc')->skip($first)->take($pageSize)->get();

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
        if (!isset($input['attrsName']))  return ['result' => false, 'msg' => 'attrsName不能為空'];
        if (!isset($input['attrsCode']))  return ['result' => false, 'msg' => 'attrsCode不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];

        //attrsCode不能重複
        $count = Attrs::select('attrsCode')->where('attrsCode', $input['attrsCode'])->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'attrsCode不能重複'];
        }

        $aData = array(
            'attrsName' => $input['attrsName'],
            'attrsCode' => $input['attrsCode'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Attrs::create($data);

            //建立log
            $this->AdminLog->fCreate('Attrs管理', '新增', null, [], $aData, $token['name']);

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
     * @param $attrsId AttrsID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Attrs::where('attrsId', '=', $id);


        //select條件
        if (!empty($input['attrsId']))   $sql->where('attrsId', $input['attrsId']);
        if (!empty($input['attrsName']))   $sql->where('attrsName', $input['attrsName']);
        if (!empty($input['attrsCode']))   $sql->where('attrsCode', $input['attrsCode']);
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', $input['isEnabled']);


        $aAttrs = $sql->get();

        if (!empty($aAttrs)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aAttrs];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  AttrsID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['attrsName']))  return ['result' => false, 'msg' => 'attrsName不能為空'];
        if (!isset($input['attrsCode']))  return ['result' => false, 'msg' => 'attrsCode不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        //檢查 Attrs 是否存在
        $checIinput = [];
        $aAttrs = $this->fGetID($id, $checIinput);
        if (!$aAttrs['result']) {
            return ['result' => false, 'msg' => '資料不存在'];
        }

        //attrsCode不能重複
        $count = Attrs::select('attrsCode')->where('attrsCode', $input['attrsCode'])->where('attrsId', '!=', $id)->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'attrsCode不能重複'];
        }

        $aData = array(
            'attrsName' => $input['attrsName'],
            'attrsCode' => $input['attrsCode'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Attrs::where('attrsId', $id)->get();
            //更新
            Attrs::where('attrsId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Attrs管理', '更新', $id, $oOldData[0], $data, $token['name']);

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

        $iAttrs = Attrs::where('attrsId', $id)->count();
        if ($iAttrs != 1) {
            return ['result' => false, 'msg' => 'Attrs不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Attrs::where('attrsId', $id)->get();
            //更新
            Attrs::where('attrsId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Attrs管理', '刪除', $id, $oOldData[0], $data, $token['name']);

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

        $oAttrs = Attrs::where('attrsId', $id)->first();
        if (empty($oAttrs)) {
            return ['result' => false, 'msg' => 'Attrs不存在'];
        }

        $aData = [
            'isEnabled' => $oAttrs->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Attrs::where('attrsId', $id)->get();
            //更新
            Attrs::where('attrsId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Attrs管理', '啟用/停用', $id, $oOldData[0], $data, $token['name']);

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

        $data = Attrs::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
