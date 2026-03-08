@extends('layout.app')

@section('title', 'إدارة الرواتب')

@section('content')
<div class="main">

    <main class="main-content">
        {{-- 1. ملخص الإحصائيات --}}
        <section class="section">
            <div class="grid grid-4">
                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">{{ auth()->user()->role->name === 'مدير النظام' ? 'إجمالي رواتب الشهر' : 'صافي راتبك المستحق' }}</h3>
                        <span class="stat-icon" style="background: #eef2ff; color: #4f46e5;">💰</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ number_format($stats['total_salaries'] ?? 0, 2) }}</p>
                    </div>
                </article>

                @if(auth()->user()->role->name === 'مدير النظام')
                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">رواتب مدفوعة</h3>
                        <span class="stat-icon" style="background: #ecfdf5; color: #10b981;">✔</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value" style="color: #10b981;">{{ number_format($stats['paid_salaries'] ?? 0, 2) }}</p>
                    </div>
                </article>
                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">رواتب معلقة</h3>
                        <span class="stat-icon" style="background: #fffbeb; color: #f59e0b;">⏳</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value" style="color: #f59e0b;">{{ number_format($stats['pending_salaries'] ?? 0, 2) }}</p>
                    </div>
                </article>
                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">متوسط الرواتب</h3>
                        <span class="stat-icon" style="background: #eff6ff; color: #3b82f6;">📊</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ number_format($stats['average_salary'] ?? 0, 2) }}</p>
                    </div>
                </article>
                @endif
            </div>
        </section>

        {{-- 2. الفلترة والإجراءات العامة (للمدير فقط) --}}
        @if(auth()->user()->role->name === 'مدير النظام')
        <section class="section">
            <article class="card">
                <div style="padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <form method="GET" action="{{ route('salaries') }}" style="display: flex; gap: 15px; flex-grow: 1;">
                        <input type="hidden" name="selected_month" value="{{ $date }}">
                        <select name="status" class="form-control" onchange="this.form.submit()" style="width: 150px;">
                            <option value="">كل الحالات</option>
                            <option value="مدفوع" {{ request('status') == 'مدفوع' ? 'selected' : '' }}>مدفوع</option>
                            <option value="معلق" {{ request('status') == 'معلق' ? 'selected' : '' }}>معلق</option>
                        </select>
                        <select name="department_id" class="form-control" onchange="this.form.submit()" style="width: 150px;">
                            <option value="">كل الأقسام</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="search" class="form-control" placeholder="بحث باسم الموظف..." value="{{ $search ?? '' }}">
                    </form>

                    <div style="display: flex; gap: 10px;">
                        <form action="{{ route('salaries.payAll') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من دفع جميع الرواتب؟')">
                            @csrf
                            <input type="hidden" name="selected_month" value="{{ $date }}">
                            <button type="submit" class="btn btn-success" style="background: #10b981; border: none;">دفع جميع الرواتب</button>
                        </form>
                        <button type="button" class="btn btn-primary" style="background: #6366f1;" onclick="openSalaryModal(null, 'جميع الموظفين')">إضافة بدل عام</button>
                    </div>
                </div>
            </article>
        </section>
        @endif

        {{-- 3. الجدول التفصيلي --}}
        <section class="section">
            <article class="card">
                <div class="table-responsive">
                    <table class="table table-hover" style="font-size: 0.82rem;">
                        <thead>
                            <tr>
                                <th>رقم الموظف</th>
                                <th>الاسم</th>
                                <th>الأساسي</th>
                                <th style="color: #10b981;">السكن</th>
                                <th style="color: #10b981;">المواصلات</th>
                                <th style="color: #10b981;">المكافآت</th>
                                <th style="color: #ef4444;">التأمين</th>
                                <th style="color: #ef4444;">الضرائب</th>
                                <th style="color: #f59e0b;">القروض</th>
                                <th style="color: #ef4444;">الجزاءات</th>
                                <th style="background: #f8fafc; font-weight: bold;">الصافي</th>
                                <th>الحالة</th>
                                @if(auth()->user()->role->name === 'مدير النظام')
                                    <th>إجراءات</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                                @php
                                    $salary = $emp->salaries->first();
                                    $basic = $salary->basic_salary ?? 0;
                                @endphp
                                <tr>
                                    <td><span class="badge badge-outline">EMP-{{ str_pad($emp->id, 3, '0', STR_PAD_LEFT) }}</span></td>
                                    <td style="font-weight: 600;">{{ $emp->first_name }}</td>
                                    <td>{{ number_format($basic) }}</td>
                                    <td style="color: #10b981;">{{ number_format($basic * (($salary->housing_percentage ?? 0) / 100), 2) }}</td>
                                    <td style="color: #10b981;">{{ number_format($basic * (($salary->transport_percentage ?? 0) / 100), 2) }}</td>
                                    <td style="color: #10b981; font-weight: bold;">{{ number_format($salary->bonuses ?? 0, 2) }}</td>
                                    <td style="color: #ef4444;">{{ number_format($basic * (($salary->health_percentage ?? 0) / 100), 2) }}</td>
                                    <td style="color: #ef4444;">{{ number_format($basic * (($salary->tax_percentage ?? 0) / 100), 2) }}</td>
                                    <td style="color: #f59e0b;">{{ number_format($salary->loan_installments ?? 0, 2) }}</td>
                                    <td style="color: #ef4444;">{{ number_format($salary->penalties ?? 0, 2) }}</td>
                                    <td style="background: #f8fafc;"><strong>{{ number_format($salary->net_salary ?? 0, 2) }}</strong></td>
                                    <td>
                                        <span class="badge {{ ($salary && $salary->status == 'مدفوع') ? 'badge-success' : 'badge-warning' }}">
                                            {{ $salary->status ?? 'معلق' }}
                                        </span>
                                    </td>
                                    @if(auth()->user()->role->name === 'مدير النظام')
                                    <td>
                                        <div style="display: flex; gap: 4px;">
                                            @if($salary && $salary->status !== 'مدفوع')
                                                <form action="{{ route('salaries.pay', $salary->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" style="background: #10b981; border:none;">دفع</button>
                                                </form>
                                            @endif
                                            <button class="btn btn-sm btn-primary" style="background: #4f46e5; border:none;"
                                                onclick="openSalaryModal({{ json_encode($salary) }}, '{{ $emp->first_name }}', 'EMP-{{ $emp->id }}', '{{ $emp->department->name ?? '' }}')">
                                                تعديل
                                            </button>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="13" style="text-align:center">لا توجد سجلات لهذا الشهر</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </main>
