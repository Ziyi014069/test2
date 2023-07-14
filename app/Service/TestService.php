<?php

namespace App\Service;

//package
use App\Packages\Common;
// use App\Packages\File;
//model

//mail
use Illuminate\Support\Facades\Mail;
use App\Packages\Email\Example;

use File;

class TestService
{

    private $Common;
    private $File;

    public function __construct()
    {
        // parent::__construct();

        $this->Common = new Common;
        $this->File = new File;
    }

    public function check($input,$file)
    {
        //mail 發送
        // Mail::to('ziyi@yeshi.tw')->send(new Example(['content'=>'hello world!!!'], '標題', 'test'));

        //common 基本用法
        // return  $this->Common->checkChineseString($input['name']);

        //file 上傳
        // return  $this->File->uploadFile($file,'test', 4, ['jpg','png']);

        // 基本格式檢查
        // $canNull = array('test');
        // foreach ($input as $key => $value) {
        //     if(empty($value) && !in_array($key,$canNull) ){
        //         return ['result'=>false,'message'=> "{$key} : 不能為空"];
        //     }
        // }

        return true;

    }
}
