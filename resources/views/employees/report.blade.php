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
        .badge-active { background-color: #dcfce7; color: #166534; }
        .badge-inactive { background-color: #fee2e2; color: #991b1b; }

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
                    32
                        <th style="width: 10%;">رقم الموظف</th>
                        <th style="width: 18%;">الاسم الكامل</th>
                        <th style="width: 12%;">القسم</th>
                        <th style="width: 15%;">المسمى الوظيفي</th>
                        <th style="width: 20%;">المدير المباشر (مدير القسم)</th>
                        <th style="width: 12%;">الهاتف</th>
                        <th style="width: 10%;">الحالة</th>
                        <th style="width: 13%;">تاريخ التعيين</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $employee)
                        @php
                            // تحديد المدير الفعلي حسب الصلاحية
                            $actualManager = null;

                            // 1. إذا كان الموظف لديه مدير مخزن في manager_id
                            if ($employee->manager_id) {
                                $manager = \App\Models\Employee::with('user.role')->find($employee->manager_id);
                                // تحقق إذا كان المدير المخزن لديه صلاحية "مدير القسم"
                                if ($manager && $manager->user && $manager->user->role &&
                                    $manager->user->role->name === 'مدير القسم') {
                                    $actualManager = $manager;
                                }
                            }

                            // 2. إذا لم يتم العثور على مدير صالح، ابحث عن مدير القسم الفعلي في نفس القسم
                            if (!$actualManager && $employee->department_id) {
                                $actualManager = \App\Models\Employee::where('department_id', $employee->department_id)
                                    ->whereHas('user.role', function($q) {
                                        $q->where('name', 'مدير القسم');
                                    })
                                    ->first();
                            }

                            $status = strtolower($employee->status ?? 'active');
                            $is_active = in_array($status, ['active', 'نشط', '1', 1]);
                        @endphp
                        <tr>
                            <td><strong style="color: #4f46e5;">ID-{{ $employee->id }}</strong></td>
                            <td style="font-weight: 600;">
                                {{ $employee->first_name }} {{ $employee->last_name }}
                                @if($employee->user && $employee->user->role && $employee->user->role->name === 'مدير القسم')
                                    <span style="background: #10b981; color: white; padding: 2px 6px; border-radius: 12px; font-size: 10px; margin-right: 5px;">
                                        مدير فعلي
                                    </span>
                                @endif
                            </td>
                            <td>{{ $employee->department->name ?? '---' }}</td>
                            <td style="color: #4b5563;">
                                {{ $employee->jobTitle->name ?? '---' }}
                                @if(($employee->jobTitle->name ?? '') && str_contains($employee->jobTitle->name, 'مدير') &&
                                    !($employee->user && $employee->user->role && $employee->user->role->name === 'مدير القسم'))
                                    <span style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 12px; font-size: 10px; margin-right: 5px;">
                                        صلاحيات محدودة
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($actualManager)
                                    <div style="font-weight: 600; color: #1f2937;">
                                        {{ $actualManager->first_name }} {{ $actualManager->last_name }}
                                    </div>
                                    <div style="font-size: 10px; color: #6b7280; margin-top: 2px;">
                                        {{ $actualManager->jobTitle->name ?? '' }}
                                        <span style="background: #eef2ff; padding: 2px 6px; border-radius: 12px; margin-right: 5px;">
                                            {{ $actualManager->user->role->name ?? '' }}
                                        </span>
                                    </div>
                                @else
                                    <span style="color: #9ca3af;">لا يوجد مدير معين</span>
                                @endif
                            </td>
                            <td dir="ltr" style="text-align: right;">{{ $employee->phone ?? '---' }}</td>
                            <td style="text-align: center;">
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
