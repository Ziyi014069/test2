<?php

namespace App\Packages\ECpay;

use Log;

/*
|--------------------------------------------------------------------------
| Opay 歐付寶
|--------------------------------------------------------------------------
*/

class Opay
{
    public function fGenerate($aData)
    {
        $MerchantID = env('MerchantID');
        $HashKey = env('HashKey');
        $HashIV = env('HashIV');

        $data = array(
            'ChoosePayment'         => $aData['ChoosePayment'],
            // 'ClientBackURL'         => asset('api/nubk/order')."?k={$aData['nubkWorkNo']}",
            'OrderResultURL'        => asset('api/nubk/order/payReturn'),
            'NeedExtraPaidInfo'     => 'Y',
            'EncryptType'           => 1,
            'ItemName'              => $aData['ItemName'],
            'MerchantID'            => $MerchantID,
            'MerchantTradeNo'       => $aData['MerchantTradeNo'],
            'MerchantTradeDate'     => $aData['MerchantTradeDate'],
            'PaymentType'           => 'aio',
            'ReturnURL'             => asset('api/nubk/order/payReturn'),
            'TotalAmount'           => $aData['TotalAmount'],
            'TradeDesc'             => $aData['TradeDesc'],

        );
        $data['CheckMacValue'] = $this->generateCheckMacValue($data, $HashKey, $HashIV);
        // return $data;
        $url = env('Opay_url');
        return $this->submitOrder($data, $url);
    }

    public function generateCheckMacValue($arParameters = array(), $HashKey = '', $HashIV = '', $encType = 0)
    {
        $sMacValue = '';
        if (isset($arParameters)) {
            // arParameters 為傳出的參數，並且做字母 A-Z 排序
            unset($arParameters['CheckMacValue']);
            // ksort($arParameters);
            uksort($arParameters, array($this, 'my_sort'));
            // 組合字串
            $sMacValue = 'HashKey=' . $HashKey;
            foreach ($arParameters as $key => $value) {
                $sMacValue .= '&' . $key . '=' . $value;
            }
            $sMacValue .= '&HashIV=' . $HashIV;
            // URL Encode 編碼
            $sMacValue = urlencode($sMacValue);
            // 轉成小寫
            $sMacValue = strtolower($sMacValue);
            // 取代為與 dotNet 相符的字元
            $sMacValue = str_replace('%2d', '-', $sMacValue);
            $sMacValue = str_replace('%5f', '_', $sMacValue);
            $sMacValue = str_replace('%2e', '.', $sMacValue);
            $sMacValue = str_replace('%21', '!', $sMacValue);
            $sMacValue = str_replace('%2a', '*', $sMacValue);
            $sMacValue = str_replace('%28', '(', $sMacValue);
            $sMacValue = str_replace('%29', ')', $sMacValue);
            // 編碼
            // SHA256 編碼
            $sMacValue = hash('sha256', $sMacValue);

            $sMacValue = strtoupper($sMacValue);
        }
        return $sMacValue;
    }

    public  function setGenerateCheckMacValue($arParameters = array(), $HashKey = '', $HashIV = '', $encType = 0)
    {

        $sMacValue = '';
        if (isset($arParameters)) {
            $aData['要產生檢查碼的data'] = $arParameters;
            // arParameters 為傳出的參數，並且做字母 A-Z 排序
            unset($arParameters['CheckMacValue']);
            // ksort($arParameters);
            uksort($arParameters, array($this, 'my_sort'));

            $aData['A-Z 排序'] = $arParameters;
            // 組合字串
            $sMacValue = 'HashKey=' . $HashKey;
            foreach ($arParameters as $key => $value) {
                $sMacValue .= '&' . $key . '=' . $value;
            }
            $sMacValue .= '&HashIV=' . $HashIV;

            $aData['組合字串'] = $sMacValue;
            // URL Encode 編碼
            $sMacValue = urlencode($sMacValue);

            $aData['urlencode'] = $sMacValue;
            // 轉成小寫
            $sMacValue = strtolower($sMacValue);
            $aData['轉小寫'] = $sMacValue;
            // 取代為與 dotNet 相符的字元
            $sMacValue = str_replace('%2d', '-', $sMacValue);
            $sMacValue = str_replace('%5f', '_', $sMacValue);
            $sMacValue = str_replace('%2e', '.', $sMacValue);
            $sMacValue = str_replace('%21', '!', $sMacValue);
            $sMacValue = str_replace('%2a', '*', $sMacValue);
            $sMacValue = str_replace('%28', '(', $sMacValue);
            $sMacValue = str_replace('%29', ')', $sMacValue);

            $aData['取代字元'] = $sMacValue;
            // 編碼
            // SHA256 編碼
            $sMacValue = hash('sha256', $sMacValue);

            $aData['SHA256'] =  $sMacValue;

            $sMacValue = strtoupper($sMacValue);

            $aData['轉大寫'] =  $sMacValue;
        }
        return $aData;
    }


