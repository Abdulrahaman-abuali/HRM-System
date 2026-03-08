<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        /* التنسيقات العامة بناءً على التصميم المعتمد لديك */
        body { font-family: 'DejaVu Sans', 'Tajawal', sans-serif; background: #fff; margin: 0; direction: rtl; }
        .report-container { max-width: 1200px; margin: auto; padding: 2rem; }

        .report-banner {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.5rem; background: #f8fafc; border: 1px solid #e5e7eb;
            border-radius: 0.75rem; margin-bottom: 2rem;
        }

        /* تنسيق الجداول الموحد */
        .table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        .table th { background: #f1f5f9; padding: 12px; border: 1px solid #e2e8f0; text-align: right; font-size: 14px; color: #475569; width: 25%; }
        .table td { padding: 12px; border: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; color: #1e293b; }

        .section-title {
            background: #eef2ff; color: #4f46e5; padding: 10px 15px;
            border-radius: 8px; font-weight: bold; margin-bottom: 15px;
            border-right: 4px solid #4f46e5; font-size: 16px;
        }

        /* تنسيق الحالات */
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-active { background-color: #dcfce7; color: #166534; }
        .badge-inactive { background-color: #fee2e2; color: #991b1b; }

        .footer-sigs { margin-top: 4rem; display: flex; justify-content: space-around; }
        .sig-box { text-align: center; width: 250px; border-top: 1px dashed #6b7280; padding-top: 10px; font-weight: bold; color: #4b5563; }

        @media print {
            .no-print { display: none !important; }
            .report-container { padding: 0; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="position: fixed; top: 20px; left: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: bold;">
            🖨️ طباعة التقرير الرسمي
        </button>
    </div>

    <div class="report-container">
        {{-- هيدر التقرير الموحد --}}
        <header class="report-banner">
            <div>
                <h1 style="color: #4f46e5; margin: 0; font-size: 24px;">نظام إدارة الموارد البشرية</h1>
                <p style="color: #64748b; margin: 5px 0;">الملف الوظيفي المعتمد للموظف</p>
            </div>
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 20px;">{{ $title }}</h2>
                <p>تاريخ الاستخراج: {{ date('Y-m-d') }}</p>
            </div>
            <div style="text-align: left;">
                <span style="background: #eef2ff; color: #4f46e5; padding: 8px 20px; border-radius: 20px; font-weight: bold; border: 1px solid #c7d2fe;">
                    رقم الموظف: ID-{{ $data->id }}
                </span>
            </div>
        </header>

        {{-- أولاً: البيانات الوظيفية --}}
        <div class="section-title">أولاً: البيانات الوظيفية والتعيين</div>
        <div style="border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden; margin-bottom: 2rem;">
            <table class="table" style="margin-bottom: 0;">
                <tr>
                    <th>الاسم الكامل للموظف</th>
                    <td style="font-weight: 600; font-size: 16px;">{{ $data->first_name }} {{ $data->last_name }}</td>
                </tr>
                <tr>
                    <th>القسم الإداري</th>
                    <td>{{ $data->department->name ?? '---' }}</td>
                </tr>
                <tr>
                    <th>المسمى الوظيفي</th>
                    <td>{{ $data->jobTitle->name ?? '---' }}</td>
                </tr>
                <tr>
                    <th>المدير المباشر</th>
                    <td style="color: #4f46e5; font-weight: 600;">
                        {{ $data->manager->first_name ?? 'الإدارة العليا' }} {{ $data->manager->last_name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>تاريخ التعيين</th>
                    <td style="font-weight: bold;">{{ $data->hire_date ?? '-' }}</td>
                </tr>
                <tr>
                    <th>حالة الموظف الحالية</th>
                    <td>
                        @php
                            $status = strtolower($data->status ?? 'active');
                            $is_active = in_array($status, ['active', 'نشط', '1', 1]);
                        @endphp
                        <span class="badge {{ $is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $is_active ? 'على رأس العمل' : 'موقوف' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- ثانياً: البيانات الشخصية والاتصال --}}
        <div class="section-title">ثانياً: المعلومات الشخصية والاتصال</div>
        <div style="border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden;">
            <table class="table" style="margin-bottom: 0;">
                <tr>
                    <th>رقم الهاتف</th>
                    <td dir="ltr" style="text-align: right;">{{ $data->phone ?? '---' }}</td>
                </tr>
                <tr>
                    <th>البريد الإلكتروني</th>
                    <td>{{ $data->email ?? '---' }}</td>
                </tr>
                <tr>
                    <th>تاريخ الميلاد</th>
                    <td>{{ $data->birth_date ?? '---' }} (العمر: {{ $data->age ?? '-' }} سنة)</td>
                </tr>
                <tr>
                    <th>العنوان السكني</th>
                    <td>{{ $data->address ?? '---' }}</td>
                </tr>
                <tr>
                    <th>نوع التوظيف</th>
                    <td>{{ $data->employment_type == 'full-time' ? 'دوام كامل' : 'تعاقد / جزئي' }}</td>
                </tr>
            </table>
        </div>

        {{-- فوتر التوقيعات الموحد --}}
        <footer class="footer-sigs">
            <div class="sig-box">توقيع الموظف</div>
            <div class="sig-box">توقيع مسؤول السجلات</div>
            <div class="sig-box">اعتماد مدير الموارد البشرية</div>
        </footer>

        <div style="margin-top: 3rem; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 15px;">
            تم توليد هذه الوثيقة آلياً وتعتبر رسمية لدى قسم الموارد البشرية.
        </div>
    </div>
</body>
</html>
