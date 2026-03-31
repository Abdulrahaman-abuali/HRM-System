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
                            <h3 class="stat-title">
                                {{ auth()->user()->role->name === 'مدير النظام' ? 'إجمالي رواتب الشهر' : 'صافي راتبك المستحق' }}
                            </h3>
                            <span class="stat-icon" style="background: #eef2ff; color: #4f46e5;">💰</span>
                        </div>
                        <div class="stat-card-body">
                            <p class="stat-value">{{ number_format($stats['total_salaries'] ?? 0, 2) }}</p>
                        </div>
                    </article>

                    @if (auth()->user()->role->name === 'مدير النظام')
                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">رواتب مدفوعة</h3>
                                <span class="stat-icon" style="background: #ecfdf5; color: #10b981;">✔</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value" style="color: #10b981;">
                                    {{ number_format($stats['paid_salaries'] ?? 0, 2) }}</p>
                            </div>
                        </article>
                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">رواتب معلقة</h3>
                                <span class="stat-icon" style="background: #fffbeb; color: #f59e0b;">⏳</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value" style="color: #f59e0b;">
                                    {{ number_format($stats['pending_salaries'] ?? 0, 2) }}</p>
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
            @if (auth()->user()->role->name === 'مدير النظام')
                <section class="section">
                    <article class="card">
                        <div
                            style="padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <form method="GET" action="{{ route('salaries') }}"
                                style="display: flex; gap: 15px; flex-grow: 1;">
                                {{-- محدد الشهر --}}
                                <input type="month" name="selected_month" class="form-control"
                                    value="{{ request('selected_month', $date) }}" style="width: 150px;"
                                    onchange="this.form.submit()">

                                <select name="status" class="form-control" onchange="this.form.submit()"
                                    style="width: 150px;">
                                    <option value="">كل الحالات</option>
                                    <option value="مدفوع" {{ request('status') == 'مدفوع' ? 'selected' : '' }}>مدفوع
                                    </option>
                                    <option value="معلق" {{ request('status') == 'معلق' ? 'selected' : '' }}>معلق</option>
                                </select>
                                <select name="department_id" class="form-control" onchange="this.form.submit()"
                                    style="width: 150px;">
                                    <option value="">كل الأقسام</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="search" class="form-control" placeholder="بحث باسم الموظف..."
                                    value="{{ $search ?? '' }}">
                            </form>

                            <div style="display: flex; gap: 10px;">
                                {{-- زر توليد رواتب الشهر --}}
                                <form action="{{ route('salaries.generate') }}" method="POST"
                                    onsubmit="return confirm('هل أنت متأكد من توليد رواتب الشهر؟')">
                                    @csrf
                                    <input type="hidden" name="year"
                                        value="{{ date('Y', strtotime(request('selected_month', $date))) }}">
                                    <input type="hidden" name="month"
                                        value="{{ date('m', strtotime(request('selected_month', $date))) }}">
                                    <input type="hidden" name="selected_month"
                                        value="{{ request('selected_month', $date) }}">
                                    <button type="submit" class="btn btn-primary"
                                        style="background: #4f46e5; border: none;">🚀 توليد رواتب الشهر</button>
                                </form>

                                <form action="{{ route('salaries.payAll') }}" method="POST"
                                    onsubmit="return confirm('هل أنت متأكد من دفع جميع الرواتب؟')">
                                    @csrf
                                    <input type="hidden" name="selected_month"
                                        value="{{ request('selected_month', $date) }}">
                                    <button type="submit" class="btn btn-success"
                                        style="background: #10b981; border: none;">💰 دفع جميع الرواتب</button>
                                </form>

                                <button type="button" class="btn btn-primary" style="background: #6366f1;"
                                    onclick="openGeneralModal()">🎁 إضافة مكافآت / خصومات عامة</button>
                            </div>
                        </div>
                    </article>
                </section>
            @endif

            {{-- 3. الجدول التفصيلي --}}
            <section class="section">
                <article class="card">
                    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table table-hover" style="font-size: 0.75rem; min-width: 1300px;">
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
                                    <th style="color: #f97316;">أيام الغياب</th>
                                    <th style="color: #f97316;">خصم الغياب</th>
                                    <th style="color: #f97316;">دقائق التأخير</th>
                                    <th style="color: #f97316;">خصم التأخير</th>
                                    <th style="background: #f8fafc;">الصافي</th>
                                    <th>الحالة</th>
                                    @if (auth()->user()->role->name === 'مدير النظام')
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
                                        <td><span
                                                class="badge badge-outline">EMP-{{ str_pad($emp->id, 3, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td style="font-weight: 600;">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                        <td>{{ number_format($basic) }}</td>
                                        <td style="color: #10b981;">
                                            {{ number_format($basic * (($salary->housing_percentage ?? 0) / 100), 2) }}
                                        </td>
                                        <td style="color: #10b981;">
                                            {{ number_format($basic * (($salary->transport_percentage ?? 0) / 100), 2) }}
                                        </td>
                                        <td style="color: #10b981; font-weight: bold;">
                                            {{ number_format($salary->bonuses ?? 0, 2) }}</td>
                                        <td style="color: #ef4444;">
                                            {{ number_format($basic * (($salary->health_percentage ?? 0) / 100), 2) }}</td>
                                        <td style="color: #ef4444;">
                                            {{ number_format($basic * (($salary->tax_percentage ?? 0) / 100), 2) }}</td>
                                        <td style="color: #f59e0b;">
                                            {{ number_format($salary->loan_installments ?? 0, 2) }}</td>
                                        <td style="color: #ef4444;">{{ number_format($salary->penalties ?? 0, 2) }}</td>
                                        <td style="text-align: center;">{{ $salary->absence_days ?? 0 }}</td>
                                        <td style="color: #f97316;">
                                            {{ number_format($salary->absence_deduction ?? 0, 2) }}</td>
                                        <td style="text-align: center;">{{ $salary->late_minutes ?? 0 }}</td>
                                        <td style="color: #f97316;">{{ number_format($salary->late_deduction ?? 0, 2) }}
                                        </td>
                                        <td style="background: #f8fafc;">
                                            <strong>{{ number_format($salary->net_salary ?? 0, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $salary && $salary->status == 'مدفوع' ? 'badge-success' : 'badge-warning' }}">
                                                {{ $salary->status ?? 'معلق' }}
                                            </span>
                                        </td>
                                        @if (auth()->user()->role->name === 'مدير النظام')
                                            <td>
                                                <div style="display: flex; gap: 4px;">
                                                    @if ($salary && $salary->status !== 'مدفوع')
                                                        <form action="{{ route('salaries.pay', $salary->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success"
                                                                style="background: #10b981; border:none;">دفع</button>
                                                        </form>
                                                    @endif
                                                    <button class="btn btn-sm btn-primary"
                                                        style="background: #4f46e5; border:none;"
                                                        onclick="openSalaryModal({{ json_encode($salary) }}, '{{ $emp->first_name }}', 'EMP-{{ $emp->id }}', '{{ $emp->department->name ?? '' }}')">
                                                        تعديل
                                                    </button>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="17" style="text-align:center">لا توجد سجلات لهذا الشهر</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </main>
    </div>

    {{-- النافذة المنبثقة (Modal) لتعديل راتب موظف واحد (مبسطة) --}}
    @if (auth()->user()->role->name === 'مدير النظام')
        <div id="salaryModal" class="modal"
            style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); overflow-y:auto;">
            <div
                style="background:#fff; margin:5% auto; padding:25px; border-radius:15px; width:90%; max-width:750px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <div
                    style="display: flex; justify-content: space-between; border-bottom:2px solid #eee; padding-bottom:15px; margin-bottom:20px;">
                    <div>
                        <h2 id="modalTitle" style="color: #1e293b; margin: 0;">تعديل الراتب</h2>
                        <p id="modalSubtitle" style="color: #64748b; margin: 5px 0 0 0; font-size: 0.85rem;"></p>
                    </div>
                    <button onclick="closeModal()"
                        style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#94a3b8;">&times;</button>
                </div>

                <form id="salaryUpdateForm" action="{{ route('salaries.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="salary_id" id="modal_salary_id">

                    {{-- عرض المعلومات الثابتة (للقراءة فقط) --}}
                    <div style="background:#f1f5f9; padding:15px; border-radius:10px; margin-bottom:20px;">
                        <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:10px;">
                            <div><strong>📌 الراتب الأساسي:</strong> <span id="display_basic">0</span> ر.ي</div>
                            <div><strong>🏠 بدل السكن:</strong> <span id="display_housing">0</span> ر.ي (<span
                                    id="display_housing_percent">0</span>%)</div>
                            <div><strong>🚗 بدل المواصلات:</strong> <span id="display_transport">0</span> ر.ي (<span
                                    id="display_transport_percent">0</span>%)</div>
                            <div><strong>🏥 التأمين الصحي:</strong> <span id="display_health">0</span> ر.ي (<span
                                    id="display_health_percent">0</span>%)</div>
                            <div><strong>📊 الضريبة:</strong> <span id="display_tax">0</span> ر.ي (<span
                                    id="display_tax_percent">0</span>%)</div>
                        </div>
                    </div>

                    {{-- العناصر المتغيرة الشهرية --}}
                    <fieldset style="border:1px solid #e2e8f0; padding:15px; border-radius:10px; margin-bottom:20px;">
                        <legend style="font-weight:bold;">📋 المتغيرات الشهرية</legend>
                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:15px;">
                            <div>
                                <label>🎁 المكافآت (ر.ي)</label>
                                <input type="number" name="bonuses" id="calc_bonuses" class="form-control"
                                    oninput="calculateNet()" value="0" step="0.01">
                            </div>
                            <div>
                                <label>💰 أقساط القروض (ر.ي)</label>
                                <input type="number" name="loan_installments" id="calc_loans" class="form-control"
                                    oninput="calculateNet()" value="0" step="0.01">
                            </div>
                            <div>
                                <label>⚠️ الجزاءات (ر.ي)</label>
                                <input type="number" name="penalties" id="calc_penalties" class="form-control"
                                    oninput="calculateNet()" value="0" step="0.01">
                            </div>
                        </div>
                    </fieldset>

                    {{-- الغياب والتأخير --}}
                    <fieldset
                        style="border:1px solid #f59e0b; padding:15px; border-radius:10px; background:#fffbeb; margin-bottom:20px;">
                        <legend style="font-weight:bold; color:#f59e0b;">⏰ الغياب والتأخير</legend>
                        <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:15px;">
                            <div>
                                <label>📅 أيام الغياب</label>
                                <input type="number" name="absence_days" id="calc_absence" class="form-control"
                                    oninput="calculateNet()" value="0" step="1">
                                <small class="text-muted" style="color:#f97316;">خصم: <span
                                        id="calc_absence_deduction">0</span> ر.ي</small>
                            </div>
                            <div>
                                <label>⏱️ دقائق التأخير</label>
                                <input type="number" name="late_minutes" id="calc_late" class="form-control"
                                    oninput="calculateNet()" value="0" step="1">
                                <small class="text-muted" style="color:#f97316;">خصم: <span
                                        id="calc_late_deduction">0</span> ر.ي</small>
                            </div>
                        </div>
                    </fieldset>

                    {{-- الصافي --}}
                    <div
                        style="background:#1e293b; padding:20px; border-radius:12px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div>الصافي المتوقع: <span id="live_net"
                                    style="font-size:1.6rem; color:#10b981; font-weight:bold;">0.00</span> ر.ي</div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="closeModal()" class="btn btn-outline"
                                style="color:#fff; border-color: #475569;">إلغاء</button>
                            <button type="submit" class="btn btn-primary"
                                style="background: #4f46e5; border: none; padding: 10px 25px;">حفظ واعتماد</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- نافذة منبثقة لإضافة مكافآت/خصومات عامة لجميع الموظفين --}}
        <div id="generalModal" class="modal"
            style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); overflow-y:auto;">
            <div
                style="background:#fff; margin:10% auto; padding:25px; border-radius:15px; width:90%; max-width:500px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <div
                    style="display: flex; justify-content: space-between; border-bottom:2px solid #eee; padding-bottom:15px; margin-bottom:20px;">
                    <div>
                        <h2 style="color: #1e293b; margin: 0;">🎁 إضافة مكافآت / خصومات عامة</h2>
                        <p style="color: #64748b; margin: 5px 0 0 0; font-size: 0.85rem;">سيتم تطبيق القيم على جميع
                            الموظفين في الشهر الحالي</p>
                    </div>
                    <button onclick="closeGeneralModal()"
                        style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#94a3b8;">&times;</button>
                </div>

                <form action="{{ route('salaries.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="salary_id" value="">
                    <input type="hidden" name="is_general" value="1">
                    <input type="hidden" name="selected_month" value="{{ request('selected_month', $date) }}">

                    <div style="margin-bottom:15px;">
                        <label class="form-label">🎁 المكافآت (ر.ي) - تضاف للجميع</label>
                        <input type="number" name="bonuses" class="form-control" placeholder="0" step="0.01"
                            value="0">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label class="form-label">💰 أقساط القروض (ر.ي) - تخصم للجميع</label>
                        <input type="number" name="loan_installments" class="form-control" placeholder="0"
                            step="0.01" value="0">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label class="form-label">⚠️ الجزاءات (ر.ي) - تخصم للجميع</label>
                        <input type="number" name="penalties" class="form-control" placeholder="0" step="0.01"
                            value="0">
                    </div>

                    <div style="background:#fff3cd; padding:10px; border-radius:8px; margin-bottom:20px;">
                        <small style="color:#856404;">⚠️ ملاحظة: هذه القيم ستضاف/تخصم من جميع الموظفين في الشهر
                            {{ $date }}</small>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="closeGeneralModal()" class="btn btn-outline">إلغاء</button>
                        <button type="submit" class="btn btn-primary" style="background: #4f46e5; border: none;">تطبيق
                            على الجميع</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        function openSalaryModal(salary, name, empCode, deptName) {
            if (!document.getElementById('salaryUpdateForm')) return;
            document.getElementById('salaryUpdateForm').reset();

            const isGeneral = (salary === null);
            document.getElementById('modalTitle').innerText = isGeneral ? 'إضافة إضافات/خصومات عامة' : 'تعديل راتب: ' +
                name;
            document.getElementById('modalSubtitle').innerText = isGeneral ? '' : 'رقم: ' + empCode + ' | القسم: ' +
                deptName;
            document.getElementById('modal_salary_id').value = salary ? salary.id : '';

            if (salary) {
                const basic = salary.basic_salary || 0;
                document.getElementById('display_basic').innerText = basic.toLocaleString();
                document.getElementById('display_housing').innerText = (basic * (salary.housing_percentage || 0) / 100)
                    .toLocaleString();
                document.getElementById('display_housing_percent').innerText = salary.housing_percentage || 0;
                document.getElementById('display_transport').innerText = (basic * (salary.transport_percentage || 0) / 100)
                    .toLocaleString();
                document.getElementById('display_transport_percent').innerText = salary.transport_percentage || 0;
                document.getElementById('display_health').innerText = (basic * (salary.health_percentage || 0) / 100)
                    .toLocaleString();
                document.getElementById('display_health_percent').innerText = salary.health_percentage || 0;
                document.getElementById('display_tax').innerText = (basic * (salary.tax_percentage || 0) / 100)
                    .toLocaleString();
                document.getElementById('display_tax_percent').innerText = salary.tax_percentage || 0;

                document.getElementById('calc_bonuses').value = salary.bonuses || 0;
                document.getElementById('calc_loans').value = salary.loan_installments || 0;
                document.getElementById('calc_penalties').value = salary.penalties || 0;
                document.getElementById('calc_absence').value = salary.absence_days || 0;
                document.getElementById('calc_late').value = salary.late_minutes || 0;
            }

            calculateNet();
            document.getElementById('salaryModal').style.display = 'block';
        }

        function closeModal() {
            if (document.getElementById('salaryModal')) {
                document.getElementById('salaryModal').style.display = 'none';
            }
        }

        function openGeneralModal() {
            document.getElementById('generalModal').style.display = 'block';
        }

        function closeGeneralModal() {
            document.getElementById('generalModal').style.display = 'none';
        }

        function calculateNet() {
            const basicText = document.getElementById('display_basic').innerText;
            const basic = parseFloat(basicText.replace(/,/g, '')) || 0;

            const housingPercent = parseFloat(document.getElementById('display_housing_percent').innerText) || 0;
            const transportPercent = parseFloat(document.getElementById('display_transport_percent').innerText) || 0;
            const healthPercent = parseFloat(document.getElementById('display_health_percent').innerText) || 0;
            const taxPercent = parseFloat(document.getElementById('display_tax_percent').innerText) || 0;

            const bonuses = parseFloat(document.getElementById('calc_bonuses').value) || 0;
            const loans = parseFloat(document.getElementById('calc_loans').value) || 0;
            const penalties = parseFloat(document.getElementById('calc_penalties').value) || 0;
            const absenceDays = parseFloat(document.getElementById('calc_absence').value) || 0;
            const lateMinutes = parseFloat(document.getElementById('calc_late').value) || 0;

            const housingAmount = basic * (housingPercent / 100);
            const transportAmount = basic * (transportPercent / 100);
            const healthAmount = basic * (healthPercent / 100);
            const taxAmount = basic * (taxPercent / 100);

            const dailyRate = basic / 30;
            const minuteRate = dailyRate / 8 / 60;
            const absenceDeduction = absenceDays * dailyRate;
            const lateDeduction = lateMinutes * minuteRate;

            document.getElementById('calc_absence_deduction').innerText = absenceDeduction.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            document.getElementById('calc_late_deduction').innerText = lateDeduction.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });

            const totalEarnings = basic + housingAmount + transportAmount + bonuses;
            const totalDeductions = healthAmount + taxAmount + loans + penalties + absenceDeduction + lateDeduction;
            const net = totalEarnings - totalDeductions;

            document.getElementById('live_net').innerText = net.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
        }

        window.onclick = function(event) {
            let modal = document.getElementById('salaryModal');
            let generalModal = document.getElementById('generalModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            if (event.target == generalModal) {
                generalModal.style.display = 'none';
            }
        }
    </script>
@endsection

@section('styles')
    <style>
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            min-width: 1300px;
        }

        .table td,
        .table th {
            white-space: nowrap;
            padding: 8px 6px;
        }

        .card {
            width: 100%;
        }
    </style>
@endsection
