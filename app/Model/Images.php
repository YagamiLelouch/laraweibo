<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Images extends Model
{
    /**
     * 关联到模型的数据表.
     *
     * @var string
     */
    protected $table = 'images';

    /**
     * 关联到模型的数据表的主键.
     *
     * @var string
     */
    protected $primaryKey = 'image_id';

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    public function article()
    {
        return $this->belongsTo('App\Model\Articles', 'article_id', 'article_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image_id',
        'article_id',
        'smallImage',
        'bigImage',
        'image'
    ];

    public static function store($small, $big, $file, $aid)
    {
        $image = new Images;
        $image->smallImage = $small;
        $image->bigImage = $big;
        $image->image = $file;
        $image->article_id = $aid;
        if ($image->save()) {
            return 1;
        } else {
            return 0;
        }
    }
}
