<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    public const ONESIGNAL_USER_ID_KEY = 'onesignal_user_id'; // This field is the player_id of the notification receiving device registered in onesignal.
    public const RECEIVATION_KEY = 'receivation'; // enable, disable, specific

    public const DISABLE_RECEIVATION = 0;
    public const ENABLE_RECEIVATION = 1;
    public const SPECIFIC_RECEIVATION = 2;

    public const RECEIVING_STATES = [
        self::DISABLE_RECEIVATION => 'disable',
        self::ENABLE_RECEIVATION => 'enable',
        self::SPECIFIC_RECEIVATION => 'specific',
    ];
}
