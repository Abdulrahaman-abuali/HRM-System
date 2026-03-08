<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('style/CSS.css') }}">
    <style>
        body { font-family: 'DejaVu Sans', 'Tajawal', sans-serif; background: #fff; margin: 0; }
        .report-container { max-width: 1200px; margin: auto; padding: 2rem; }
        .report-banner {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.5rem; background: #f8fafc; border: 1px solid #e5e7eb;
            border-radius: 0.75rem; margin-bottom: 2rem;
        }
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f1f5f9; padding: 12px; border-bottom: 2px solid #e2e8f0; text-align: right; font-size: 14px; }
        .table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; }

        /* تنسيق الحالات */
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-active { background-color: #dcfce7; color: #166534; } /* أخضر للنشط */
        .badge-inactive { background-color: #fee2e2; color: #991b1b; } /* أحمر للموقوف */

        .footer-sigs { margin-top: 5rem; display: flex; justify-content: space-around; }
        .sig-box { text-align: center; width: 250px; border-top: 1px dashed #6b7280; padding-top: 10px; font-weight: bold; }

        @media print { .no-print { display: none !important; } .report-container { padding: 0; } }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 20px; left: 20px;">
        <button onclick="window.print()" class="btn btn-primary" style="padding: 10px 20px; cursor: pointer;">🖨️ طباعة التقرير الرسمي</button>
    </div>

    <div class="report-container">
        <header class="report-banner">
            <div>
                <h1 style="color: #4f46e5; margin: 0; font-size: 24px;">نظام إدارة الموارد البشرية</h1>
                <p style="color: #64748b; margin: 5px 0;">سجل بيانات الموظفين - نسخة التقرير</p>
            </div>
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 20px;">{{ $title }}</h2>
                <p>تاريخ الاستخراج: {{ date('Y-m-d') }}</p>
            </div>
            <div style="text-align: left;">
                <span style="background: #eef2ff; color: #4f46e5; padding: 8px 20px; border-radius: 20px; font-weight: bold; border: 1px solid #c7d2fe;">
                    إجمالي العدد: {{ $stats['total'] ?? $data->count() }}
                </span>
            </div>
        </header>

        <div style="border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden; background: #fff;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 10%;">رقم الموظف</th>
                        <th style="width: 20%;">الاسم الكامل</th>
                        <th style="width: 15%;">القسم</th>
                        <th style="width: 15%;">المسمى الوظيفي</th>
                        <th style="width: 15%;">المدير المباشر</th>
                        <th style="width: 12%;">الهاتف</th>
                        <th style="width: 13%; background: #eef2ff; text-align: center;">الحالة</th>
                        <th style="width: 15%; background: #eef2ff; text-align: center;">تاريخ التعيين</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $employee)
                        <tr>
                            <td><strong style="color: #4f46e5;">ID-{{ $employee->id }}</strong></td>
                            <td style="font-weight: 600;">{{ $employee->first_name }} {{ $employee->last_name }}</td>
                            <td>{{ $employee->department->name ?? '---' }}</td>
                            {{-- عرض المسمى الوظيفي --}}
                            <td style="color: #4b5563;">{{ $employee->jobTitle->name ?? '---' }}</td>
                             <td>{{ $employee->manager->first_name ?? 'لا يوجد مدير مباشر' }} {{ $employee->manager->last_name ?? '' }}</td>
                            <td dir="ltr" style="text-align: right;">{{ $employee->phone ?? '---' }}</td>
                            {{-- عرض الحالة بشكل ملون --}}
                            <td style="text-align: center;">
                                @php
                                    $status = strtolower($employee->status ?? 'active');
                                    $is_active = in_array($status, ['active', 'نشط', '1', 1]);
                                @endphp
                                <span class="badge {{ $is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $is_active ? 'على رأس العمل' : 'موقوف' }}
                                </span>
                            </td>
                            <td style="background: #f8fafc; font-weight: bold; text-align: center;">
                                {{ $employee->hire_date ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <footer class="footer-sigs">
            <div class="sig-box">توقيع مسؤول السجلات</div>
            <div class="sig-box">اعتماد مدير الموارد البشرية</div>
        </footer>
    </div>
</body>
</html>
