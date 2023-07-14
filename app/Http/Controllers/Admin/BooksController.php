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
use App\Models\Books;
use App\Models\Attrs;
use App\Models\Books_attrs_mapping;
use App\Models\Teacher;
use App\Models\Books_teacher_mapping;


class BooksController extends Controller
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

        //books attrs
        $sBooksAttrs = Books_attrs_mapping::select('Books_attrs_mapping.*',
                    DB::raw('GROUP_CONCAT(Books_attrs_mapping.attrsId) as attrsIds'),
                    DB::raw('GROUP_CONCAT(attrs.attrsName) as attrsNames'))
                                ->join('attrs','attrs.attrsId','Books_attrs_mapping.attrsId')
                                ->groupBy('Books_attrs_mapping.booksId')
                                ->toSql();
        //books teacher
        $sBooksTeacher = Books_teacher_mapping::select('Books_teacher_mapping.*',
                    DB::raw('GROUP_CONCAT(Books_teacher_mapping.teacherId) as teacherIds'),
                    DB::raw('GROUP_CONCAT(teacher.teacherName) as teacherNames'))
                                ->join('teacher','teacher.teacherId','Books_teacher_mapping.teacherId')
                                ->groupBy('Books_teacher_mapping.booksId')
                                ->toSql();

        $sql = Books::select('Books.*','sBooksAttrs.attrsIds','sBooksAttrs.attrsNames','sBooksTeacher.teacherIds','sBooksTeacher.teacherNames')
                ->leftjoin(DB::raw('('.$sBooksAttrs.') AS sBooksAttrs'),'sBooksAttrs.booksId','=','Books.booksId')
                ->leftjoin(DB::raw('('.$sBooksTeacher.') AS sBooksTeacher'),'sBooksTeacher.teacherId','=','Books.booksId');


        //時間區間
        //if (!empty($input['startTime'])) return $sql->where('createTime', '>=', $input['startTime']);
        //if (!empty($input['endTime'])) return  $sql->where('createTime', '<=', $input['endTime']);

        if (!empty($input['booksId']))   $sql->where('Books.booksId', 'like', '%' . $input['booksId'] . '%');
        if (!empty($input['booksName']))   $sql->where('Books.booksName', 'like', '%' . $input['booksName'] . '%');
        if (!empty($input['booksCode']))   $sql->where('Books.booksCode', 'like', '%' . $input['booksCode'] . '%');
        if (!empty($input['isEnabled']))   $sql->where('Books.isEnabled', 'like', '%' . $input['isEnabled'] . '%');
        if (!empty($input['isPublished']))   $sql->where('Books.isPublished', 'like', '%' . $input['isPublished'] . '%');
        if (!empty($input['category']))   $sql->where('Books.category', 'like', '%' . $input['category'] . '%');
        if (!empty($input['teacherId']))   $sql->where('Books.teacherId', $input['isPublished']);


        //分頁
        $page = isset($input['page']) ? $input['page'] : 1; //哪一頁
        $pageSize = isset($input['limit']) ? $input['limit'] : 10; //每頁所要顯示筆數
        $first = $pageSize * ($page - 1); //初始資料索引

        $total =0;
        $data = $sql->orderBy('Books.booksId', 'desc')->skip($first)->take($pageSize)->get();

        foreach ($data as $key => $value) {
            $value['img'] = asset('').$value['coverImage'];
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
        if (!isset($input['booksName']))  return ['result' => false, 'msg' => 'booksName不能為空'];
        if (!isset($input['booksCode']))  return ['result' => false, 'msg' => 'booksCode不能為空'];
        if (!isset($input['coverImage']))  return ['result' => false, 'msg' => 'coverImage不能為空'];
        if (!isset($input['description']))  return ['result' => false, 'msg' => 'description不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];
        if (!isset($input['isPublished']))  return ['result' => false, 'msg' => 'isPublished不能為空'];
        if (!isset($input['category']))  return ['result' => false, 'msg' => 'category不能為空'];
        if (!isset($input['attrs']))  return ['result' => false, 'msg' => 'attrs不能為空'];
        if (!isset($input['teacherId']))  return ['result' => false, 'msg' => 'teacherId不能為空'];

        //booksCode不能重複
        $count = Books::select('booksCode')->where('booksCode', $input['booksCode'])->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'booksCode不能重複'];
        }

        //教師檢查
        $oTeacher = Teacher::select('teacherId')->where('teacherId', $input['teacherId'])->first();
        if (empty($oTeacher)) {
            return ['result' => false, 'msg' => 'teacher不存在'];
        }

        //屬性 檢查
        $aAttrs = Attrs::select('AttrsId')->get();
        $authority = [];
        foreach ($aAttrs as $key => $value) {
            $authority[] = $value['AttrsId'];
        }
        $aAttrs = explode(',',$input['attrs']);
        if(count($aAttrs) > 3) return ['result' => false, 'msg' => 'attrs不能超過3個'];
        foreach ($aAttrs as $key => $value) {
            if(!in_array($value, $authority)){
                return ['result' => false, 'msg' => '屬性資料錯誤'];
            }
        }

        $aData = array(
            'booksName' => $input['booksName'],
            'booksCode' => $input['booksCode'],
            'coverImage' => $input['coverImage'],
            'description' => $input['description'],
            'isEnabled' => $input['isEnabled'],
            'isPublished' => $input['isPublished'],
            'category' => $input['category'],
            'teacherId' => $token['teacherId'],
        );

        $data = $this->Common->fLaravelCreateDate($aData, $token['name']);

        DB::beginTransaction();
        try {
            //新增
            $oCreateBooks = Books::create($data);

            //增加書籍屬性
            foreach ($aAttrs as $key => $value) {
                $aMapping = [
                    'booksId' => $oCreateBooks['booksId'],
                    'attrsId' => $value
                ];
                Books_attrs_mapping::create($aMapping);
            }

            //增加書籍教師
            $aMapping = [
                'booksId' => $oCreateBooks['booksId'],
                'teacherId' => $input['teacherId']
            ];
            Books_teacher_mapping::create($aMapping);

            //建立log
            $this->AdminLog->fCreate('Books管理', '新增', null, [], $aData, $token['name']);

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
     * @param $booksId BooksID
     *
     * @return {array
     */
    public function fGetID($id, $input)
    {
        $sql = Books::where('booksId', '=', $id);


        //select條件
        if (!empty($input['booksId']))   $sql->where('booksId', $input['booksId']);
        if (!empty($input['booksName']))   $sql->where('booksName', $input['booksName']);
        if (!empty($input['booksCode']))   $sql->where('booksCode', $input['booksCode']);
        if (!empty($input['coverImage']))   $sql->where('coverImage', $input['coverImage']);
        if (!empty($input['description']))   $sql->where('description', $input['description']);
        if (!empty($input['isEnabled']))   $sql->where('isEnabled', $input['isEnabled']);
        if (!empty($input['isPublished']))   $sql->where('isPublished', $input['isPublished']);
        if (!empty($input['category']))   $sql->where('category', $input['category']);


        $aBooks = $sql->get();

        if (!empty($aBooks)) {
            return ['result' => true, 'msg' => 'success', 'data' => $aBooks];
        } else {
            return  ['result' => false, 'msg' => '查無資料'];
        }
    }

    /**
     * 更新
     *
     * @param $id  BooksID
     *
     * @return {array
     */
    public function fUpdate(Request $request, $id)
    {
        // $input = $requset->all();
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //檢查
        if (!isset($input['booksName']))  return ['result' => false, 'msg' => 'booksName不能為空'];
        if (!isset($input['booksCode']))  return ['result' => false, 'msg' => 'booksCode不能為空'];
        if (!isset($input['coverImage']))  return ['result' => false, 'msg' => 'coverImage不能為空'];
        if (!isset($input['description']))  return ['result' => false, 'msg' => 'description不能為空'];
        if (!isset($input['isEnabled']))  return ['result' => false, 'msg' => 'isEnabled不能為空'];
        if (!isset($input['isPublished']))  return ['result' => false, 'msg' => 'isPublished不能為空'];
        if (!isset($input['category']))  return ['result' => false, 'msg' => 'category不能為空'];
        if (!isset($input['attrs']))  return ['result' => false, 'msg' => 'attrs不能為空'];
        if (!isset($input['teacherId']))  return ['result' => false, 'msg' => 'teacherId不能為空'];


        //檢查 Books 是否存在
        $checIinput = [];
        $aBooks = $this->fGetID($id, $checIinput);
        if (!$aBooks['result']) {
            return ['result' => false, 'msg' => '資料不存在'];
        }

        //booksCode不能重複
        $count = Books::select('booksCode')->where('booksCode', $input['booksCode'])->where('booksId', '!=', $id)->count();
        if ($count > 0) {
            return ['result' => false, 'msg' => 'booksCode不能重複'];
        }

        //教師檢查
        $oTeacher = Teacher::select('teacherId')->where('teacherId', $input['teacherId'])->first();
        if (empty($oTeacher)) {
            return ['result' => false, 'msg' => 'teacher不存在'];
        }

        //屬性 檢查
        $aAttrs = Attrs::select('AttrsId')->get();
        $authority = [];
        foreach ($aAttrs as $key => $value) {
            $authority[] = $value['AttrsId'];
        }
        $aAttrs = explode(',',$input['attrs']);
        if(count($aAttrs) > 3) return ['result' => false, 'msg' => 'attrs不能超過3個'];
        foreach ($aAttrs as $key => $value) {
            if(!in_array($value, $authority)){
                return ['result' => false, 'msg' => '屬性資料錯誤'];
            }
        }

        $aData = array(
            'booksName' => $input['booksName'],
            'booksCode' => $input['booksCode'],
            'coverImage' => $input['coverImage'],
            'description' => $input['description'],
            'isEnabled' => $input['isEnabled'],
            'isPublished' => $input['isPublished'],
            'category' => $input['category'],
            'teacherId' => $input['teacherId'],
        );

        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {


            //取得更新前資料
            $oOldData = Books::where('booksId', $id)->get();
            //更新
            Books::where('booksId', $id)->update($data);

            //增加書籍屬性
            Books_attrs_mapping::where('booksId', $id)->delete();
            foreach ($aAttrs as $key => $value) {
                $aMapping = [
                    'booksId' => $id,
                    'attrsId' => $value
                ];
                Books_attrs_mapping::create($aMapping);
            }

            //增加書籍教師
            Books_teacher_mapping::where('booksId', $id)->delete();
            $aMapping = [
                'booksId' => $id,
                'teacherId' => $input['teacherId']
            ];
            Books_teacher_mapping::create($aMapping);

            //建立log
            $this->AdminLog->fCreate('Books管理', '更新', $id, $oOldData[0], $data, $token['name']);

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

        $iBooks = Books::where('booksId', $id)->count();
        if ($iBooks != 1) {
            return ['result' => false, 'msg' => 'Books不存在'];
        }

        $aData = [];
        $data = $this->Common->fLaravelDeleteDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Books::where('booksId', $id)->get();
            //更新
            Books::where('booksId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books管理', '刪除', $id, $oOldData[0], $data, $token['name']);

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

        $oBooks = Books::where('booksId', $id)->first();
        if (empty($oBooks)) {
            return ['result' => false, 'msg' => 'Books不存在'];
        }

        $aData = [
            'isEnabled' => $oBooks->isEnabled ? 0 : 1
        ];
        $data = $this->Common->fLaravelUpdateDate($aData, $token['name']);

        DB::beginTransaction();
        try {

            //取得更新前資料
            $oOldData = Books::where('booksId', $id)->get();
            //更新
            Books::where('booksId', $id)->update($data);
            //建立log
            $this->AdminLog->fCreate('Books管理', '啟用/停用', $id, $oOldData[0], $data, $token['name']);

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

        $data = Books::select('*')->where('isEnabled', 1)->where('isRemoved', 0)->get();

        return ['result' => true, 'msg' => 'success', 'data' => $data];
    }
}
