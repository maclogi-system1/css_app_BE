<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

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
                font-family: Consolas, monaco, monospace;
            }
            .container {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100vw;
                height: 100vh;
                flex-direction: column;
                color: #fff;
                text-shadow: #d1d5db 1px 1px 8px;
            }
            .app-name {
                font-size: 3rem;
                text-transform: uppercase;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <p>Welcome</p>
            <p class="app-name">{{ config('app.name') }}</p>
        </div>
    </body>
</html>
