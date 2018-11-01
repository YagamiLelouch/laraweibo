<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $table = "field";

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'c_field',
        'e_field'
    ];

    public static function getField($table_id)
    {
        $data = Field::where('table_id', $table_id)->get();
        return $data->toArray();
    }
}
