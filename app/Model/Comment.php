<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * 关联到模型的数据表.
     */
    protected $table = 'comments';

    /**
     * 关联到模型的数据表的主键.
     * @var string
     */

    protected $primaryKey = 'comment_';

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'user_id',
        'article_id',
        'comment_content',
    ];

    public static function pComment($user_id, $content, $aid)
    {
        return Comment::create([
            'comment_content' => $content,
            'article_id' => $aid,
            'user_id' => $user_id
        ]);
    }

}