</div>

{{-- 4. النافذة المنبثقة (Modal) --}}
@if(auth()->user()->role->name === 'مدير النظام')
<div id="salaryModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); overflow-y:auto;">
    <div style="background:#fff; margin:5% auto; padding:25px; border-radius:15px; width:90%; max-width:850px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; border-bottom:2px solid #eee; padding-bottom:15px; margin-bottom:20px;">
            <div>
                <h2 id="modalTitle" style="color: #1e293b; margin: 0;">تعديل الراتب</h2>
                <p id="modalSubtitle" style="color: #64748b; margin: 5px 0 0 0; font-size: 0.85rem;"></p>
            </div>
            <button onclick="closeModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>

        <form id="salaryUpdateForm" action="{{ route('salaries.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="salary_id" id="modal_salary_id">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <fieldset style="border:1px solid #e2e8f0; padding:15px; border-radius:10px; background: #f8fafc;">
                    <legend style="font-weight:bold; color:#059669; padding:0 10px; background: #fff;">➕ الإضافات</legend>
                    <div id="section_basic"><label class="form-label">الأساسي</label><input type="number" name="basic_salary" id="calc_basic" class="form-control" oninput="calculateNet()" placeholder="0"></div>
                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <div style="flex:1"><label class="form-label">سكن %</label><input type="number" name="housing_percentage" id="calc_housing" class="form-control" oninput="calculateNet()" placeholder="0"></div>
                        <div style="flex:1"><label class="form-label">مواصلات %</label><input type="number" name="transport_percentage" id="calc_transport" class="form-control" oninput="calculateNet()" placeholder="0"></div>
                    </div>
                    <div style="margin-top:10px;"><label class="form-label">مكافآت (ر.ي)</label><input type="number" name="bonuses" id="calc_bonuses" class="form-control" oninput="calculateNet()" placeholder="0"></div>
                </fieldset>

                <fieldset style="border:1px solid #fee2e2; padding:15px; border-radius:10px; background: #fffaf9;">
                    <legend style="font-weight:bold; color:#dc2626; padding:0 10px; background: #fff;">➖ الخصومات</legend>
                    <div style="display:flex; gap:10px;">
                        <div style="flex:1"><label class="form-label">تأمين %</label><input type="number" name="health_percentage" id="calc_health" class="form-control" oninput="calculateNet()" placeholder="0"></div>
                        <div style="flex:1"><label class="form-label">ضرائب %</label><input type="number" name="tax_percentage" id="calc_tax" class="form-control" oninput="calculateNet()" placeholder="0"></div>
                    </div>
                    <div id="section_loans" style="margin-top:10px;"><label class="form-label">قروض (ر.ي)</label><input type="number" name="loan_installments" id="calc_loans" class="form-control" oninput="calculateNet()" placeholder="0"></div>
                    <div style="margin-top:10px;"><label class="form-label">جزاءات (ر.ي)</label><input type="number" name="penalties" id="calc_penalties" class="form-control" oninput="calculateNet()" placeholder="0"></div>
                </fieldset>
            </div>

            <div style="margin-top:25px; padding:20px; background:#1e293b; color:#fff; border-radius:12px; display:flex; justify-content:space-between; align-items:center;">
                <div>الصافي المتوقع: <span id="live_net" style="font-size:1.6rem; color:#10b981; font-weight:bold;">0.00</span> ر.ي</div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="closeModal()" class="btn btn-outline" style="color:#fff; border-color: #475569;">إلغاء</button>
                    <button type="submit" class="btn btn-primary" style="background: #4f46e5; border: none; padding: 10px 25px;">حفظ واعتماد</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function openSalaryModal(salary, name, empCode, deptName) {
    if(!document.getElementById('salaryUpdateForm')) return;
    document.getElementById('salaryUpdateForm').reset();

    const isGeneral = (salary === null);
    document.getElementById('modalTitle').innerText = isGeneral ? 'إضافة إضافات/خصومات عامة' : 'تعديل راتب: ' + name;
    document.getElementById('modalSubtitle').innerText = isGeneral ? '' : 'رقم: ' + empCode + ' | القسم: ' + deptName;
    document.getElementById('modal_salary_id').value = salary ? salary.id : '';
    document.getElementById('section_basic').style.display = isGeneral ? 'none' : 'block';

    const fields = {
        'calc_basic': salary ? salary.basic_salary : '',
        'calc_housing': salary ? salary.housing_percentage : '',
        'calc_transport': salary ? salary.transport_percentage : '',
        'calc_bonuses': salary ? salary.bonuses : '',
        'calc_health': salary ? salary.health_percentage : '',
        'calc_tax': salary ? salary.tax_percentage : '',
        'calc_loans': salary ? salary.loan_installments : '',
        'calc_penalties': salary ? salary.penalties : ''
    };

    // التعديل هنا: إزالة الأصفار من الحقول لتسريع الإدخال
    for (let id in fields) {
        let val = fields[id];
        document.getElementById(id).value = (val == 0) ? '' : val;
    }

    calculateNet();
    document.getElementById('salaryModal').style.display = 'block';
}

function closeModal() { if(document.getElementById('salaryModal')) document.getElementById('salaryModal').style.display = 'none'; }

function calculateNet() {
    const basic = parseFloat(document.getElementById('calc_basic').value) || 0;
    const h = (parseFloat(document.getElementById('calc_housing').value) || 0) / 100;
    const t = (parseFloat(document.getElementById('calc_transport').value) || 0) / 100;
    const b = parseFloat(document.getElementById('calc_bonuses').value) || 0;
    const hl = (parseFloat(document.getElementById('calc_health').value) || 0) / 100;
    const tx = (parseFloat(document.getElementById('calc_tax').value) || 0) / 100;
    const l = parseFloat(document.getElementById('calc_loans').value) || 0;
    const p = parseFloat(document.getElementById('calc_penalties').value) || 0;

    const net = (basic + (basic * h) + (basic * t) + b) - ((basic * hl) + (basic * tx) + l + p);
    document.getElementById('live_net').innerText = net.toLocaleString(undefined, {minimumFractionDigits: 2});
}
</script>
@endsection