    // 定義自定義比較函數
    public function my_sort($a, $b)
    {

        for ($i = 0; $i < strlen($a); $i++) {
            $a_first = substr(strtolower($a), $i, 1);
            $b_first = substr(strtolower($b), $i, 1);
            if ($a_first != $b_first) {
                return ($a_first < $b_first) ? -1 : 1;
            }
        }
        // 取得第一個單詞的首字母
        // $a_first = substr($a, 0, 1);
        // $b_first = substr($b, 0, 1);

        // 如果首字母不同，直接比較
        // if ($a_first != $b_first) {
        //     return ($a_first < $b_first) ? -1 : 1;
        // }

        // 如果首字母相同，比較第二個單詞的首字母

        // $a_second = substr($a, 1, 1);
        // $b_second = substr($b, 1, 1);

        // if ($a_second != $b_second) {
        //     return ($a_second > $b_second) ? -1 : 1;
        // }

        // 如果第二個單詞的首字母也相同，比較第三個單詞的首字母，以此類推。
        // 如果需要比較更多的單詞，請繼續在這裡添加類似的代碼。
        // $a_third = substr(strstr(strstr($a, ' '), ' '), 1, 1);
        // $b_third = substr(strstr(strstr($b, ' '), ' '), 1, 1);

        // if ($a_third != $b_third) {
        //     return ($a_third < $b_third) ? -1 : 1;
        // }

        // 如果所有單詞的首字母都相同，則將其視為相等。
        return 0;
    }


    private function submitOrder($ecpay, $url)
    {
        $result = '<form name="sunTech" id="order_form" method="post" action=' . $url . '>';
        foreach ($ecpay as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $sub_key => $sub_value)
                    $result .= '<input type="hidden" name="' . $key . '["' . $sub_key . '"]" value="' . $sub_value . '">';
            } else {
                $result .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
            }
        }
        $result .= '</form><script type="text/javascript">document.getElementById(\'order_form\').submit();</script>';

        return $result;
    }


    public function getOrder($input)
    {
        $aData = [
            'MerchantID' => env('MerchantID'),
            'MerchantTradeNo' => $input['MerchantTradeNo'],
            'TimeStamp' => time(),
            'PlatformID' => ''
        ];
        $aData['CheckMacValue'] = $this->generateCheckMacValue($aData, env('HashKey'), env('HashIV'));

        $url =  'https://payment-stage.funpoint.com.tw/Cashier/QueryTradeInfo/V5';
        $data = http_build_query($aData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     // 'Content-Type: multipart/form-data'
        //     'Content-Type: multipart/form-data; boundary=----------------------------749c186646f5'
        // ]);
        $result = curl_exec($ch);
        curl_close($ch);
        // return $result;
        $result = explode('&', $result);
        $array = [];
        foreach ($result as $key => $value) {
            $aVal = explode('=', $value);
            $array[$aVal[0]] = !empty($aVal[1]) ? $aVal[1] : '';
        }

        return $array;
    }


    public function fTestAdd($input)
    {
        // return 1;
        $MerchantID = '1000060';
        $HashKey = env('HashKey2');
        $HashIV = env('HashIV2');

        $data = array(
            'MerchantMemberID'         => $MerchantID . 'B00001',
            // 'ClientBackURL'         => asset('api/nubk/order')."?k={$aData['nubkWorkNo']}",
            'ClientRedirectURL'        => asset('api/nubk/order/payReturn'),
            // 'NeedExtraPaidInfo'     => 'Y',
            'stage '               => 0,
            // 'ItemName'              => $aData['ItemName'],
            'MerchantID'            => $MerchantID,
            'MerchantTradeNo'       => 'zztest001',
            'MerchantTradeDate'     => date('Y-m-d H:i:s'),
            // 'PaymentType'           => 'aio',
            'ServerReplyURL'             => asset('api/nubk/order/payReturn'),
            'TotalAmount'           => 1500,
            'TradeDesc '             => '交易描述',

        );
        $data['CheckMacValue'] = $this->generateCheckMacValue($data, $HashKey, $HashIV);
        // return $data;
        $url = 'https://payment-stage.funpoint.com.tw/MerchantMember/TradeWithBindingCardID';
        return $this->submitOrder($data, $url);
    }
}
