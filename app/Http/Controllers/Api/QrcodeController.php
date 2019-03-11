<?php

/*
|--------------------------------------------------------------------------
| 坦白言——小程序后端
| www.tanbaiyan.com
| 觉得不错的话 记得在Github上点个star哦~
|
*/

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\System;
use Storage;
use GuzzleHttp;

class QrcodeController extends Controller
{
    /***************************************************************************
     *  此方法用于 判断是否已经保存用户太阳码 如果存在则直接取 没有就用官方接口获取
     *  还要判断存在数据库的access_token是否超时
     *  IF嵌套有点多 但没啥时间可以做优化...
     ***************************************************************************/
    public function index($token)
    {
        $user_res = User::where('token', $token)->first();
        if($user_res){
            //判断一下token是否到期
            $now = date('Y-m-d H:i:s');
            $token_time = date('Y-m-d H:i:s', $user_res->token_time);
            $overtime = date('Y-m-d H:i:s', strtotime("{$token_time} + 5 hours"));
            if($now > $overtime){
                return response()->json(['status' => 300, 'error' => 'Token Timeout'], 300);
            }else{
                //获取小程序太阳码
                $uid = $user_res->id;
                //判断太阳码文件是否存在
                $exists = Storage::disk('qrcode')->exists($uid.'.jpg');
                if($exists){
                    return redirect(config('app_url').'/qrcode/' . $uid . '.jpg');
                }else{
                	//不存在则需要调用官方接口进行生成太阳码并保存在服务器
                	$res = System::where('id', 1)->first();
                	if($res){
                		//数据库存在access_token 但需要判断是否超时
                		$overtime = date('Y-m-d H:i:s', strtotime("{$res->updated_at} + 2 hours"));
                		if($now > $overtime){
                			//此时超时 需要调用自定义函数重新获取access_token
                			$res = $this->getAccessToken();
                			if($res){
                				//此时数据库中的access_token正常能使用 则进行调用官方接口获取太阳码
                				$res = $this->qrcode($res, $uid);
                				if($res){
                                    //保存图片成功
                					return redirect(config('app_url').'/qrcode/' . $uid . '.jpg');
                				}
                			}
                		}else{
                			//此时没超时 可正常使用access_token
                			$res = $this->qrcode($res, $uid);
            				if($res){
                                //保存图片成功
            					return redirect(config('app_url').'/qrcode/' . $uid . '.jpg');
            				}
                		}
                	}else{
                		//数据库不存在access_token 则需要调用官方接口获取access_token
                		$res = $this->getAccessToken(1);
                		if($res){
                			//成功存入access_token 但还需要调用官方接口获取太阳码
                			$res = $this->qrcode($res, $uid);
                			if($res){
            					return redirect(config('app_url').'/qrcode/' . $uid . '.jpg');
            				}
                		}else{
                			return response()->json(['status' => 500, 'error' => 'Server exception'], 500);
                		}
                	}
                }
            }
        }else{
            return response()->json(['status' => 400, 'error' => 'The user does not exist'], 400);
        }
    }


    /**************************************************
     *  调用官方接口获取新的access_token并存入数据库
     **************************************************/
    public function getAccessToken($status = 0)
    {
    	$appid = getenv('MINI_APPID');
        $secret = getenv('MINI_APPSECRET');
		$api_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
		$data = file_get_contents($api_url);
		$data = json_decode($data);
        if($status == 1){
            $system = new System;
            $system->access_token = $data->access_token;
            $res = $system->save();
        }else{
            $res = System::where('id', 1)->update(['access_token' => $data->access_token]);
        }
            
		if($res){
			return $data->access_token;
		}else{
			return false;
		}
    }

    /************************************
     *  调用官方接口获得太阳码并存储
     ************************************/
    public function qrcode($access_token, $uid)
    {
        $api_url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token->access_token;
        $http = new GuzzleHttp\Client;

        $data = [
            'page' => 'pages/confession/confession',
            'scene' => $uid,
            'is_hyaline' => true
        ];

        $data = json_encode($data);

        $response = $http->post($api_url, [
            'body' => $data
        ]);

        $res = $response->getBody();
        if(json_decode($res)!=null){
            return false;
        }

        //存储图片
        $res = Storage::disk('qrcode')->put($uid.'.jpg', $res);
        if($res){
            return true;
        }else{
            return false;
        }
        
    }
}
