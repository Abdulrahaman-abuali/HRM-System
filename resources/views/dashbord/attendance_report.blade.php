<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    {{-- ربط ملف التنسيق الرئيسي --}}
    <link rel="stylesheet" href="{{ asset('style/CSS.css') }}">

    <style>
        /* خطوط تدعم العربية وتنسيقات الطباعة */
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

        /* تنسيقات خاصة بالطباعة */
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

        /* تلوين الحالات بناءً على نوعها */
        .status-present { color: #16a34a; font-weight: bold; }
        .status-absent { color: #dc2626; font-weight: bold; }
        .status-late { color: #f59e0b; font-weight: bold; }
    </style>
</head>
<body>

    {{-- زر الطباعة العائم (يختفي عند الطباعة) --}}
    <div class="no-print print-floating-btn">
        <button onclick="window.print()" class="btn btn-primary" style="box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);">
            <span>🖨️ طباعة السجل أو حفظ PDF</span>
        </button>
    </div>

    <div class="report-print-container">
        {{-- ترويسة التقرير الرسمية --}}
        <header class="report-banner">
            <div class="company-brand">
                <h1 class="page-title" style="color: #4f46e5;">نظام إدارة الموارد البشرية</h1>
                <p class="page-subtitle">قسم إدارة الشؤون الإدارية</p>
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
                            <th>التاريخ</th>
                            <th>وقت الحضور</th>
                            <th>وقت الانصراف</th>
                            <th style="background: #eef2ff; font-weight: bold;">الحالة</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalPresent = 0;
                            $totalAbsent = 0;
                            $totalLate = 0;
                        @endphp

                        @forelse($data as $row)
                            @php
                                // إحصائيات سريعة
                                if($row->status == 'حاضر' || $row->status == 'approved') $totalPresent++;
                                elseif($row->status == 'غائب' || $row->status == 'rejected') $totalAbsent++;
                                elseif($row->status == 'تأخير' || $row->status == 'pending') $totalLate++;
                            @endphp
                            <tr>
                                <td><span class="badge badge-outline">EMP-{{ $row->employee_id ?? '---' }}</span></td>
                                <td class="activity-strong">{{ $row->employee_name }}</td>
                                <td>{{ $row->date ?? $row->start_date }}</td>
                                <td>{{ $row->check_in ?? '--:--' }}</td>
                                <td>{{ $row->check_out ?? '--:--' }}</td>
                                <td style="background: #f8fafc;">
                                    @php
                                        $statusText = $row->status;
                                        if($statusText == 'approved') $statusText = 'معتمدة';
                                        elseif($statusText == 'rejected') $statusText = 'مرفوضة';
                                        elseif($statusText == 'pending') $statusText = 'انتظار';
                                    @endphp
                                    <span class="badge {{ in_array($row->status, ['حاضر', 'approved']) ? 'badge-success' : (in_array($row->status, ['غائب', 'rejected']) ? 'badge-danger' : 'badge-warning') }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="page-subtitle">{{ $row->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">لا توجد سجلات حضور أو إجازات لهذه الفترة</td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($data->count() > 0)
                    <tfoot>
                        <tr class="total-summary-row">
                            <td colspan="2">إجمالي ملخص الفترة</td>
                            <td colspan="2" style="color: #16a34a;">عدد الحضور: {{ $totalPresent }}</td>
                            <td style="color: #dc2626;">عدد الغياب: {{ $totalAbsent }}</td>
                            <td style="background: #4f46e5; color: #fff;">التأخير: {{ $totalLate }}</td>
                            <td>---</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- قسم التوقيعات والاعتماد --}}
        <div class="footer" style="margin-top: 4rem; display: flex; justify-content: space-around;">
            <div style="text-align: center; width: 250px;">
                <p class="activity-strong">توقيع مسؤول الحضور</p>
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
