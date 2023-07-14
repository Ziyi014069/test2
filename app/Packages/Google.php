<?php

namespace App\Packages;

class Google
{
    /**
     * Geocoding 付費API 以地址查詢經緯度
     *
     * @param request $request
     * @return array
     */
    public function geocoding($request)
    {
        $address_str = $request->post('all_addr');

        // https://maps.googleapis.com/maps/api/geocode/json?address=台灣台北市萬華區康定路190號&key=YOUR_API_KEY
        $geocoding_api = "https://maps.googleapis.com/maps/api/geocode/json?address=";
        $url = $geocoding_api.$address_str."&key=".\Config::get('app.google_api_key');

        $json = @file_get_contents($url);// ＠關掉警告，避免影響API正常運作

        if ($json == null) {
            // 錯誤，空白
            return [
                'result'    => false,
                'errorInfo' => "無法解析地址：".$address_str,
            ];
        }
        $data = json_decode($json, true);

        if ($data['status'] == "OK") {
            $result = $data["results"];
            return [
                'result' => true,
                'data'   => [
                    'lat' => $result[0]['geometry']['location']['lat'],
                    'lng' => $result[0]['geometry']['location']['lng'],
                ],
            ];
        } else {
            return [
                'result'    => false,
                'errorInfo' => $data["error_message"],
            ];
        }
    }

    /**
     * Google API Distance Matrix 付費(backup function, please test before use)
     *
     * @param request $request
     * @param object $store
     * @return void
     */
    public function googleDistance($request, $store)
    {
        // https://maps.googleapis.com/maps/api/distancematrix/json?key=AIzaSyCUfdD1183vpR7zOcuKpUFvwg63GG6aBhA&origins=24.175135,120.686697&destinations=24.175937,120.686132&language=en
        // 輸入兩點經緯度及交通方式，查詢距離與時間
        $geocoding_api = "https://maps.googleapis.com/maps/api/distancematrix/json?";
        $url = $geocoding_api."key=".\Config::get('app.google_api_key')."&origins=".$request->get('lang').",".$request->get('long')."&destinations=".$store->lang.",".$store->long."&language=en";
        $json = @file_get_contents($url);//＠關掉警告，避免影響API正常運作

        if($json!=null){
            $data = json_decode($json, true);

            if($data['status']=="OK"){
                $result = $data['rows'][0]['elements'][0]['distance']['text'];
                $result = str_replace( " ", "",$result);
                $store->distance = $result;
            }else{
                //錯誤訊息
                return response()->json($data["error_message"], 200);
            }
        }else{
            //錯誤，空白，不處理
        }
    }
}
