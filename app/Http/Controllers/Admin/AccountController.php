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
use App\Models\Account;
use App\Models\Role;
use App\Models\Teacher;


class AccountController extends Controller
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

        $sql = Account::select('account.accountId','account.roleId','account.account','account.isEnabled',
                'account.created_at','account.creator'
                ,'account.updated_at','account.lastUpdater','account.teacherId','role.roleName','teacher.teacherName')
                ->join('role','role.roleId','account.roleId')
                ->join('teacher','teacher.teacherId','account.teacherId');

        //搜尋條件
        if (!empty($input['startTime'])) $sql->where('account.createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) $sql->where('createTime', '<=', $input['endTime']);
        if (!empty($input['roleId']))   $sql->where('account.roleId', $input['roleId']);
        if (!empty($input['account']))   $sql->where('account.account', 'like', '%' . $input['account'] . '%');
        if (!empty($input['accountName']))   $sql->where('account.accountName', 'like', '%' . $input['accountName'] . '%');
        if (isset($input['isEnabled']))   $sql->where('account.isEnabled', $input['isEnabled']);

        //分頁
        $page = isset($input['page']) ? $input['page'] : 1; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('accountId', 'desc')->skip($first)->take($pageSize)->get();

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
        if (!isset($input['roleId']))  return ['result' => false, 'msg' => 'roleId不能為空'];
        if (!isset($input['account']))  return ['result' => false, 'msg' => 'account不能為空'];
        if (!isset($input['password']))  return ['result' => false, 'msg' => 'password不能為空'];
        if (!isset($input['teacherId']))  return ['result' => false, 'msg' => 'teacherId不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];

        if(!filter_var($input['account'], FILTER_VALIDATE_EMAIL)){
            return  ['result' => false, 'msg' => "帳號格式錯誤"];
        } else if (!in_array($input['isEnabled'], array('0', 1))) {
            return ['result' => false, 'msg' => "狀態格式錯誤"];
        }else if(!$this->Common->checkPassword($input['password'])){
            return ['result' => false, 'msg' => "密碼格式錯誤"];
        }

        //帳號不能重複
        $count = Account::select('account')->where('account', $input['account'])->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => '帳號不能重複'];
        }
        //角色存在
        $count = Role::select('roleId')->where('roleId', $input['roleId'])->count();
        if ($count < 0) {
            return ['result' => false,'msg' => '角色不存在'];
        }

        //如果有teacherId 在檢查
        if(!empty($input['如果有teacherId'])){
            $count = Teacher::select('Id')->where('Id', $input['如果有teacherId'])->count();
            if ($count < 0) {
                return ['result' => false, 'msg' => '教師不存在'];
            }
        }


        $aData = array(
            'roleId' => $input['roleId'],
            'account' => $input['account'],
            'password' => Hash::make($input['password']),
            'teacherId' => $input['teacherId'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            Account::create($data);

            //建立log
            $this->AdminLog->fCreate('Account管理', '新增', null, [], $aData, $token['name']);

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
     * @param $accountId AccountID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Account::where('accountId', '=', $id);


        //select條件
        if (!empty($input['accountId']))   $sql->where('accountId', $input['accountId']);
        if (!empty($input['roleId']))   $sql->where('roleId', $input['roleId']);
        if (!empty($input['account']))   $sql->where('account', $input['account']);
        if (!empty($input['password']))   $sql->where('password', $input['password']);
        if (!empty($input['teacherId']))   $sql->where('teacherId', $input['teacherId']);
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', $input['isEnabled']);


        $aAccount = $sql->get();

        if (!empty($aAccount)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aAccount];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  AccountID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['roleId']))  return ['result' => false, 'msg' => 'roleId不能為空'];
        if (!isset($input['account']))  return ['result' => false, 'msg' => 'account不能為空'];
        if (!isset($input['password']))  return ['result' => false, 'msg' => 'password不能為空'];
        if (!isset($input['teacherId']))  return ['result' => false, 'msg' => 'teacherId不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        if (!filter_var($input['account'], FILTER_VALIDATE_EMAIL)) {
            return  ['result' => false, 'msg' => "帳號格式錯誤"];
        } else if (!in_array($input['isEnabled'], array('0', 1))) {
            return ['result' => false, 'msg' => "狀態格式錯誤"];
        }

        //帳號不能重複
        $count = Account::select('account')->where('account', $input['account'])->where('accountId', '!=', $id)->count();
        if ($count > 0) {
            return ['result' => false,'msg' => '帳號不能重複'];
        }
        //角色存在
        $count = Role::select('roleId')->where('roleId', $input['roleId'])->count();
        if ($count < 0) {
            return ['result' => false,'msg' => '角色不存在'];
        }

        //檢查 Account 是否存在
        $oAccount = Account::where('accountId', $id)->first();
        if (empty($oAccount)) {
            return ['result' => false, 'msg' => 'Account不存在'];
        }

        //如果有teacherId 在檢查
        if(!empty($input['如果有teacherId'])){
            $count = Teacher::select('Id')->where('Id', $input['如果有teacherId'])->count();
            if ($count < 0) {
                return ['result' => false, 'msg' => '教師不存在'];
            }
        }

        $aData = array(
            'roleId' => $input['roleId'],
            'account' => $input['account'],
            'password' => Hash::make($input['password']),
            'teacherId' => $input['teacherId'],
            'isEnabled' => $input['isEnabled'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Account::where('accountId', $id)->get();
            //更新
            Account::where('accountId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Account管理', '更新', $id, $oOldData, $data, $token['name']);

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

        $iAccount = Account::where('accountId', $id)->count();
        if ($iAccount != 1) {
            return ['result' => false, 'msg' => 'Account不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Account::where('accountId', $id)->get();
            //更新
            Account::where('accountId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Account管理', '刪除', $id, $oOldData[0], $data, $token['name']);

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

        $oAccount = Account::where('accountId', $id)->first();
        if (empty($oAccount)) {
            return ['result' => false, 'msg' => 'Account不存在'];
        }

        $aData = [
            'isEnabled' => $oAccount->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Account::where('accountId', $id)->get();
            //更新
            Account::where('accountId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Account管理', '啟用/停用', $id, $oOldData[0], $data, $token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '更新成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '更新失敗'];
        }
    }

    //更改密碼
    public function fChangePassword(Request $request, $id){

        $token = Jwt::verifyToken($request->bearerToken());
        $input = json_decode($request->getContent(), true);

        //檢查
        if (empty($input['password'])) {
            return  ['result' => false, 'msg' => "密碼不能為空"];
        }
        if (!$this->Common->checkPassword($input['password'])) {
            return ['result' => false, 'msg' => "密碼格式錯誤"];
        }

        $Account =  Account::select('accountId')->where('accountId', $id)->first();
        if (!$Account) return ['result' => false, 'msg' => '帳號不存在'];

        DB::beginTransaction();
        try {

            $aData = [
                'password' => Hash::make($input['password'])
            ];
            $aData = $this->Common->fLaravelUpdateDate($aData, $token['name']);

            $oOldAccount = Account::where('accountId', $id)->get();
            Account::where('accountId', $id)->update($aData);

            $this->AdminLog->fCreate('帳號管理','更改密碼',$id,$oOldAccount[0],$aData,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '更改密碼成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '更改密碼失敗'];
        }
    }

    //取得下拉資料
    public function fSelectData()
    {

        $data = Account::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
