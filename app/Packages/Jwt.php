<?php

namespace App\Packages;

use Illuminate\Http\Request;

class Jwt
{
    private static $header = array(
        'alg' => 'HS256',
        'typ' => 'JWT'
    );

    private static $key = '';

    function __construct($sKey = '') {
        if (empty($sKey)) {
            self::$key = env('JWT_KEY', 'Wistrend');
        } else {
            self::$key = $sKey;
        }
    }

    public static function getToken(array $payload)
    {
        if (empty(self::$key)) {
            self::$key =  env('JWT_KEY', 'Wistrend');
        }

        if(is_array($payload))
        {
            $base64header = self::base64UrlEncode(json_encode(self::$header, JSON_UNESCAPED_UNICODE));
            $base64payload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
            $token = $base64header . '.' . $base64payload . '.' . self::signature($base64header . '.' . $base64payload, self::$key, self::$header['alg']);
            return $token;
        }else{
            return false;
        }
    }

    private static function base64UrlEncode(string $input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    private static function base64UrlDecode(string $input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    private static function signature(string $input, string $key, string $alg = 'HS256')
    {
        $alg_config = array(
            'HS256'=>'sha256'
        );
        return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key,true));
    }

    public static function verifyToken(string $Token, Request $request = null)
    {
        if (empty(self::$key)) {
            self::$key =  env('JWT_KEY', 'Wistrend');
        }

        $sReferer = "";

        if (!is_null($request)) {
            $sReferer = $request->headers->get('referer');

            $aReferer = explode("/", (explode("?", $sReferer))[0]);
            $sReferer = $aReferer[count($aReferer) - 1];
        }

        $tokens = explode('.', $Token);

        if (count($tokens) != 3) {
            return false;
        }

        list($base64header, $base64payload, $sign) = $tokens;

        $base64decodeheader = json_decode(self::base64UrlDecode($base64header), JSON_OBJECT_AS_ARRAY);
        if (empty($base64decodeheader['alg'])) {
            return false;
        }

        if (self::signature($base64header . '.' . $base64payload, self::$key, $base64decodeheader['alg']) !== $sign) {
            return false;
        }

        $aPayload = json_decode(self::base64UrlDecode($base64payload), JSON_OBJECT_AS_ARRAY);

        if (!is_null($request)) {
            if (!(isset($aPayload['page']) && strtolower(trim($aPayload['page'])) == strtolower(trim($sReferer)))) {
                return false;
            }
        }

        return $aPayload;
    }
}
