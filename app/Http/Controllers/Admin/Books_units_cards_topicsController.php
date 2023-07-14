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
use App\Models\Books_units_cards_topics;


class Books_units_cards_topicsController extends Controller
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

    	$sql = Books_units_cards_topics::select('topicsId','cardsId','topic','topicFileType','pathOfTopicVideo','pathOfTopicImage','pathOfTopicSound','topicCategory','optionA','pathOfOptionA','optionB','pathOfOptionB','optionC','pathOfOptionC','optionD','pathOfOptionD','answerOptions','answer','isEnabled','created_at','updated_at','creator','ipOfCreator','lastUpdater','ipOfLastUpdater','isRemoved','removeTime','remover','ipOfRemover','memo');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if(!empty($input['topicsId']))   $sql->where('topicsId','like','%'.$input['topicsId'].'%');
if(!empty($input['cardsId']))   $sql->where('cardsId','like','%'.$input['cardsId'].'%');
if(!empty($input['topic']))   $sql->where('topic','like','%'.$input['topic'].'%');
if(!empty($input['topicFileType']))   $sql->where('topicFileType','like','%'.$input['topicFileType'].'%');
if(!empty($input['pathOfTopicVideo']))   $sql->where('pathOfTopicVideo','like','%'.$input['pathOfTopicVideo'].'%');
if(!empty($input['pathOfTopicImage']))   $sql->where('pathOfTopicImage','like','%'.$input['pathOfTopicImage'].'%');
if(!empty($input['pathOfTopicSound']))   $sql->where('pathOfTopicSound','like','%'.$input['pathOfTopicSound'].'%');
if(!empty($input['topicCategory']))   $sql->where('topicCategory','like','%'.$input['topicCategory'].'%');
if(!empty($input['optionA']))   $sql->where('optionA','like','%'.$input['optionA'].'%');
if(!empty($input['pathOfOptionA']))   $sql->where('pathOfOptionA','like','%'.$input['pathOfOptionA'].'%');
if(!empty($input['optionB']))   $sql->where('optionB','like','%'.$input['optionB'].'%');
if(!empty($input['pathOfOptionB']))   $sql->where('pathOfOptionB','like','%'.$input['pathOfOptionB'].'%');
if(!empty($input['optionC']))   $sql->where('optionC','like','%'.$input['optionC'].'%');
if(!empty($input['pathOfOptionC']))   $sql->where('pathOfOptionC','like','%'.$input['pathOfOptionC'].'%');
if(!empty($input['optionD']))   $sql->where('optionD','like','%'.$input['optionD'].'%');
if(!empty($input['pathOfOptionD']))   $sql->where('pathOfOptionD','like','%'.$input['pathOfOptionD'].'%');
if(!empty($input['answerOptions']))   $sql->where('answerOptions','like','%'.$input['answerOptions'].'%');
if(!empty($input['answer']))   $sql->where('answer','like','%'.$input['answer'].'%');
if(!empty($input['isEnabled']))   $sql->where('isEnabled','like','%'.$input['isEnabled'].'%');


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1 ; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total = $sql->count();
        $data = $sql->orderBy('topicsId','desc')->skip($first)->take($pageSize)->get();

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
        if(!isset($input['cardsId']))  return ['result' => false, 'msg' => 'cardsId不能為空'];
