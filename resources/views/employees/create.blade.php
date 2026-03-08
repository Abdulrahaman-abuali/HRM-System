@extends('layout.app')

@section('title', 'إضافة موظف جديد')

@section('content')
<main class="main-content">
    <section class="section">
        <div class="auth-wrapper" style="max-width: 800px; margin: auto;">
            <div class="auth-card">
                <header class="auth-header" style="text-align: center; margin-bottom: 2rem;">
                    <div class="brand-logo">
                        <span class="brand-logo-mark">HR</span>
                    </div>
                    <h1 class="brand-title">إضافة موظف جديد</h1>
                    <p class="brand-subtitle">أدخل بيانات الموظف وإنشاء حساب دخول له مع تحديد الراتب</p>
                </header>

                <form action="{{ route('employees.store') }}" method="POST" class="auth-form">
                    @csrf

                    {{-- القسم الأول: البيانات الشخصية --}}
                    <h4 style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">البيانات الشخصية</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="first_name" class="form-label">الاسم الأول</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" placeholder="أدخل الاسم الأول" value="{{ old('first_name') }}" required>
                            @error('first_name') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="last_name" class="form-label">اسم العائلة</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" placeholder="أدخل اسم العائلة" value="{{ old('last_name') }}" required>
                            @error('last_name') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">الهاتف المحمول</label>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="أدخل الهاتف المحمول" value="{{ old('phone') }}" required>
                            @error('phone') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="gender" class="form-label">النوع (الجنس)</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="" disabled {{ old('gender') ? '' : 'selected' }}>اختر النوع</option>
                                <option value="ذكر" {{ old('gender') == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                                <option value="أنثى" {{ old('gender') == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                            </select>
                            @error('gender') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="birth_date" class="form-label">تاريخ الميلاد</label>
                            <input type="date" id="birth_date" name="birth_date" class="form-control" value="{{ old('birth_date') }}" required>
                            @error('birth_date') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="age" class="form-label">العمر</label>
                            <input type="number" id="age" name="age" class="form-control" value="{{ old('age') }}" readonly placeholder="يتم حسابه تلقائياً">
                        </div>
                    </div>

                     {{-- إضافة حقل العنوان السكني --}}
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="address" class="form-label">العنوان السكني الحالي</label>
                        <input type="text" id="address" name="address" class="form-control" placeholder="المحافظة - المديرية - اسم الشارع" value="{{ old('address') }}" required>
                        @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- القسم الثاني: البيانات الوظيفية والمالية --}}
                    <h4 style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">البيانات الوظيفية والراتب</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="department_id" class="form-label">القسم</label>
                            <select id="department_id" name="department_id" class="form-control" required>
                                <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>اختر القسم</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="job_title_id" class="form-label">المسمى الوظيفي</label>
                            <select id="job_title_id" name="job_title_id" class="form-control" required>
                                <option value="" disabled {{ old('job_title_id') ? '' : 'selected' }}>اختر المسمى الوظيفي</option>
                                @foreach($job_titles as $job)
                                    <option value="{{ $job->id }}" {{ old('job_title_id') == $job->id ? 'selected' : '' }}>{{ $job->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- إضافة حقل نوع التوظيف --}}
                        <div class="form-group">
                            <label for="employment_type" class="form-label">نوع التوظيف</label>
                            <select id="employment_type" name="employment_type" class="form-control" required>
                                <option value="" disabled selected>اختر النوع</option>
                                <option value="full-time" {{ old('employment_type') == 'full-time' ? 'selected' : '' }}>دوام كامل</option>
                                <option value="part-time" {{ old('employment_type') == 'part-time' ? 'selected' : '' }}>دوام جزئي</option>
                                <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>تعاقد / فريلانس</option>
                            </select>
                        </div>

                        {{-- إضافة حقل المدير المباشر --}}
                        <div class="form-group">
                            <label for="manager_id" class="form-label">المدير المباشر</label>
                            <select id="manager_id" name="manager_id" class="form-control">
                                <option value="" selected>بدون مدير (إدارة عليا)</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>{{ $manager->first_name }} {{ $manager->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- إضافة حقل الراتب الأساسي هنا --}}
                        <div class="form-group">
                            <label for="basic_salary" class="form-label">الراتب الأساسي (ر.ي)</label>
                            <input type="number" step="0.001" id="basic_salary" name="basic_salary" class="form-control" placeholder="أدخل الراتب" value="{{ old('basic_salary') }}" required>
                            @error('basic_salary') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="allowances" class="form-label">البدلات إن وجدت (ر.ي)</label>
                            <input type="number" step="0.001" id="allowances" name="allowances" class="form-control" placeholder="0.000" value="{{ old('allowances', 0) }}">
                        </div>

                        <div class="form-group">
                            <label for="hire_date" class="form-label">تاريخ التعيين</label>
                            <input type="date" id="hire_date" name="hire_date" class="form-control" value="{{ old('hire_date') }}" required>
                        </div>
                    </div>

                    {{-- القسم الثالث: بيانات الحساب --}}
                    <h4 style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">بيانات حساب الدخول</h4>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 2rem;">
                        <div class="form-group">
                            <label for="email" class="form-label">البريد الالكتروني</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="أدخل البريد الالكتروني" value="{{ old('email') }}" required>
                            @error('email') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="password" class="form-label">كلمة المرور</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="أدخل كلمة المرور" required>
                                @error('password') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group">
                                <label for="role_id" class="form-label">الصلاحية (الدور)</label>
                                <select id="role_id" name="role_id" class="form-control" required>
                                    <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>اختر الصلاحية</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">حفظ الموظف وإنشاء الحساب</button>
                        <a href="{{ route('employees.index') }}" class="btn btn-outline" style="flex: 1; text-align: center;">إلغاء</a>
                    </div>
                </form>

                <footer class="auth-footer" style="margin-top: 2rem; text-align: center;">
                    <p class="auth-footer-text">© 2026 شركة البرمجيات الصغيرة - نظام إدارة الموارد البشرية</p>
                </footer>
            </div>
        </div>
    </section>
</main>

<script>
    // سكربت حساب العمر تلقائياً
    document.getElementById('birth_date').addEventListener('change', function() {
        const birthDate = new Date(this.value);
        if (!isNaN(birthDate.getTime())) {
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            document.getElementById('age').value = age;
        }
    });
</script>
@endsection
