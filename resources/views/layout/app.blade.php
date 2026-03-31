<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الموارد البشرية - @yield('title')</title>

    <link rel="stylesheet" href="{{ asset('style/CSS.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
 <style>
    /* إصلاح مشكلة الشريط الجانبي */
    .layout {
        display: flex;
        width: 100%;
        min-height: 100vh;
      
    }

    .sidebar {
        flex-shrink: 0;
        width: 280px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff;
        height: 100vh;
        position: sticky;
        top: 0;
        overflow-y: auto;
    }

    .main {
        flex: 1;
        min-width: 0;
        overflow-x: hidden;
        background: #f8fafc;
    }

    .content-body {
        overflow-x: auto;
        width: 100%;
    }

    /* تحسين عرض الجداول داخل المحتوى */
    .card {
        width: 100%;
        overflow-x: auto;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        min-width: 100%;
        width: 100%;
    }
</style>
    <style>
        .nav-link-active {
            background-color: rgba(255, 255, 255, 0.2);
            border-right: 4px solid #fff;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        /* تمييز خاص لقائمة مدير القسم */
        .dept-manager-section {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 10px;
            padding-top: 10px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="app app-dashboard">
    <div class="layout">

        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <span class="sidebar-logo-mark">HR</span>
                    <span class="sidebar-logo-text">نظام الموارد البشرية</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    @auth
                        {{-- 1. قائمة مدير النظام --}}
                        @if (auth()->user()->role?->name === 'مدير النظام')
                            <li class="nav-item">
                                <a href="{{ route('dashbord') }}"
                                    class="nav-link {{ request()->routeIs('dashbord') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">📊</span><span class="nav-text">لوحة التحكم</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('employees.index') }}"
                                    class="nav-link {{ request()->routeIs('employees.*') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">👥</span><span class="nav-text">الموظفون</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('leave') }}"
                                    class="nav-link {{ request()->routeIs('leave') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">🕒</span><span class="nav-text">الحضور والانصراف</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.leaves.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.leaves.*') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">📅</span><span class="nav-text">الإجازات</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('salaries') }}"
                                    class="nav-link {{ request()->routeIs('salaries') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">💰</span><span class="nav-text">الرواتب</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('tasks.admin') }}"
                                    class="nav-link {{ request()->routeIs('tasks.admin') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">📋</span><span class="nav-text">المهام</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('performance') }}"
                                    class="nav-link {{ request()->routeIs('performance') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">📊</span><span class="nav-text">تقييم الأداء</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports') }}"
                                    class="nav-link {{ request()->routeIs('reports') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">📈</span><span class="nav-text">التقارير</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('notifications') }}"
                                    class="nav-link {{ request()->routeIs('notifications') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">🔔</span><span class="nav-text">الإشعارات</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}"
                                    class="nav-link {{ request()->routeIs('users.*') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">🧩</span><span class="nav-text">المستخدمون والصلاحيات</span>
                                </a>
                            </li>
                        @endif

                        {{-- 2. قائمة مدير القسم (الجديدة) --}}
                        @if (auth()->user()->role?->name === 'مدير القسم')
                            <li class="nav-item">
                                <a href="{{ route('dashbord') }}"
                                    class="nav-link {{ request()->routeIs('dashbord') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">🏢</span><span class="nav-text">إحصائيات القسم</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('employees.index') }}"
                                    class="nav-link {{ request()->routeIs('employees.*') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">👥</span><span class="nav-text">موظفي القسم</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('leave') }}"
                                    class="nav-link {{ request()->routeIs('leave') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">🕒</span><span class="nav-text">تحضير القسم</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.leaves.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.leaves.*') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">📅</span><span class="nav-text">طلبات إجازات القسم</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('notifications') }}"
                                    class="nav-link {{ request()->routeIs('notifications') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">🔔</span><span class="nav-text">تنبيهات القسم</span>
                                </a>
                            </li>
                        @endif

                        {{-- 3. قائمة الموظف --}}
                        @if (auth()->user()->role?->name === 'موظف')
                            <li class="nav-item">
                                <a href="{{ route('employee.dashboard') }}"
                                    class="nav-link {{ request()->routeIs('employee.dashboard') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">🏠</span><span class="nav-text">لوحة الموظف</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('leaves.index') }}"
                                    class="nav-link {{ request()->routeIs('leaves.*') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">📅</span><span class="nav-text">طلبات الإجازة</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('attendance.index') }}"
                                    class="nav-link {{ request()->routeIs('attendance.index') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">🕒</span><span class="nav-text">سجل الحضور</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('salaries') }}"
                                    class="nav-link {{ request()->routeIs('salaries') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">💰</span><span class="nav-text">كشوف الرواتب</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('tasks.index') }}"
                                    class="nav-link {{ request()->routeIs('tasks.index') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">📋</span><span class="nav-text">متابعة المهام</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('notifications') }}"
                                    class="nav-link {{ request()->routeIs('notifications') ? 'nav-link-active' : '' }}">
                                    <span class="nav-icon">🔔</span><span class="nav-text">الإشعارات</span>
                                    @php
                                        $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
                                            ->where('is_read', 0)
                                            ->count();
                                    @endphp
                                    @if ($unreadCount > 0)
                                        <span class="badge"
                                            style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; margin-right: 5px;">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </nav>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-logout" style="width: 100%; text-align: right;">
                        <span class="nav-icon">🚪</span>
                        <span class="nav-text">تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="main">
            <header class="main-header">
                <div class="header-left">
                    <h1 class="page-title">@yield('title')</h1>
                    <p class="page-subtitle">نظام إدارة الموارد البشرية - شركة البرمجيات الصغيرة</p>
                </div>
                <div class="header-right">
                    <div class="header-date-pill">
                        <span class="header-date-label">تاريخ اليوم</span>
                        <span class="header-date-value">{{ now()->translatedFormat('l، d F Y') }}</span>
                    </div>
                    <div class="header-user">
                        <div class="header-user-avatar">{{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                        <div class="header-user-info">
                            <span class="header-user-name">{{ auth()->user()->name }}</span>
                            <span class="header-user-role">{{ auth()->user()->role->name ?? 'مستخدم' }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-body" style="overflow-x: auto; width: 100%;">
                @if (session('success'))
                    <div
                        style="padding: 15px; background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; border-radius: 8px; margin: 20px auto; max-width: 90%; text-align: center;">
                        <strong>✅ {{ session('success') }}</strong>
                    </div>
                @endif

                @if (session('error'))
                    <div
                        style="padding: 15px; background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; border-radius: 8px; margin: 20px auto; max-width: 90%; text-align: center;">
                        <strong>❌ {{ session('error') }}</strong>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        style="padding: 15px; background-color: #fff3cd; color: #664d03; border: 1px solid #ffe69c; border-radius: 8px; margin: 20px auto; max-width: 90%;">
                        <ul style="margin: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- تم تنظيف هذا الجزء من Reverb ليعمل النظام بدون أخطاء --}}
    <script type="module">
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });

        // تم تعطيل Echo لتجنب أخطاء الاتصال بـ Reverb
        window.addEventListener('load', () => {
            console.log('🚀 System Loaded Successfully');
        });
    </script>

    @yield('script')
</body>

</html>