if(!isset($input['topic']))  return ['result' => false, 'msg' => 'topic不能為空'];
if(!isset($input['topicFileType']))  return ['result' => false, 'msg' => 'topicFileType不能為空'];
if(!isset($input['pathOfTopicVideo']))  return ['result' => false, 'msg' => 'pathOfTopicVideo不能為空'];
if(!isset($input['pathOfTopicImage']))  return ['result' => false, 'msg' => 'pathOfTopicImage不能為空'];
if(!isset($input['pathOfTopicSound']))  return ['result' => false, 'msg' => 'pathOfTopicSound不能為空'];
if(!isset($input['topicCategory']))  return ['result' => false, 'msg' => 'topicCategory不能為空'];
if(!isset($input['optionA']))  return ['result' => false, 'msg' => 'optionA不能為空'];
if(!isset($input['pathOfOptionA']))  return ['result' => false, 'msg' => 'pathOfOptionA不能為空'];
if(!isset($input['optionB']))  return ['result' => false, 'msg' => 'optionB不能為空'];
if(!isset($input['pathOfOptionB']))  return ['result' => false, 'msg' => 'pathOfOptionB不能為空'];
if(!isset($input['optionC']))  return ['result' => false, 'msg' => 'optionC不能為空'];
if(!isset($input['pathOfOptionC']))  return ['result' => false, 'msg' => 'pathOfOptionC不能為空'];
if(!isset($input['optionD']))  return ['result' => false, 'msg' => 'optionD不能為空'];
if(!isset($input['pathOfOptionD']))  return ['result' => false, 'msg' => 'pathOfOptionD不能為空'];
if(!isset($input['answerOptions']))  return ['result' => false, 'msg' => 'answerOptions不能為空'];
if(!isset($input['answer']))  return ['result' => false, 'msg' => 'answer不能為空'];
if(!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        $aData = array(
                                  'cardsId' => $input['cardsId'],
 'topic' => $input['topic'],
 'topicFileType' => $input['topicFileType'],
 'pathOfTopicVideo' => $input['pathOfTopicVideo'],
 'pathOfTopicImage' => $input['pathOfTopicImage'],
 'pathOfTopicSound' => $input['pathOfTopicSound'],
 'topicCategory' => $input['topicCategory'],
 'optionA' => $input['optionA'],
 'pathOfOptionA' => $input['pathOfOptionA'],
 'optionB' => $input['optionB'],
 'pathOfOptionB' => $input['pathOfOptionB'],
 'optionC' => $input['optionC'],
 'pathOfOptionC' => $input['pathOfOptionC'],
 'optionD' => $input['optionD'],
 'pathOfOptionD' => $input['pathOfOptionD'],
 'answerOptions' => $input['answerOptions'],
 'answer' => $input['answer'],
 'isEnabled' => $input['isEnabled'],
 );

        $data = $this->Common->fLaravelCreateDate($aData,$token['name']);

        DB::beginTransaction();
        try {
            //新增
            Books_units_cards_topics::create($data);

            //建立log
            $this->AdminLog->fCreate('Books_units_cards_topics管理','新增',null,[],$aData,$token['name']);

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
     * @param $topicsId Books_units_cards_topicsID
     *
     * @return {array
     */
    public function fGetID($id,$input){
        $sql = Books_units_cards_topics::where('topicsId', '=', $id);


        //select條件
        if(!empty($input['topicsId']))   $sql->where('topicsId',$input['topicsId']);
if(!empty($input['cardsId']))   $sql->where('cardsId',$input['cardsId']);
if(!empty($input['topic']))   $sql->where('topic',$input['topic']);
if(!empty($input['topicFileType']))   $sql->where('topicFileType',$input['topicFileType']);
if(!empty($input['pathOfTopicVideo']))   $sql->where('pathOfTopicVideo',$input['pathOfTopicVideo']);
if(!empty($input['pathOfTopicImage']))   $sql->where('pathOfTopicImage',$input['pathOfTopicImage']);
if(!empty($input['pathOfTopicSound']))   $sql->where('pathOfTopicSound',$input['pathOfTopicSound']);
if(!empty($input['topicCategory']))   $sql->where('topicCategory',$input['topicCategory']);
if(!empty($input['optionA']))   $sql->where('optionA',$input['optionA']);
if(!empty($input['pathOfOptionA']))   $sql->where('pathOfOptionA',$input['pathOfOptionA']);
if(!empty($input['optionB']))   $sql->where('optionB',$input['optionB']);
if(!empty($input['pathOfOptionB']))   $sql->where('pathOfOptionB',$input['pathOfOptionB']);
if(!empty($input['optionC']))   $sql->where('optionC',$input['optionC']);
if(!empty($input['pathOfOptionC']))   $sql->where('pathOfOptionC',$input['pathOfOptionC']);
if(!empty($input['optionD']))   $sql->where('optionD',$input['optionD']);
if(!empty($input['pathOfOptionD']))   $sql->where('pathOfOptionD',$input['pathOfOptionD']);
if(!empty($input['answerOptions']))   $sql->where('answerOptions',$input['answerOptions']);
if(!empty($input['answer']))   $sql->where('answer',$input['answer']);
if(!empty($input['isEnabled']))   $sql->where('isEnabled',$input['isEnabled']);


        $aBooks_units_cards_topics = $sql->get();

        if (!empty($aBooks_units_cards_topics)){
            return ['result' => true, 'msg' => 'success','data' => $aBooks_units_cards_topics];
        }else{
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  Books_units_cards_topicsID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id){
        // $input = $requset->all();
        $input = json_decode($request->getContent(),true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if(!isset($input['cardsId']))  return ['result' => false, 'msg' => 'cardsId不能為空'];
if(!isset($input['topic']))  return ['result' => false, 'msg' => 'topic不能為空'];
if(!isset($input['topicFileType']))  return ['result' => false, 'msg' => 'topicFileType不能為空'];
if(!isset($input['pathOfTopicVideo']))  return ['result' => false, 'msg' => 'pathOfTopicVideo不能為空'];
if(!isset($input['pathOfTopicImage']))  return ['result' => false, 'msg' => 'pathOfTopicImage不能為空'];
if(!isset($input['pathOfTopicSound']))  return ['result' => false, 'msg' => 'pathOfTopicSound不能為空'];
if(!isset($input['topicCategory']))  return ['result' => false, 'msg' => 'topicCategory不能為空'];
if(!isset($input['optionA']))  return ['result' => false, 'msg' => 'optionA不能為空'];
if(!isset($input['pathOfOptionA']))  return ['result' => false, 'msg' => 'pathOfOptionA不能為空'];
if(!isset($input['optionB']))  return ['result' => false, 'msg' => 'optionB不能為空'];
if(!isset($input['pathOfOptionB']))  return ['result' => false, 'msg' => 'pathOfOptionB不能為空'];
if(!isset($input['optionC']))  return ['result' => false, 'msg' => 'optionC不能為空'];
if(!isset($input['pathOfOptionC']))  return ['result' => false, 'msg' => 'pathOfOptionC不能為空'];
if(!isset($input['optionD']))  return ['result' => false, 'msg' => 'optionD不能為空'];
if(!isset($input['pathOfOptionD']))  return ['result' => false, 'msg' => 'pathOfOptionD不能為空'];
if(!isset($input['answerOptions']))  return ['result' => false, 'msg' => 'answerOptions不能為空'];
if(!isset($input['answer']))  return ['result' => false, 'msg' => 'answer不能為空'];
if(!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];


        //檢查 Books_units_cards_topics 是否存在
        $checIinput = [];
        $aBooks_units_cards_topics = $this->fGetID($id,$checIinput);
        if(!$aBooks_units_cards_topics['result']){
            return ['result' => false, 'msg' => '資料不存在'];
        }

        $aData = array(
                                  'cardsId' => $input['cardsId'],
 'topic' => $input['topic'],
 'topicFileType' => $input['topicFileType'],
 'pathOfTopicVideo' => $input['pathOfTopicVideo'],
 'pathOfTopicImage' => $input['pathOfTopicImage'],
 'pathOfTopicSound' => $input['pathOfTopicSound'],
 'topicCategory' => $input['topicCategory'],
 'optionA' => $input['optionA'],
 'pathOfOptionA' => $input['pathOfOptionA'],
 'optionB' => $input['optionB'],
 'pathOfOptionB' => $input['pathOfOptionB'],
 'optionC' => $input['optionC'],
 'pathOfOptionC' => $input['pathOfOptionC'],
 'optionD' => $input['optionD'],
 'pathOfOptionD' => $input['pathOfOptionD'],
 'answerOptions' => $input['answerOptions'],
 'answer' => $input['answer'],
 'isEnabled' => $input['isEnabled'],
 );

        $data = $this->Common->fLaravelUpdateDate($aData,$token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Books_units_cards_topics::where('topicsId', $id)->get();
            //更新
            Books_units_cards_topics::where('topicsId',$id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books_units_cards_topics管理','更新',$id,$oOldData[0],$data,$token['name']);

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

        $iBooks_units_cards_topics = Books_units_cards_topics::where('topicsId',$id)->count();
        if($iBooks_units_cards_topics != 1 ){
            return ['result' => false, 'msg' => 'Books_units_cards_topics不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData,$token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Books_units_cards_topics::where('topicsId', $id)->get();
            //更新
            Books_units_cards_topics::where('topicsId',$id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books_units_cards_topics管理','刪除',$id,$oOldData[0],$data,$token['name']);

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

        $oBooks_units_cards_topics = Books_units_cards_topics::where('topicsId',$id)->first();
        if(empty($oBooks_units_cards_topics) ){
            return ['result' => false, 'msg' => 'Books_units_cards_topics不存在'];
        }

        $aData = [
            'isEnabled' => $oBooks_units_cards_topics->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Books_units_cards_topics::where('topicsId', $id)->get();
            //更新
            Books_units_cards_topics::where('topicsId',$id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books_units_cards_topics管理','啟用/停用',$id,$oOldData[0],$data,$token['name']);

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

        $data = Books_units_cards_topics::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
