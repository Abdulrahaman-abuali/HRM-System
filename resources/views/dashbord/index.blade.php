@extends('layout.app')

@section('title', 'لوحة التحكم - HR System')

@section('content')
    <style>
        .dashboard-wrapper {
            background-color: #f8f9fa;
            padding: 25px;
            font-family: 'Cairo', sans-serif;
            direction: rtl;
        }

        /* كروت الإحصائيات الرئيسية */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 14px;
            color: #6c757d;
            margin: 0;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .stat-footer {
            font-size: 12px;
            color: #6c757d;
        }

        /* شبكة المحتوى */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eef2f6;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .card-subtitle {
            font-size: 12px;
            color: #6c757d;
            margin: 5px 0 0 0;
        }

        .btn-link {
            color: #4f46e5;
            font-size: 12px;
            text-decoration: none;
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: 500;
            color: #1e293b;
        }

        .item-detail {
            font-size: 12px;
            color: #6c757d;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-approved {
            background: #d1fae5;
            color: #059669;
        }

        .badge-active {
            background: #d1fae5;
            color: #059669;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        .progress-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 8px;
        }

        .progress-fill {
            height: 100%;
            background: #4f46e5;
            border-radius: 10px;
        }
    </style>

    <div class="dashboard-wrapper">
        {{-- 1. كروت الإحصائيات الرئيسية --}}
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">إجمالي الموظفين</h3>
                    <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">👥</div>
                </div>
                <div class="stat-value">{{ $stats['total_employees'] }}</div>
                <div class="stat-footer">موظف نشط في الشركة</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">الحضور اليوم</h3>
                    <div class="stat-icon" style="background: #ecfdf5; color: #10b981;">🕒</div>
                </div>
                <div class="stat-value">{{ $stats['today_attendance'] }}</div>
                <div class="stat-footer">
                    نسبة الحضور:
                    {{ $stats['total_employees'] > 0 ? round(($stats['today_attendance'] / $stats['total_employees']) * 100) : 0 }}%
                    <div class="progress-bar">
                        <div class="progress-fill"
                            style="width: {{ $stats['total_employees'] > 0 ? ($stats['today_attendance'] / $stats['total_employees']) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">طلبات الانتظار</h3>
                    <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;">📋</div>
                </div>
                <div class="stat-value">{{ $stats['pending_leaves'] + ($pendingLoanRequests ?? 0) }}</div>
                <div class="stat-footer">
                    إجازة: {{ $stats['pending_leaves'] }} | قروض: {{ $pendingLoanRequests ?? 0 }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <h3 class="stat-title">إشعارات غير مقروءة</h3>
                    <div class="stat-icon" style="background: #fef2f2; color: #ef4444;">🔔</div>
                </div>
                <div class="stat-value">{{ $stats['unread_notifications'] }}</div>
                <div class="stat-footer">تنبيهات تحتاج إلى مراجعة</div>
            </div>
        </div>

        {{-- 2. الصف العلوي: الطلبات العاجلة + الرواتب --}}
        <div class="grid-2">
            {{-- طلبات الإجازة المعلقة --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">📅 طلبات الإجازة المعلقة</h3>
                        <p class="card-subtitle">بحاجة إلى موافقتك</p>
                    </div>
                    <a href="{{ route('attendance') }}" class="btn-link">عرض الكل →</a>
                </div>
                @forelse($latestLeaves as $leave)
                    <div class="list-item">
                        <div>
                            <div class="item-name">{{ $leave->employee->first_name }} {{ $leave->employee->last_name }}
                            </div>
                            <div class="item-detail">{{ $leave->leaveType->name ?? 'إجازة' }} • من
                                {{ $leave->start_date }} إلى {{ $leave->end_date }}</div>
                        </div>
                        <span class="badge badge-pending">قيد المراجعة</span>
                    </div>
                @empty
                    <div class="list-item">لا توجد طلبات إجازة معلقة</div>
                @endforelse
            </div>

            {{-- طلبات القروض المعلقة --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">💰 طلبات القروض المعلقة</h3>
                        <p class="card-subtitle">بحاجة إلى موافقتك</p>
                    </div>
                    <a href="{{ route('attendance') }}" class="btn-link">عرض الكل →</a>
                </div>
                @forelse($pendingLoans ?? [] as $loan)
                    <div class="list-item">
                        <div>
                            <div class="item-name">{{ $loan->employee->first_name }} {{ $loan->employee->last_name }}
                            </div>
                            <div class="item-detail">{{ number_format($loan->amount, 2) }} ريال • {{ $loan->months }} شهر
                            </div>
                        </div>
                        <span class="badge badge-pending">قيد المراجعة</span>
                    </div>
                @empty
                    <div class="list-item">لا توجد طلبات قروض معلقة</div>
                @endforelse
            </div>
        </div>

        {{-- 3. الصف الأوسط: الموظفون الجدد + المنفصلون + الرواتب --}}
        <div class="grid-3">
            {{-- أحدث الموظفين --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">✨ أحدث الموظفين</h3>
                        <p class="card-subtitle">آخر 5 موظفين تم تعيينهم</p>
                    </div>
                    <a href="{{ route('employees.index') }}" class="btn-link">عرض الكل →</a>
                </div>
                @forelse($recentEmployees ?? [] as $emp)
                    <div class="list-item">
                        <div>
                            <div class="item-name">{{ $emp->first_name }} {{ $emp->last_name }}</div>
                            <div class="item-detail">{{ $emp->department->name ?? 'بدون قسم' }} •
                                {{ $emp->jobTitle->name ?? '' }}</div>
                        </div>
                        <span class="badge badge-active">نشط</span>
                    </div>
                @empty
                    <div class="list-item">لا يوجد موظفين جدد</div>
                @endforelse
            </div>

            {{-- الموظفون المنفصلون --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">⚠️ آخر المنفصلين</h3>
                        <p class="card-subtitle">آخر 5 موظفين تم فصلهم</p>
                    </div>
                    <a href="{{ route('employees.index') }}" class="btn-link">عرض الكل →</a>
                </div>
                @forelse($inactiveEmployees ?? [] as $emp)
                    <div class="list-item">
                        <div>
                            <div class="item-name">{{ $emp->first_name }} {{ $emp->last_name }}</div>
                            <div class="item-detail">{{ $emp->department->name ?? 'بدون قسم' }}</div>
                        </div>
                        <span class="badge badge-inactive">منفصل</span>
                    </div>
                @empty
                    <div class="list-item">لا يوجد موظفين منفصلين</div>
                @endforelse
            </div>

            {{-- حالة الرواتب --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">💰 حالة الرواتب</h3>
                        <p class="card-subtitle">شهر {{ now()->translatedFormat('F Y') }}</p>
                    </div>
                    <a href="{{ route('salaries') }}" class="btn-link">إدارة →</a>
                </div>
                @php
                    $currentMonth = now()->format('Y-m');
                    $totalSalaries = \App\Models\Salary::where('month', $currentMonth)->count();
                    $paidSalaries = \App\Models\Salary::where('month', $currentMonth)
                        ->where('status', 'مدفوع')
                        ->count();
                    $totalAmount = \App\Models\Salary::where('month', $currentMonth)->sum('net_salary');
                @endphp
                <div class="list-item">
                    <div>
                        <div class="item-name">الموظفين</div>
                        <div class="item-detail">{{ $paidSalaries }} / {{ $totalSalaries }} مدفوع</div>
                    </div>
                    <span
                        class="badge {{ $paidSalaries == $totalSalaries && $totalSalaries > 0 ? 'badge-active' : 'badge-pending' }}">
                        {{ $paidSalaries == $totalSalaries && $totalSalaries > 0 ? 'مكتمل' : 'قيد التنفيذ' }}
                    </span>
                </div>
                <div class="list-item" style="border-bottom: none;">
                    <div>
                        <div class="item-name">إجمالي المستحق</div>
                        <div class="item-detail">{{ number_format($totalAmount, 2) }} ريال</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. النشاطات الأخيرة --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">📋 آخر نشاطات النظام</h3>
                    <p class="card-subtitle">أحدث العمليات التي تمت في النظام</p>
                </div>
                @if (auth()->user()->role?->name === 'مدير النظام')
                    <a href="{{ route('activity.log') }}" class="btn-link">عرض السجل الكامل →</a>
                @endif
               
            </div>
            @foreach ($activities as $activity)
                <div class="list-item">
                    <div>
                        <div class="item-name">{!! $activity->description !!}</div>
                        <div class="item-detail">{{ $activity->created_at->diffForHumans() }}</div>
                    </div>
                    <span style="font-size: 20px;">✅</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
