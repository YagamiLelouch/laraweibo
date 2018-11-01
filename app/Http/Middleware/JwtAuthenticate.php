<?php

namespace App\Http\Middleware;

use Redis2;
use Closure;
use \Firebase\JWT\JWT;
use App\Model\User;
//use Illuminate\Support\Facades\Redirect;
class JwtAuthenticate
{
    public $encodeToken;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //判断token是否存在
        if(!$this->checkToken()) {

                $request->noToken =[
                    'meta'=>[
                        'code'=>401,
                        'message'=>'Unauthorized',
                    ]
                ];
        }
        $request->jwt = [
            'encode'=>$this->encodeToken,
            'decode'=>$this->checkToken(),
        ];

        return $next($request);
    }



    /**
     * 验证token 是否过期，是否正常
     *
     * @return false|object
     */
    public function checkToken() {
       //两种方式解析token,一个是http头，一个是传参数,返回解析后的token
        if(! $decodeToken = $this->parseToken($header = 'authorization', $method = 'bearer', $query = 'ZLINK_TK')) {
            return false;
        }
        return $decodeToken;

    }

    /**
     * 两种方式解析token,一个是http头，一个是传参数,返回解析后的token
     *
     * @param string $header
     * @param string $method
     * @param string $query
     *
     * @return false|object
     */
    public function parseToken($header = 'authorization', $method = 'bearer', $query = 'ZLINK_TK')
    {

        //通过http头解析token
        if (! $token = $this->parseAuthHeader($header, $method)) {
            //从input中取出token
            if (! $token = \Request::input($query)) {
                //从cookie中取出token
                if(! $token = \Request::cookie($query)) {
//                    \Log::error(__METHOD__.'=>token error or undefinded');
                    return false;
                }
            }
        }
        //通过"."分割token
        if (count(explode('.', $token)) !== 3) {
//            \Log::error(__METHOD__.'=>Wrong number of segments');
            return false;

        }
        //读取redis里面的jwt是否有token
        if(User::isJwtExists($token) != 1) {
//            \Log::error(__METHOD__.'=>token not exists or expire');
            return false;
        }
        $this->encodeToken = $token;

        //解码token
        $decodeToken = (array)JWT::decode($token, env('JWT_KEY', ''), array('HS256'));
        //print_r($decodeToken);
        return $decodeToken;
    }

    /**
     * Parse token from the authorization header.
     *
     * @param string $header
     * @param string $method
     *
     * @return false|string
     */
    protected function parseAuthHeader($header = 'authorization', $method = 'bearer')
    {
        //请求header文件里面是否有authorization
        $header = \Request::header($header);
        //starts_with 函数判断字符串开头是否为指定内容
        //判断头文件authorization的开头是否为bearer
        if (! starts_with(strtolower($header), $method)) {
            return false;
        }

        //该函数返回一个字符串或者数组。该字符串或数组是将 subject 中全部的 search 都被 replace 替换之后的结果。类似于正则
        return trim(str_ireplace($method, '', $header));
    }









}
