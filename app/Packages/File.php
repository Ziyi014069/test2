<?php

namespace App\Packages;

use Illuminate\Support\Facades\File as facadesFile;
use Image;

class File
{
    //上傳檔案
    public function uploadFile($file, $path, $size, $type){

        if(!$this->verifyDeputyName($file,$type)){
            return ['result'=>false , 'msg'=>'檔案類型錯誤'];
        }

        $imageSize = $file->getSize();
        $fileSize = number_format($imageSize / 1048576,2);
        if( $fileSize > $size){
            return ['result'=>false , 'msg'=>'檔案大小不符合規定'];
        }

        //檔案名稱
        list($usec, $sec) = explode(" ", microtime());
        $date = date("YmdHisx",$sec);
        $date = str_replace('x', substr($usec,2,3), $date);
        $fileName  = $date.'.'.$file->getClientOriginalExtension();
        // $path2 = public_path().'Photos/' . $path . $fileName;

        if (!file_exists('Photos/'.$path)) {
            // path does not exist
            mkdir('Photos/'.$path, 0755, true);
        }
        //搬移
        // Image::make($file)->resize(500, 500)->save($path)
        $file->move('Photos/'.$path, $fileName);
        $fileUrl = asset('').'Photos/' .$path.'/'.$fileName;

        return ['result'=>true , 'fileName'=>$fileName, 'url'=>$fileUrl ,'filePath'=> 'Photos/' .$path.'/'.$fileName, 'msg'=>'上傳成功'];

    }

    //刪除檔案
    public function deleteFile($path, $fileName)
    {
        if ($fileName) {
            facadesFile::delete($path.'/'.$fileName);
        }
    }


    //驗證副檔名
    public function verifyDeputyName($file, array $type = [])
    {
        if (!in_array($file->getClientOriginalExtension(), $type)){
            return false;
        }else{
            return true;
        }
    }

    //圖片壓縮
    public function resizeImage($file)
    {
        $name = time() . '.jpg'; //随机生成的字符串,可以自行拼接图片名字
        $path = public_path() . '/ss/' . $name; // ss 是我的存放照片的文件夹
        $img = Image::make($image)->resize(500, 500)->save($path); //压缩并保存照片
    }

}
