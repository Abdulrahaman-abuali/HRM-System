@extends('reports.layout')

@section('content')
    {{-- بطاقات المؤشرات --}}
    <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
        <div style="flex:1; background:#4f46e5; color:#fff; padding:20px; border-radius:15px;">
            <div>إجمالي الرواتب السنوية</div>
            <h2>{{ number_format($financials['total_payroll'] ?? 0, 2) }} ر.ي</h2>
        </div>
        <div style="flex:1; background:#fff; border:1px solid #e2e8f0; padding:20px; border-radius:15px;">
            <div>معدل الدوران الوظيفي</div>
            <h2 style="color:#ef4444;">{{ $stats['turnover_rate'] ?? 0 }}%</h2>
            <small>المغادرون: {{ $stats['left_count'] ?? 0 }}</small>
        </div>
        <div style="flex:1; background:#fff; border:1px solid #e2e8f0; padding:20px; border-radius:15px;">
            <div>معدل التعيين الجديد</div>
            <h2 style="color:#10b981;">+{{ $stats['hiring_rate'] ?? 0 }}%</h2>
            <small>المنضمون: {{ $stats['new_hires'] ?? 0 }}</small>
        </div>
        <div style="flex:1; background:#fff; border:1px solid #e2e8f0; padding:20px; border-radius:15px;">
            <div>رصيد الإجازات</div>
            <h2 style="color:#f59e0b;">{{ $stats['leave_balance'] ?? 0 }} يوم</h2>
        </div>
    </div>

    {{-- جدول المغادرين --}}
    <div style="margin-bottom: 2rem;">
        <h3>سجل المغادرين</h3>
        <table class="report-table">
            <thead>
                32<th>الموظف</th><th>تاريخ المغادرة</th><th>السبب</th></tr>
            </thead>
            <tbody>
                @forelse($data->where('status', 'inactive') as $emp)
                    <tr><td>{{ $emp->first_name }} {{ $emp->last_name }}</td><td>{{ $emp->updated_at?->format('Y-m-d') }}</td><td>{{ $emp->termination_reason ?? 'استقالة' }}</td></tr>
                @empty
                    <tr><td colspan="3">لا يوجد مغادرون</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
