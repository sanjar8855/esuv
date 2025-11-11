<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Login</title>

    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-flags.min.css') }}">
</head>
<body class=" d-flex flex-column">
<div class="page">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <a href="." class="navbar-brand navbar-brand-autodark">
                <img src="{{ asset('tabler/img/logo/dark-blue.png') }}" alt="" width="120">
            </a>
        </div>
        <div class="card card-md">
            <div class="card-body">
                <h2 class="h2 text-center mb-4">Tizimga kirish</h2>
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Login yoki Telefon</label>
                        <input type="text" name="login" class="form-control @error('login') is-invalid @enderror"
                               placeholder="Login yoki telefon raqam (901234567)"
                               value="{{old('login')}}"
                               autocomplete="username"
                               required>
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Login yoki 9 ta raqamli telefon raqamingizni kiriting</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">
                            Parol
                        </label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Parolingiz"
                                   autocomplete="current-password"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }} />
                            <span class="form-check-label">Meni eslab qol</span>
                        </label>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Kirish</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center text-secondary mt-3">
            Hali akkauntingiz yo'qmi? <a href="./sign-up.html" tabindex="-1">Ro'yxatdan o'tish</a>
        </div>
    </div>
</div>
</body>
</html>
