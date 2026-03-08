@extends('layout.app')

@section('title', 'تعديل بيانات الموظف')

@section('content')
<main class="main-content">
    <section class="section">
        <div class="auth-wrapper" style="max-width: 800px; margin: auto;">
            <div class="auth-card">
                <header class="auth-header" style="text-align: center; margin-bottom: 2rem;">
                    <div class="brand-logo">
                        <span class="brand-logo-mark">HR</span>
                    </div>
                    <h1 class="brand-title">تعديل بيانات الموظف</h1>
                    <p class="brand-subtitle">تحديث البيانات الشخصية والوظيفية للموظف: <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong></p>
                </header>

                <form action="{{ route('employees.update', $employee->id) }}" method="POST" class="auth-form">
                    @csrf
                    @method('PUT')

                    {{-- القسم الأول: البيانات الشخصية --}}
                    <h4 style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">البيانات الشخصية</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">

                        <div class="form-group">
                            <label for="first_name" class="form-label">الاسم الأول</label>
                            <input type="text" id="first_name" name="first_name" class="form-control"
                                   value="{{ old('first_name', $employee->first_name) }}" required>
                            @error('first_name') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="last_name" class="form-label">اسم العائلة</label>
                            <input type="text" id="last_name" name="last_name" class="form-control"
                                   value="{{ old('last_name', $employee->last_name) }}" required>
                            @error('last_name') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">الهاتف المحمول</label>
                            <input type="text" id="phone" name="phone" class="form-control"
                                   value="{{ old('phone', $employee->phone) }}" required>
                            @error('phone') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="gender" class="form-label">النوع (الجنس)</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="ذكر" {{ old('gender', $employee->gender) == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                                <option value="أنثى" {{ old('gender', $employee->gender) == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="birth_date" class="form-label">تاريخ الميلاد</label>
                            <input type="date" id="birth_date" name="birth_date" class="form-control"
                                   value="{{ old('birth_date', $employee->birth_date) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="age" class="form-label">العمر</label>
                            <input type="number" id="age" name="age" class="form-control"
                                   value="{{ old('age', $employee->age) }}" readonly>
                        </div>
                    </div>

                    {{-- حقل العنوان الجديد --}}
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="address" class="form-label">العنوان</label>
                        <input type="text" id="address" name="address" class="form-control"
                               placeholder="أدخل العنوان السكني الحالي" value="{{ old('address', $employee->address) }}" required>
                        @error('address') <div class="text-danger" style="color: red; font-size: 0.875rem;">{{ $message }}</div> @enderror
                    </div>

                    {{-- القسم الثاني: البيانات الوظيفية --}}
                    <h4 style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">البيانات الوظيفية</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">

                        <div class="form-group">
                            <label for="department_id" class="form-label">القسم</label>
                            <select id="department_id" name="department_id" class="form-control" required>
                                <option value="">اختر القسم</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="job_title_id" class="form-label">المسمى الوظيفي</label>
                            <select id="job_title_id" name="job_title_id" class="form-control" required>
                                <option value="">اختر المسمى الوظيفي</option>
                                @foreach($job_titles as $job)
                                    <option value="{{ $job->id }}"
                                        {{ old('job_title_id', $employee->job_title_id) == $job->id ? 'selected' : '' }}>
                                        {{ $job->title ?? $job->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- حقل نوع التوظيف الجديد --}}
                        <div class="form-group">
                            <label for="employment_type" class="form-label">نوع التوظيف</label>
                            <select id="employment_type" name="employment_type" class="form-control" required>
                                <option value="full-time" {{ old('employment_type', $employee->employment_type) == 'full-time' ? 'selected' : '' }}>دوام كامل</option>
                                <option value="part-time" {{ old('employment_type', $employee->employment_type) == 'part-time' ? 'selected' : '' }}>دوام جزئي</option>
                                <option value="contract" {{ old('employment_type', $employee->employment_type) == 'contract' ? 'selected' : '' }}>تعاقد</option>
                            </select>
                        </div>

                        {{-- حقل المدير المباشر الجديد --}}
                      <div class="form-group">
    <label for="manager_id" class="form-label">المدير المباشر</label>
    <select id="manager_id" name="manager_id" class="form-control">
        <option value="" selected>بدون مدير (إدارة عليا)</option>
        @foreach($managers as $manager)
            {{-- إضافة (int) تضمن أن المقارنة دقيقة بين الأرقام --}}
            @if((int)$manager->id !== (int)$employee->id)
                <option value="{{ $manager->id }}" {{ old('manager_id', $employee->manager_id) == $manager->id ? 'selected' : '' }}>
                    {{ $manager->first_name }} {{ $manager->last_name }}
                </option>
            @endif
        @endforeach
    </select>
</div>

                        <div class="form-group">
                            <label for="hire_date" class="form-label">تاريخ التعيين</label>
                            <input type="date" id="hire_date" name="hire_date" class="form-control"
                                   value="{{ old('hire_date', $employee->hire_date) }}" required>
                        </div>

                    </div>

                    <div class="form-actions" style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">تحديث الموظف</button>
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
        } else {
            document.getElementById('age').value = '';
        }
    });
</script>
@endsection
