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
        'create_all_user_info' => '全ユーザーの登録',
        'create_company_user_info' => '　所属企業のユーザー登録',
        'view_all_user_info' => 'ユーザー一覧',
        'view_company_user_info' => '所属企業のユーザー一覧',
        'view_my_user_info' => '自分一覧',
        'edit_all_user_info' => '全てのユーザー情報の編集"',
        'edit_company_user_info' => '企業に所属するユーザー情報の編集',
        'edit_my_user_info' => '自分のユーザー情報の編集',
        'delete_all_user_info' => '全 ユーザー削除',
        'delete_company_user_info' => '所属企業のユーザー削除',

        'view_roles' => '権限確認',
        'create_roles' => '権限登録',
        'edit_roles' => 'ロールの編集',
        'delete_roles' => '権限削除',

        'view_all_shops' => '全店舗 一覧',
        'view_all_company_shops' => '所属企業の全店舗 一覧',
        'view_company_contract_shops' => '所属企業の契約中店舗一覧', // Only shops under contract with the company you belong to
        'view_shops' => '自身の担当店舗', // Only your own shop
        'create_all_shops' => '全店舗の登録',
        'create_all_company_shops' => '全所属企業の店舗登録',
        'edit_all_shops' => '全ての店舗の編集可能',
        'edit_all_company_shops' => '企業に所属する店舗を編集可能',
        'edit_shops' => ' 編集権限のある店舗を編集可能',
        'delete_all_shops' => '全店舗の削除',
        'delete_all_company_shops' => '全所属企業の店舗削除',

        'edit_owned_company' => '所属する企業の編集',
        'edit_all_companies' => '全ての企業の編集',
        'create_companies' => 'create_companies',
        'delete_companies' => 'delete_companies',
        'view_companies' => 'view_companies',

        'mq_accountings' => 'MQ会計機能',
        'kpi_dashboard' => 'KPIダッシュボード',
        'policy_management' => '施策管理',
        'ai_policy_simulation' => 'AI施策シミュレーション',

        'view_all_macros' => 'マクロ機全店舗 一覧', // All shop
        'view_all_company_macros' => 'view_all_company_macros', // All company shops
        'view_macros' => 'view_macros', // Only your own shop
        'create_all_macros' => '全店舗マクロ新規作成',
        'edit_all_macros' => '全マクロの編集',
        'edit_all_company_macros' => 'edit_all_company_macros',
        'edit_macros' => 'マクロの編集',
        'delete_all_macros' => '全マクロの削除',
        'delete_all_company_macros' => 'delete_all_company_macros',
        'delete_macros' => 'マクロの削除',
    ];
}
