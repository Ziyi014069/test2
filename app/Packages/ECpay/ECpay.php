<?php

namespace App\Packages\ECpay;

/*
|--------------------------------------------------------------------------
| ECPay 綠界科技金流
|--------------------------------------------------------------------------
*/
class ECpay
{
    //我的HostUrl
    private $myHostUrl;
    //介接URL路徑
    private $ECPayUrl;
    //介接查詢訂單URL路徑
    private $ECPayQueryOrderUrl;
    //介接查詢電子發票URL路徑
    private $QueryInvoiceUrl;
    //介接發送電子發票URL路徑
    private $InvoiceSendUrl;
    //HashKey
    private $HashKey;
    //HashIV
    private $HashIV;
    //特店編號(由綠界提供)
    private $MerchantID;
    //交易類型
    private $PaymentType = 'aio'; #固定值
    //交易描敘
    private $TradeDesc = '搜尋獎學金描敘';
    //商品名稱
    private $ItemName = '搜尋獎學金';
    //付款完成通知回傳網址
    private $ReturnURL = 'member/order/return';
    //Server 端回傳付款相關資訊
    // private $PaymentInfoURL;
    //Client 端回傳付款結果網址，沒帶此參數則會顯示綠界的付款完成頁
    private $OrderResultURL = 'member/order/result';
    //付款方式
    private $ChoosePayment = 'Credit';
    //檢查碼
    private $CheckMacValue;
    //CheckMacValue 加密類型
    private $EncryptType = 1; #固定值，使用 SHA256 加密
    //ECPay訂單
    private $myOrder = array();
    //訂單資料
    private $order;
    //電子發票：延遲天數
    private $DelayDay = '0';
    //電子發票：捐贈註記
    private $Donation = '0';
    //電子發票：商品數量
    private $InvoiceItemCount = '1';
    //電子發票：商品單位
    private $InvoiceItemWord = '次';
    //電子發票：開立註記
    private $InvoiceMark = 'Y';
    //電子發票：字軌類別
    private $InvType = '07';
    //電子發票：列印註記
    private $Print = '0';
    //電子發票：課稅類別
    private $TaxType = '1';


    /**
     * 建構設置參數
     *
     * @ReturnURL: 付款完成通知回傳網址
     *
     * @OrderResultURL: Client 端回傳付款結果網址，沒帶此參數則會顯示綠界的付款完成頁
     *
     */
    public function __construct()
    {
        //參數配制
        $this->config();
    }


    /**
     * 參數配制
     *
     * @return {this} $this
     */
    public function config()
    {
        $this->myHostUrl          = asset('');
        $this->ECPayUrl           = env('ECPayUrl');
        $this->HashKey            = env('HashKey');
        $this->HashIV             = env('HashIV');
        $this->MerchantID         = env('MerchantID');
        $this->ECPayQueryOrderUrl = env('ECPayQueryOrderUrl');
        $this->InvoiceSendUrl     = env('ECPaySendInvoiceUrl');
        $this->QueryInvoiceUrl    = env('ECPayQueryInvoiceUrl');
        $this->ReturnURL          = $this->myHostUrl.'/'.$this->ReturnURL;
        $this->OrderResultURL     = $this->myHostUrl.'/'.$this->OrderResultURL;
    }


    /**
     * 生成訂單
     *
     * @param {object} $order 訂單資料
     *
     * @return {form} 表單HTML
     */
    public function generate($order)
    {
        //取得訂單資料
        $this->order = $order;

        //設置訂單參數
        $this->setOrderParam();

        //設定訂單檢查碼
        $this->setCheckMacValue();

         $this->myOrder;

        //送出訂單
        return $this->submitOrder();
    }


