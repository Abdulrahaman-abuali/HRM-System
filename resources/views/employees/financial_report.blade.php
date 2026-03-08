<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', 'Tajawal', sans-serif; background: #fff; margin: 0; direction: rtl; }
        .report-container { max-width: 1200px; margin: auto; padding: 2rem; }
        .report-banner {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.5rem; background: #f8fafc; border: 1px solid #e5e7eb;
            border-radius: 0.75rem; margin-bottom: 2rem;
        }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { background: #f1f5f9; padding: 12px; border: 1px solid #e2e8f0; text-align: right; font-size: 14px; }
        .table td { padding: 12px; border: 1px solid #f1f5f9; font-size: 14px; }
        .section-title { background: #ecfdf5; color: #10b981; padding: 10px; border-radius: 8px; font-weight: bold; margin: 20px 0; border-right: 4px solid #10b981; }
        .footer-sigs { margin-top: 4rem; display: flex; justify-content: space-around; }
        .sig-box { text-align: center; width: 250px; border-top: 1px dashed #6b7280; padding-top: 10px; font-weight: bold; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="report-container">
        <header class="report-banner">
            <div>
                <h1 style="color: #10b981; margin: 0; font-size: 24px;">الدائرة المالية - مسيرات الرواتب</h1>
                <p style="color: #64748b; margin: 5px 0;">كشف استحقاقات الموظف التفصيلي</p>
            </div>
            <div style="text-align: center;">
                <h2 style="margin: 0;">{{ $data->first_name }} {{ $data->last_name }}</h2>
                <p>الرقم الوظيفي: ID-{{ $data->id }}</p>
            </div>
            <div style="text-align: left;">
                <p>تاريخ التقرير: {{ date('Y-m-d') }}</p>
            </div>
        </header>

        <div class="section-title">💰 سجل الرواتب والمستحقات (آخر 12 شهر)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>الشهر</th>
                    <th>الراتب الأساسي</th>
                    <th>إجمالي البدلات</th>
                    <th>الخصومات / الجزاءات</th>
                    <th style="background: #f0fdf4;">الصافي المستلم</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->salaries()->latest()->get() as $salary)
                <tr>
                    <td>{{ $salary->month }}</td>
                    <td>{{ number_format($salary->basic_salary) }} ر.ي</td>
                    <td style="color: #10b981;">+{{ number_format($salary->allowances ?? 0) }} ر.ي</td>
                    <td style="color: #ef4444;">-{{ number_format($salary->penalties ?? 0) }} ر.ي</td>
                    <td style="font-weight: bold; background: #f9fafb;">
                        {{ number_format($salary->basic_salary + ($salary->allowances ?? 0) - ($salary->penalties ?? 0)) }} ر.ي
                    </td>
                    <td>{{ $salary->status }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <footer class="footer-sigs">
            <div class="sig-box">توقيع الموظف</div>
            <div class="sig-box">المحاسب المختص</div>
            <div class="sig-box">اعتماد المدير المالي</div>
        </footer>
    </div>
</body>
</html>
