<?php

namespace App\Packages;

class Sms
{
    public function mitakeSms($mobile, $content){
        //三竹簡訊傳送隨機驗證碼
        ob_start(); //開啟緩存，因為三竹api輸出的不是json，會導致api崩壞

        $api_domain = "https://smsapi.mitake.com.tw";//可能會改
        $username = env('SMS_ACCOUNT', '');//客戶的帳號/統編
        $passowrd = env('SMS_PASSWORD', '');//客戶的帳號密碼

        $url = $api_domain.'/api/mtk/SmSend?';
        $url .= 'CharsetURL=UTF-8';

        // parameters
        $data = [
            'username' => $username,
            'password' => $passowrd,
            'dstaddr' => $mobile,
            'smbody' => $content
        ];
        $data = http_build_query($data);

        // 設定curl網址
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        // 設定Header
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER,0);
        // 執行
        $result = curl_exec($curl);
        curl_close($curl);

        $response = ob_get_contents();//將緩存存起來
        ob_end_clean();//清掉三竹簡訊產生的資訊
        return $response;
    }
}
