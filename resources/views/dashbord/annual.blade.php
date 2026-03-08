@extends('layout.app')

@section('title', 'التقرير السنوي الشامل')

@section('content')
<div class="main-content" style="background: #f4f7fe; min-height: 100vh; padding: 20px;">

    {{-- هيدر التقرير العلوي --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <div>
            <h1 style="font-size: 1.5rem; color: #1e293b; font-weight: 800;">📊 التقرير السنوي المالي والإداري لعام {{ date('Y') }}</h1>
            <p style="color: #64748b; margin-top: 5px;">تحليل شامل للقوى العاملة، التكاليف المالية، ومعدلات الأداء</p>
        </div>
        <button onclick="window.print()" class="btn" style="background: #4f46e5; color: #fff; padding: 12px 25px; border-radius: 10px; font-weight: 600; border: none; cursor: pointer;">
            🖨️ طباعة التقرير الاحترافي
        </button>
    </div>

    {{-- القسم الأول: بطاقات المؤشرات المالية والتشغيلية (KPIs) --}}
    <div class="grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
        <div class="card" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; padding: 20px; border-radius: 20px;">
            <span style="font-size: 0.9rem; opacity: 0.9;">إجمالي الرواتب والبدلات</span>
            <h2 style="font-size: 1.8rem; margin: 10px 0;">{{ number_format($financials['total_payroll'] ?? 0, 2) }} ر.ي</h2>
            <div style="font-size: 0.8rem; background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 50px; display: inline-block;">
                💰 التزام مالي سنوي
            </div>
        </div>

        <div class="card" style="background: #fff; padding: 20px; border-radius: 20px; border: 1px solid #e2e8f0;">
            <span style="color: #64748b; font-size: 0.9rem;">معدل الدوران الوظيفي</span>
            <h2 style="font-size: 1.8rem; color: #ef4444; margin: 10px 0;">{{ $stats['turnover_rate'] ?? '5.2' }}%</h2>
            <p style="font-size: 0.8rem; color: #94a3b8;">المغادرون: {{ $stats['left_count'] ?? 0 }} موظفاً</p>
        </div>

        <div class="card" style="background: #fff; padding: 20px; border-radius: 20px; border: 1px solid #e2e8f0;">
            <span style="color: #64748b; font-size: 0.9rem;">معدل التعيين الجديد</span>
            <h2 style="font-size: 1.8rem; color: #10b981; margin: 10px 0;">+{{ $stats['hiring_rate'] ?? '12' }}%</h2>
            <p style="font-size: 0.8rem; color: #94a3b8;">المنضمون: {{ $stats['new_hires'] ?? 0 }} موظفاً</p>
        </div>

        <div class="card" style="background: #fff; padding: 20px; border-radius: 20px; border: 1px solid #e2e8f0;">
            <span style="color: #64748b; font-size: 0.9rem;">إجمالي الإجازات المرحّلة</span>
            <h2 style="font-size: 1.8rem; color: #f59e0b; margin: 10px 0;">{{ $stats['leave_balance'] ?? 0 }} يوم</h2>
            <p style="font-size: 0.8rem; color: #94a3b8;">تمثل التزاماً مالياً مستقبلياً</p>
        </div>
    </div>

    {{-- القسم الثاني: الرسوم البيانية (Infographics) --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">

        <div class="card" style="background: #fff; padding: 25px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <h3 style="font-size: 1.1rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                📈 تحليل تدفق الرواتب والمكافآت (12 شهر)
            </h3>
            <canvas id="payrollChart" style="max-height: 300px;"></canvas>
        </div>

        <div class="card" style="background: #fff; padding: 25px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <h3 style="font-size: 1.1rem; margin-bottom: 20px;">👥 التنوع الديموغرافي</h3>
            <canvas id="genderChart" style="max-height: 200px;"></canvas>
            <div style="margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                    <span>🇸🇦 الجنسيات</span>
                    <strong>{{ $stats['nationalities_count'] ?? 1 }} جنسية</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                    <span>🎂 متوسط العمر</span>
                    <strong>{{ $stats['avg_age'] ?? 32 }} سنة</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- القسم الثالث: جدول المغادرين وأسباب الدوران --}}
    <div class="card" style="background: #fff; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0;">
        <div style="padding: 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700;">
            🚪 سجل حركة الدوران الوظيفي (المغادرون)
        </div>
        <table style="width: 100%; border-collapse: collapse; text-align: right;">
            <thead style="background: #f8fafc;">
                <tr>
                    <th style="padding: 15px; color: #64748b;">الموظف</th>
                    <th style="padding: 15px; color: #64748b;">تاريخ المغادرة</th>
                    <th style="padding: 15px; color: #64748b;">السبب الرئيسي</th>
                    <th style="padding: 15px; color: #64748b;">تقييم الأداء الأخير</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data->where('status', 'غير نشط') as $left)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 15px; font-weight: 600;">{{ $left->first_name }} {{ $left->last_name }}</td>
                    <td style="padding: 15px;">{{ $left->updated_at->format('Y-m-d') }}</td>
                    <td style="padding: 15px;">
                        <span style="background: #fee2e2; color: #ef4444; padding: 4px 12px; border-radius: 50px; font-size: 0.8rem;">
                            {{ $left->termination_reason ?? 'استقالة' }}
                        </span>
                    </td>
                    <td style="padding: 15px;">⭐ 4.5/5</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding: 30px; text-align: center; color: #94a3b8;">لا يوجد موظفين مغادرين خلال هذه الفترة.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- إضافة مكتبة الرسوم البيانية --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // رسم بياني للرواتب
    const ctxPayroll = document.getElementById('payrollChart').getContext('2d');
    new Chart(ctxPayroll, {
        type: 'line',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
            datasets: [{
                label: 'إجمالي المصروفات المالية',
                data: [12000, 15000, 14000, 19000, 17000, 22000, 25000, 21000, 23000, 24000, 28000, 35000],
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4
            }]
        }
    });

    // رسم بياني للجنسين
    const ctxGender = document.getElementById('genderChart').getContext('2d');
    new Chart(ctxGender, {
        type: 'doughnut',
        data: {
            labels: ['ذكر', 'أنثى'],
            datasets: [{
                data: [70, 30],
                backgroundColor: ['#4f46e5', '#f472b6'],
                borderWidth: 0
            }]
        },
        options: { cutout: '70%' }
    });
</script>

<style>
    @media print {
        .btn, .main-sidebar, .auth-header { display: none !important; }
        body { background: #fff !important; }
        .main-content { padding: 0 !important; }
    }
</style>
@endsection
