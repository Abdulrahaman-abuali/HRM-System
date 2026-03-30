@extends('layout.app')

@section('title', 'سجل الحضور والانصراف')

@section('content')
<div class="main-content">


    @auth
        {{-- ================================================= --}}
        {{-- 1. واجهة الموظف: الأرشيف التاريخي الشخصي فقط      --}}
        {{-- ================================================= --}}
        @if(auth()->user()->role?->name === 'موظف')
            <section class="section">
                <article class="card shadow-sm" style="border-top: 4px solid #6366f1; border-radius: 8px;">
                    <header class="card-header" style="background: #f8fafc; padding: 15px; border-bottom: 1px solid #eee;">
                        <h2 style="font-size: 1.1rem; margin: 0; color: #312e81;">📋 الأرشيف التاريخي والسجلات السابقة</h2>
                    </header>
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table" style="width: 100%; border-collapse: collapse;">
                                <thead style="background: #f1f5f9;">
                                    <tr>
                                        <th style="padding: 12px; text-align: right;">التاريخ</th>
                                        <th style="padding: 12px; text-align: right;">وقت الحضور</th>
                                        <th style="padding: 12px; text-align: right;">وقت الانصراف</th>
                                        <th style="padding: 12px; text-align: right;">ساعات العمل</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-history-tbody">
                                    @forelse($records as $rec)
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 12px;"><strong>{{ $rec->date }}</strong></td>
                                            <td style="padding: 12px;">{{ $rec->check_in }}</td>
                                            <td style="padding: 12px;">{{ $rec->check_out ?? '—' }}</td>
                                            <td style="padding: 12px;">
                                                @if($rec->check_in && $rec->check_out)
                                                    {{ \Carbon\Carbon::parse($rec->check_in)->diff(\Carbon\Carbon::parse($rec->check_out))->format('%h س و %i د') }}
                                                @else — @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" style="text-align: center; padding: 30px;">لا توجد سجلات سابقة.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div style="padding: 15px;">{{ $records->links() }}</div>
                    </div>
                </article>
            </section>

        {{-- ================================================= --}}
        {{-- 2. واجهة المدير: حالة اليوم (الحضور والغياب) + الأرشيف --}}
        {{-- ================================================= --}}
        @elseif(in_array(auth()->user()->role?->name, ['مدير النظام', 'مدير القسم']))
            @php
                $presentToday = $employees->filter(fn($e) => $e->attendanceRecords->isNotEmpty());
                $absentToday = $employees->filter(fn($e) => $e->attendanceRecords->isEmpty());
            @endphp

            <div style="display: flex; gap: 10px; margin-bottom: 20px; align-items: flex-end;">
                <form method="GET" action="{{ route('leave') }}" style="display: flex; gap: 10px; align-items: flex-end;">
                    <div class="form-group">
                        <label style="font-size: 0.8rem; display: block; margin-bottom: 5px;">معاينة تاريخ محدد:</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="background: #6366f1; border: none; padding: 7px 15px; border-radius: 5px; color: white;">تحديث البيانات</button>
                </form>
                <div style="display: flex; gap: 10px;">
                    <span style="background: #eef2ff; color: #4338ca; padding: 8px 15px; border-radius: 5px; border-right: 4px solid #4338ca;">إجمالي الحضور: <span id="total-present">{{ $presentToday->count() }}</span></span>
                    <span style="background: #fef2f2; color: #b91c1c; padding: 8px 15px; border-radius: 5px; border-right: 4px solid #b91c1c;">إجمالي الغياب: {{ $absentToday->count() }}</span>
                </div>
            </div>

            {{-- جدول الحاضرين اليوم (مضاف إليه وقت الانصراف وساعات العمل) --}}
            <section class="section" style="margin-bottom: 30px;">
                <article class="card shadow-sm" style="border-top: 4px solid #10b981; border-radius: 8px;">
                    <header class="card-header" style="background: #f8fafc; padding: 15px;">
                        <h2 style="font-size: 1rem; margin: 0; color: #065f46;">✅ سجل الحاضرين بتاريخ ({{ $date }})</h2>
                    </header>
                    <div class="card-body" style="padding: 0;">
                        <table class="table" style="width: 100%;">
                            <thead style="background: #f9fafb;">

                                    <th style="padding: 12px; text-align: right;">الموظف</th>
                                    <th style="padding: 12px; text-align: right;">وقت الحضور</th>
                                    <th style="padding: 12px; text-align: right;">وقت الانصراف</th>
                                    <th style="padding: 12px; text-align: right;">ساعات العمل</th>
                                    <th style="padding: 12px; text-align: right;">الحالة</th>
                                </tr>
                            </thead>
                            <tbody id="present-tbody">
                                @forelse($presentToday as $emp)
                                    @php
                                        $rec = $emp->attendanceRecords->first();
                                        $workHours = '—';
                                        if ($rec->check_in && $rec->check_out) {
                                            $workHours = \Carbon\Carbon::parse($rec->check_in)->diff(\Carbon\Carbon::parse($rec->check_out))->format('%h س و %i د');
                                        }
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f3f4f6;">
                                        <td style="padding: 12px;">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                        <td style="padding: 12px;">{{ $rec->check_in }}</td>
                                        <td style="padding: 12px;">{{ $rec->check_out ?? 'بانتظار الخروج' }}</td>
                                        <td style="padding: 12px;">{{ $workHours }}</td>
                                        <td style="padding: 12px;"><span class="badge" style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 10px;">{{ $rec->check_out ? 'مكتمل' : 'على رأس العمل' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" style="text-align: center; padding: 20px;">لا يوجد حضور مسجل لهذا اليوم.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            {{-- جدول الغائبين اليوم --}}
            <section class="section" style="margin-bottom: 30px;">
                <article class="card shadow-sm" style="border-top: 4px solid #ef4444; border-radius: 8px;">
                    <header class="card-header" style="background: #fef2f2; padding: 15px;">
                        <h2 style="font-size: 1rem; margin: 0; color: #991b1b;">❌ موظفون لم يسجلوا حضوراً اليوم</h2>
                    </header>
                    <div class="card-body" style="padding: 0;">
                        <table class="table" style="width: 100%;">
                            <tbody id="absent-tbody">
                                @forelse($absentToday as $emp)
                                    <tr style="border-bottom: 1px solid #fee2e2;">
                                        <td style="padding: 12px;">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                        <td style="padding: 12px;">{{ $emp->department->name ?? '—' }}</td>
                                        <td style="padding: 12px; text-align: left;"><span class="badge" style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 10px;">غائب / لم يدخل</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" style="text-align: center; padding: 20px;">الكل حاضر اليوم!</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            {{-- أرشيف السجلات العامة للمدير --}}
            <section class="section">
                <article class="card shadow-sm" style="border-top: 4px solid #4f46e5; border-radius: 8px;">
                    <header class="card-header" style="background: #f8fafc; padding: 15px;">
                        <h2 style="font-size: 1rem; margin: 0; color: #312e81;">📅 أرشيف السجلات التاريخية (جميع الموظفين)</h2>
                    </header>
                    <div class="card-body" style="padding: 0;">
                        <table class="table" style="width: 100%;">
                            <thead style="background: #f1f5f9;">
                                <tr>
                                    <th style="padding: 12px; text-align: right;">اسم الموظف</th>
                                    <th style="padding: 12px; text-align: right;">التاريخ</th>
                                    <th style="padding: 12px; text-align: right;">الحضور</th>
                                    <th style="padding: 12px; text-align: right;">الانصراف</th>
                                </tr>
                            </thead>
                            <tbody id="history-tbody">
                                @foreach($allHistory as $history)
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 12px;">{{ $history->employee->first_name }} {{ $history->employee->last_name }}</td>
                                        <td style="padding: 12px;">{{ $history->date }}</td>
                                        <td style="padding: 12px;">{{ $history->check_in }}</td>
                                        <td style="padding: 12px;">{{ $history->check_out ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                       @if($allHistory instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div style="padding: 15px;">{{ $allHistory->links() }}</div>
                        @endif
                    </div>
                </article>
            </section>
        @endif
    @endauth
</div>
<script>
    // Function to update tables
    function updateTables() {
    // 1. الحصول على التاريخ الحالي المختار من حقل الإدخال في الصفحة
    const dateInput = document.querySelector('input[name="date"]');
    const selectedDate = dateInput ? dateInput.value : new Date().toISOString().split('T')[0];

    // 2. تعديل الرابط ليرسل التاريخ (ويمكن للسيرفر استنتاج القسم من المستخدم المسجل)
    // ملاحظة: السيرفر في Laravel سيعرف من هو "مدير القسم" تلقائياً عبر Session
    fetch(`/face-attendance/latest?date=${selectedDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {

                // --- تحديث جدول الحاضرين ---
                const presentTbody = document.getElementById('present-tbody');
                if (presentTbody) {
                    // نفلتر البيانات التي تحتوي على وقت حضور حقيقي
                    const presentData = data.data.filter(r => r.check_in && r.check_in !== '--');

                    presentTbody.innerHTML = ''; // مسح الجدول القديم

                    presentData.forEach(record => {
                        // داخل حلقة presentData.forEach
                    presentTbody.innerHTML += `
                        <tr style="border-bottom: 1px solid #f3f4f6; ${record.is_late ? 'background-color: #fff9f0;' : ''}">
                            <td style="padding: 12px;">${record.employee_name}</td>
                            <td style="padding: 12px;">${record.check_in}</td>
                            <td style="padding: 12px;">${record.check_out || 'بانتظار الخروج'}</td>
                            <td style="padding: 12px;">${record.work_hours}</td>
                            <td style="padding: 12px;">
                                <span class="badge" style="
                                    padding: 4px 10px;
                                    border-radius: 10px;
                                    font-weight: bold;
                                    background: ${record.is_late ? '#fef3c7' : (record.check_out ? '#dcfce7' : '#e0f2fe')};
                                    color: ${record.is_late ? '#92400e' : (record.check_out ? '#166534' : '#0369a1')};
                                ">
                                    ${record.status}
                                </span>
                            </td>
                        </tr>
                    `;
                    });
                }

                // --- تحديث إجمالي عدد الحضور في المربعات العلوية ---
                const totalSpan = document.getElementById('total-present');
                if (totalSpan) {
                    const count = data.data.filter(r => r.check_in && r.check_in !== '--').length;
                    totalSpan.innerText = count;
                }

                // --- تحديث جدول الأرشيف (اختياري التحديث اللحظي له) ---
                const historyTbody = document.getElementById('history-tbody');
                if (historyTbody) {
                    historyTbody.innerHTML = '';
                    data.data.forEach(record => {
                        historyTbody.innerHTML += `
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;">${record.employee_name}</td>
                                <td style="padding: 12px;">${record.date ? record.date.split('T')[0] : selectedDate}</td>
                                <td style="padding: 12px;">${record.check_in || '--'}</td>
                                <td style="padding: 12px;">${record.check_out || '--'}</td>
                            </tr>
                        `;
                    });
                }
            }
        })
        .catch(error => console.error('خطأ في جلب بيانات البصمة:', error));
}

// تعديل وقت التحديث ليكون منطقياً (كل 10 ثوانٍ بدلاً من ثانية واحدة لتخفيف الضغط)
updateTables();
let attendanceInterval = setInterval(updateTables, 10000);

// التحديث عند العودة للتبويب
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        updateTables();
    }
});
</script>
@endsection
