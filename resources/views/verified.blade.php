<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>JuztPoint - Point-of-Sale Repid Deployment with Progressive Web App Software</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('platform.login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ env('POS_BACKOFFICE_URL', 'backoffice.juztpoint.com') }}">BackOffice</a>
                    @else
                        <a href="{{ env('POS_BACKOFFICE_URL', 'backoffice.juztpoint.com') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ env('POS_BACKOFFICE_URL', 'backoffice.juztpoint.com') }}/register">Get Started</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    {{ config('app.name') }}
                </div>
                <div class="subtitle m-b-md">
                     Thank you! Your email have been successful verified.
                </div>

                <div class="links">

                    <a href="{{ env('POS_BACKOFFICE_URL', 'backoffice.juztpoint.com') }}/login">Proceed to Login</a>

                </div>

            </div>
        </div>

    </body>
</html>
