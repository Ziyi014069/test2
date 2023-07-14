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
use App\Models\Books_units;
use App\Models\Books;


class Books_unitsController extends Controller
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

        $sql = Books_units::select('Books_units.*','books.booksName')
                    ->join('books','books.booksId','Books_units.booksId');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['unitsId']))   $sql->where('unitsId', 'like', '%' . $input['unitsId'] . '%');
        if (!empty($input['booksId']))   $sql->where('booksId', 'like', '%' . $input['booksId'] . '%');
        if (!empty($input['unitsName']))   $sql->where('unitsName', 'like', '%' . $input['unitsName'] . '%');
        if (!empty($input['unitsCode']))   $sql->where('unitsCode', 'like', '%' . $input['unitsCode'] . '%');
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', 'like', '%' . $input['isEnabled'] . '%');


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('Books_units.unitsId', 'desc')->skip($first)->take($pageSize)->get();

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
        if (!isset($input['booksId']))  return ['result' => false, 'msg' => 'booksId不能為空'];
        if (!isset($input['unitsName']))  return ['result' => false, 'msg' => 'unitsName不能為空'];
        if (!isset($input['unitsCode']))  return ['result' => false, 'msg' => 'unitsCode不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        //books是否存在
        $oBooks = Books::select('booksId')->where('booksId', $input['booksId'])->first();
        if (empty($oBooks)) {
            return ['result' => false, 'msg' => 'Books不存在'];
        }

        $aData = array(
            'booksId' => $input['booksId'],
            'unitsName' => $input['unitsName'],
            'unitsCode' => $input['unitsCode'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Books_units::create($data);

            //建立log
            $this->AdminLog->fCreate('Books_units管理', '新增', null, [], $aData, $token['name']);

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
     * @param $unitsId Books_unitsID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Books_units::where('unitsId', '=', $id);


        //select條件
        if (!empty($input['unitsId']))   $sql->where('unitsId', $input['unitsId']);
        if (!empty($input['booksId']))   $sql->where('booksId', $input['booksId']);
        if (!empty($input['unitsName']))   $sql->where('unitsName', $input['unitsName']);
        if (!empty($input['unitsCode']))   $sql->where('unitsCode', $input['unitsCode']);
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', $input['isEnabled']);


        $aBooks_units = $sql->get();

        if (!empty($aBooks_units)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aBooks_units];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  Books_unitsID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['booksId']))  return ['result' => false, 'msg' => 'booksId不能為空'];
        if (!isset($input['unitsName']))  return ['result' => false, 'msg' => 'unitsName不能為空'];
        if (!isset($input['unitsCode']))  return ['result' => false, 'msg' => 'unitsCode不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        //檢查 Books_units 是否存在
        $iBooks_units = Books_units::where('unitsId', $id)->count();
        if ($iBooks_units != 1) {
            return ['result' => false, 'msg' => 'Books_units不存在'];
        }

        //booksCode不能重複
        $count = Books_units::select('unitsCode')->where('unitsCode', $input['unitsCode'])->where('unitsId', '!=', $id)->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'unitsCode不能重複'];
        }

        //books不能重複
        $oBooks = Books::select('booksId')->where('booksId', $input['booksId'])->first();
        if (empty($oBooks)) {
            return ['result' => false, 'msg' => 'Books不存在'];
        }

        $aData = array(
            'booksId' => $input['booksId'],
            'unitsName' => $input['unitsName'],
            'unitsCode' => $input['unitsCode'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Books_units::where('unitsId', $id)->get();
            //更新
            Books_units::where('unitsId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books_units管理', '更新', $id, $oOldData, $data, $token['name']);

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

        $iBooks_units = Books_units::where('unitsId', $id)->count();
        if ($iBooks_units != 1) {
            return ['result' => false, 'msg' => 'Books_units不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Books_units::where('unitsId', $id)->get();
            //更新
            Books_units::where('unitsId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books_units管理', '刪除', $id, $oOldData, $data, $token['name']);

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

        $oBooks_units = Books_units::where('unitsId', $id)->first();
        if (empty($oBooks_units)) {
            return ['result' => false, 'msg' => 'Books_units不存在'];
        }

        $aData = [
            'isEnabled' => $oBooks_units->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Books_units::where('unitsId', $id)->get();
            //更新
            Books_units::where('unitsId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books_units管理', '啟用/停用', $id, $oOldData, $data, $token['name']);

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

        $data = Books_units::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
