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
use App\Models\Teacher;


class TeacherController extends Controller
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

        $sql = Teacher::select('teacherId', 'teacherName', 'teacherCode', 'pathOfAvater', 'isEnabled', 'created_at', 'updated_at', 'creator', 'ipOfCreator', 'lastUpdater', 'ipOfLastUpdater', 'isRemoved', 'removeTime', 'remover', 'ipOfRemover', 'memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['teacherId']))   $sql->where('teacherId', 'like', '%' . $input['teacherId'] . '%');
        if (!empty($input['teacherName']))   $sql->where('teacherName', 'like', '%' . $input['teacherName'] . '%');
        if (!empty($input['teacherCode']))   $sql->where('teacherCode', 'like', '%' . $input['teacherCode'] . '%');
        if (!empty($input['pathOfAvater']))   $sql->where('pathOfAvater', 'like', '%' . $input['pathOfAvater'] . '%');
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', 'like', '%' . $input['isEnabled'] . '%');


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('teacherId', 'desc')->skip($first)->take($pageSize)->get();

        foreach ($data as $key => $value) {
            $value['img'] = asset('').$value['pathOfAvater'];
        }

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
        if (!isset($input['teacherName']))  return ['result' => false, 'msg' => 'teacherName不能為空'];
        if (!isset($input['teacherCode']))  return ['result' => false, 'msg' => 'teacherCode不能為空'];
        if (!isset($input['pathOfAvater']))  return ['result' => false, 'msg' => 'pathOfAvater不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];

        //teacherCode不能重複
        $count = Teacher::select('teacherName')->where('teacherCode', $input['teacherCode'])->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'teacherCode不能重複'];
        }

        $aData = array(
            'teacherName' => $input['teacherName'],
            'teacherCode' => $input['teacherCode'],
            'pathOfAvater' => $input['pathOfAvater'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Teacher::create($data);

            //建立log
            $this->AdminLog->fCreate('Teacher管理', '新增', null, [], $aData, $token['name']);

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
     * @param $teacherId TeacherID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Teacher::where('teacherId', '=', $id);


        //select條件
        if (!empty($input['teacherId']))   $sql->where('teacherId', $input['teacherId']);
        if (!empty($input['teacherName']))   $sql->where('teacherName', $input['teacherName']);
        if (!empty($input['teacherCode']))   $sql->where('teacherCode', $input['teacherCode']);
        if (!empty($input['pathOfAvater']))   $sql->where('pathOfAvater', $input['pathOfAvater']);
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', $input['isEnabled']);


        $aTeacher = $sql->get();

        if (!empty($aTeacher)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aTeacher];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  TeacherID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['teacherName']))  return ['result' => false, 'msg' => 'teacherName不能為空'];
        if (!isset($input['teacherCode']))  return ['result' => false, 'msg' => 'teacherCode不能為空'];
        if (!isset($input['pathOfAvater']))  return ['result' => false, 'msg' => 'pathOfAvater不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        //檢查 Teacher 是否存在
        $checIinput = [];
        $aTeacher = $this->fGetID($id, $checIinput);
        if (!$aTeacher['result']) {
            return ['result' => false, 'msg' => '資料不存在'];
        }

        $aData = array(
            'teacherName' => $input['teacherName'],
            'teacherCode' => $input['teacherCode'],
            'pathOfAvater' => $input['pathOfAvater'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Teacher::where('teacherId', $id)->get();
            //更新
            Teacher::where('teacherId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Teacher管理', '更新', $id, $oOldData[0], $data, $token['name']);

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

        $iTeacher = Teacher::where('teacherId', $id)->count();
        if ($iTeacher != 1) {
            return ['result' => false, 'msg' => 'Teacher不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Teacher::where('teacherId', $id)->get();
            //更新
            Teacher::where('teacherId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Teacher管理', '刪除', $id, $oOldData[0], $data, $token['name']);

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

        $oTeacher = Teacher::where('teacherId', $id)->first();
        if (empty($oTeacher)) {
            return ['result' => false, 'msg' => 'Teacher不存在'];
        }

        $aData = [
            'isEnabled' => $oTeacher->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Teacher::where('teacherId', $id)->get();
            //更新
            Teacher::where('teacherId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Teacher管理', '啟用/停用', $id, $oOldData[0], $data, $token['name']);

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

        $data = Teacher::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
