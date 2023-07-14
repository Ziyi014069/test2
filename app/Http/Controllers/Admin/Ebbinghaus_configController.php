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
use App\Models\Ebbinghaus_config;


class Ebbinghaus_configController extends Controller
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

    	$sql = Ebbinghaus_config::select('id','stage','score','minute','day','expriedTime','expiredDay','amount','created_at','updated_at','creator','ipOfCreator','lastUpdater','ipOfLastUpdater','isRemoved','removeTime','remover','ipOfRemover','memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if(!empty($input['id']))   $sql->where('id','like','%'.$input['id'].'%');
if(!empty($input['stage']))   $sql->where('stage','like','%'.$input['stage'].'%');
if(!empty($input['score']))   $sql->where('score','like','%'.$input['score'].'%');
if(!empty($input['minute']))   $sql->where('minute','like','%'.$input['minute'].'%');
if(!empty($input['day']))   $sql->where('day','like','%'.$input['day'].'%');
if(!empty($input['expriedTime']))   $sql->where('expriedTime','like','%'.$input['expriedTime'].'%');
if(!empty($input['expiredDay']))   $sql->where('expiredDay','like','%'.$input['expiredDay'].'%');
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
        if(!isset($input['stage']))  return ['result' => false, 'msg' => 'stage不能為空'];
if(!isset($input['score']))  return ['result' => false, 'msg' => 'score不能為空'];
if(!isset($input['minute']))  return ['result' => false, 'msg' => 'minute不能為空'];
if(!isset($input['day']))  return ['result' => false, 'msg' => 'day不能為空'];
if(!isset($input['expriedTime']))  return ['result' => false, 'msg' => 'expriedTime不能為空'];
if(!isset($input['expiredDay']))  return ['result' => false, 'msg' => 'expiredDay不能為空'];
if(!isset($input['amount']))  return ['result' => false, 'msg' => 'amount不能為空'];


        $aData = array(
                                  'stage' => $input['stage'],
 'score' => $input['score'],
 'minute' => $input['minute'],
 'day' => $input['day'],
 'expriedTime' => $input['expriedTime'],
 'expiredDay' => $input['expiredDay'],
 'amount' => $input['amount'],
 );

        $data = $this->Common->fLaravelCreateDate($aData,$token['name']);

        DB::beginTransaction();
        try {
            //新增
            Ebbinghaus_config::create($data);

            //建立log
            $this->AdminLog->fCreate('Ebbinghaus_config管理','新增',null,[],$aData,$token['name']);

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
     * @param $id Ebbinghaus_configID
     *
     * @return {array
     */
    public function fGetID($id,$input){
        $sql = Ebbinghaus_config::where('id', '=', $id);


        //select條件
        if(!empty($input['id']))   $sql->where('id',$input['id']);
if(!empty($input['stage']))   $sql->where('stage',$input['stage']);
if(!empty($input['score']))   $sql->where('score',$input['score']);
if(!empty($input['minute']))   $sql->where('minute',$input['minute']);
if(!empty($input['day']))   $sql->where('day',$input['day']);
if(!empty($input['expriedTime']))   $sql->where('expriedTime',$input['expriedTime']);
if(!empty($input['expiredDay']))   $sql->where('expiredDay',$input['expiredDay']);
if(!empty($input['amount']))   $sql->where('amount',$input['amount']);


        $aEbbinghaus_config = $sql->get();

        if (!empty($aEbbinghaus_config)){
            return ['result' => true, 'msg' => 'success','data' => $aEbbinghaus_config];
        }else{
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  Ebbinghaus_configID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id){
        // $input = $requset->all();
        $input = json_decode($request->getContent(),true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if(!isset($input['stage']))  return ['result' => false, 'msg' => 'stage不能為空'];
if(!isset($input['score']))  return ['result' => false, 'msg' => 'score不能為空'];
if(!isset($input['minute']))  return ['result' => false, 'msg' => 'minute不能為空'];
if(!isset($input['day']))  return ['result' => false, 'msg' => 'day不能為空'];
if(!isset($input['expriedTime']))  return ['result' => false, 'msg' => 'expriedTime不能為空'];
if(!isset($input['expiredDay']))  return ['result' => false, 'msg' => 'expiredDay不能為空'];
if(!isset($input['amount']))  return ['result' => false, 'msg' => 'amount不能為空'];


        //檢查 Ebbinghaus_config 是否存在
        $iEbbinghaus_config = Ebbinghaus_config::where('id',$id)->count();
        if($iEbbinghaus_config != 1 ){
            return ['result' => false, 'msg' => 'Ebbinghaus_config不存在'];
        }

        $aData = array(
                                  'stage' => $input['stage'],
 'score' => $input['score'],
 'minute' => $input['minute'],
 'day' => $input['day'],
 'expriedTime' => $input['expriedTime'],
 'expiredDay' => $input['expiredDay'],
 'amount' => $input['amount'],
 );

        $data = $this->Common->fLaravelUpdateDate($aData,$token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Ebbinghaus_config::where('id', $id)->get();
            //更新
            Ebbinghaus_config::where('id',$id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Ebbinghaus_config管理','更新',$id,$oOldData,$data,$token['name']);

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

        $iEbbinghaus_config = Ebbinghaus_config::where('id',$id)->count();
        if($iEbbinghaus_config != 1 ){
            return ['result' => false, 'msg' => 'Ebbinghaus_config不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData,$token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Ebbinghaus_config::where('id', $id)->get();
            //更新
            Ebbinghaus_config::where('id',$id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Ebbinghaus_config管理','刪除',$id,$oOldData,$data,$token['name']);

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

        $oEbbinghaus_config = Ebbinghaus_config::where('id',$id)->first();
        if(empty($oEbbinghaus_config) ){
            return ['result' => false, 'msg' => 'Ebbinghaus_config不存在'];
        }

        $aData = [
            'isEnabled' => $oEbbinghaus_config->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Ebbinghaus_config::where('id', $id)->get();
            //更新
            Ebbinghaus_config::where('id',$id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Ebbinghaus_config管理','啟用/停用',$id,$oOldData,$data,$token['name']);

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

        $data = Ebbinghaus_config::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
