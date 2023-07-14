<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//package
use App\Packages\Jwt;

//Laravel
use Hash;

//Model
use App\Models\Account;
use App\Models\Role;
use App\Models\MenuFunction;
use Illuminate\Support\Facades\Mail;

use App;
use Auth;
use Validator;
use Redirect;

class LoginController extends Controller
{
    public function fLogin(Request $request)
    {

        $input = json_decode($request->getContent(), true);
        // $input = $request->all();

        //資料檢查
        if (empty($input['account']) || !filter_var($input['account'], FILTER_VALIDATE_EMAIL)) {
            return ['result' => false, 'message' => '帳號密碼錯誤'];
        } else if (empty($input['password'])) {
            return ['result' => false, 'message' => '帳號密碼錯誤'];
        } else if (empty($input['captcha'])) {
            return ['result' => false, 'message' => '帳號密碼錯誤'];
        } else if (empty($input['codeToken'])) {
            return ['result' => false, 'message' => '帳號密碼錯誤'];
        }

        //驗證碼
        $captcha = Jwt::verifyToken($input['codeToken']);
        if ($input['captcha'] != $captcha['code'] ||  $captcha['expireTime'] < time()) {
            return ['result' => false, 'message' => '驗證碼錯誤'];
        }

        //留給root使用
        if ($input['account'] == 'root@yeshi.tw' && $input['password'] == '1234Qwer') {

            $aData = [
                'id' => 0,
                'name' => 'root',
                'account' => 'root',
                'role' => 'root',
                'expireTime' => time() + 7 * 24 * 60 * 60,
                'teacherId' => 0,
                // 'auth' => $this->fGetRoleMenu(0)
            ];
            $token = Jwt::getToken($aData);
            $aData['token'] = $token;
            $aData['auth'] = $this->fGetRoleMenu(0);
            return ['result' => true, 'message' => 'success', 'data' => $aData];
        }

        //正常登入
        $oAccount = Account::select('Account.accountId', 'Account.accountName', 'Account.password', 'Account.account', 'Account.roleId', 'Role.roleName', 'Account.teacherId')
            ->join('Role', 'Role.roleId', '=', 'Account.roleId')
            ->where('Account.account', '=', $input['account'])
            ->where('Account.isEnabled', 1)
            ->where('Account.isRemoved', 0)
            ->where('Role.isEnabled', 1)
            ->where('Role.isRemoved', 0)
            ->first();
        if (!empty($oAccount)) {
            if (Hash::check($input['password'], $oAccount['password'])) {
                //success
                $aData = [
                    'id' => $oAccount['accountId'],
                    'name' => $oAccount['accountName'],
                    'account' => $oAccount['account'],
                    'role' => $oAccount['roleName'],
                    'expireTime' => time() + 7 * 24 * 60 * 60,
                    'teacherId' => $oAccount['teacherId'],
                    // 'auth' => $this->fGetRoleMenu($oAccount['roleId'])
                ];
                $token = Jwt::getToken($aData);

                if ($token) {
                    $aData['token'] = $token;
                    $aData['auth'] = $this->fGetRoleMenu($oAccount['roleId']);
                    return ['result' => true, 'message' => 'success', 'data' => $aData];
                } else {
                    return ['result' => false, 'message' => '帳號密碼錯誤'];
                }
            } else {
                return ['result' => false, 'message' => '帳號密碼錯誤'];
            }
        } else {
            return ['result' => false, 'message' => '帳號密碼錯誤'];
        }
    }

