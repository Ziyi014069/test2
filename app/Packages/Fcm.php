<?php

namespace App\Packages;

class Fcm
{
    //發送推播
    public function fpushFCM($token,$title,$body){

        $headers = array(
            'Content-Type:application/json',
            "Authorization: key=AAAA8lPk6kk:APA91bEvxUlCv7MYASTjXFC6QMAxPV_zSuxlL1ysU9wv_3nqr_raby_EA-SdCHEWLBmB3cezUDHbV6WlQN9Nw0CkMVFw_zfK07F5aAFGIp0UbCAs5cpT9GoszY0xOs50umrh9T1Yz188",
            'Sender:id=1040789596745',
        );

        $fields = array(
            'to'		    => $token,
            'notification'	=> array(
                                    'title'	=> $title,
                                    'body' 	=> $body,
                                    'icon' => 'icon_notification',
                                    'largeIcon' => 'app_logo',
                                    'sound' => 'coin.mp3',
                                    ),
            'data' => array(
                'title'	=> $title,
                'body' 	=> $body,
                'web_title' => 'webTitle',
                'push_service' => 'notification',
                'web_id' => 'new_web',
                'web_content' => 'dist/index.html#/notifyListFcm',
                'display_top_title' => false,
                "foreground_not_goto_page"=>false,
                "receiver_confirm"=>true
            ),
            "priority"=>"high",
            "content_available"=>true
        );


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

    }
}
