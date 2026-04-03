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

                    <form action="{{ route('employees.store') }}" method="POST" class="auth-form"
                        enctype="multipart/form-data">
                        @csrf

                        {{-- القسم الأول: البيانات الشخصية --}}
                        <h4
                            style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">
                            البيانات الشخصية</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label for="first_name" class="form-label">الاسم الأول</label>
                                <input type="text" id="first_name" name="first_name" class="form-control"
                                    placeholder="أدخل الاسم الأول" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name" class="form-label">اسم العائلة</label>
                                <input type="text" id="last_name" name="last_name" class="form-control"
                                    placeholder="أدخل اسم العائلة" value="{{ old('last_name') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="form-label">الهاتف المحمول</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                    placeholder="أدخل الهاتف المحمول" value="{{ old('phone') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="gender" class="form-label">النوع (الجنس)</label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="" disabled {{ old('gender') ? '' : 'selected' }}>اختر النوع
                                    </option>
                                    <option value="ذكر" {{ old('gender') == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                                    <option value="أنثى" {{ old('gender') == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="birth_date" class="form-label">تاريخ الميلاد</label>
                                <input type="date" id="birth_date" name="birth_date" class="form-control"
                                    value="{{ old('birth_date') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="age" class="form-label">العمر</label>
                                <input type="number" id="age" name="age" class="form-control"
                                    value="{{ old('age') }}" readonly placeholder="يتم حسابه تلقائياً">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="address" class="form-label">العنوان السكني الحالي</label>
                            <input type="text" id="address" name="address" class="form-control"
                                placeholder="المحافظة - المديرية - اسم الشارع" value="{{ old('address') }}" required>
                        </div>

                        <div class="form-group" style="grid-column: span 2; margin-bottom: 1.5rem;">
                            <label for="profile_image" class="form-label">صورة الموظف الشخصية</label>
                            <div
                                style="display: flex; align-items: center; gap: 1rem; border: 2px dashed #e5e7eb; padding: 1rem; border-radius: 8px;">
                                <input type="file" id="profile_image" name="profile_image" class="form-control"
                                    accept="image/*" onchange="previewImage(event)">
                                <div id="image_preview_container"
                                    style="width: 80px; height: 80px; border: 1px solid #ddd; border-radius: 50%; overflow: hidden; background: #f9fafb; display: flex; align-items: center; justify-content: center;">
                                    <img id="output"
                                        style="width: 100%; height: 100%; object-fit: cover; display: none;" />
                                    <span id="placeholder_text" style="color: #9ca3af; font-size: 0.7rem;">Preview</span>
                                </div>
                            </div>
                        </div>

                        {{-- القسم الثاني: البيانات الوظيفية --}}
                        <h4
                            style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">
                            البيانات الوظيفية والراتب</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label for="department_id" class="form-label">القسم</label>
                                <select id="department_id" name="department_id" class="form-control" required>
                                    <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>اختر
                                        القسم</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="job_title_id" class="form-label">المسمى الوظيفي</label>
                                <select id="job_title_id" name="job_title_id" class="form-control" required>
                                    <option value="" disabled {{ old('job_title_id') ? '' : 'selected' }}>اختر
                                        المسمى الوظيفي</option>
                                    @foreach ($job_titles as $job)
                                        <option value="{{ $job->id }}"
                                            {{ old('job_title_id') == $job->id ? 'selected' : '' }}>{{ $job->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="employment_type" class="form-label">نوع التوظيف</label>
                                <select id="employment_type" name="employment_type" class="form-control" required>
                                    <option value="" disabled selected>اختر النوع</option>
                                    <option value="full-time"
                                        {{ old('employment_type') == 'full-time' ? 'selected' : '' }}>دوام كامل</option>
                                    <option value="part-time"
                                        {{ old('employment_type') == 'part-time' ? 'selected' : '' }}>دوام جزئي</option>
                                    <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>
                                        تعاقد / فريلانس</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="manager_id" class="form-label">المدير المباشر (مدير القسم)</label>
                                <select id="manager_id" name="manager_id" class="form-control">
                                    <option value="" selected>اختر القسم أولاً...</option>
                                </select>
                            </div>

                            <div id="contract_dates_container"
                                style="display: none; grid-column: span 2; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; background: #f9fafb; padding: 1rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                                <div class="form-group">
                                    <label for="contract_start_date" class="form-label">تاريخ بداية العقد</label>
                                    <input type="date" id="contract_start_date" name="contract_start_date"
                                        class="form-control" value="{{ old('contract_start_date') }}">
                                </div>
                                <div class="form-group">
                                    <label for="contract_end_date" class="form-label">تاريخ نهاية العقد</label>
                                    <input type="date" id="contract_end_date" name="contract_end_date"
                                        class="form-control" value="{{ old('contract_end_date') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="basic_salary" class="form-label">الراتب الأساسي (ر.ي)</label>
                                <input type="number" step="0.001" id="basic_salary" name="basic_salary"
                                    class="form-control" value="{{ old('basic_salary') }}" required>
                            </div>
                            {{-- بعد حقل basic_salary --}}
                            <div class="form-group">
                                <label for="housing_percentage">بدل السكن (%)</label>
                                <input type="number" step="0.1" id="housing_percentage" name="housing_percentage"
                                    class="form-control" value="{{ old('housing_percentage', 10) }}">
                                <small class="text-muted">نسبة مئوية من الراتب الأساسي</small>
                            </div>

                            <div class="form-group">
                                <label for="transport_percentage">بدل المواصلات (%)</label>
                                <input type="number" step="0.1" id="transport_percentage"
                                    name="transport_percentage" class="form-control"
                                    value="{{ old('transport_percentage', 5) }}">
                                <small class="text-muted">نسبة مئوية من الراتب الأساسي</small>
                            </div>
                        </div>

                        {{-- القسم الثالث: بيانات الحساب --}}
                        <div id="account_info_section">
                            <h4
                                style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">
                                بيانات حساب الدخول</h4>
                            <div style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 2rem;">
                                <div class="form-group">
                                    <label for="email" class="form-label">البريد الالكتروني</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                        value="{{ old('email') }}" required>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="form-group">
                                        <label for="password" class="form-label">كلمة المرور</label>
                                        <input type="password" id="password" name="password" class="form-control"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="role_id" class="form-label">الصلاحية (الدور)</label>
                                        <select id="role_id" name="role_id" class="form-control" required>
                                            <option value="" disabled selected>اختر الصلاحية</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions" style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">حفظ الموظف وإنشاء
                                الحساب</button>
                            <a href="{{ route('employees.index') }}" class="btn btn-outline"
                                style="flex: 1; text-align: center;">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        // معاينة الصورة
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('output');
                var placeholder = document.getElementById('placeholder_text');
                output.src = reader.result;
                output.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // حساب العمر
        document.getElementById('birth_date').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            if (!isNaN(birthDate.getTime())) {
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
                document.getElementById('age').value = age;
            }
        });

        // جلب المديرين وفحص توفر منصب مدير القسم
        document.getElementById('department_id').addEventListener('change', function() {
            const deptId = this.value;
            const managerSelect = document.getElementById('manager_id');
            const jobSelect = document.getElementById('job_title_id');
            const jobOptions = jobSelect.options;

            managerSelect.innerHTML = '<option value="">جاري التحميل...</option>';

            if (deptId) {
                // التعديل: جلب الموظفين الذين يملكون دور "مدير القسم"
                fetch(`/get-managers/${deptId}`)
                    .then(response => response.json())
                    .then(data => {
                        managerSelect.innerHTML = '<option value="">بدون مدير (إدارة عليا)</option>';
                        if (data.length > 0) {
                            data.forEach((manager, index) => {
                                const option = document.createElement('option');
                                option.value = manager.id;
                                // إضافة توضيح أن هذا الشخص يحمل صلاحية مدير القسم
                                option.textContent = manager.first_name + ' ' + manager.last_name +
                                    ' (مدير القسم)';
                                if (index === 0) option.selected = true;
                                managerSelect.appendChild(option);
                            });
                        } else {
                            managerSelect.innerHTML =
                                '<option value="" selected>لا يوجد مدير لهذا القسم</option>';
                        }
                    });

                // فحص توفر منصب "مدير القسم" لمنع تكرار المسمى الوظيفي إذا وجد مدير
                fetch(`/check-section-manager/${deptId}`)
                    .then(response => response.json())
                    .then(data => {
                        for (let i = 0; i < jobOptions.length; i++) {
                            if (jobOptions[i].text.includes('مدير')) {
                                jobOptions[i].style.display = data.has_manager ? 'none' : 'block';
                                jobOptions[i].disabled = data.has_manager;
                            }
                        }
                    });
            }
        });

        // التحكم في تواريخ العقد
        document.getElementById('employment_type').addEventListener('change', function() {
            const container = document.getElementById('contract_dates_container');
            const startDate = document.getElementById('contract_start_date');
            const endDate = document.getElementById('contract_end_date');

            if (this.value === 'contract') {
                container.style.display = 'grid';
                startDate.setAttribute('required', 'required');
                endDate.setAttribute('required', 'required');
            } else {
                container.style.display = 'none';
                startDate.removeAttribute('required');
                endDate.removeAttribute('required');
                startDate.value = '';
                endDate.value = '';
            }
        });

        window.addEventListener('load', function() {
            const type = document.getElementById('employment_type').value;
            if (type === 'contract') {
                document.getElementById('contract_dates_container').style.display = 'grid';
            }
        });
    </script>
@endsection
