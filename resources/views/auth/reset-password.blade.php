<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-size: 16px;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            }
            .container {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100vw;
                height: 100vh;
                flex-direction: column;
                color: #d1d5db;
            }
            .mb-5 {
                margin-bottom: 1.25rem;
            }
            .mb-8 {
                margin-bottom: 2rem;
            }
            .text-center {
                text-align: center;
            }
            h1, h2 {
                display: block;
                width: 100%;
                text-align: center;
                margin: 0 auto 1.5rem auto;
                color: #3d3d40;
            }
            h1 {
                font-weight: 600;
            }
            h2 {
                font-weight: 500;
            }
            form {
                background: #fff;
                border-radius: 4px;
                padding: 6rem;
                max-width: 28rem;
                width: 100%;
                box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            }
            input {
                padding: .75rem 1.25rem;
                outline: none;
                width: 100%;
                border-radius: 4px;
                border: 1px solid #e4e4e7;
            }
            input:focus {
                outline: none;
            }
            button {
                padding: .5rem 1.5rem;
                background: #eef2f5;
                border: none;
                border-radius: 20px;
                cursor: pointer;
                color: #3d3d40;
            }
            button:hover {
                background: #dee1e4;
            }
            .error {
                font-size: .75rem;
                color: #f00;
                margin-top: .25rem;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />

                <h1>CSS</h1>

                <h2>{{ __('Reset Password') }}</h2>

                <div class="mb-5">
                    <input type="email" name="email" placeholder="Email" />
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror()
                </div>

                <div class="mb-5">
                    <input type="password" name="password" placeholder="New password" />
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror()
                </div>

                <div class="mb-8">
                    <input type="password" name="password_confirmation" placeholder="Password confirmation" />
                </div>
                <div class="text-center">
                    <button type="submit">{{ __('Reset') }}</button>
                </div>
            </form>
        </div>
    </body>
</html>
