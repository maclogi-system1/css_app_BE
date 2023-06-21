<?php

namespace App\Models;

use App\Support\Traits\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqAccounting extends Model
{
    use HasFactory, HasCompositePrimaryKey;

    protected $table = 'mq_accounting';
    protected $fillable = [
        'store_id',
        'year',
        'month',
        'mq_kpi_id',
        'mq_access_num_id',
        'mq_ad_sales_amnt_id',
        'mq_user_trends_id',
        'mq_cost_id',
        'ltv_2y_amnt',
        'lim_cpa',
        'cpo_via_ad',
        'create_at',
        'updated_at',
    ];

    /**
     * @var array
     */
    protected $primaryKey = ['store_id', 'year', 'month'];

    public $incrementing = false;

    public function mqKpi()
    {
        return $this->belongsTo(MqKpi::class);
    }

    public function mqAccessNum()
    {
        return $this->belongsTo(MqAccessNum::class);
    }

    public function mqAdSalesAmnt()
    {
        return $this->belongsTo(MqAdSalesAmnt::class);
    }

    public function mqUserTrends()
    {
        return $this->belongsTo(MqUserTrend::class, 'mq_user_trends_id');
    }

    public function mqCost()
    {
        return $this->belongsTo(MqCost::class);
    }
}
