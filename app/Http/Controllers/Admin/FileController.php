<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//Laravel
use DB;
//package
use App\Packages\Common;
use App\Packages\File;
use App\Packages\Jwt;

class FileController extends Controller
{
    private $Common;
    private $File;

    public function __construct()
    {
        // parent::__construct();
        $this->Common = new Common;
        $this->File = new File;
    }

    //檔案上傳
    public function fUpload(Request $request){
        $input = $request->all();
        // $token = Jwt::verifyToken($request->bearerToken());

        if ($request->file('file')->isValid()) {
            $file = $request->file('file');
            // $fileSize = $file->getSize();
            // $extension = $file->getClientOriginalExtension();
            // $mimeType = $file->getMimeType();

            //path
            if(empty($input['path'])) return ['result'=>false , 'msg'=>'path不存在'];

            //上傳檔案
            $result = $this->File->uploadFile($file,$input['path'],2,['jpg','png','jpeg']);
            if(!$result['result']){
                return $result;
            }else{

                //file data
                $aData = [
                    'url'=>  $result['url']
                ];
                return $result;
            }
        }else{
            return ['result'=>false , 'msg'=>'檔案不存在'];
        }
    }
}
