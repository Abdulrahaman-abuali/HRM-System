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
                        <p class="brand-subtitle">تحديث بيانات: <strong>{{ $employee->first_name }}
                                {{ $employee->last_name }}</strong></p>
                    </header>

                    <form action="{{ route('employees.update', $employee->id) }}" method="POST" class="auth-form"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- القسم الأول: البيانات الشخصية --}}
                        <h4
                            style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">
                            البيانات الشخصية</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label for="first_name" class="form-label">الاسم الأول</label>
                                <input type="text" id="first_name" name="first_name" class="form-control"
                                    value="{{ old('first_name', $employee->first_name) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name" class="form-label">اسم العائلة</label>
                                <input type="text" id="last_name" name="last_name" class="form-control"
                                    value="{{ old('last_name', $employee->last_name) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="form-label">الهاتف المحمول</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                    value="{{ old('phone', $employee->phone) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="gender" class="form-label">النوع (الجنس)</label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="ذكر"
                                        {{ old('gender', $employee->gender) == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                                    <option value="أنثى"
                                        {{ old('gender', $employee->gender) == 'أنثى' ? 'selected' : '' }}>أنثى</option>
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
                                    value="{{ old('age', $employee->age) }}" readonly placeholder="يتم حسابه تلقائياً">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="address" class="form-label">العنوان السكني الحالي</label>
                            <input type="text" id="address" name="address" class="form-control"
                                value="{{ old('address', $employee->address) }}" required>
                        </div>

                        <div class="form-group" style="grid-column: span 2; margin-bottom: 1.5rem;">
                            <label for="profile_image" class="form-label">صورة الموظف الشخصية</label>
                            <div
                                style="display: flex; align-items: center; gap: 1rem; border: 2px dashed #e5e7eb; padding: 1rem; border-radius: 8px;">
                                <input type="file" id="profile_image" name="profile_image" class="form-control"
                                    accept="image/*" onchange="previewImage(event)">
                                <div id="image_preview_container"
                                    style="width: 80px; height: 80px; border: 1px solid #ddd; border-radius: 50%; overflow: hidden; background: #f9fafb; display: flex; align-items: center; justify-content: center;">
                                    @if ($employee->profile_image)
                                        <img id="output"
                                            src="{{ asset('storage/employee_faces/' . $employee->profile_image) }}"
                                            style="width: 100%; height: 100%; object-fit: cover;" />
                                    @else
                                        <img id="output"
                                            style="width: 100%; height: 100%; object-fit: cover; display: none;" />
                                        <span id="placeholder_text"
                                            style="color: #9ca3af; font-size: 0.7rem;">Preview</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- القسم الثاني: البيانات الوظيفية والمالية --}}
                        <h4
                            style="margin-bottom: 1rem; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">
                            البيانات الوظيفية والراتب</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label for="department_id" class="form-label">القسم</label>
                                <select id="department_id" name="department_id" class="form-control" required>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="job_title_id" class="form-label">المسمى الوظيفي</label>
                                <select id="job_title_id" name="job_title_id" class="form-control" required>
                                    @foreach ($job_titles as $job)
                                        <option value="{{ $job->id }}"
                                            {{ old('job_title_id', $employee->job_title_id) == $job->id ? 'selected' : '' }}>
                                            {{ $job->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="employment_type" class="form-label">نوع التوظيف</label>
                                <select id="employment_type" name="employment_type" class="form-control" required>
                                    <option value="full-time"
                                        {{ old('employment_type', $employee->employment_type) == 'full-time' ? 'selected' : '' }}>
                                        دوام كامل</option>
                                    <option value="part-time"
                                        {{ old('employment_type', $employee->employment_type) == 'part-time' ? 'selected' : '' }}>
                                        دوام جزئي</option>
                                    <option value="contract"
                                        {{ old('employment_type', $employee->employment_type) == 'contract' ? 'selected' : '' }}>
                                        تعاقد / فريلانس</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="manager_id" class="form-label">المدير المباشر (مدير القسم)</label>
                                <select id="manager_id" name="manager_id" class="form-control">
                                    <option value="">بدون مدير (إدارة عليا)</option>
                                    @foreach ($managers as $manager)
                                        {{-- التأكد من أن المدير المتاح هو من يملك صلاحية مدير القسم --}}
                                        <option value="{{ $manager->id }}"
                                            {{ old('manager_id', $employee->manager_id) == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->first_name }} {{ $manager->last_name }} (مدير القسم)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- حقول التعاقد --}}
                            <div id="contract_dates_container"
                                style="display: {{ old('employment_type', $employee->employment_type) == 'contract' ? 'grid' : 'none' }}; grid-column: span 2; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; background: #f9fafb; padding: 1rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                                <div class="form-group">
                                    <label for="contract_start_date" class="form-label">تاريخ بداية العقد</label>
                                    <input type="date" id="contract_start_date" name="contract_start_date"
                                        class="form-control"
                                        value="{{ old('contract_start_date', $employee->contract_start_date) }}">
                                </div>
                                <div class="form-group">
                                    <label for="contract_end_date" class="form-label">تاريخ نهاية العقد</label>
                                    <input type="date" id="contract_end_date" name="contract_end_date"
                                        class="form-control"
                                        value="{{ old('contract_end_date', $employee->contract_end_date) }}">
                                </div>
                            </div>

                            <div class="form-group" style="grid-column: span 2;">
                                <label for="basic_salary" class="form-label">الراتب الأساسي (ر.ي)</label>
                                <input type="number" step="0.001" id="basic_salary" name="basic_salary"
                                    class="form-control"
                                    value="{{ old('basic_salary', $employee->salary->basic_salary ?? '') }}" required>
                            </div>
                        </div>
                        {{-- بعد حقل basic_salary --}}
                        <div class="form-group" style="grid-column: span 2;">
                            <label for="role_id" class="form-label">صلاحية النظام</label>
                            <select id="role_id" name="role_id" class="form-control" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id', $employee->user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">تحديد الصلاحية يتحكم في ما يمكن للموظف رؤيته في
                                النظام</small>
                        </div>
                        <div class="form-actions" style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">تحديث بيانات الموظف</button>
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
                output.src = reader.result;
                output.style.display = 'block';
                if (document.getElementById('placeholder_text')) {
                    document.getElementById('placeholder_text').style.display = 'none';
                }
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

        // مراقبة تغيير القسم لجلب المديرين وفحص توفر منصب المدير
        document.getElementById('department_id').addEventListener('change', function() {
            const deptId = this.value;
            const managerSelect = document.getElementById('manager_id');
            const jobSelect = document.getElementById('job_title_id');
            const jobOptions = jobSelect.options;

            managerSelect.innerHTML = '<option value="">جاري التحميل...</option>';

            if (deptId) {
                fetch(`/get-managers/${deptId}`)
                    .then(response => response.json())
                    .then(data => {
                        managerSelect.innerHTML = '<option value="">بدون مدير (إدارة عليا)</option>';
                        if (data.length > 0) {
                            data.forEach((manager, index) => {
                                const option = document.createElement('option');
                                option.value = manager.id;
                                option.textContent = manager.first_name + ' ' + manager.last_name;
                                if (index === 0) option.selected = true;
                                managerSelect.appendChild(option);
                            });
                        } else {
                            managerSelect.innerHTML =
                                '<option value="" selected>بدون مدير (لا يوجد مديرين)</option>';
                        }
                    });

                // فحص توفر منصب مدير في القسم
                fetch(`/check-section-manager/${deptId}`)
                    .then(response => response.json())
                    .then(data => {
                        const isCurrentManager = "{{ $employee->jobTitle->name ?? '' }}".includes('مدير') &&
                            "{{ $employee->department_id }}" == deptId;
                        for (let i = 0; i < jobOptions.length; i++) {
                            if (jobOptions[i].text.includes('مدير')) {
                                if (data.has_manager && !isCurrentManager) {
                                    jobOptions[i].style.display = 'none';
                                    jobOptions[i].disabled = true;
                                } else {
                                    jobOptions[i].style.display = 'block';
                                    jobOptions[i].disabled = false;
                                }
                            }
                        }
                    });
            }
        });

        // مراقبة تغيير نوع التوظيف لإظهار حقول العقد
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
                // أهم خطوة: حذف خاصية "مطلوب" وتفريغ الحقل
                startDate.removeAttribute('required');
                endDate.removeAttribute('required');
                startDate.value = '';
                endDate.value = '';
            }
        });
    </script>
@endsection
