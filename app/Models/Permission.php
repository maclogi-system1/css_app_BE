<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_name',
    ];

    protected $hidden = [
        'pivot',
    ];

    public const PERMISSION_SEED = [
        'edit_shops' => ' 編集権限のある店舗を編集可能',
        'edit_all_company_shops' => '企業に所属する店舗を編集可能',
        'edit_all_shops' => '全ての店舗の編集可能',
        'edit_owned_company' => '所属する企業の編集',
        'edit_all_companies' => '全ての企業の編集',
        'edit_roles' => 'ロールの編集',
        'edit_my_user_info' => '自分のユーザー情報の編集',
        'edit_company_user_info' => '企業に所属するユーザー情報の編集',
        'edit_all_user_info' => '全てのユーザー情報の編集"',
    ];
}
