<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ECOM DASHBOARD | Login</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/login') }}/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/login') }}/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/login') }}/css/iofrm-style.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/login') }}/css/iofrm-theme29.css">
    <style>
        .text-logo {
            color: #fff;
            font-weight: 700;
            font-size: 32px;
            letter-spacing: 2px;
            text-decoration: none;
            display: inline-block;
            padding: 10px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-left: -48px;
        }

        .text-logo:hover {
            color: #fff;
            text-decoration: none;
        }

        .text-logo i {
            margin-right: 10px;
            font-size: 28px;
        }

        .demo-box {
            margin-top: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #f4f7fb;
            border: 1px dashed #cfdae8;
            font-size: 12px;
            color: #38506d;
            line-height: 1.7;
        }

        .demo-box strong {
            color: #1a3a6b;
        }

        @media (max-width: 767px) {
            .text-logo {
                font-size: 24px;
                letter-spacing: 1.5px;
            }

            .text-logo i {
                font-size: 20px;
                margin-right: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="form-body without-side">
        <div class="website-logo">
            <a href="{{ route('home') }}">
                <span class="text-logo">
                    <i class="fas fa-store"></i> ECOM DASHBOARD
                </span>
            </a>
        </div>
        <div class="iofrm-layout">
            <div class="img-holder">
                <div class="bg"></div>
                <div class="info-holder">
                    <img src="{{ asset('assets/login') }}/images/graphic3.svg" alt="">
                </div>
            </div>
            <div class="form-holder">
                <div class="form-content">
                    <div class="form-items">
                        <h3>Login to account</h3>
                        <p>Manage shops, products and the shared catalog from one place.</p>

                        @if (session('alert'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('alert') }}
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('login') }}" method="post">
                            @csrf
                            <input class="form-control" type="email" name="email" value="{{ old('email') }}"
                                   placeholder="E-mail Address" required autofocus autocomplete="username">
                            <input class="form-control" type="password" name="password" placeholder="Password"
                                   required autocomplete="current-password">
                            <div class="form-button">
                                <button id="submit" type="submit" class="ibtn">Login</button>
                                <a href="{{ route('password.request') }}">Forget password?</a>
                            </div>
                        </form>

                        <div class="demo-box">
                            <strong>Demo accounts</strong><br>
                            Admin: <strong>admin@ecom.test</strong> / <strong>password</strong><br>
                            Shop owner: <strong>owner@ecom.test</strong> / <strong>password</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/login') }}/js/jquery.min.js"></script>
    <script src="{{ asset('assets/login') }}/js/popper.min.js"></script>
    <script src="{{ asset('assets/login') }}/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/login') }}/js/main.js"></script>
</body>

</html>
