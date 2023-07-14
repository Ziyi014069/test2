<?php

namespace App\Packages;

class Common
{
    //中文字串 檢查
    public function checkChineseString($str){
        if (!preg_match("/^\p{Han}{2,8}$/u", $str)) {
            return false;
        }else{
            return true;
        }
    }

    //email格式 檢查
    public function checkEmail($str){
        if (!filter_var($str, FILTER_VALIDATE_EMAIL)) {
            return false;
        }else{
            return true;
        }
    }

    //密碼格式檢查
    public function checkPassword($str,$s = 8,$e = 10){
        $chaCheck = preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/", $str);
        $numCheck = (mb_strlen($str) >= $s) && (mb_strlen($str) <= $e);
        if ($chaCheck && $numCheck) {
            return true;
        }else{
            return false;
        }
    }

    //手機格式檢查
    public function checkMobile($str){
        if (!preg_match("/^09[0-9]{8}$/", $str)) {
            return false;
        }else{
            return true;
        }
    }

    //網址格式檢查
    public function checkUrl($str){
        if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $str)) {
            return false;
        }else{
            return true;
        }
    }

    //

    //產生隨機字串
    public function random_string($length = 6, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        return $string;
    }

    public function addCreateDate($data,$name = 'system'){

        $data['createTime'] = date("Y-m-d H:i:s");
        $data['creator'] = $name;
        $data['ipOfCreator'] = $_SERVER['REMOTE_ADDR'];
        $data['updateTime'] = date("Y-m-d H:i:s");
        $data['lastUpdater'] = $name;
        $data['ipOfLastUpdater'] = $_SERVER['REMOTE_ADDR'];
        return $data;
    }

    public function addUpdateDate($data,$name = 'system'){

        $data['updateTime'] = date("Y-m-d H:i:s");
        $data['lastUpdater'] = $name;
        $data['ipOfLastUpdater'] = $_SERVER['REMOTE_ADDR'];
        return $data;
    }

    public function addDeleteDate($data,$name = 'system'){

        $data['updateTime'] = date('Y-m-d H:i:s');
        $data['lastUpdater'] = $name;
        $data['ipOfLastUpdater'] = $_SERVER['REMOTE_ADDR'];
        $data['isRemoved'] = 1;
        $data['removeTime'] = date('Y-m-d H:i:s');
        $data['remover'] = $name;
        $data['ipOfRemover'] = $_SERVER['REMOTE_ADDR'];
        return $data;
    }

    //彩威使用
    public function fIsconCreateData($data,$name = 'system'){
        $data['CreatedDate'] = date("Y-m-d H:i:s");
        $data['CreatedBy'] = $name;
        $data['UpdatedDate'] = date("Y-m-d H:i:s");
        $data['UpdatedBy'] = $name;
        return $data;
    }

    //彩威使用
    public function fIsconUpdateData($data, $name = 'system')
    {
        $data['UpdatedDate'] = date("Y-m-d H:i:s");
        $data['UpdatedBy'] = $name;
        return $data;
    }

    //無條件捨去
    public function floor_dec($v, $precision){
        $c = pow(10, $precision);
        return floor($v * $c) / $c;
    }

    //create not time
    public function fLaravelCreateDate($data, $name = 'system'){
        $data['creator'] = $name;
        $data['ipOfCreator'] = $_SERVER['REMOTE_ADDR'];
        $data['lastUpdater'] = $name;
        $data['ipOfLastUpdater'] = $_SERVER['REMOTE_ADDR'];
        return $data;
    }

    //update not time
    public function fLaravelUpdateDate($data, $name = 'system'){

        $data['lastUpdater'] = $name;
        $data['ipOfLastUpdater'] = $_SERVER['REMOTE_ADDR'];
        return $data;
    }

    //delete not time
    public function fLaravelDeleteDate($data,$name = 'system'){

        $data['lastUpdater'] = $name;
        $data['ipOfLastUpdater'] = $_SERVER['REMOTE_ADDR'];
        $data['isRemoved'] = 1;
        $data['removeTime'] = date('Y-m-d H:i:s');
        $data['remover'] = $name;
        $data['ipOfRemover'] = $_SERVER['REMOTE_ADDR'];
        return $data;
    }
}
