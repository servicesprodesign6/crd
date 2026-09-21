<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="title" content="{{ getAppName() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.vcard.visitor_limit_reached') }} | {{ getAppName() }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
            overflow: hidden;
        }

        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.08);
            animation: float 6s ease-in-out infinite;
        }

        .circle1 { width: 120px; height: 120px; top: 10%; left: 15%; }
        .circle2 { width: 80px; height: 80px; bottom: 15%; right: 20%; }
        .circle3 { width: 60px; height: 60px; bottom: 25%; left: 30%; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        .card {
            position: relative;
            background: #ffffff;
            padding: 60px 50px;
            border-radius: 20px;
            text-align: center;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
            z-index: 10;
        }

        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: 600;
            color: #111827;
        }

        .desc {
            margin-top: 12px;
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
        }

        .btn-custom {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            font-size: 15px;
            font-weight: 500;
            color: #fff;
            background: linear-gradient(90deg, #4f46e5, #3b82f6);
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
            transition: 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>
<body>

    <div class="bg-circle circle1"></div>
    <div class="bg-circle circle2"></div>
    <div class="bg-circle circle3"></div>

    <div class="card">
        <div class="icon">⚠️</div>
        <div class="title">{{ __('messages.vcard.visitor_limit_reached') }}</div>
        <div class="desc">
            {{ __('messages.vcard.visitor_limit_exceeded') }}
        </div>

        <a href="/" class="btn-custom">{{ __('messages.vcard.go_to_home') }}</a>
    </div>

</body>
</html>
