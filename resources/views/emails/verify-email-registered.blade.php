<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <p>このメールは、マクロジに新しいメールアドレスを登録されている方へ、ご本人さま確認のため自動的にお送りしています。<br/>
        下記の認証URLを24時間以内にクリックして、メールアドレスの認証を完了してください。</p>
    <p>****************************************</p>
    <p>◆認証URL◆</p>
    <a href="{{ $url }}">{{ $url }}</a>
    <p>****************************************</p>
    <p>ログイン情報：<br/>
    企業ID： {{ $user->company->company_id }}<br/>
    メールアドレス： {{ $user->email }}<br/>
    @isset ($password)
    パスワード： {{ $password }}</p>
    @endisset
</body>
</html>
