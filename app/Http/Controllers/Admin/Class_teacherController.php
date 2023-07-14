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
use App\Models\Class_teacher;


class Class_teacherController extends Controller
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

        $sql = Class_teacher::select('id', 'classId', 'teacherId', 'teacherType', 'created_at', 'updated_at', 'creator', 'ipOfCreator', 'lastUpdater', 'ipOfLastUpdater', 'isRemoved', 'removeTime', 'remover', 'ipOfRemover', 'memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['id']))   $sql->where('id', 'like', '%' . $input['id'] . '%');
        if (!empty($input['classId']))   $sql->where('classId', 'like', '%' . $input['classId'] . '%');
        if (!empty($input['teacherId']))   $sql->where('teacherId', 'like', '%' . $input['teacherId'] . '%');
        if (!empty($input['teacherType']))   $sql->where('teacherType', 'like', '%' . $input['teacherType'] . '%');


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
        if (!isset($input['classId']))  return ['result' => false, 'msg' => 'classId不能為空'];
        if (!isset($input['teacherId']))  return ['result' => false, 'msg' => 'teacherId不能為空'];
        if (!isset($input['teacherType']))  return ['result' => false, 'msg' => 'teacherType不能為空'];


        $aData = array(
            'classId' => $input['classId'],
            'teacherId' => $input['teacherId'],
            'teacherType' => $input['teacherType'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Class_teacher::create($data);

            //建立log
            $this->AdminLog->fCreate('Class_teacher管理', '新增', null, [], $aData, $token['name']);

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
     * @param $id Class_teacherID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Class_teacher::where('id', '=', $id);


        //select條件
        if (!empty($input['id']))   $sql->where('id', $input['id']);
        if (!empty($input['classId']))   $sql->where('classId', $input['classId']);
        if (!empty($input['teacherId']))   $sql->where('teacherId', $input['teacherId']);
        if (!empty($input['teacherType']))   $sql->where('teacherType', $input['teacherType']);


        $aClass_teacher = $sql->get();

        if (!empty($aClass_teacher)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aClass_teacher];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  Class_teacherID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['classId']))  return ['result' => false, 'msg' => 'classId不能為空'];
        if (!isset($input['teacherId']))  return ['result' => false, 'msg' => 'teacherId不能為空'];
        if (!isset($input['teacherType']))  return ['result' => false, 'msg' => 'teacherType不能為空'];


        //檢查 Class_teacher 是否存在
        $iClass_teacher = Class_teacher::where('id', $id)->count();
        if ($iClass_teacher != 1) {
            return ['result' => false, 'msg' => 'Class_teacher不存在'];
        }

        $aData = array(
            'classId' => $input['classId'],
            'teacherId' => $input['teacherId'],
            'teacherType' => $input['teacherType'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Class_teacher::where('id', $id)->get();
            //更新
            Class_teacher::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Class_teacher管理', '更新', $id, $oOldData, $data, $token['name']);

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

        $iClass_teacher = Class_teacher::where('id', $id)->count();
        if ($iClass_teacher != 1) {
            return ['result' => false, 'msg' => 'Class_teacher不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Class_teacher::where('id', $id)->get();
            //更新
            Class_teacher::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Class_teacher管理', '刪除', $id, $oOldData, $data, $token['name']);

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

        $oClass_teacher = Class_teacher::where('id', $id)->first();
        if (empty($oClass_teacher)) {
            return ['result' => false, 'msg' => 'Class_teacher不存在'];
        }

        $aData = [
            'isEnabled' => $oClass_teacher->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Class_teacher::where('id', $id)->get();
            //更新
            Class_teacher::where('id', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Class_teacher管理', '啟用/停用', $id, $oOldData, $data, $token['name']);

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

        $data = Class_teacher::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
