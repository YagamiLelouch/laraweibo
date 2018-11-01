<?php

namespace App\Http\Controllers;

use App\Model\Articles;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Model\Comment;

class CommentController extends Controller
{
    /**发表评论
     * @param Request $request
     */

   public function pComment(Request $request)
   {
       $params = $request->only('uid', 'content', 'aid');
       //数据写入评论表
       $data = Comment::pComment($params['uid'], $params['content'], $params['aid']);
       //article的评论数加1
       if($data) {
           $plusComment = Articles::plusComment($params['aid']);
       } else {
           return $this->returnCode('400');
       }

       if ($plusComment) {
           return $this->returnCode('200');
       } else {
           return $this->returnCode('400');
       }
   }

   /**
    * 动态显示评论内容
    */
   public function getComment(Request $request)
   {
       $params = $request->only('page', 'aid');
       //数据库读取评论和相关信息
       $data = Articles::getComment($params['aid']);

       $pages['page'] = $params['page'] ? $params['page'] : 1;
       $pages['limit'] = 5;
       $colData = collect($data);
       $pages['count'] = $colData->count();
       $pages['pages'] = ceil($pages['count']/$pages['limit']);
       //定义一次性显示的翻页数
       $pagesU['num'] = 3;

       $data = $colData->forPage($pages['page'], $pages['limit']);

       return json_encode($data);



   }
}
