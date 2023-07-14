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
use App\Models\Experience_config;


class Experience_configController extends Controller
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
    public function fList(Request $request){

    	$input = $request->all();

    	$sql = Experience_config::select('id','level','experience','amount','created_at','updated_at','creator','ipOfCreator','lastUpdater','ipOfLastUpdater','isRemoved','removeTime','remover','ipOfRemover','memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if(!empty($input['id']))   $sql->where('id','like','%'.$input['id'].'%');
if(!empty($input['level']))   $sql->where('level','like','%'.$input['level'].'%');
if(!empty($input['experience']))   $sql->where('experience','like','%'.$input['experience'].'%');
if(!empty($input['amount']))   $sql->where('amount','like','%'.$input['amount'].'%');


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1 ; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('id','desc')->skip($first)->take($pageSize)->get();

        return [ 'total' => $total , 'data' => $data ];
    }

    /**
     * 新增
     *
     * @param
     *
     * @return {array
     */
    public function fAdd(Request $request){
        // $input = $requset->all();
        $input = json_decode($request->getContent(),true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if(!isset($input['level']))  return ['result' => false, 'msg' => 'level不能為空'];
if(!isset($input['experience']))  return ['result' => false, 'msg' => 'experience不能為空'];
if(!isset($input['amount']))  return ['result' => false, 'msg' => 'amount不能為空'];


        $aData = array(
                                  'level' => $input['level'],
 'experience' => $input['experience'],
 'amount' => $input['amount'],
 );

        $data = $this->Common->fLaravelCreateDate($aData,$token['name']);

        DB::beginTransaction();
        try {
            //新增
            Experience_config::create($data);

            //建立log
            $this->AdminLog->fCreate('Experience_config管理','新增',null,[],$aData,$token['name']);

            DB::commit();
            return ['result' => true , 'msg' => '新增成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '新增失敗'];
        }

    }


    /**
     * 取單一
     *
     * @param $id Experience_configID
     *
     * @return {array
     */
    public function fGetID($id,$input){
        $sql = Experience_config::where('id', '=', $id);


        //select條件
        if(!empty($input['id']))   $sql->where('id',$input['id']);
if(!empty($input['level']))   $sql->where('level',$input['level']);
if(!empty($input['experience']))   $sql->where('experience',$input['experience']);
if(!empty($input['amount']))   $sql->where('amount',$input['amount']);


        $aExperience_config = $sql->get();

        if (!empty($aExperience_config)){
            return ['result' => true, 'msg' => 'success','data' => $aExperience_config];
        }else{
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  Experience_configID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id){
        // $input = $requset->all();
        $input = json_decode($request->getContent(),true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if(!isset($input['level']))  return ['result' => false, 'msg' => 'level不能為空'];
if(!isset($input['experience']))  return ['result' => false, 'msg' => 'experience不能為空'];
if(!isset($input['amount']))  return ['result' => false, 'msg' => 'amount不能為空'];


        //檢查 Experience_config 是否存在
        $iExperience_config = Experience_config::where('id',$id)->count();
        if($iExperience_config != 1 ){
            return ['result' => false, 'msg' => 'Experience_config不存在'];
        }

        $aData = array(
                                  'level' => $input['level'],
 'experience' => $input['experience'],
 'amount' => $input['amount'],
 );

        $data = $this->Common->fLaravelUpdateDate($aData,$token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Experience_config::where('id', $id)->get();
            //更新
            Experience_config::where('id',$id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Experience_config管理','更新',$id,$oOldData,$data,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '更新成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '更新失敗'];
        }
    }

    //刪除
    public function fDelete(Request $request, $id){

        $token = Jwt::verifyToken($request->bearerToken());

        $iExperience_config = Experience_config::where('id',$id)->count();
        if($iExperience_config != 1 ){
            return ['result' => false, 'msg' => 'Experience_config不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData,$token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Experience_config::where('id', $id)->get();
            //更新
            Experience_config::where('id',$id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Experience_config管理','刪除',$id,$oOldData,$data,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '刪除成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '刪除失敗'];
        }
    }

    //啟用 /停用
    public function fEnable(Request $request, $id){

        $token = Jwt::verifyToken($request->bearerToken());

        $oExperience_config = Experience_config::where('id',$id)->first();
        if(empty($oExperience_config) ){
            return ['result' => false, 'msg' => 'Experience_config不存在'];
        }

        $aData = [
            'isEnabled' => $oExperience_config->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Experience_config::where('id', $id)->get();
            //更新
            Experience_config::where('id',$id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Experience_config管理','啟用/停用',$id,$oOldData,$data,$token['name']);

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

        $data = Experience_config::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
