<?php

namespace App\Packages;

use App\Packages\Jwt;
use Illuminate\Support\Facades\Crypt;

class Captcha
{
    //
    public function pictureBack()
    {
        $im = imagecreatetruecolor(100, 28);
        $text_color = imagecolorallocate($im, 233, 14, 91);
        $path = resource_path('css/arial.ttf');
        $nums = [];
        for ($i = 0; $i < 4; $i++) {
            $nums[] = rand(0, 9);
        }
        for ($i = 0; $i < 40; $i++) {
            $color = imagecolorallocate($im, rand(200, 240), 14, 91);
            imagefilledellipse($im, rand(0, 100), rand(0, 28), rand(0, 2), rand(0, 5), $color);
        }
        imagettftext($im, 18, rand(-12, 12), 5, 22,  $text_color, $path, $nums[0]);
        imagettftext($im, 18, rand(-12, 12), 30, 22, $text_color, $path, $nums[1]);
        imagettftext($im, 18, rand(-12, 12), 55, 22, $text_color, $path, $nums[2]);
        imagettftext($im, 18, rand(-12, 12), 80, 22, $text_color, $path, $nums[3]);
        ob_start();
        imagejpeg($im);
        $img = ob_get_clean();
        ob_end_clean();
        imagedestroy($im);

        $aData=[
                    'code' => implode($nums),
                    'expireTime' => time() + 5*60,
                ];
        $token = Jwt::getToken($aData);

        return ['result' => true, 'message' => 'success','data' => [
                'img' => base64_encode($img),
                'codeToken' => $token
            ]];

    }
}
