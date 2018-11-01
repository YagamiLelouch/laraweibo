<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $table='collections';

    protected $primaryKey='article_id,user_id';

    public $timestamps=false;

    protected $fillable=[
        'article_id',
        'user_id'
    ];

    /**添加收藏
     * @param $user_id
     * @param $article_id
     * @return static
     */
    public static function add($user_id,$article_id)
    {
        return Collection::create([
           'article_id'=>$article_id,
            'user_id'=>$user_id
            ]);
    }

    /**
     * 获取收藏
     */

    public static function getCollection($user_id)
    {
        $data=Collection::where('user_id',$user_id)->get();
        return $data->toArray();
    }

    /**
     * 取消收藏
     */
    public static function deleteCollection($user_id,$article_id)
    {
        $user=Collection::where('user_id',$user_id)->where('article_id',$article_id);
        return $user->delete();
    }


}
