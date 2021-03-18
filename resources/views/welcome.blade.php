<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MESone</title>
    <style>
        * {
            box-sizing: border-box;
            border: 0 solid #e2e8f0;
        }

        html {
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
        }

        body {
            position: relative;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            --bg-opacity: 1;
            background-color: #f7fafc;
            background-color: rgba(247, 250, 252, var(--bg-opacity));
        }

        svg {
            width: 200px;
            height: 175px;
        }

        h1{
            text-align: center;
        }

        .wrapper {
            display: flex;
            justify-content: center;
            flex-direction: column;
        }

        .version {
            text-align: center;
            --text-opacity: 1;
            color: #a0aec0;
            color: rgba(160, 174, 192, var(--text-opacity))
        }
    </style>
</head>
<body>
<div class="wrapper">
    <svg viewBox="0 0 200 175" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            fill="#f00"
            d="M112.563 21.9849L100 0L0 175H25.1256L49.2063 132.859C51.881 128.178 56.895 125.284 62.3297 125.284H171.591L163.068 110.369H62.0574L112.563 21.9849Z"
        />
        <path
            fill="#f00"
            d="M66.0234 146.779C68.698 142.099 73.7121 139.205 79.1468 139.205H179.545L200 175H49.8972L66.0234 146.779Z"
        />
        <path
            fill="#000"
            d="M154.545 95.4546L121.996 38.4936L89.4473 95.4546H154.545Z"
        />
    </svg>
    <h1>MESone API</h1>
    <div class="version">Version: {{ config('mesone.version') }}</div>
</div>
</body>
</html>
