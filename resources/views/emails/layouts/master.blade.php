<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <style>
        body { margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f5f7; color: #333333; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f5f7; padding-bottom: 40px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background-color: #ffffff; padding: 20px 30px; text-align: center; border-bottom: 2px solid #f4f5f7; }
        .header img { max-width: 150px; }
        .content { padding: 30px; line-height: 1.6; font-size: 16px; }
        .footer { background-color: #f4f5f7; text-align: center; padding: 20px 30px; font-size: 12px; color: #888888; }
        .footer a { color: #0044cc; text-decoration: none; }
        .btn { display: inline-block; background-color: #0044cc; color: #ffffff !important; text-decoration: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        h1, h2, h3 { color: #111111; margin-top: 0; }
    </style>
</head>
<body>
    <center class="wrapper">
        <table class="main" width="100%">
            <!-- Header -->
            <tr>
                <td class="header">
                    <h2 style="margin:0; color:#0044cc;">{{ config('app.name') }}</h2>
                </td>
            </tr>
            <!-- Body Content -->
            <tr>
                <td class="content">
                    @yield('content')
                </td>
            </tr>
            <!-- Footer -->
            <tr>
                <td class="footer">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                    <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
