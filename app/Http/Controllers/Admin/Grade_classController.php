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
use App\Models\Grade_class;


class Grade_classController extends Controller
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

        $sql = Grade_class::select('classId', 'className', 'classCode', 'isEnabled', 'created_at', 'updated_at', 'creator', 'ipOfCreator', 'lastUpdater', 'ipOfLastUpdater', 'isRemoved', 'removeTime', 'remover', 'ipOfRemover', 'memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['classId']))   $sql->where('classId', 'like', '%' . $input['classId'] . '%');
        if (!empty($input['className']))   $sql->where('className', 'like', '%' . $input['className'] . '%');
        if (!empty($input['classCode']))   $sql->where('classCode', 'like', '%' . $input['classCode'] . '%');
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', 'like', '%' . $input['isEnabled'] . '%');


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('classId', 'desc')->skip($first)->take($pageSize)->get();

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
        if (!isset($input['className']))  return ['result' => false, 'msg' => 'className不能為空'];
        if (!isset($input['classCode']))  return ['result' => false, 'msg' => 'classCode不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];

        //classCode 不能重複
        $count = Grade_class::select('classCode')->where('classCode', $input['classCode'])->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'classCode不能重複'];
        }


        $aData = array(
            'className' => $input['className'],
            'classCode' => $input['classCode'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Grade_class::create($data);

            //建立log
            $this->AdminLog->fCreate('Grade_class管理', '新增', null, [], $aData, $token['name']);

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
     * @param $classId Grade_classID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Grade_class::where('classId', '=', $id);


        //select條件
        if (!empty($input['classId']))   $sql->where('classId', $input['classId']);
        if (!empty($input['className']))   $sql->where('className', $input['className']);
        if (!empty($input['classCode']))   $sql->where('classCode', $input['classCode']);
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', $input['isEnabled']);


        $aGrade_class = $sql->get();

        if (!empty($aGrade_class)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aGrade_class];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  Grade_classID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['className']))  return ['result' => false, 'msg' => 'className不能為空'];
        if (!isset($input['classCode']))  return ['result' => false, 'msg' => 'classCode不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        //檢查 Grade_class 是否存在
        $iGrade_class = Grade_class::where('classId', $id)->count();
        if ($iGrade_class != 1) {
            return ['result' => false, 'msg' => 'Grade_class不存在'];
        }

        $aData = array(
            'className' => $input['className'],
            'classCode' => $input['classCode'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Grade_class::where('classId', $id)->get();
            //更新
            Grade_class::where('classId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Grade_class管理', '更新', $id, $oOldData, $data, $token['name']);

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

        $iGrade_class = Grade_class::where('classId', $id)->count();
        if ($iGrade_class != 1) {
            return ['result' => false, 'msg' => 'Grade_class不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Grade_class::where('classId', $id)->get();
            //更新
            Grade_class::where('classId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Grade_class管理', '刪除', $id, $oOldData, $data, $token['name']);

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

        $oGrade_class = Grade_class::where('classId', $id)->first();
        if (empty($oGrade_class)) {
            return ['result' => false, 'msg' => 'Grade_class不存在'];
        }

        $aData = [
            'isEnabled' => $oGrade_class->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Grade_class::where('classId', $id)->get();
            //更新
            Grade_class::where('classId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Grade_class管理', '啟用/停用', $id, $oOldData, $data, $token['name']);

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

        $data = Grade_class::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