    /**
     * 設置訂單參數
     *
     * @return {object} $this->myOrder ECPay訂單
     */
    private function setOrderParam()
    {
        //將傳遞參數依照第一個英文字母，由 A 到 Z 的順序來排序

        if($this->order['paymentType'] == 'atm'){
            $this->myOrder = array(
                'ChoosePayment'      => 'ATM',
                'ClientBackURL'      => $this->myHostUrl.'member/order',//Client 端返回特店的按鈕連結
                'CustomerAddr'       => isset($this->order['address']) ? urlencode($this->order['address']) : "",
                'CustomerEmail'      => urlencode($this->order['email']),//電子發票：客戶電子信箱
                'CustomerIdentifier' => isset($this->order['unifiedBusinessNo']) ? $this->order['unifiedBusinessNo'] : "",//統一編號
                'CustomerName'       => isset($this->order['memberName']) ? urlencode($this->order['memberName']) : "",
                'DelayDay'           => $this->DelayDay,//電子發票：延遲天數
                'Donation'           => $this->Donation,//電子發票：捐贈註記
                'EncryptType'        => $this->EncryptType,
                'ExpireDate'         => 7,
                'InvoiceItemCount'   => $this->order['InvoiceItemCount'],//電子發票：商品數量
                'InvoiceItemName'    => urlencode($this->order['InvoiceItemName']),//電子發票：商品名稱
                'InvoiceItemPrice'   => $this->order['InvoiceItemPrice'],//電子發票：商品價格
                'InvoiceItemWord'    => urlencode($this->order['InvoiceItemWord']),//電子發票：商品單位
                'InvoiceMark'        => $this->InvoiceMark,//電子發票：開立註記
                'InvType'            => $this->InvType,//電子發票：字軌類別
                'ItemName'           => $this->order['ItemName'],
                'MerchantID'         => $this->MerchantID,
                'MerchantTradeDate'  => date('Y/m/d H:i:s', strtotime($this->order['createTime'])),   //(yyyy/MM/dd HH:mm:ss)
                'MerchantTradeNo'    => $this->order['orderNo'],
                'PaymentInfoURL'     => $this->myHostUrl.'member/order/atmResult',//Server 端回傳付款相關資訊
                'PaymentType'        => $this->PaymentType,
                'Print'              => isset($this->order['Print']) ? $this->order['Print'] : $this->Print,//電子發票：列印註記
                'RelateNumber'       => $this->order['orderNo'],//電子發票：特店自訂編號
                'ReturnURL'          => $this->myHostUrl.'member/order/atmReturn',//付款完成通知回傳網址
                'TaxType'            => $this->TaxType,//電子發票：課稅類別
                'TotalAmount'        => $this->order['money'],
                'TradeDesc'          => $this->order['TradeDesc']
            );

            $string = json_encode($this->myOrder);
            //更新進atm payment info 裡 重產的時候更新訂單編號
            $updateAtmData = array(
                'ecpayOrderArray'  => $string //json_encode($this->myOrder)
            );

        }else{
            $this->myOrder = array(
                'ChoosePayment'      => $this->ChoosePayment,
                'ClientBackURL'      => $this->OrderResultURL.'/'.$this->order['orderNo'],//Client 端返回特店的按鈕連結
                'CustomerAddr'       => isset($this->order['address']) ? urlencode($this->order['address']) : "",
                'CustomerEmail'      => urlencode($this->order['email']),//電子發票：客戶電子信箱
                'CustomerIdentifier' => isset($this->order['unifiedBusinessNo']) ? $this->order['unifiedBusinessNo'] : "",//統一編號
                'CustomerName'       => isset($this->order['memberName']) ? urlencode($this->order['memberName']) : "",
                'DelayDay'           => $this->DelayDay,//電子發票：延遲天數
                'Donation'           => $this->Donation,//電子發票：捐贈註記
                'EncryptType'        => $this->EncryptType,
                'InvoiceItemCount'   => $this->order['InvoiceItemCount'],//電子發票：商品數量
                'InvoiceItemName'    => urlencode($this->order['InvoiceItemName']),//電子發票：商品名稱
                'InvoiceItemPrice'   => $this->order['InvoiceItemPrice'],//電子發票：商品價格
                'InvoiceItemWord'    => urlencode($this->order['InvoiceItemWord']),//電子發票：商品單位
                'InvoiceMark'        => $this->InvoiceMark,//電子發票：開立註記
                'InvType'            => $this->InvType,//電子發票：字軌類別
                'ItemName'           => $this->order['ItemName'],
                'MerchantID'         => $this->MerchantID,
                'MerchantTradeDate'  => date('Y/m/d H:i:s', strtotime($this->order['createTime'])),   //(yyyy/MM/dd HH:mm:ss)
                'MerchantTradeNo'    => $this->order['orderNo'],
                'PaymentType'        => $this->PaymentType,
                'Print'              => isset($this->order['Print']) ? $this->order['Print'] : $this->Print,//電子發票：列印註記
                'RelateNumber'       => $this->order['orderNo'],//電子發票：特店自訂編號
                'ReturnURL'          => $this->ReturnURL,
                'TaxType'            => $this->TaxType,//電子發票：課稅類別
                'TotalAmount'        => $this->order['money'],
                'TradeDesc'          => $this->order['TradeDesc']
            );
        }
    }