    //取得登入身分權限資料
    private function fGetRoleMenu($roleId)
    {
        //取得權限id
        if ($roleId == 0) {
            //root登入 用所有權限
            $aMenuFunction = MenuFunction::select('menuFunctionId')
                ->where('isEnabled', 1)
                ->where('isRemoved', 0)
                ->orderBy('menuFunctionId')
                ->get();
            $authority = [];
            foreach ($aMenuFunction as $key => $value) {
                $authority[] = $value['menuFunctionId'];
            }
        } else {
            $oRole = Role::where('roleId', $roleId)->first();
            //帳號權限
            $authority = $oRole->authorizedFunctionIds ? explode(',', $oRole->authorizedFunctionIds) : array();
        }



        //取得第一層選單資料
        $oFirstMenuFunctionData = MenuFunction::select('menuFunctionId', 'menuFunctionName', 'pathOfMenuFunction', 'icon', 'menuFunctionAlias', 'menuFunctionOfParentId')
            ->where('isCategory', 1)
            ->where('isEnabled', 1)
            ->where('isRemoved', 0)
            ->orderBy('orderOfMenuFunction')
            ->get();
        //取得第二層選單資料
        $oSecondMenuFunctionData = MenuFunction::select('menuFunctionId', 'menuFunctionName', 'pathOfMenuFunction', 'icon', 'menuFunctionAlias', 'menuFunctionOfParentId'
                , 'isShowLink', 'isHiddenTag')
            ->where('isOperation', 1)
            ->where('isEnabled', 1)
            ->where('isRemoved', 0)
            ->orderBy('orderOfMenuFunction')
            ->get();
        //取得第三層選單資料
        $oThirdMenuFunctionData = MenuFunction::select('menuFunctionId', 'menuFunctionName', 'pathOfMenuFunction', 'icon', 'menuFunctionAlias', 'menuFunctionOfParentId')
            ->where('isFunction', 1)
            ->where('isEnabled', 1)
            ->where('isRemoved', 0)
            ->orderBy('orderOfMenuFunction')
            ->get();
        //回傳的選單資料
        $aData = array();

        //第一層
        foreach ($oFirstMenuFunctionData as $key => $value) {
            if (in_array($value['menuFunctionId'], $authority)) {
                $aArray = [
                    'id' => $value['menuFunctionId'],
                    'path' => isset($value['pathOfMenuFunction']) ? $value['pathOfMenuFunction'] : '',
                    'meta' => [
                        'title' => $value['menuFunctionName'],
                        'icon' => $value['icon'],
                        'rank' => $value['menuFunctionId'],
                    ],
                    'children' => []
                ];
                array_push($aData, $aArray);
            }
        }

        //第二層
        $aChildren = array();
        foreach ($oSecondMenuFunctionData as $key => $value) {
            if (in_array($value['menuFunctionId'], $authority)) {
                $aArray = [
                    'id' => $value['menuFunctionId'],
                    // 'isChildren' => $value['isChildren'],
                    'path' => isset($value['pathOfMenuFunction']) ? $value['pathOfMenuFunction'] : '',
                    'name' => $value['menuFunctionAlias'],
                    'meta' => [
                        'title' => $value['menuFunctionName'],
                        'icon' => $value['icon'],
                        'showLink' => ($value['isShowLink'] == 1 ) ? true : false,
                        'hiddenTag' => ($value['isHiddenTag'] == 1 ) ? true : false,
                        'Role' => ($roleId == 0) ? ['root'] : [$oRole->roleName],
                        'auths' => [],
                    ],

                ];
                if (!isset($aChildren[$value['menuFunctionOfParentId']])) {
                    $aChildren[$value['menuFunctionOfParentId']] = [];
                }
                $aChildren[$value['menuFunctionOfParentId']][] = $aArray;
            }
        }
        foreach ($aData as $key => $value) {
            if (isset($aData[$key]['children'])) {
                if (!empty($aChildren[$value['id']])) {
                    $aData[$key]['children'] = $aChildren[$value['id']];
                }
            }
        }

        //第三層
        $aChildren = array();
        if (!empty($oThirdMenuFunctionData)) {
            foreach ($oThirdMenuFunctionData as $key => $value) {
                if (in_array($value['menuFunctionId'], $authority)) {
                    if (!isset($aChildren[$value['menuFunctionOfParentId']])) {
                        $aChildren[$value['menuFunctionOfParentId']] = [];
                    }
                    $aChildren[$value['menuFunctionOfParentId']][] = $value['menuFunctionAlias'];
                }
            }

            foreach ($aData as $key => $value) {
                if (!empty($value['children'])) {
                    foreach ($value['children'] as $key2 => $value2) {
                        if (isset($aChildren[$value2['id']])) {
                            $aData[$key]['children'][$key2]['meta']['auths'] =  $aChildren[$value2['id']];
                        }
                    }
                }
            }
        }


        return $aData;
    }

    //修改密碼
    public function fAccountInfo(Request $request)
    {
        //uid
        $token = Jwt::verifyToken($request->bearerToken());

        if($token['account'] == 'root'){
            return  ['result' => true, 'message' => 'success', 'data' => $token];
        }

        $oAccount = Account::select('*')->where('account', '=', $token['account'])->first();
        if ($oAccount) {

            return ['result' => true, 'message' => 'success', 'data' => $oAccount];
        } else {
            return ['result' => false, 'message' => '帳號不存在'];
        }
    }

    //修改密碼
    public function changePassword(Request $request)
    {
        //uid
        $token = Jwt::verifyToken($request->bearerToken());

        $input = $request->all();

        if (empty($input['oldPassword'])) {
            return ['result' => false, 'message' => '舊密碼不能為空'];
        } else if (empty($input['password'])) {
            return ['result' => false, 'message' => '新密碼不能為空'];
        }else if($token['account'] == 'root'){
            return ['result' => false, 'message' => 'root帳號沒有此功能'];
        }


        $Account = Account::select('password')->where('account', '=', $token['account'])->first();
        if ($Account) {
            if (Hash::check($input['oldPassword'], $Account['password'])) {

                $aData = [
                    'password' => Hash::make($input['password'])
                ];
                Account::where('account', '=', $token['account'])->update($aData);
                // $Account->password = Hash::make($input['password']);

                // $Account->save();

                return ['result' => true, 'message' => 'success'];
            } else {
                return ['result' => false, 'message' => '舊密碼錯誤'];
            }
        } else {
            return ['result' => false, 'message' => '帳號密碼錯誤'];
        }
    }

    //取得驗證碼
    public function pictureBack(Request $request)
    {
        $im = imagecreatetruecolor(100, 28);

        $text_color = imagecolorallocate($im, 233, 14, 91);
        // set background to white
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $white);

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

        $aData = [
            'code' => implode($nums),
            'expireTime' => time() + 5 * 60,
        ];
        $token = Jwt::getToken($aData);

        return ['result' => true, 'message' => 'success', 'data' => [
            'img' => base64_encode($img),
            'codeToken' => $token,
            'code' => implode($nums)
        ]];
    }
}
