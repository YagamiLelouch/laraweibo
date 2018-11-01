<?php

namespace App\Model;

use App\Article;
use Illuminate\Database\Eloquent\Model;
use DB;

class Articles extends Model
{
    /**
     * 关联到模型的数据表.
     */
    protected $table = 'articles';

    /**
     * 关联到模型的数据表的主键.
     * @var string
     */

    protected $primaryKey = 'article_id';

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
    public function user()
    {
        return $this->belongsTo('App\Model\User','user_id','user_id');
    }

    public function images()
    {
        return $this->hasMany('App\Model\Images', 'article_id', 'article_id');
    }

    public function comment()
    {
        return $this->hasMany('App\Model\Comment', 'article_id', 'article_id');
    }

    protected $fillable = [
        'article_id',
        'user_id',
        'content',
        'content_over',
    ];

    /**
     * 发布内容到数据库
     * @param $params
     * @return static
     */
    public static function publish($params)
    {
        return Articles::create([
            'content'=>$params['content'],
            'user_id'=>$params['user_id']
        ]);
    }

    /**
     * 读取数据库内容
     */
    public static function getArticles()
    {
//        return Articles::all()->user();
        $data=Articles::with(['user', 'images', 'comment'])->get();
        return $data->toArray();
    }

    /**
     * 收藏数增加1
     */
    public static function plus($article_id)
    {
        return DB::table('Articles')->where('article_id',$article_id)->increment('collection_count',1);
    }

    /**
     * 收藏数减少1
     */
    public static function minus($article_id)
    {
        return DB::table('Articles')->where('article_id',$article_id)->decrement('collection_count',1);
    }

    /**
     * 通过文章id获取到文章内容和用户名
     *
     */

    public static function colContent($article_id)
    {
       $data = Articles::whereIn('article_id',$article_id)->with('user')->get();
       return $data->toArray();
    }


   /**
    * 通过username获取用户content信息
    */
//   public static function userContent($username)
//   {
//       $data = Articles::with(['user' => function($query) use($username){
//           $query->where('username',$username);
//       }])->get();
//
//       $data = $data->toArray();
//
//
//   }

    /**
     * 通过用户id获取文章内容
     */

    public static function userContent($id)
    {
        $data = Articles::where('user_id', $id)->get();
        return $data->toArray();
    }

    /**
     * 通过关键词q获取到文章内容并通过关联user表获取用户信息
     */
    public static function searchAll($q)
    {
//        $data = Articles::where('content', 'like', '%q%')->get();
//        dd($data->toArray());
        $data = DB::table('articles')
            ->join('user_info', 'articles.user_id', '=', 'user_info.user_id')
            ->where('articles.content', 'like', '%'.$q.'%')
            ->select('articles.*', 'user_info.username', 'user_info.user_id')
            ->get();
      $contents = json_decode(json_encode($data), true);
      return $contents;
    }

    public static function getComment($aid)
    {

        $data = DB::table('articles')
            ->select('comments.comment_content', 'user_info.username', 'comments.ctime')
            ->join('comments', 'articles.article_id', '=', 'comments.article_id')
            ->join('user_info', 'comments.user_id', '=', 'user_info.user_id')
            ->where('articles.article_id', '=', $aid)
            ->get();
        return json_decode(json_encode($data), true);

    }

    //评论+1
    public static function plusComment($aid)
    {
        return DB::table('Articles')->where('article_id',$aid)->increment('comment_count',1);
    }


}