    /**
     * 設定訂單檢查碼
     *
     * @return {string} $this->myOrder['CheckMacValue'] 檢查碼
     */
    private function setCheckMacValue()
    {
        $this->myOrder['CheckMacValue'] = $this->getCheckMacValue($this->myOrder);
    }


    /**
     * 驗證檢查碼
     *
     * @param $input 回傳資料
     *
     * @return {boolean}
     */
    public function verifyCheckMacValue($input)
    {
        $data = $input;

        unset($data['CheckMacValue']);
        //排序
        ksort($data);

        //取得檢查碼
        $CheckMacValue = $this->getCheckMacValue($data);

        if ($CheckMacValue == $input['CheckMacValue']) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 取得檢查碼
     *
     * @param {object} $data 資料
     *
     * @return {string} $CheckMacValue 檢查碼
     */
    private function getCheckMacValue($data)
    {

        //參數最前面加上 HashKey、最後面加上 HashIV
        $data = array_merge(array('HashKey'=>$this->HashKey), $data);
        $data['HashIV'] = $this->HashIV;

        //取得URL query
        $paramsJoined = array();

        foreach($data as $key => $value) {
           $paramsJoined[] = "$key=$value";
        }

        $query = implode('&', $paramsJoined);

        //轉為小寫再urlencode 轉換表更換字元
        $data = strtolower(urlencode($query));

        $data = str_replace('%2d', '-', $data);
        $data = str_replace('%5f', '_', $data);
        $data = str_replace('%2e', '.', $data);
        $data = str_replace('%21', '!', $data);
        $data = str_replace('%2a', '*', $data);
        $data = str_replace('%28', '(', $data);
        $data = str_replace('%29', ')', $data);

        //SHA256 加密方式來產生雜凑值
        $encrypt = hash('sha256', $data);

        //轉大寫產生 CheckMacValue
        $CheckMacValue = strtoupper($encrypt);

        return $CheckMacValue;
    }

    //排序
    public function cmp($a, $b)
    {
        $a = preg_replace('@^(a|an|the) @', '', $a);
        $b = preg_replace('@^(a|an|the) @', '', $b);
        return strcasecmp($a, $b);
    }


    /**
     * 送出訂單
     *
     * @return {form} 表單HTML
     */
    private function submitOrder()
    {
        $result = '<form name="sunTech" id="order_form" method="post" action='.$this->ECPayUrl.'>';
        foreach ($this->myOrder as $key => $value) {
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


    /**
     * 取得訂單結果
     *
     * @param $orderNo 訂單編號
     *
     * @return 付款結果 1: 成功, 0: 失敗
     */
    public function getOrderResult($orderNo)
    {
        $url = $this->ECPayQueryOrderUrl;

        //httpMethod
        $httpMethod = 'POST';

        //header設定
        $ContentType = 'Content-Type: application/json';

        //header內容
        $header = array(
            'Content-Type: application/x-www-form-urlencoded'
        );

        //body資料
        $body = array(
            'MerchantID'        => $this->MerchantID,//商戶ID
            'MerchantTradeNo'   => $orderNo,//單號
            'PlatformID'        => '',
            'TimeStamp'         => time(),//發起時間
        );

        //取得驗證碼
        $body['CheckMacValue'] = (new ECPay)->getCheckMacValue($body);

        //curl 請求
        $result = $this->curl(
            $url,
            $httpMethod,
            $header,
            $body
        );

        //切割字串存成陣列(2次)
        $array = explode("&",$result);

        for($i=0; $i < count($array); $i++){
            $key_value = explode('=', $array [$i]);
            $resultArray[$key_value [0]] = $key_value [1];
        }

        return $resultArray['TradeStatus'];//交易狀態
    }


    /**
     * 寄送發票
     *
     * @param $order 訂單資料
     */
    public function sendInvoice($order, $invoice = null)
    {
        //查詢發票
        $invoice = $invoice ? $invoice : $this->queryInvoice($order['orderNo']);

        //開立發票
        if ($invoice) {
            return $this->openingAnInvoice($invoice, $order['email']);
        }
    }


    /**
     * 查詢發票
     *
     * @param $RelateNumber 合作特店自訂編號
     *
     * @return {string} 發票號碼
     */
    public function queryInvoice($RelateNumber)
    {
        //將傳遞參數依照第一個英文字母，由 A 到 Z 的順序來排序
        $myInvoiceParam = array(
            'MerchantID'   => $this->MerchantID,
            'RelateNumber' => $RelateNumber,//合作特店自訂編號
            'TimeStamp'    => time(),
        );

        //設定訂單檢查碼
        $myInvoiceParam['CheckMacValue'] = $this->getInvoiceCheckMacValue($myInvoiceParam);

        $url = $this->QueryInvoiceUrl;

        //httpMethod
        $httpMethod = 'POST';

        //header設定
        $ContentType = 'Content-Type: application/json';

        //header內容
        $header = array(
            'Content-Type: application/x-www-form-urlencoded'
        );

        //body資料
        $body = $myInvoiceParam;

        //curl 請求
        $result = $this->curl(
            $url,
            $httpMethod,
            $header,
            $body
        );

        $array = explode("&",$result);

        for($i=0; $i < count($array); $i++){
            $key_value = explode('=', $array [$i]);
            $resultArray[$key_value [0]] = $key_value [1];
        }

        //回傳發票號碼
        return isset($resultArray['IIS_Number']) ? $resultArray['IIS_Number'] : null;
    }


    /**
     * 查詢訂單狀態
     *
     * @param $RelateNumber 合作特店自訂編號
     *
     * @return {string} 發票號碼
     */
    public function querySearchOrder($orderNo)
    {
        //將傳遞參數依照第一個英文字母，由 A 到 Z 的順序來排序
        $myInvoiceParam = array(
            'MerchantID'   => $this->MerchantID,
            'MerchantTradeNo' => $orderNo,//交易編號
            'TimeStamp'    => time()

        );

        //設定訂單檢查碼
        $myInvoiceParam['CheckMacValue'] = $this->getInvoiceCheckMacValue($myInvoiceParam);

        $url = $this->ECPayQueryOrderUrl;

        //httpMethod
        $httpMethod = 'POST';

        //header設定
        $ContentType = 'Content-Type: application/x-www-form-urlencoded';

        //header內容
        $header = array(
            'Content-Type: application/x-www-form-urlencoded'
        );

        //body資料
        $body = $myInvoiceParam;

        //curl 請求
        $result = $this->curl(
            $url,
            $httpMethod,
            $header,
            $body
        );



        //回傳
        return $result;
    }

    /**
     * 開立發票
     *
     * @param $InvoiceNo 發票號碼
     * @param $email 信箱
     */
    public function openingAnInvoice($InvoiceNo, $email)
    {
        //將傳遞參數依照第一個英文字母，由 A 到 Z 的順序來排序
        $myInvoiceParam = array(
            'InvoiceNo'  => $InvoiceNo,//發票號碼
            'InvoiceTag' => 'I',//發送內容類型 I: 發票開立II: 發票作廢A: 折讓開立AI: 折讓作廢AW:發票中獎
            'MerchantID' => $this->MerchantID,
            'Notified'   => 'C',//發送對象 C: 發送通知給客戶M: 發送通知給合作特店A: 皆發送通知
            'Notify'     => 'E',//發送方式 S:簡訊E:電子郵件A:皆通知時
            'NotifyMail' => urlencode($email),//發送電子郵件
            'TimeStamp'  => time(),
        );
        //設定訂單檢查碼
        $myInvoiceParam['CheckMacValue'] = $this->getInvoiceCheckMacValue($myInvoiceParam);
        $myInvoiceParam['NotifyMail'] = $email;//去除 URL Encode

        $url = $this->InvoiceSendUrl;

        //httpMethod
        $httpMethod = 'POST';

        //header設定
        $ContentType = 'Content-Type: application/json';

        //header內容
        $header = array(
            'Content-Type: application/x-www-form-urlencoded'
        );

        //body資料
        $body = $myInvoiceParam;

        //curl 請求
        $result = $this->curl(
            $url,
            $httpMethod,
            $header,
            $body
        );

        $array = explode("&",$result);

        for($i=0; $i < count($array); $i++){
            $key_value = explode('=', $array [$i]);
            $resultArray[$key_value [0]] = $key_value [1];
        }

        return $resultArray;
    }



    /**
     * 取得電子發票檢查碼
     *
     * @param {object} $data 資料
     *
     * @return {string} $CheckMacValue 檢查碼
     */
    private function getInvoiceCheckMacValue($data)
    {
        //參數最前面加上 HashKey、最後面加上 HashIV
        $data = array_merge(array('HashKey'=>env('InvoiceHashKey')), $data);
        $data['HashIV'] = env('InvoiceHashIV');

        //取得URL query
        $paramsJoined = array();

        foreach($data as $key => $value) {
           $paramsJoined[] = "$key=$value";
        }

        $query = implode('&', $paramsJoined);

        //轉為小寫再urlencode 轉換表更換字元
        $data = strtolower(urlencode($query));

        //SHA256 加密方式來產生雜凑值
        $encrypt = md5($data);

        //轉大寫產生 CheckMacValue
        $CheckMacValue = strtoupper($encrypt);

        return $CheckMacValue;
    }


    //httpMethod 設定
    public function httpMethod($method)
    {
        switch(strtolower($method)){
            case 'get':  $result = array('option'=>'normal','action'=>false); break;
            case 'post': $result = array('option'=>'normal','action'=>true); break;
            case 'put': $result = array('option'=>'custom','action'=>'PUT'); break;
            case 'delete':  $result = array('option'=>'custom','action'=>'DELETE'); break;
            default: $result = array('option'=>'normal','action'=>false); break;
        }
        return $result;
    }


    //建立CURL
    public function curl($url,$httpMethod,$header=null,$body=null)
    {
        //建立CURL連線
        $ch = curl_init();
        $body = http_build_query($body);
        //參數設定
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);

        $result = $this->httpMethod($httpMethod);

        if($result['option']=='normal'){
            curl_setopt($ch, CURLOPT_POST, $result['action']);

        }else if($result['option']=='custom'){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $result['action']);
        }

        if(isset($header)&&$header!=null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        }

        if(isset($body)&&$body!=null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }


        //執行CURL
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
