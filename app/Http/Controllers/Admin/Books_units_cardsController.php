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
use App\Models\Books_units_cards;
use App\Models\Books_units;
use App\Models\Books;


class Books_units_cardsController extends Controller
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

        $sql = Books_units_cards::select('cardsId', 'booksId', 'unitsId', 'cardsName', 'cardsCode', 'cardsCategory', 'cardLevel', 'isEnabled', 'created_at', 'updated_at', 'creator', 'ipOfCreator', 'lastUpdater', 'ipOfLastUpdater', 'isRemoved', 'removeTime', 'remover', 'ipOfRemover', 'memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['cardsId']))   $sql->where('cardsId', 'like', '%' . $input['cardsId'] . '%');
        if (!empty($input['booksId']))   $sql->where('booksId', 'like', '%' . $input['booksId'] . '%');
        if (!empty($input['unitsId']))   $sql->where('unitsId', 'like', '%' . $input['unitsId'] . '%');
        if (!empty($input['cardsName']))   $sql->where('cardsName', 'like', '%' . $input['cardsName'] . '%');
        if (!empty($input['cardsCode']))   $sql->where('cardsCode', 'like', '%' . $input['cardsCode'] . '%');
        if (!empty($input['cardsCategory']))   $sql->where('cardsCategory', 'like', '%' . $input['cardsCategory'] . '%');
        if (!empty($input['cardLevel']))   $sql->where('cardLevel', 'like', '%' . $input['cardLevel'] . '%');
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', 'like', '%' . $input['isEnabled'] . '%');


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('cardsId', 'desc')->skip($first)->take($pageSize)->get();

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
        if (!isset($input['unitsId']))  return ['result' => false, 'msg' => 'unitsId不能為空'];
        if (!isset($input['cardsName']))  return ['result' => false, 'msg' => 'cardsName不能為空'];
        if (!isset($input['cardsCode']))  return ['result' => false, 'msg' => 'cardsCode不能為空'];
        if (!isset($input['cardsCategory']))  return ['result' => false, 'msg' => 'cardsCategory不能為空'];
        if (!isset($input['cardLevel']))  return ['result' => false, 'msg' => 'cardLevel不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];

        //cardsCategory  單題題目 群組題目 連續題目
        if(!in_array($input['cardsCategory'],['單題題目','群組題目','連續題目'])){
            return ['result' => false, 'msg' => 'cardsCategory錯誤'];
        }
        //cardLevel  單題題目 群組題目 連續題目
        if(!in_array($input['cardLevel'],['易','中','難'])){
            return ['result' => false, 'msg' => 'cardLevel錯誤'];
        }

        //books是否存在
        $oBooks = Books::select('booksId')->where('booksId', $input['booksId'])->first();
        if (empty($oBooks)) {
            return ['result' => false, 'msg' => 'Books不存在'];
        }
        //unitsId是否存在
        $oBooks_units = Books_units::select('unitsId')->where('unitsId', $input['unitsId'])->first();
        if (empty($oBooks_units)) {
            return ['result' => false, 'msg' => 'unitsId不存在'];
        }



        $aData = array(
            'booksId' => $input['booksId'],
            'unitsId' => $input['unitsId'],
            'cardsName' => $input['cardsName'],
            'cardsCode' => $input['cardsCode'],
            'cardsCategory' => $input['cardsCategory'],
            'cardLevel' => $input['cardLevel'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Books_units_cards::create($data);

            //建立log
            $this->AdminLog->fCreate('Books_units_cards管理', '新增', null, [], $aData, $token['name']);

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
     * @param $cardsId Books_units_cardsID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Books_units_cards::where('cardsId', '=', $id);


        //select條件
        if (!empty($input['cardsId']))   $sql->where('cardsId', $input['cardsId']);
        if (!empty($input['booksId']))   $sql->where('booksId', $input['booksId']);
        if (!empty($input['unitsId']))   $sql->where('unitsId', $input['unitsId']);
        if (!empty($input['cardsName']))   $sql->where('cardsName', $input['cardsName']);
        if (!empty($input['cardsCode']))   $sql->where('cardsCode', $input['cardsCode']);
        if (!empty($input['cardsCategory']))   $sql->where('cardsCategory', $input['cardsCategory']);
        if (!empty($input['cardLevel']))   $sql->where('cardLevel', $input['cardLevel']);
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', $input['isEnabled']);


        $aBooks_units_cards = $sql->get();

        if (!empty($aBooks_units_cards)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aBooks_units_cards];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  Books_units_cardsID
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
        if (!isset($input['unitsId']))  return ['result' => false, 'msg' => 'unitsId不能為空'];
        if (!isset($input['cardsName']))  return ['result' => false, 'msg' => 'cardsName不能為空'];
        if (!isset($input['cardsCode']))  return ['result' => false, 'msg' => 'cardsCode不能為空'];
        if (!isset($input['cardsCategory']))  return ['result' => false, 'msg' => 'cardsCategory不能為空'];
        if (!isset($input['cardLevel']))  return ['result' => false, 'msg' => 'cardLevel不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];

        //cardsCategory  單題題目 群組題目 連續題目
        if(!in_array($input['cardsCategory'],['單題題目','群組題目','連續題目'])){
            return ['result' => false, 'msg' => 'cardsCategory錯誤'];
        }
        //cardLevel  單題題目 群組題目 連續題目
        if(!in_array($input['cardLevel'],['易','中','難'])){
            return ['result' => false, 'msg' => 'cardLevel錯誤'];
        }

        //books是否存在
        $oBooks = Books::select('booksId')->where('booksId', $input['booksId'])->first();
        if (empty($oBooks)) {
            return ['result' => false, 'msg' => 'Books不存在'];
        }
        //unitsId是否存在
        $oBooks_units = Books_units::select('unitsId')->where('unitsId', $input['unitsId'])->first();
        if (empty($oBooks_units)) {
            return ['result' => false, 'msg' => 'unitsId不存在'];
        }

        //檢查 Books_units_cards 是否存在
        $iBooks_units_cards = Books_units_cards::where('cardsId', $id)->count();
        if ($iBooks_units_cards != 1) {
            return ['result' => false, 'msg' => 'Books_units_cards不存在'];
        }

        //Books_units_cards不能重複
        $count = Books_units_cards::select('cardsCode')->where('cardsCode', $input['cardsCode'])->where('cardsId', '!=', $id)->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'cardsCode不能重複'];
        }


        $aData = array(
            'booksId' => $input['booksId'],
            'unitsId' => $input['unitsId'],
            'cardsName' => $input['cardsName'],
            'cardsCode' => $input['cardsCode'],
            'cardsCategory' => $input['cardsCategory'],
            'cardLevel' => $input['cardLevel'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Books_units_cards::where('cardsId', $id)->get();
            //更新
            Books_units_cards::where('cardsId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books_units_cards管理', '更新', $id, $oOldData, $data, $token['name']);

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

        $iBooks_units_cards = Books_units_cards::where('cardsId', $id)->count();
        if ($iBooks_units_cards != 1) {
            return ['result' => false, 'msg' => 'Books_units_cards不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Books_units_cards::where('cardsId', $id)->get();
            //更新
            Books_units_cards::where('cardsId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books_units_cards管理', '刪除', $id, $oOldData, $data, $token['name']);

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

        $oBooks_units_cards = Books_units_cards::where('cardsId', $id)->first();
        if (empty($oBooks_units_cards)) {
            return ['result' => false, 'msg' => 'Books_units_cards不存在'];
        }

        $aData = [
            'isEnabled' => $oBooks_units_cards->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Books_units_cards::where('cardsId', $id)->get();
            //更新
            Books_units_cards::where('cardsId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books_units_cards管理', '啟用/停用', $id, $oOldData, $data, $token['name']);

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

        $data = Books_units_cards::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
