<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $guarded = array('id');

    // 以下を追記
    public static $rules = array(
        'fname' => 'required',
        'n_child' => 'required',
    );
}
