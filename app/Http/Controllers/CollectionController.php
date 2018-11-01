<?php

namespace App\Http\Controllers;

use App\Model\Articles;
use App\Model\Collection;
use Illuminate\Http\Request;
use Validator;
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

class CollectionController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt', ['only' => ['addCollection', 'deleteCollection']]);
    }

    /**添加收藏
     * @param Request $request
     * @return array
     */
    public function addCollection(Request $request)
    {
        $params=$request->only('article_id');
        $params['user_id']= $request->jwt['decode']['data']->user_id;
        //收藏信息写入收藏表
        $data=Collection::add($params['user_id'],$params['article_id']);
        if($data){
            $data1=Articles::plus($params['article_id']);
            if($data1){
                return $this->returnCode(200,'add successfully');
            }else{
                return $this->returnCode(500,'fail');
            }
        }else{
            return $this->returnCode(500,'fail');
        }
    }

    /**
     * 取消收藏
     */
    public function deleteCollection(Request $request)
    {
        $params=$request->only('article_id');
        $params['user_id']= $request->jwt['decode']['data']->user_id;
        //收藏信息从收藏表删除
        $data=Collection::deleteCollection($params['user_id'],$params['article_id']);
        if($data){
            $data1=Articles::minus($params['article_id']);
            if($data1){
                return $this->returnCode(200,'add successfully');
            }else{
                return $this->returnCode(500,'fail');
            }
        }else{
            return $this->returnCode(500,'fail');
        }
    }

}
