<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkedUserInfo extends Model
{
    protected $table = 'linked_service_user_info';

    protected $fillable = [
        'user_id', 'service_id', 'linked_service_user_id',
    ];
}
