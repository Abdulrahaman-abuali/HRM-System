@extends('layout.app')

@section('title', 'تعديل بيانات الدخول والصلاحيات')

@section('content')
    <div class="main">
        <header class="main-header">
            <div class="header-left">
                <h1 class="page-title">تعديل بيانات الدخول والصلاحيات</h1>
                <p class="page-subtitle">تعديل البريد الإلكتروني، كلمة المرور، والصلاحيات للمستخدم:
                    <strong>{{ $user->name }}</strong></p>
            </div>
            <div class="header-right">
                <a href="{{ route('users.index') }}" class="btn btn-outline">رجوع للقائمة</a>
            </div>
        </header>

        <main class="main-content">
            <section class="section">
                <article class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('users.update', $user->id) }}">
                            @csrf
                            @method('PUT')

                            <h4
                                style="margin-bottom: 1.5rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">
                                بيانات الدخول والصلاحيات</h4>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">

                                <div class="form-group">
                                    <label class="form-label">البريد الإلكتروني (لتسجيل الدخول)</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <span class="text-danger"
                                            style="color: red; font-size: 0.875rem;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">كلمة المرور الجديدة</label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="اترك الحقل فارغاً إذا لم ترد تغييرها">
                                    @error('password')
                                        <span class="text-danger"
                                            style="color: red; font-size: 0.875rem;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">الدور (الصلاحية)</label>
                                    <select name="role_id" class="form-control" required>
                                        <option value="" disabled>-- اختر الصلاحية --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <span class="text-danger"
                                            style="color: red; font-size: 0.875rem;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">حالة الحساب</label>
                                    <select name="status" class="form-control" required>
                                        @php
                                            $currentStatus = $user->is_active == 1 ? 'نشط' : 'غير نشط';
                                            // إذا كان هناك حقل status منفصل في جدول users، استخدمه
                                            if (isset($user->status) && $user->status == 'مغلق') {
                                                $currentStatus = 'مغلق';
                                            }
                                        @endphp
                                        <option value="نشط"
                                            {{ old('status', $currentStatus) == 'نشط' ? 'selected' : '' }}>نشط</option>
                                        <option value="غير نشط"
                                            {{ old('status', $currentStatus) == 'غير نشط' ? 'selected' : '' }}>غير نشط
                                        </option>
                                        <option value="مغلق"
                                            {{ old('status', $currentStatus) == 'مغلق' ? 'selected' : '' }}>مغلق</option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger"
                                            style="color: red; font-size: 0.875rem;">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>

                            <div
                                style="margin-top: 2rem; border-top: 1px solid #e5e7eb; padding-top: 1.5rem; display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                                <a href="{{ route('users.index') }}" class="btn btn-outline"
                                    style="text-align: center; text-decoration: none;">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </article>
            </section>
        </main>
    </div>
@endsection
