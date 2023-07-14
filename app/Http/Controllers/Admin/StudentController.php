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
use App\Models\Student;
use App\Models\Grade_class;


class StudentController extends Controller
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

        $sql = Student::select('studentId', 'classId', 'studentName', 'studentCode', 'password', 'gender', 'pathOfAvater', 'accessToken', 'pushToken', 'experienceTotal', 'level', 'isEnabled', 'created_at', 'updated_at', 'creator', 'ipOfCreator', 'lastUpdater', 'ipOfLastUpdater', 'isRemoved', 'removeTime', 'remover', 'ipOfRemover', 'memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['studentId']))   $sql->where('studentId', 'like', '%' . $input['studentId'] . '%');
        if (!empty($input['classId']))   $sql->where('classId', 'like', '%' . $input['classId'] . '%');
        if (!empty($input['studentName']))   $sql->where('studentName', 'like', '%' . $input['studentName'] . '%');
        if (!empty($input['studentCode']))   $sql->where('studentCode', 'like', '%' . $input['studentCode'] . '%');
        if (!empty($input['password']))   $sql->where('password', 'like', '%' . $input['password'] . '%');
        if (!empty($input['gender']))   $sql->where('gender', 'like', '%' . $input['gender'] . '%');
        if (!empty($input['pathOfAvater']))   $sql->where('pathOfAvater', 'like', '%' . $input['pathOfAvater'] . '%');
        if (!empty($input['accessToken']))   $sql->where('accessToken', 'like', '%' . $input['accessToken'] . '%');
        if (!empty($input['pushToken']))   $sql->where('pushToken', 'like', '%' . $input['pushToken'] . '%');
        if (!empty($input['experienceTotal']))   $sql->where('experienceTotal', 'like', '%' . $input['experienceTotal'] . '%');
        if (!empty($input['level']))   $sql->where('level', 'like', '%' . $input['level'] . '%');
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', 'like', '%' . $input['isEnabled'] . '%');


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('studentId', 'desc')->skip($first)->take($pageSize)->get();

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
        if (!isset($input['classId']))  return ['result' => false, 'msg' => 'classId不能為空'];
        if (!isset($input['studentName']))  return ['result' => false, 'msg' => 'studentName不能為空'];
        if (!isset($input['studentCode']))  return ['result' => false, 'msg' => 'studentCode不能為空'];
        if (!isset($input['password']))  return ['result' => false, 'msg' => 'password不能為空'];
        if (!isset($input['gender']))  return ['result' => false, 'msg' => 'gender不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];

        //cardsCategory  單題題目 群組題目 連續題目
        if(!in_array($input['gender'],['男','女'])){
            return ['result' => false, 'msg' => 'gender錯誤'];
        }

        //studentCode 不能重複
        $count = Student::select('studentCode')->where('studentCode', $input['studentCode'])->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'studentCode不能重複'];
        }

        //檢查 classId 是否存在
        $oGrade_class = Grade_class::where('classId', $input['classId'])->first();
        if (empty($oGrade_class)) {
            return ['result' => false, 'msg' => 'classId不存在'];
        }


        $aData = array(
            'classId' => $input['classId'],
            'studentName' => $input['studentName'],
            'studentCode' => $input['studentCode'],
            'password' => Hash::make($input['password']),
            'gender' => $input['gender'],
            'pathOfAvater' => $input['pathOfAvater'] ? $input['pathOfAvater'] : null,
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Student::create($data);

            //建立log
            $this->AdminLog->fCreate('Student管理', '新增', null, [], $aData, $token['name']);

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
     * @param $studentId StudentID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Student::where('studentId', '=', $id);


        //select條件
        if (!empty($input['studentId']))   $sql->where('studentId', $input['studentId']);
        if (!empty($input['classId']))   $sql->where('classId', $input['classId']);
        if (!empty($input['studentName']))   $sql->where('studentName', $input['studentName']);
        if (!empty($input['studentCode']))   $sql->where('studentCode', $input['studentCode']);
        if (!empty($input['password']))   $sql->where('password', $input['password']);
        if (!empty($input['gender']))   $sql->where('gender', $input['gender']);
        if (!empty($input['pathOfAvater']))   $sql->where('pathOfAvater', $input['pathOfAvater']);
        if (!empty($input['accessToken']))   $sql->where('accessToken', $input['accessToken']);
        if (!empty($input['pushToken']))   $sql->where('pushToken', $input['pushToken']);
        if (!empty($input['experienceTotal']))   $sql->where('experienceTotal', $input['experienceTotal']);
        if (!empty($input['level']))   $sql->where('level', $input['level']);
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', $input['isEnabled']);


        $aStudent = $sql->get();

        if (!empty($aStudent)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aStudent];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  StudentID
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
        if (!isset($input['studentName']))  return ['result' => false, 'msg' => 'studentName不能為空'];
        if (!isset($input['studentCode']))  return ['result' => false, 'msg' => 'studentCode不能為空'];
        if (!isset($input['gender']))  return ['result' => false, 'msg' => 'gender不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];

        //cardsCategory  單題題目 群組題目 連續題目
        if(!in_array($input['gender'],['男','女'])){
            return ['result' => false, 'msg' => 'gender錯誤'];
        }

        //studentCode 不能重複
        $count = Student::select('studentCode')->where('studentCode', $input['studentCode'])
                    ->where('studentId','!=', $id)->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'studentCode不能重複'];
        }

        //檢查 classId 是否存在
        $oGrade_class = Grade_class::where('classId', $input['classId'])->first();
        if (empty($oGrade_class)) {
            return ['result' => false, 'msg' => 'classId不存在'];
        }

        //student是否存在
        $oStudent = Student::where('studentId', $id)->first();
        if (empty($oStudent)) {
            return ['result' => false, 'msg' => 'Student不存在'];
        }


        $aData = array(
            'classId' => $input['classId'],
            'studentName' => $input['studentName'],
            'studentCode' => $input['studentCode'],
            'gender' => $input['gender'],
            'pathOfAvater' => $input['pathOfAvater'] ? $input['pathOfAvater'] : null,
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Student::where('studentId', $id)->get();
            //更新
            Student::where('studentId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Student管理', '更新', $id, $oOldData, $data, $token['name']);

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

        $iStudent = Student::where('studentId', $id)->count();
        if ($iStudent != 1) {
            return ['result' => false, 'msg' => 'Student不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Student::where('studentId', $id)->get();
            //更新
            Student::where('studentId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Student管理', '刪除', $id, $oOldData, $data, $token['name']);

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

        $oStudent = Student::where('studentId', $id)->first();
        if (empty($oStudent)) {
            return ['result' => false, 'msg' => 'Student不存在'];
        }

        $aData = [
            'isEnabled' => $oStudent->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Student::where('studentId', $id)->get();
            //更新
            Student::where('studentId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Student管理', '啟用/停用', $id, $oOldData, $data, $token['name']);

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

        $data = Student::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }

    //修改密碼
    public function fChangePassword(Request $request,$id)
    {
        //uid
        $token = Jwt::verifyToken($request->bearerToken());

        $input = $request->all();

        if (empty($input['password'])) {
            return ['result' => false, 'message' => '密碼不能為空'];
        }

        //student是否存在
        $oStudent = Student::where('studentId', $id)->first();
        if (!empty($oStudent)) {
            DB::beginTransaction();
            try {

                //取得更新前資料
                $oOldData = Student::where('studentId', $id)->get();
                //更新
                $data = [
                    'password' => Hash::make($input['password'])
                ];
                Student::where('studentId', $id)->update($data);
                //建立log
                $this->AdminLog->fCreate('Student管理', '修改密碼', $id, $oStudent, $data, $token['name']);

                DB::commit();
                return ['result' => true, 'msg' => '更新成功'];
            } catch (Exception $e) {
                Log::info($e);
                DB::rollback();
                return ['result' => false, 'msg' => '更新失敗'];
            }
        }else{
            return ['result' => false, 'msg' => 'Student不存在'];
        }

    }
}
