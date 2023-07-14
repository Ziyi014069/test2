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
use App\Models\Coin;


class CoinController extends Controller
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

        $sql = Coin::select('id', 'coinName', 'coinCode', 'created_at', 'updated_at', 'creator', 'ipOfCreator', 'lastUpdater', 'ipOfLastUpdater', 'isRemoved', 'removeTime', 'remover', 'ipOfRemover', 'memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['id']))   $sql->where('id', 'like', '%' . $input['id'] . '%');
        if (!empty($input['coinName']))   $sql->where('coinName', 'like', '%' . $input['coinName'] . '%');
        if (!empty($input['coinCode']))   $sql->where('coinCode', 'like', '%' . $input['coinCode'] . '%');


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
        if (!isset($input['coinName']))  return ['result' => false, 'msg' => 'coinName不能為空'];
        if (!isset($input['coinCode']))  return ['result' => false, 'msg' => 'coinCode不能為空'];


        $aData = array(
            'coinName' => $input['coinName'],
            'coinCode' => $input['coinCode'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Coin::create($data);

            //建立log
            $this->AdminLog->fCreate('Coin管理', '新增', null, [], $aData, $token['name']);

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
     * @param $id CoinID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Coin::where('id', '=', $id);


        //select條件
        if (!empty($input['id']))   $sql->where('id', $input['id']);
        if (!empty($input['coinName']))   $sql->where('coinName', $input['coinName']);
        if (!empty($input['coinCode']))   $sql->where('coinCode', $input['coinCode']);


        $aCoin = $sql->get();

        if (!empty($aCoin)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aCoin];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  CoinID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['coinName']))  return ['result' => false, 'msg' => 'coinName不能為空'];
        if (!isset($input['coinCode']))  return ['result' => false, 'msg' => 'coinCode不能為空'];


        //檢查 Coin 是否存在
        $iCoin = Coin::where('id', $id)->count();
        if ($iCoin != 1) {
            return ['result' => false, 'msg' => 'Coin不存在'];
        }

        $aData = array(
            'coinName' => $input['coinName'],
            'coinCode' => $input['coinCode'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Coin::where('id', $id)->get();
            //更新
            Coin::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Coin管理', '更新', $id, $oOldData, $data, $token['name']);

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

        $iCoin = Coin::where('id', $id)->count();
        if ($iCoin != 1) {
            return ['result' => false, 'msg' => 'Coin不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Coin::where('id', $id)->get();
            //更新
            Coin::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Coin管理', '刪除', $id, $oOldData, $data, $token['name']);

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

        $oCoin = Coin::where('id', $id)->first();
        if (empty($oCoin)) {
            return ['result' => false, 'msg' => 'Coin不存在'];
        }

        $aData = [
            'isEnabled' => $oCoin->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Coin::where('id', $id)->get();
            //更新
            Coin::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Coin管理', '啟用/停用', $id, $oOldData, $data, $token['name']);

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

        $data = Coin::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
