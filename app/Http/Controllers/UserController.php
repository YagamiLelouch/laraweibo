<?php

namespace App\Http\Controllers;

use App\Model\Articles;
use Validator;
use App\Model\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Model\User;
use Redis;
use Jenssegers\Date\Date;
use \Firebase\JWT\JWT;
use App\Events\SignInEvent;
use App\Events\LogEvent;
use App\Jobs\SendEmail;
use Cookie;
use Agent;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt', ['only' => ['profile', 'edit', 'changePassword', 'collection']]);
    }

    /**
     * 显示主页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
     public function profile(Request $request)
     {
         $id = $request->jwt['decode']['data']->user_id;
//         $id= json_decode(Cookie::get('ZLINK_USERINFO'))->user_id;
         $info = User::userInfo($id);


         return view('user.profile',compact('info'));
     }

    /**修改资料
     * @param Request $request
     * @return array
     */
     public function edit(Request $request)
     {
         $params=$request->only('username','email','phone');
         $params['user_id']= $request->jwt['decode']['data']->user_id;
//         $params['user_id']= 1;
         $data=User::edit($params);
         if($data){
             return $this->returnCode(200,'');
         }else{
             return $this->returnCode(500,'');
         }
     }

     /**
      * 显示密码修改页
      */
     public function password(Request $request)
     {
         return view('user.change_password');
     }

     /**
      * 修改密码
      */
     public function changePassword(Request $request)
     {
         $params=$request->only('password','newpassword');
         $params['user_id']= $request->jwt['decode']['data']->user_id;
         //查询用户原密码是否正确
         $user=User::checkPassword($params['user_id'],$params['password']);
         if($user){
             //修改密码
             $data=User::changePassword($params['user_id'],$params['newpassword']);
             if($data){
                 return $this->returnCode('200','密码修改成功');
             }else{
                 return $this->returnCode('500','密码修改失败');
             }

         }else{
             return $this->returnCode(401,'密码错误');
         }


     }

    /**收藏页面展示
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function collection(Request $request)
    {

        //获取jwt的加密和解密
//        $jwt = $this->getJwtDecode();
        //通过jwt得到user_id
        $user_id = $request->jwt['decode']['data']->user_id;
        //通过用户id获取用户的收藏信息
        $coll=Collection::getCollection($user_id);
        if($coll){
            foreach($coll as $v){
                $article_id[] = $v['article_id'];
            }
            $collections=array_flip($article_id);
        }else{
            //获取用户收藏文章id的信息
            $collections=[];
        }

        //通过article 的id获取内容
        $contents = Articles::colContent($article_id);


        return view('user.collection',compact('contents','collections'));
    }

    /**
     * 获取JWT
     */
    public function getJwtDecode()
    {
        //获取token
        $params['token'] = Cookie::get('ZLINK_TK');
        //退出操作
        $token = $params['token'];
        //读取redis里面的jwt是否有token
        if(User::isJwtExists($token) != 1) {
//            \Log::error(__METHOD__.'=>token not exists or expire');
            return false;
        }

        $Jwt2['encode'] = $token;

        //解码token
        $Jwt2['decode'] = (array)JWT::decode($token, env('JWT_KEY', ''), array('HS256'));

        return $Jwt2;
    }

}
