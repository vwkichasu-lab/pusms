<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pentecost University Scholarship Management System</title>
    <link rel="icon" href="{{ asset('images/pentvars-3d.png') }}">
    <style>
        :root {
            --blue: #053a82;
            --gold: #f3b51b;
            --line: #dbe3ee;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background: #ffffff;
            color: #020617;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .loader {
            width: min(520px, calc(100vw - 48px));
            display: grid;
            justify-items: center;
            gap: 22px;
            text-align: center;
        }

        .logo-stage {
            width: 190px;
            height: 190px;
            position: relative;
            display: grid;
            place-items: center;
        }

        .logo-ring {
            position: absolute;
            inset: 8px;
            border: 2px solid var(--line);
            border-radius: 28px;
            animation: ringFade 2.8s ease forwards;
        }

        .logo-piece {
            width: 150px;
            filter: drop-shadow(0 12px 22px rgba(15, 23, 42, .16));
            animation: assemble 2.8s cubic-bezier(.16, 1, .3, 1) forwards;
            transform-origin: center;
        }

        .spark {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--gold);
            opacity: 0;
            animation: spark 2.8s ease forwards;
        }

        .spark.one {
            top: 16px;
            left: 44px;
        }

        .spark.two {
            right: 30px;
            top: 58px;
            animation-delay: .25s;
        }

        .spark.three {
            left: 28px;
            bottom: 44px;
            animation-delay: .45s;
        }

        .system-name {
            opacity: 0;
            transform: translateY(10px);
            animation: titleIn .75s ease 2.2s forwards;
        }

        .system-name h1 {
            margin: 0;
            color: var(--blue);
            font-size: clamp(1.35rem, 4vw, 2rem);
            line-height: 1.15;
            font-weight: 900;
        }

        .bar {
            width: min(320px, 80vw);
            height: 8px;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #f8fafc;
            opacity: 0;
            animation: titleIn .4s ease 2.45s forwards;
        }

        .bar span {
            display: block;
            height: 100%;
            width: 0;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--blue), var(--gold));
            animation: loadBar 1.2s ease 2.45s forwards;
        }

        .start-button {
            opacity: 0;
            transform: translateY(10px);
            animation: titleIn .75s ease 3s forwards;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
            height: 48px;
            padding: 0 22px;
            border-radius: 8px;
            background: var(--blue);
            color: #ffffff;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 10px 24px rgba(5, 58, 130, .22);
        }

        .start-button:focus-visible {
            outline: 3px solid var(--gold);
            outline-offset: 3px;
        }

        @keyframes assemble {
            0% {
                opacity: 0;
                transform: translateY(42px) scale(.62) rotate(-7deg);
                clip-path: inset(76% 24% 0 24%);
            }
            40% {
                opacity: 1;
                transform: translateY(10px) scale(.82) rotate(0deg);
                clip-path: inset(38% 12% 0 12%);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1) rotate(0deg);
                clip-path: inset(0 0 0 0);
            }
        }

        @keyframes ringFade {
            0% {
                opacity: 0;
                transform: scale(.72);
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: scale(1.08);
            }
        }

        @keyframes spark {
            0%, 24% {
                opacity: 0;
                transform: scale(.2);
            }
            36% {
                opacity: 1;
                transform: scale(1);
            }
            100% {
                opacity: 0;
                transform: scale(.4) translateY(-18px);
            }
        }

        @keyframes titleIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes loadBar {
            to {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="loader" aria-label="Loading Pentecost University Scholarship Management System">
        <div class="logo-stage" aria-hidden="true">
            <div class="logo-ring"></div>
            <span class="spark one"></span>
            <span class="spark two"></span>
            <span class="spark three"></span>
            <img class="logo-piece" src="{{ asset('images/pentvars-3d.png') }}" alt="">
        </div>

        <section class="system-name">
            <h1>Scholarship In Pentecost University</h1>
        </section>

        <div class="bar" aria-hidden="true">
            <span></span>
        </div>

        <a class="start-button" href="/admin/login">Get Started</a>
    </main>
</body>
</html>
