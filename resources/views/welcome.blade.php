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
                font-family: Consolas, monaco, monospace;
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
