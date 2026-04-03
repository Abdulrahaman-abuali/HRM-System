@extends('layout.app')

@section('title', 'التقارير والإحصائيات')

@section('content')
<div class="main">
    <main class="main-content">

        {{-- القسم الأول: التقارير الجاهزة السريعة --}}
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">التقارير الجاهزة ({{ date('Y') }})</h2>
            </div>
            <div class="grid grid-4">
                {{-- 1. تقرير الحضور --}}
                <article class="card report-card">
                    <div class="card-body">
                        <div class="stat-icon" style="background: #eef2ff; color: #4f46e5; margin-bottom: 15px;">📋</div>
                        <h3 class="card-title">الحضور الشهري</h3>
                        <p class="card-subtitle">كشف كامل بحالات الحضور والغياب لعام {{ date('Y') }}</p>
                        <form action="{{ route('reports.generate') }}" method="POST" target="_blank">
                            @csrf
                            <input type="hidden" name="module" value="attendance">
                            <input type="hidden" name="from_date" value="{{ date('Y-m-01') }}">
                            <input type="hidden" name="to_date" value="{{ date('Y-m-t') }}">

                            {{-- إضافة فلتر القسم --}}
                            <select name="department_id" class="form-control" style="margin-top: 10px; font-size: 12px;">
                                <option value="">كل الأقسام</option>
                                @foreach(\App\Models\Department::all() as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary w-full" style="margin-top: 15px; background: #4f46e5;">توليد الآن</button>
                        </form>
                    </div>
                </article>

                {{-- 2. تقرير الرواتب --}}
                <article class="card report-card">
                    <div class="card-body">
                        <div class="stat-icon" style="background: #ecfdf5; color: #10b981; margin-bottom: 15px;">💰</div>
                        <h3 class="card-title">الرواتب الشهرية</h3>
                        <p class="card-subtitle">ملخص الرواتب والبدلات (نفس شاشة الرواتب)</p>
                        <form action="{{ route('reports.generate') }}" method="POST" target="_blank">
                            @csrf
                            <input type="hidden" name="module" value="payroll">
                            <input type="hidden" name="from_date" value="{{ date('Y-m-01') }}">
                            <input type="hidden" name="to_date" value="{{ date('Y-m-t') }}">

                            {{-- إضافة فلتر القسم --}}
                            <select name="department_id" class="form-control" style="margin-top: 10px; font-size: 12px;">
                                <option value="">كل الأقسام</option>
                                @foreach(\App\Models\Department::all() as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary w-full" style="margin-top: 15px; background: #10b981;">توليد الآن</button>
                        </form>
                    </div>
                </article>

                {{-- 3. تقرير الموظفين --}}
                <article class="card report-card">
                    <div class="card-body">
                        <div class="stat-icon" style="background: #fffbeb; color: #f59e0b; margin-bottom: 15px;">👥</div>
                        <h3 class="card-title">بيانات الموظفين</h3>
                        <p class="card-subtitle">قائمة تفصيلية ببيانات الاتصال والمسميات</p>
                        <form action="{{ route('reports.generate') }}" method="POST" target="_blank">
                            @csrf
                            <input type="hidden" name="module" value="employees">
                            <input type="hidden" name="from_date" value="{{ date('Y-01-01') }}">
                            <input type="hidden" name="to_date" value="{{ date('Y-12-31') }}">

                            {{-- إضافة فلتر القسم --}}
                            <select name="department_id" class="form-control" style="margin-top: 10px; font-size: 12px;">
                                <option value="">كل الأقسام</option>
                                @foreach(\App\Models\Department::all() as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary w-full" style="margin-top: 15px; background: #f59e0b;">توليد الآن</button>
                        </form>
                    </div>
                </article>

                {{-- 4. التقرير السنوي --}}
                <article class="card report-card" style="border: 2px solid #ef4444;">
                    <div class="card-body">
                        <div class="stat-icon" style="background: #fee2e2; color: #ef4444; margin-bottom: 15px;">📅</div>
                        <h3 class="card-title">التقرير السنوي</h3>
                        <p class="card-subtitle">ملخص الأداء المالي والنمو لعام {{ date('Y') }}</p>
                        <form action="{{ route('reports.generate') }}" method="POST" target="_blank">
                            @csrf
                            <input type="hidden" name="module" value="annual_summary">
                            <input type="hidden" name="from_date" value="{{ date('Y-01-01') }}">
                            <input type="hidden" name="to_date" value="{{ date('Y-12-31') }}">
                            <button type="submit" class="btn btn-danger w-full" style="margin-top: 15px;">توليد تقرير السنة</button>
                        </form>
                    </div>
                </article>
            </div>
        </section>

        {{-- القسم الثاني: كشف تقرير موظف محدد (مع التاريخ) --}}
        <section class="section">
            <article class="card">
                <header class="card-header" style="border-bottom: 1px solid #f1f5f9; padding: 20px;">
                    <h2 class="card-title">👤 كشف تقرير موظف محدد</h2>
                    <p class="card-subtitle">استخراج بيانات تفصيلية لموظف واحد خلال فترة زمنية محددة</p>
                </header>
                <form action="{{ route('reports.generate') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="module" value="single_employee">
                    <div class="card-body" style="padding: 25px;">
                        <div class="grid grid-custom-4" style="gap: 20px;">
                            {{-- حقل البحث --}}
                            <div class="form-group">
                                <label class="form-label">اسم الموظف</label>
                                <input type="text" name="employee_name" class="form-control" list="employees_list" placeholder="ابدأ بكتابة الاسم..." required>
                                <datalist id="employees_list">
                                    @foreach(\App\Models\Employee::all() as $emp)
                                        <option value="{{ $emp->first_name }} {{ $emp->last_name }}">
                                    @endforeach
                                </datalist>
                            </div>
                            {{-- نوع الكشف --}}
                            <div class="form-group">
                                <label class="form-label">نوع الكشف</label>
                                <select name="report_type" class="form-control" required>
                                    <option value="personal">📄 كشف بيانات شخصية</option>
                                    <option value="financial">💰 كشف مالي (نفس الرواتب)</option>
                                </select>
                            </div>
                            {{-- من تاريخ --}}
                            <div class="form-group">
                                <label class="form-label">من تاريخ</label>
                                <input type="date" name="from_date" class="form-control" value="{{ date('Y-m-01') }}" required>
                            </div>
                            {{-- إلى تاريخ --}}
                            <div class="form-group">
                                <label class="form-label">إلى تاريخ</label>
                                <input type="date" name="to_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <footer class="card-footer" style="background: #f8fafc; padding: 15px 25px; display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary" style="background: #4f46e5;">توليد تقرير الموظف 🚀</button>
                    </footer>
                </form>
            </article>
        </section>

        {{-- القسم الثالث: كشف تقرير عام مخصص --}}
        <section class="section">
            <article class="card">
                <header class="card-header" style="border-bottom: 1px solid #f1f5f9; padding: 20px;">
                    <h2 class="card-title">📋 كشف تقرير عام مخصص</h2>
                    <p class="card-subtitle">استخراج بيانات الوحدات لفترة زمنية محددة مع اختيار القسم</p>
                </header>
                <form action="{{ route('reports.generate') }}" method="POST" target="_blank">
                    @csrf
                    <div class="card-body" style="padding: 25px;">
                        <div class="grid grid-custom-4" style="gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">الوحدة المستهدفة</label>
                                <select name="module" class="form-control" required>
                                    <option value="">-- اختر الوحدة --</option>
                                    <option value="employees">بيانات الموظفين</option>
                                    <option value="attendance">الحضور والانصراف</option>
                                    <option value="payroll">الرواتب والمالية (شاشة الرواتب)</option>
                                    <option value="annual_summary">ملخص سنوي</option>
                                </select>
                            </div>
                            {{-- حقل القسم المخصص --}}
                            <div class="form-group">
                                <label class="form-label">القسم</label>
                                <select name="department_id" class="form-control">
                                    <option value="">جميع الأقسام</option>
                                    @foreach(\App\Models\Department::all() as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">من تاريخ</label>
                                <input type="date" name="from_date" class="form-control" value="{{ date('Y-m-01') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">إلى تاريخ</label>
                                <input type="date" name="to_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <footer class="card-footer" style="background: #f8fafc; padding: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <label class="checkbox-label" style="margin-left: 20px; cursor: pointer;">
                                <input type="checkbox" name="export_pdf" value="1"> تصدير كـ PDF مباشر
                            </label>
                            <button type="reset" class="btn btn-outline">إعادة ضبط</button>
                            <button type="submit" class="btn btn-primary" style="background: #4f46e5; padding: 10px 30px;">توليد التقرير العام 🚀</button>
                        </div>
                    </footer>
                </form>
            </article>
        </section>

    </main>
</div>

<style>
    .w-full { width: 100%; }
    .report-card { border: 1px solid #e2e8f0; transition: all 0.3s ease; border-radius: 12px; height: 100%; }
    .report-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.1); }
    .grid-4, .grid-custom-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; margin-top: 5px; }
    .form-label { font-weight: 600; color: #334155; display: block; }
    .btn { cursor: pointer; padding: 10px 20px; border-radius: 10px; font-weight: 600; border: none; }
    .btn-primary { color: #fff; }
    .btn-outline { background: #fff; border: 1px solid #cbd5e1; color: #64748b; }

    @media (max-width: 1200px) { .grid-4, .grid-custom-4 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .grid-4, .grid-custom-4 { grid-template-columns: 1fr; } }
</style>
@endsection
