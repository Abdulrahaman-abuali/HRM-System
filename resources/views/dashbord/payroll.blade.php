<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    <link rel="stylesheet" href="{{ asset('style/CSS.css') }}">

    <style>
        /* خط يدعم العربية لضمان ظهور الكلمات بشكل صحيح في الـ PDF */
        body { font-family: 'DejaVu Sans', 'Tajawal', sans-serif; background: #fff; padding: 0; }

        .report-print-container { max-width: 1200px; margin: auto; padding: 2rem; }

        .report-banner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            margin-bottom: 2rem;
        }

        .total-summary-row {
            background: #f1f5f9 !important;
            font-weight: 700;
            font-size: 1rem;
        }

        /* تنسيقات خاصة بالطباعة الورقية */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; }
            .report-print-container { padding: 0; max-width: 100%; }
            .card { border: none; box-shadow: none; }
            .report-banner { border: 1px solid #ddd; background: #fff !important; }
        }

        .print-floating-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <div class="no-print print-floating-btn">
        <button onclick="window.print()" class="btn btn-primary" style="box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);">
            <span>🖨️ طباعة التقرير أو حفظ PDF</span>
        </button>
    </div>

    <div class="report-print-container">
        <header class="report-banner">
            <div class="company-brand">
                <h1 class="page-title" style="color: #4f46e5;">نظام إدارة الموارد البشرية</h1>
                <p class="page-subtitle">قسم الشؤون المالية والرواتب</p>
            </div>
            <div class="report-info" style="text-align: center;">
                <h2 class="section-title" style="font-size: 1.4rem;">{{ $title }}</h2>
                <p class="page-subtitle">الفترة: {{ $from }} ⬅️ {{ $to }}</p>
            </div>
            <div class="report-meta" style="text-align: left;">
                <div class="badge badge-info">تاريخ الاستخراج: {{ date('Y-m-d') }}</div>
                <p class="page-subtitle" style="margin-top: 5px;">المسؤول: {{ auth()->user()->name }}</p>
            </div>
        </header>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>رقم الموظف</th>
                            <th>الاسم الكامل</th>
                            <th>الأساسي</th>
                            <th style="color: #16a34a;">البدلات (+)</th>
                            <th style="color: #dc2626;">الخصومات (-)</th>
                            <th style="background: #eef2ff; font-weight: bold;">الصافي النهائي</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalBase = 0; $totalExtras = 0; $totalDeducts = 0; $totalNet = 0;
                        @endphp

                        @forelse($data as $row)
                            @php
                                // حساب الإضافات مع حماية ضد القيم الفارغة
                                $basic = $row->basic_salary ?? 0;
                                $housing = $row->housing_percentage ?? 0;
                                $transport = $row->transport_percentage ?? 0;
                                $bonuses = $row->bonuses ?? 0;

                                $extras = $bonuses + ($basic * ($housing + $transport) / 100);

                                // حساب الخصومات
                                $penalties = $row->penalties ?? 0;
                                $loans = $row->loan_installments ?? 0;
                                $health = $row->health_percentage ?? 0;
                                $tax = $row->tax_percentage ?? 0;

                                $deducts = $penalties + $loans + $health + $tax;

                                // تجميع الإجماليات
                                $totalBase += $basic;
                                $totalExtras += $extras;
                                $totalDeducts += $deducts;
                                $totalNet += ($row->net_salary ?? 0);
                            @endphp
                            <tr>
                                <td><span class="badge badge-outline">EMP-{{ $row->employee->id }}</span></td>
                                <td class="activity-strong">{{ $row->employee->first_name }} {{ $row->employee->last_name }}</td>
                                <td>{{ number_format($basic, 2) }}</td>
                                <td style="color: #16a34a;">{{ number_format($extras, 2) }}</td>
                                <td style="color: #dc2626;">{{ number_format($deducts, 2) }}</td>
                                <td class="salary-amount-net" style="background: #f8fafc; font-weight: bold;">
                                    {{ number_format($row->net_salary ?? 0, 2) }}
                                </td>
                                <td>
                                    <span class="badge {{ ($row->status == 'مدفوع') ? 'badge-success' : 'badge-warning' }}">
                                        {{ $row->status ?? 'معلق' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">لا توجد سجلات رواتب لهذه الفترة</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($data->count() > 0)
                    <tfoot>
                        <tr class="total-summary-row">
                            <td colspan="2">الإجمالي الكلي (ريال يمني)</td>
                            <td>{{ number_format($totalBase, 2) }}</td>
                            <td style="color: #16a34a;">{{ number_format($totalExtras, 2) }}</td>
                            <td style="color: #dc2626;">{{ number_format($totalDeducts, 2) }}</td>
                            <td style="background: #4f46e5; color: #fff;">{{ number_format($totalNet, 2) }}</td>
                            <td>---</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="footer" style="margin-top: 4rem; display: flex; justify-content: space-around;">
            <div style="text-align: center; width: 250px;">
                <p class="activity-strong">توقيع المحاسب المالي</p>
                <div style="margin-top: 3rem; border-top: 1px dashed #6b7280;"></div>
            </div>
            <div style="text-align: center; width: 250px;">
                <p class="activity-strong">اعتماد مدير الموارد البشرية</p>
                <div style="margin-top: 3rem; border-top: 1px dashed #6b7280;"></div>
            </div>
        </div>
    </div>

</body>
</html>
