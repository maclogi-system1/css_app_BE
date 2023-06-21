<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqAccessNum extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mq_access_num';

    protected $fillable = [
        'access_flow_sum',
        'search_flow_num',
        'ranking_flow_num',
        'instagram_flow_num',
        'google_flow_num',
        'cpc_num',
        'display_num',
    ];

    public $timestamps = false;
}
