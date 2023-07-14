<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//package
use App\Packages\Common;
use App\Packages\Jwt;
use App\Packages\AdminLog;

//Model
use App\Models\MenuFunction;

//Laravel
use DB;
use Exception;
use Log;

class MenuFunctionController extends Controller
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

        //取得第一層選單資料
        $oFirstMenuFunctionData = MenuFunction::select('menuFunctionId', 'menuFunctionName', 'pathOfMenuFunction', 'icon', 'menuFunctionAlias', 'menuFunctionOfParentId',
                                                'isEnabled','orderOfMenuFunction','isChildren','isCategory','isOperation','isFunction','memo', 'isShowLink', 'isHiddenTag')
                                                ->where('isCategory', 1)->get();
        //取得第二層選單資料
        $oSecondMenuFunctionData = MenuFunction::select('menuFunctionId','menuFunctionName','pathOfMenuFunction','icon','menuFunctionAlias','menuFunctionOfParentId',
                                                'isEnabled','orderOfMenuFunction','isChildren','isCategory','isOperation','isFunction','memo', 'isShowLink', 'isHiddenTag')
                                                ->where('isOperation', 1)->get();
        //取得第三層選單資料
        $oThirdMenuFunctionData = MenuFunction::select('menuFunctionId','menuFunctionName','pathOfMenuFunction','icon','menuFunctionAlias','menuFunctionOfParentId',
                                                'isEnabled','orderOfMenuFunction','isChildren','isCategory','isOperation','isFunction','memo', 'isShowLink', 'isHiddenTag')
                                                ->where('isFunction', 1)->get();
        //回傳的選單資料
        $aData = array();

        //第一層
        foreach ($oFirstMenuFunctionData as $key => $value) {
            $aArray = [
                'menuFunctionId' => $value['menuFunctionId'],
                'menuFunctionName' => $value['menuFunctionName'],
                'menuFunctionAlias' => $value['menuFunctionAlias'],
                'pathOfMenuFunction' => $value['pathOfMenuFunction'],
                'icon' => $value['icon'],
                'isEnabled' => $value['isEnabled'],
                'orderOfMenuFunction' => $value['orderOfMenuFunction'],
                'menuFunctionOfParentId' => $value['menuFunctionOfParentId'],
                'isChildren' => $value['isChildren'],
                'isCategory' => $value['isCategory'],
                'isOperation' => $value['isOperation'],
                'isFunction' => $value['isFunction'],
                'isShowLink' => $value['isShowLink'],
                'isHiddenTag' => $value['isHiddenTag'],
                'memo' => $value['memo']
            ];
            array_push($aData, $aArray);
        }

        //第二層
        $aChildren = array();
        foreach ($oSecondMenuFunctionData as $key => $value) {
            $aArray = [
                'menuFunctionId' => $value['menuFunctionId'],
                'menuFunctionName' => $value['menuFunctionName'],
                'menuFunctionAlias' => $value['menuFunctionAlias'],
                'pathOfMenuFunction' => $value['pathOfMenuFunction'],
                'icon' => $value['icon'],
                'isEnabled' => $value['isEnabled'],
                'orderOfMenuFunction' => $value['orderOfMenuFunction'],
                'menuFunctionOfParentId' => $value['menuFunctionOfParentId'],
                'isChildren' => $value['isChildren'],
                'isCategory' => $value['isCategory'],
                'isOperation' => $value['isOperation'],
                'isFunction' => $value['isFunction'],
                'isShowLink' => $value['isShowLink'],
                'isHiddenTag' => $value['isHiddenTag'],
                'memo' => $value['memo']

            ];
            if (!isset($aChildren[$value['menuFunctionOfParentId']])) {
                $aChildren[$value['menuFunctionOfParentId']] = [];
            }
            $aChildren[$value['menuFunctionOfParentId']][] = $aArray;
        }

        foreach ($aData as $key => $value) {
            if (!isset($aData[$key]['children'])) {
                if (!empty($aChildren[$value['menuFunctionId']])) {
                    $aData[$key]['children'] = $aChildren[$value['menuFunctionId']];
                }
            }
        }

        //第三層
        $aChildren = array();
        if (!empty($oThirdMenuFunctionData)) {
            foreach ($oThirdMenuFunctionData as $key => $value) {
                $aArray = [
                    'menuFunctionId' => $value['menuFunctionId'],
                    'menuFunctionName' => $value['menuFunctionName'],
                    'menuFunctionAlias' => $value['menuFunctionAlias'],
                    'pathOfMenuFunction' => $value['pathOfMenuFunction'],
                    'icon' => $value['icon'],
                    'isEnabled' => $value['isEnabled'],
                    'orderOfMenuFunction' => $value['orderOfMenuFunction'],
                    'menuFunctionOfParentId' => $value['menuFunctionOfParentId'],
                    'isChildren' => $value['isChildren'],
                    'isCategory' => $value['isCategory'],
                    'isOperation' => $value['isOperation'],
                    'isFunction' => $value['isFunction'],
                    'isShowLink' => $value['isShowLink'],
                    'isHiddenTag' => $value['isHiddenTag'],
                    'memo' => $value['memo']
                ];
                if (!isset($aChildren[$value['menuFunctionOfParentId']])) {
                    $aChildren[$value['menuFunctionOfParentId']] = [];
                }
                $aChildren[$value['menuFunctionOfParentId']][] = $aArray;
            }
            foreach ($aData as $key => $value) {
                if (!empty($value['children'])) {
                    foreach ($value['children'] as $key2 => $value2) {
                        if (!isset($aData[$key]['children'][$key2]['children'])) {
                            if (isset($aChildren[$value2['menuFunctionId']])) {

                                $aData[$key]['children'][$key2]['children'] = $aChildren[$value2['menuFunctionId']];
                            }
                        }
                    }
                }
            }
        }


        return $aData;
    }


    //新增
    public function fAdd(Request $request)
    {
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //資料檢查
        if(empty($input['menuFunctionName'])){
            return ['result' => false, 'msg' => '選單名稱不能為空'];
        }else if(empty($input['menuFunctionAlias'])){
            return ['result' => false, 'msg' => '選單別名不能為空'];
        } else if (empty($input['pathOfMenuFunction'])) {
            return ['result' => false, 'msg' => '選單路徑不能為空'];
        }else if ($input['isCategory'] == 1 && ($input['isOperation'] != 0 || $input['isFunction'] != 0)) {
            return ['result' => false, 'msg' => '大類選單格式錯誤'];
        } else if ($input['isOperation'] == 1 && ($input['isCategory'] != 0 || $input['isFunction'] != 0)) {
            return ['result' => false, 'msg' => '頁面選單格式錯誤'];
        } else if ($input['isFunction'] == 1 && ($input['isOperation'] != 0 || $input['isCategory'] != 0)) {
            return ['result' => false, 'msg' => '功能選單格式錯誤'];
        } else if (!in_array($input['isEnabled'], array('0', 1))) {
            return ['result' => false, 'msg' => '狀態格式錯誤'];
        } else if (!in_array($input['isShowLink'], array('0', 1))) {
            return ['result' => false, 'msg' => 'isShowLink格式錯誤'];
        } else if (!in_array($input['isHiddenTag'], array('0', 1))) {
            return ['result' => false, 'msg' => 'isHiddenTag格式錯誤'];
        }

        //別名不能重複
        $iAlias = MenuFunction::select('menuFunctionAlias')->where('menuFunctionAlias', $input['menuFunctionAlias'])->count();
        if ($iAlias > 0) {
            return ['result' => false, 'msg' => '選單別名不能重複'];
        }
        //是否有父層選單
        if (isset($input['menuFunctionOfParentId']) && $input['isCategory'] == 0) {
            $iParent = MenuFunction::select('menuFunctionId')->where('menuFunctionId', $input['menuFunctionOfParentId'])->count();
            if ($iParent < 1) {
                return ['result' => false, 'msg' => '父層選單不存在'];
            }
        } else {
            $input['menuFunctionOfParentId'] = 0;
        }

        $aData = [
            'menuFunctionName' => $input['menuFunctionName'],
            'menuFunctionAlias' => $input['menuFunctionAlias'],
            'pathOfMenuFunction' => $input['pathOfMenuFunction'],
            'menuFunctionOfParentId' => $input['menuFunctionOfParentId'],
            'isCategory' => $input['isCategory'],
            'isOperation' => $input['isOperation'],
            'isFunction' => $input['isFunction'],
            'orderOfMenuFunction' => empty($input['orderOfMenuFunction']) ? 1 : $input['orderOfMenuFunction'],
            'icon' => empty($input['icon']) ? 'setting' : $input['icon'],
            'isEnabled' => $input['isEnabled'],
            'isShowLink' => $input['isShowLink'],
            'isHiddenTag' => $input['isHiddenTag'],
            'memo' => $input['memo']
        ];
        $aData = $this->Common->fLaravelCreateDate($aData, $token['name']);


        DB::beginTransaction();
        try {
            MenuFunction::create($aData);

            $this->AdminLog->fCreate('選單管理','新增',null,[],$aData,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '新增成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '新增失敗'];
        }
    }

    //更新
    public function fUpdate(Request $request,$id)
    {
        $input = json_decode($request->getContent(), true);
        $token = Jwt::verifyToken($request->bearerToken());

        //資料檢查
        if (empty($input['menuFunctionName'])) {
            return ['result' => false, 'msg' => '選單名稱不能為空'];
        } else if (empty($input['menuFunctionAlias'])) {
            return ['result' => false, 'msg' => '選單別名不能為空'];
        } else if (empty($input['pathOfMenuFunction'])) {
            return ['result' => false, 'msg' => '選單路徑不能為空'];
        } else if ($input['isCategory'] == 1 && ($input['isOperation'] != 0 || $input['isFunction'] != 0)) {
            return ['result' => false, 'msg' => '大類選單格式錯誤'];
        } else if ($input['isOperation'] == 1 && ($input['isCategory'] != 0 || $input['isFunction'] != 0)) {
            return ['result' => false, 'msg' => '頁面選單格式錯誤'];
        } else if ($input['isFunction'] == 1 && ($input['isOperation'] != 0 || $input['isCategory'] != 0)) {
            return ['result' => false, 'msg' => '功能選單格式錯誤'];
        } else if (!in_array($input['isEnabled'], array('0', 1))) {
            return ['result' => false, 'msg' => '狀態格式錯誤'];
        } else if (!in_array($input['isShowLink'], array('0', 1))) {
            return ['result' => false, 'msg' => 'isShowLink格式錯誤'];
        } else if (!in_array($input['isHiddenTag'], array('0', 1))) {
            return ['result' => false, 'msg' => 'isHiddenTag格式錯誤'];
        }

        //選單存在
        $oMenuFunction = MenuFunction::select('menuFunctionId')->where('menuFunctionId', $id)->first();
        if (empty($oMenuFunction)) {
            return ['result' => false, 'msg' => '選單不存在'];
        }


        //別名不能重複
        $iAlias = MenuFunction::select('menuFunctionAlias')->where('menuFunctionAlias', $input['menuFunctionAlias'])->where('menuFunctionId','!=',$id)->count();
        if ($iAlias > 0) {
            return ['result' => false, 'msg' => '選單別名不能重複'];
        }
        //是否有父層選單
        if (isset($input['menuFunctionOfParentId']) && $input['isCategory'] == 0) {
            if($input['menuFunctionOfParentId'] != 0){
                $iParent = MenuFunction::select('menuFunctionId')->where('menuFunctionId', $input['menuFunctionOfParentId'])->count();
                if ($iParent < 1) {
                    return ['result' => false, 'msg' => '父層選單不存在'];
                }
            }

        } else {
            $input['menuFunctionOfParentId'] = 0;
        }

        // $aKeys = ['menuFunctionName', 'menuFunctionAlias', 'pathOfMenuFunction', 'menuFunctionOfParentId', 'isCategory', 'isOperation',
        //     'isFunction', 'orderOfMenuFunction', 'isEnable', 'memo',];
        // foreach($input as $key => $value){
        //     if(in_array($key,$aKeys)){
        //         $aData[$key] = $value;
        //     }
        // }


        $aData = [
            'menuFunctionName' => $input['menuFunctionName'],
            'menuFunctionAlias' => $input['menuFunctionAlias'],
            'pathOfMenuFunction' => $input['pathOfMenuFunction'],
            'menuFunctionOfParentId' => $input['menuFunctionOfParentId'],
            'isCategory' => $input['isCategory'],
            'isOperation' => $input['isOperation'],
            'isFunction' => $input['isFunction'],
            'orderOfMenuFunction' => $input['orderOfMenuFunction'],
            'icon' => empty($input['icon']) ? 'setting' : $input['icon'],
            'isEnabled' => $input['isEnabled'],
            'isShowLink' => $input['isShowLink'],
            'isHiddenTag' => $input['isHiddenTag'],
            'memo' => $input['memo']
        ];
        $aData = $this->Common->fLaravelUpdateDate($aData, $token['name']);


        DB::beginTransaction();
        try {

            $oMenuFunction = MenuFunction::where('menuFunctionId', $id)->get();
            MenuFunction::where('menuFunctionId', $id)->update($aData);

            $this->AdminLog->fCreate('語言能力管理','更新',$id,$oMenuFunction,$aData,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '更新成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '更新失敗'];
        }
    }

    //啟用 停用
    public function fEnable(Request $request, $id)
    {
        $token = Jwt::verifyToken($request->bearerToken());
        // $input = json_decode($request->getContent(), true);

        //檢查
        $oMenuFunction =  MenuFunction::select('isEnabled')->where('menuFunctionId', $id)->first();
        if (!$oMenuFunction) return ['result' => false, 'msg' => '選單不存在'];


        DB::beginTransaction();
        try {

            $aData = [
                'isEnabled' => $oMenuFunction->isEnabled ? 0 : 1
            ];
            $aData = $this->Common->fLaravelUpdateDate($aData, $token['name']);

            $oMenuFunction = MenuFunction::where('menuFunctionId', $id)->get();
            MenuFunction::where('menuFunctionId', $id)->update($aData);

            $this->AdminLog->fCreate('語言能力管理','啟用停用',$id,$oMenuFunction,$aData,$token['name']);

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

        //檢查
        $oMenuFunction =  MenuFunction::select('menuFunctionId')->where('menuFunctionId', $id)->where('isRemoved', 0)->first();
        if (!$oMenuFunction) return ['result' => false, 'msg' => '選單不存在'];

        DB::beginTransaction();
        try {

            $aData = [];
            $aData = $this->Common->fLaravelDeleteDate($aData, $token['name']);

            $oMenuFunction = MenuFunction::where('menuFunctionId', $id)->get();
            MenuFunction::where('menuFunctionId', $id)->update($aData);

            $this->AdminLog->fCreate('語言能力管理','刪除',$id,$oMenuFunction,$aData,$token['name']);

            DB::commit();
            return ['result' => true, 'msg' => '刪除成功'];
        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
            return ['result' => false, 'msg' => '刪除失敗'];
        }
    }


    //列表
    public function fTree(Request $request)
    {

        //取得第一層選單資料
        $oFirstMenuFunctionData = MenuFunction::select('menuFunctionId', 'menuFunctionName', 'pathOfMenuFunction', 'icon', 'menuFunctionAlias', 'menuFunctionOfParentId',
                                                'isEnabled','orderOfMenuFunction','isChildren','isCategory','isOperation','isFunction','memo')
                                                ->where('isCategory', 1)->where('isEnabled', 1)->where('isRemoved', 0)->get();
        //取得第二層選單資料
        $oSecondMenuFunctionData = MenuFunction::select('menuFunctionId','menuFunctionName','pathOfMenuFunction','icon','menuFunctionAlias','menuFunctionOfParentId',
                                                'isEnabled','orderOfMenuFunction','isChildren','isCategory','isOperation','isFunction','memo')
                                                ->where('isOperation', 1)->where('isEnabled', 1)->where('isRemoved', 0)->get();
        //取得第三層選單資料
        $oThirdMenuFunctionData = MenuFunction::select('menuFunctionId','menuFunctionName','pathOfMenuFunction','icon','menuFunctionAlias','menuFunctionOfParentId',
                                                'isEnabled','orderOfMenuFunction','isChildren','isCategory','isOperation','isFunction','memo')
                                                ->where('isFunction', 1)->where('isEnabled', 1)->where('isRemoved', 0)->get();
        //回傳的選單資料
        $aData = array();

        //第一層
        foreach ($oFirstMenuFunctionData as $key => $value) {
            $aArray = [
                'menuFunctionId' => $value['menuFunctionId'],
                'menuFunctionName' => $value['menuFunctionName'],
                'menuFunctionAlias' => $value['menuFunctionAlias'],
                'pathOfMenuFunction' => $value['pathOfMenuFunction'],
                'icon' => $value['icon'],
                'isEnabled' => $value['isEnabled'],
                'orderOfMenuFunction' => $value['orderOfMenuFunction'],
                'menuFunctionOfParentId' => $value['menuFunctionOfParentId'],
                'isChildren' => $value['isChildren'],
                'isCategory' => $value['isCategory'],
                'isOperation' => $value['isOperation'],
                'isFunction' => $value['isFunction'],
                'memo' => $value['memo']
            ];
            array_push($aData, $aArray);
        }

        //第二層
        $aChildren = array();
        foreach ($oSecondMenuFunctionData as $key => $value) {
            $aArray = [
                'menuFunctionId' => $value['menuFunctionId'],
                'menuFunctionName' => $value['menuFunctionName'],
                'menuFunctionAlias' => $value['menuFunctionAlias'],
                'pathOfMenuFunction' => $value['pathOfMenuFunction'],
                'icon' => $value['icon'],
                'isEnabled' => $value['isEnabled'],
                'orderOfMenuFunction' => $value['orderOfMenuFunction'],
                'menuFunctionOfParentId' => $value['menuFunctionOfParentId'],
                'isChildren' => $value['isChildren'],
                'isCategory' => $value['isCategory'],
                'isOperation' => $value['isOperation'],
                'isFunction' => $value['isFunction'],
                'memo' => $value['memo']

            ];
            if (!isset($aChildren[$value['menuFunctionOfParentId']])) {
                $aChildren[$value['menuFunctionOfParentId']] = [];
            }
            $aChildren[$value['menuFunctionOfParentId']][] = $aArray;
        }

        foreach ($aData as $key => $value) {
            if (!isset($aData[$key]['children'])) {
                if (!empty($aChildren[$value['menuFunctionId']])) {
                    $aData[$key]['children'] = $aChildren[$value['menuFunctionId']];
                }
            }
        }

        //第三層
        $aChildren = array();
        if (!empty($oThirdMenuFunctionData)) {
            foreach ($oThirdMenuFunctionData as $key => $value) {
                $aArray = [
                    'menuFunctionId' => $value['menuFunctionId'],
                    'menuFunctionName' => $value['menuFunctionName'],
                    'menuFunctionAlias' => $value['menuFunctionAlias'],
                    'pathOfMenuFunction' => $value['pathOfMenuFunction'],
                    'icon' => $value['icon'],
                    'isEnabled' => $value['isEnabled'],
                    'orderOfMenuFunction' => $value['orderOfMenuFunction'],
                    'menuFunctionOfParentId' => $value['menuFunctionOfParentId'],
                    'isChildren' => $value['isChildren'],
                    'isCategory' => $value['isCategory'],
                    'isOperation' => $value['isOperation'],
                    'isFunction' => $value['isFunction'],
                    'memo' => $value['memo']
                ];
                if (!isset($aChildren[$value['menuFunctionOfParentId']])) {
                    $aChildren[$value['menuFunctionOfParentId']] = [];
                }
                $aChildren[$value['menuFunctionOfParentId']][] = $aArray;
            }
            foreach ($aData as $key => $value) {
                if (!empty($value['children'])) {
                    foreach ($value['children'] as $key2 => $value2) {
                        if (!isset($aData[$key]['children'][$key2]['children'])) {
                            if (isset($aChildren[$value2['menuFunctionId']])) {

                                $aData[$key]['children'][$key2]['children'] = $aChildren[$value2['menuFunctionId']];
                            }
                        }
                    }
                }
            }
        }


        return $aData;
    }
}
