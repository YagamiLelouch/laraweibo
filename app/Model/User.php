<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Redis;
use Cookie;
use GuzzleHttp\Client;
class User extends Model
{
    /**
     * 关联到模型的数据表.
     *
     * @var string
     */
    protected $table = 'user_info';

    /**
     * 关联到模型的数据表的主键.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'username',
        'password',
        'phone',
        'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];
    /**
     * 用户注册,将注册信息写入数据库
     * @param  [type] $params [description]
     * @return [type]         [description]
     */

//    public function __construct()
//    {
//
//        parent::__construct();
//        $this->middleware('jwt', ['only' => ['islogin']]);
//    }
    public function articles()
    {
        return $this->hasMany('App\Model\Articles','user_id','user_id');
    }

    /**
     * 退出登录，清理jwt
     * @param  string $key jwt
     * @return [type]      [description]
     */
    public static function delJwt($key)
    {
        //strtoupper — 将字符串转化为大写
        $key = 'apollo:jwt:md5:'.strtoupper(md5($key));
        //读取
        $data = Redis::connection('read')->hGetAll($key);
        return $data;
        $key2 = 'apollo:jwt:uid:'.$data['uid'].':iat:'.$data['iat'];
        Redis::connection('write')->del($key2);
        $r = Redis::connection('write')->del($key);
        return $r;
    }

    /**
     * 判断jwt是否存在可用
     * @param  [type]  $key [description]
     * @return boolean      [description]
     */
    public static function isJwtExists($key)
    {
        $key = 'apollo:jwt:md5:'.strtoupper(md5($key));
        return Redis::connection('write')->exists($key);
    }


    /**注册
     * @param $params
     * @return array
     */
    public static function signUp($params)
    {
        //判断是手机注册还是邮箱注册.邮箱和手机注册所需要的注册信息不同
        switch ($params['method']) {
            case 'email':
                User::create([
                    'username' => $params['username'],
                    'email' => $params['email'],
                    'password' => $params['password'],
                ]);
                return User::where('username', $params['username'])
                    ->first();
                break;
            case 'phone':
                User::create([
                    'username' => $params['username'],
                    'phone' => $params['phone'],
                    'password' => $params['password'],
                ]);
                return User::where('username', $params['username'])
                    ->first();
                break;
            default:
                return [
                    'meta'=>[
                        'code'=>'500',
                        'message'=>'注册失败',
                    ]
                ];
                break;
        }
    }

    /**
     * 登陆成功把jwt存入redis
     * @param string $key
     * @param int $expire 多少秒后过期
     * @param string $value
     */
    public static function setJwt($key, $expire, $values)
    {
        //$key为jwt加密后的数据
        //将值 value 关联到 key ，并将 key 的生存时间设为 seconds (以秒为单位)。
        Redis::setex('apollo:jwt:uid:'.$values['uid'].':iat:'.$values['iat'], $expire, $key);//把uid与jwt的对应关系存入redis
        //设置key的位置并再加密key
        $key = 'apollo:jwt:md5:'.strtoupper(md5($key));
        //写入设置key
        $r = Redis::connection('write')->hMset($key, $values);
        //为给定 key 设置生存时间，当 key 过期时(生存时间为 0 )，它会被自动删除.成功为1,失败为0
        return Redis::connection('write')->expire($key, $expire);
    }

    public static function login($params)
    {
        switch ($params['method']) {
            case 'name':
                //验证用户名\密码\isvalid
                return User::where('username', $params['account'])
                    ->where('password', $params['password'])
                    ->first();
                break;
            case 'email':
                return User::where('email', $params['account'])
                    ->where('password', $params['password'])
                    ->first();
                break;
            case 'phone':
                return User::where('phone', $params['account'])
                    ->where('password', $params['password'])
                    ->first();
                break;
            default:
                return null;
                break;
        }
    }


    /**
     * 判断是否登录
     * @return [bool]         [description]
     */
    public static function isLogin(){
        $params = [
            //获取token
            'token' => Cookie::get(env('ZLINK_TK', 'ZLINK_TK')),
            //time — 返回当前的 Unix 时间戳
            'timestamp' => time()
        ];
        //token不存在,返回false
        if (!$params['token']) {
            return false;
        }

        return 1;
    }

    public static function userInfo($user_id)
    {
        $data=User::where('user_id',$user_id)->get();
       return $data->toArray();
    }

    public static function edit($params)
    {
        $user =  User::where('user_id', $params['user_id'])->first();
        $user->username=$params['username'];
        $user->email=$params['email'];
        $user->phone=$params['phone'];
        $data=$user->save();
        return $data;
    }

    /**
     * 用户原密码是否正确
     */
    public static function checkPassword($user_id,$password)
    {
        return User::select('user_id')
            ->where('user_id',$user_id)
            ->where('password',md5($password))
            ->first();
    }

    /**修改密码
     * @param $user_id
     * @param $newpassword
     * @return mixed
     */
    public static function changePassword($user_id,$newpassword)
    {
        $user=User::where('user_id',$user_id)->first();
        $user->password=md5($newpassword);
        $data=$user->save();
        return $data;
    }

    /**
     * 重置密码
     */

    public static function resetPassword($params)
    {
        if (isset($params['email']) && $params['email']) {
            $user =  User::where('email', $params['email'])->first();
        } else {
            $user =  User::where('phone', $params['phone'])->first();
        }
        //psd为数据库的密码字段
        $user->password = md5($params['password']);
        $user->save();
        return $user;
    }

//    /**
//     * 通过文章id获取到文章内容和用户名
//     *
//     */
//
//    public static function colContent($article_id)
//    {
//        $data = User::with(['articles' => function ($query) use($article_id) {
//            $query->whereIn('article_id',[1,8]);
//    }])->get();
//        dd($data->toArray());
//    }



    /*
     * 通过字段获取id
     */

    public static function getId($username)
    {
        $data = User::where('username',$username)->first();
        return $data['user_id'];

    }

    public static function allInfo()
    {
        $data = User::all();
        return $data->toArray();
    }

//    /**
//     * 通过关键词q获取到文章内容并通过关联user表获取用户信息
//     */
//    public static function searchAll($q)
//    {
//        $data = User::with(['articles' => function ($query) use ($q) {
//            $query->where('content', 'like', '%'.$q.'%');
//    }])->get();
//        dd($data->toArray());
//    }




}
