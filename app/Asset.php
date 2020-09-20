<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $guarded = array('id');

    public static $rules = array(
        'familyid' => 'required',
        'familyname' => 'required',
        'iid' => 'required',
        'iname' => 'required',
        'category' => 'required',
        'kingaku' => 'required',
    );
}
