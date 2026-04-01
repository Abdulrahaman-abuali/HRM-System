<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة الموارد البشرية</title>
    <link rel="stylesheet" href="{{ asset('style/CSS.css') }}">
    <style>
        /* إضافة تنسيقات للرسائل */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
    </style>
</head>
<body class="auth-page">
    <main class="auth-wrapper">
        <section class="auth-card">
            <header class="auth-header">
                <div class="brand-logo">
                    <span class="brand-logo-mark">HR</span>
                </div>
                <h1 class="brand-title">نظام إدارة الموارد البشرية</h1>
                <p class="brand-subtitle">شركة البرمجيات الصغيرة</p>
            </header>

            {{-- عرض رسائل الخطأ --}}
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            {{-- عرض أخطاء التحقق --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            {{-- عرض رسائل النجاح --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form class="auth-form" action="{{ route('login') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="form-control"
                        placeholder="name@company.com"
                        value="{{ old('email') }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-control"
                        placeholder="أدخل كلمة المرور"
                        required
                    >
                </div>

                <div class="form-row form-row-between">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" class="checkbox-input">
                        <span class="checkbox-text">تذكرني</span>
                    </label>

                    <a href="#" class="link-forgot-password">نسيت كلمة المرور؟</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    تسجيل الدخول
                </button>
            </form>

            <footer class="auth-footer">
                <p class="auth-footer-text">
                    © 2025 شركة البرمجيات الصغيرة - نظام إدارة الموارد البشرية
                </p>
            </footer>
        </section>
    </main>
</body>
</html>
