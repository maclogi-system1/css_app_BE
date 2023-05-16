<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attributeを受け入れなければなりません。',
    'accepted_if' => ':other は :value の場合は:attribute を受け入れなければなりません。',
    'active_url' => ':attribute は有効なURLではありません。',
    'after' => ':attributeは:dateの後の日時でなければなりません。',
    'after_or_equal' => ':attributeは:dateの後または同じのものでなければなりません。',
    'alpha' => ':attributeは文字のみでなければなりません。',
    'alpha_dash' => ':attribute は文字、数字、ダッシュ、アンダースコアのみでなければなりません。',
    'alpha_num' => ':attributeは文字、数字のみでなければなりません。',
    'array' => ':attributeはアレイでなければなりません。',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => ':attributeは:dateの前の日付でなければなりません。',
    'before_or_equal' => ':attributeは:dateの前または同じでなければなりません。',
    'between' => [
        'array' => ':attributeは:min と :max itemsの項目の間でなければなりません。',
        'file' => ':attributeは:min と :max kilobytesの間でなければなりません。',
        'numeric' => ':attributeは:min と :maxの間でなければなりません。',
        'string' => ' :attribute は :min と :max charactersの間でなければなりません。',
    ],
    'boolean' => ':attributeフィールドには true または falseしかありません。',
    'confirmed' => ':attribute は一致されていないと確認しました。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attribute は有効な日付ではありません.',
    'date_equals' => ':attribute は:dateと同じでなければなりません。',
    'date_format' => ':attributeは:formatのフォーマットと一致していなりません。',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => ':attributeが辞退しなければなりません。',
    'declined_if' => ':other は :valueの場合は:attributeが辞退しなければなりません。',
    'different' => ':attribute と :other は異にしなければなりません。',
    'digits' => ':attribute は:digits digitsにしなければなりません。',
    'digits_between' => ':attributeは:min と :max digitsの間でなければなりません。',
    'dimensions' => ':attribute は無効な画像サイズがあります。',
    'distinct' => ':attributeフィールドには重複する値があります。',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => ':attributeは有効なメールアドレスでなければなりません。',
    'ends_with' => ':attributeの終わりはいずれかの: :valuesにしなければなりません。',
    'enum' => '選択した:attributeは無効です。',
    'exists' => '選択した:attributeは無効です。',
    'file' => ':attributeはファイルしかできません。',
    'filled' => ':attributeフィールドには値を持たなければなりません。',
    'gt' => [
        'array' => ':attributeは:value itemsより持たなければなりません。',
        'file' => ':attributeは:value kilobytesより大きいなければなりません。',
        'numeric' => ' :attributeは:valueより大きいなければなりません。',
        'string' => ':attributeは:value charactersより大きいなければなりません。',
    ],
    'gte' => [
        'array' => ':attributeは:value itemsまたはより上がなければなりません。',
        'file' => ' :attributeは:value kilobytesより大きいまたは同じなものでなければなりません。 ',
        'numeric' => ':attributeは:valueより大きいまたは同じなものでなければなりません。',
        'string' => ':attributeは:value charactersより大きいまたは同じなものでなければなりません。',
    ],
    'image' => ':attributeは画像でなければなりません。',
    'in' => '選択した:attributeが無効です。',
    'in_array' => ':attributeフィールドは:otherに存在していません。',
    'integer' => ' :attributeは整数でなければなりません。',
    'ip' => ':attributeは有効なIPアドレスでなければなりません。',
    'ipv4' => ':attributeは有効なIPv4アドレスでなければなりません。',
    'ipv6' => ':attributeは有効なIPv6アドレスでなければなりません。',
    'json' => ':attributeは有効なJSON stringでなければなりません。',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => ':attributeは:value itemsより少ないものでなければなりません。',
        'file' => ':attributeは:value kilobytesより小さいものでなければなりません。',
        'numeric' => ':attributeは:valueよりちいさいものでなければなりません。',
        'string' => ':attributeは:value charactersより少ないものでなければなりません。',
    ],
    'lte' => [
        'array' => ':attributeは:value itemsより大きいものではありません。',
        'file' => ':attributeは:value kilobytesより小さいまたは同じなものでなければなりません。',
        'numeric' => ':attributeは:valueより少ないまたは同じなものでなければなりません。',
        'string' => ':attributeは:value charactersより少ないまたは同じなものでなければなりません。',
    ],
    'mac_address' => ':attributeは有効なMACアドレスでなければなりません。',
    'max' => [
        'array' => ':attributeは:max itemsより大きくないものでなければなりません。',
        'file' => ':attributeは:max kilobytesより大きくないものでなければなりません。',
        'numeric' => ':attributeは:maxより大きくないものでなければなりません。',
        'string' => ':attributeは :max charactersより大きくないものでなければなりません。',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => ':attributeは: :valuesの種類がなければなりません。',
    'mimetypes' => ':attributeは: :valuesの種類がなければなりません。',
    'min' => [
        'array' => ':attributeは項目の:minの一番小さいものでなければなりません。',
        'file' => ':attributeはmin kilobytesの一番小さいものでなければなりません。',
        'numeric' => ':attributeはleast :minの一番小さいものでなければなりません。',
        'string' => ':attributeは:min charactersの一番小さいものでなければなりません。',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => ':attributeは :valueの複数でなければなりません。',
    'not_in' => '選択した:attributeは無効です。',
    'not_regex' => ':attributeフォーマットは無効なフォーマットです。',
    'numeric' => ':attributeは数字でなければなりません。',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => ':attributeフィールドは現在でなければなりません。',
    'prohibited' => ':attributeフィールドは禁止されています',
    'prohibited_if' => ':other は :valueの場合は:attributeフィールドが禁止されています。',
    'prohibited_unless' => ':otherが:valuesにない限り、:attributeフィールドは禁止されています。',
    'prohibits' => ':attributeフィールドは:otherの存在を禁止します。',
    'regex' => ':attributeフォーマットは無効なフォーマットです。',
    'required' => '入力必須項目が未入力です。',
    'required_array_keys' => ':attributeフィールドは: :valuesにエントリーがふくまれています。',
    'required_if' => ':other は :valueの場合は:attributeフィールドが必須です。',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_unless' => ':other が :valuesにない限り、 :attributeフィールドは必須です。',
    'required_with' => ':valuesは現在の場合は :attributeフィールドが必須です。',
    'required_with_all' => ':valuesは現在の場合は :attributeフィールドが必須です。',
    'required_without' => ':valuesは現在ではないの場合は :attributeフィールドが必須です。',
    'required_without_all' => ':valuesが現在のものがないの場合は:attributeフィールドが必須です。',
    'same' => ':attributeと:other一致しなければなりません。',
    'size' => [
        'array' => ':attributeは:size itemsを含まれなければなりません。',
        'file' => ':attributeが:size kilobytesでなければなりません。',
        'numeric' => ':attributeは:sizeでなければなりません。',
        'string' => ':attributeは:size charactersでなければなりません。',
    ],
    'starts_with' => ':attributeの始めはいずれかの: :valuesにしなければなりません。',
    'string' => ':attributeはstringでなければなりません。',
    'timezone' => ':attributeは有効なタイムゾーンでなければなりません。',
    'unique' => ':attributeはすでに存在しています。',
    'uploaded' => ':attributeアップロードが失敗しました。',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => ':attributeは有効なURLでなければなりません。',
    'ulid' => ':attributeは有効なULIDでなければなりません。',
    'uuid' => ':attributeは有効なUUIDでなければなりません。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
