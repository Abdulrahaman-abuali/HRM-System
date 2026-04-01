@extends('layout.app')

@section('title', 'تغيير كلمة المرور')

@section('content')
<div class="main">
    <main class="main-content">
        <section class="section">
            <div class="auth-wrapper" style="max-width: 500px; margin: 50px auto;">
                <div class="auth-card">
                    <header class="auth-header" style="text-align: center;">
                        <h1 class="brand-title">تغيير كلمة المرور</h1>
                        <p class="brand-subtitle">هذه أول مرة تسجل فيها الدخول. يرجى تغيير كلمة المرور الخاصة بك.</p>
                    </header>

                    @if(session('warning'))
                        <div class="alert alert-warning" style="background: #fef3c7; color: #d97706; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" style="background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('password.update') }}" method="POST" class="auth-form">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">كلمة المرور الحالية</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">كلمة المرور الجديدة</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                            <small class="text-muted">يجب أن تتكون من 8 أحرف على الأقل</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">تأكيد كلمة المرور الجديدة</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" style="width: 100%;">تغيير كلمة المرور</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>

<style>
    .auth-wrapper {
        background: #f8fafc;
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .auth-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .brand-title {
        color: #1e293b;
        margin-bottom: 10px;
    }
    .brand-subtitle {
        color: #64748b;
        margin-bottom: 30px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #334155;
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        transition: all 0.3s;
    }
    .form-control:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .btn-primary {
        background: #4f46e5;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    .btn-primary:hover {
        background: #4338ca;
    }
    .text-muted {
        font-size: 12px;
        color: #94a3b8;
        display: block;
        margin-top: 5px;
    }
</style>
@endsection
