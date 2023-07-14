<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//excel
use App\Exports\ReportExport;
use App\Imports\ReportImport;
use Excel;
//Laravel
use Illuminate\Support\Facades\Storage;
use DB;
//

class ExcelController extends Controller
{
    public function fImport(Request $request){
        //去除時間限制
        set_time_limit(0);
        //調整memory_limit上限
        ini_set('memory_limit', '512M');

        // $files = $request->file('files');

        // $aExcel = Excel::toArray(new ReportImport, $files);

        // //整理excel資料 匯入第一個sheet
        // if(!empty($aExcel[0])){
        //     $aData = [];
        //     $aKey = $aExcel[0][0];
        //     unset($aExcel[0][0]);
        //     //先把
        //     foreach ($aExcel[0] as $key => $value) {
        //         $aTemp = [];
        //         foreach ($aKey as $key2 => $value2) {
        //             $aTemp[$value2] = $value[$key2];
        //         }
        //         $aData[] = $aTemp;
        //     }
        //     return $aData;
        // }else{
        //     return ['result'=>false,'msg'=>'上傳檔案不符合格式'];
        // }
    }
}
