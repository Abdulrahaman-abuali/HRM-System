@extends('layout.app')

@section('title', 'لوحة التحكم - HR System')

@section('content')
<style>
    :root {
        --bg-body: #f8f9fa;
        --card-border: #eef0f2;
        --text-dark: #333333;
        --text-muted: #888888;
        --accent-blue: #007bff;
    }

    .dashboard-wrapper {
        background-color: var(--bg-body);
        padding: 30px;
        font-family: 'Cairo', sans-serif;
        direction: rtl;
    }

    /* العناوين الرئيسية */
    .main-section-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
        text-align: right;
    }

    /* كروت الإحصائيات - مطابقة للصورة */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--card-border);
        border-radius: 8px;
        padding: 20px;
        position: relative;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .stat-icon-top {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 18px;
    }

    .stat-main-info {
        text-align: left;
        margin-top: 10px;
    }

    .stat-label {
        display: block;
        font-size: 14px;
        color: var(--text-dark);
        font-weight: 600;
        margin-bottom: 5px;
        text-align: left;
    }

    .stat-num {
        font-size: 32px;
        font-weight: 700;
        color: #000;
    }

    .stat-footer-text {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: auto;
    }

    /* شبكة المحتوى الوسطى */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .card {
        background: #fff;
        border: 1px solid var(--card-border);
        border-radius: 8px;
        padding: 20px;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .card-title-group h3 { font-size: 16px; margin: 0; color: #333; }
    .card-title-group p { font-size: 12px; color: var(--text-muted); margin: 0; }

    .btn-outline-sm {
        border: 1px solid #ddd;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 11px;
        color: #666;
        text-decoration: none;
    }

    /* الجداول - مطابقة للصورة */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table th {
        background: #f1f3f5;
        padding: 10px;
        font-size: 12px;
        color: #666;
        text-align: center;
        border: 1px solid #eee;
    }

    .custom-table td {
        padding: 12px;
        font-size: 13px;
        text-align: center;
        border: 1px solid #eee;
    }

    /* الحالات (Badge) - Rounded كما في الصورة */
    .badge-pill {
        padding: 2px 15px;
        border-radius: 20px;
        font-size: 12px;
        border: 1px solid transparent;
    }
    .status-hader { background: #e6fffa; color: #38b2ac; border-color: #38b2ac50; }
    .status-late { background: #fff5f5; color: #e53e3e; border-color: #e53e3e50; }
    .status-pending { background: #fffaf0; color: #dd6b20; border-color: #dd6b2050; }

    /* التايملاين (النشاطات) */
    .activity-row {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f8f9fa;
    }
    .activity-text { flex-grow: 1; font-size: 13px; margin: 0 10px; }
    .activity-time { font-size: 11px; color: #bbb; }

    /* الشبكة السفلية - 3 أعمدة */
    .bottom-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .list-item-minimal {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        border-bottom: 1px solid #f8f9fa;
    }
    .item-label { font-size: 13px; color: #333; }
    .item-sub { font-size: 11px; color: #999; }
</style>

<div class="dashboard-wrapper">
    <h1 class="main-section-title">إحصائيات سريعة</h1>

    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-icon-top">👥</span>
            <div class="stat-main-info">
                <span class="stat-label">إجمالي الموظفين</span>
                <span class="stat-num">{{ $stats['total_employees'] }}</span>
            </div>
            <p class="stat-footer-text">موظف نشط في الشركة</p>
        </div>

        <div class="stat-card">
            <span class="stat-icon-top">🕒</span>
            <div class="stat-main-info">
                <span class="stat-label">الحضور اليوم</span>
                <span class="stat-num">{{ $stats['today_attendance'] }}</span>
            </div>
            <p class="stat-footer-text">موظف مسجل حضورهم اليوم</p>
        </div>

        <div class="stat-card">
            <span class="stat-icon-top">📅</span>
            <div class="stat-main-info">
                <span class="stat-label">طلبات الإجازة المعلقة</span>
                <span class="stat-num">{{ $stats['pending_leaves'] }}</span>
            </div>
            <p class="stat-footer-text">في انتظار اعتماد الموارد البشرية</p>
        </div>

        <div class="stat-card">
            <span class="stat-icon-top">🔔</span>
            <div class="stat-main-info">
                <span class="stat-label">إشعارات النظام</span>
                <span class="stat-num">{{ $stats['unread_notifications'] }}</span>
            </div>
            <p class="stat-footer-text">تنبيهات تحتاج إلى مراجعة</p>
        </div>
    </div>

    <div class="content-grid">
        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3>الحضور اليوم</h3>
                    <p>سجل الحضور والانصراف لليوم الحالي</p>
                </div>
                <a href="{{ route('attendance.index') }}" class="btn-outline-sm">عرض الكل</a>
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>الموظف</th>
                        <th>القسم</th>
                        <th>وقت الدخول</th>
                        <th>وقت الخروج</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayAttendance as $record)
                    <tr>
                        <td>{{ $record->employee->first_name }} {{ $record->employee->last_name }}</td>
                        <td>{{ $record->employee->department ?? 'تطوير البرمجيات' }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->check_in)->format('h:i ص') }}</td>
                        <td>—</td>
                        <td>
                            <span class="badge-pill {{ $record->status == 'late' ? 'status-late' : 'status-hader' }}">
                                {{ $record->status == 'late' ? 'متأخر' : 'حاضر' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3>آخر نشاطات النظام</h3>
                    <p>سجل العمليات الأخيرة في النظام</p>
                </div>
                <a href="#" class="btn-outline-sm">عرض السجل الكامل</a>
            </div>
            <div class="activities-container">
                @foreach($activities as $activity)
                <div class="activity-row">
                    <span style="font-size: 14px;">✅</span>
                    <p class="activity-text">{!! $activity->description !!}</p>
                    <span class="activity-time">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bottom-grid">
        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3>أحدث طلبات الإجازة</h3>
                    <p>متابعة حالة طلبات الإجازة الجديدة</p>
                </div>
                <a href="{{ route('leaves.index') }}" class="btn-outline-sm">كل الطلبات</a>
            </div>
            @foreach($latestLeaves as $leave)
            <div class="list-item-minimal">
                <div>
                    <span class="item-label">{{ $leave->employee->first_name }}</span><br>
                    <span class="item-sub">{{ $leave->leaveType->name }} • {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }}</span>
                </div>
                <span class="badge-pill {{ $leave->status == 'pending' ? 'status-pending' : 'status-hader' }}">
                    {{ $leave->status == 'pending' ? 'قيد المراجعة' : 'معتمدة' }}
                </span>
            </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3>تنبيهات الرواتب</h3>
                    <p>متابعة حالة كشوف الرواتب الحالية</p>
                </div>
                <a href="#" class="btn-outline-sm">إدارة الرواتب</a>
            </div>
            <div class="list-item-minimal">
                <div>
                    <span class="item-label">رواتب شهر نوفمبر 2025</span><br>
                    <span class="item-sub">تم دفع رواتب جميع الموظفين</span>
                </div>
                <span class="badge-pill status-hader">مدفوعة</span>
            </div>
            <div class="list-item-minimal" style="border: none;">
                <div>
                    <span class="item-label">رواتب شهر ديسمبر 2025</span><br>
                    <span class="item-sub">لم يتم اعتماد الكشوف النهائية بعد</span>
                </div>
                <span class="badge-pill status-pending">قيد التجهيز</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title-group">
                    <h3>مهام الموارد البشرية اليوم</h3>
                    <p>قائمة بالمهام التي يفضل إنجازها اليوم</p>
                </div>
                <a href="#" class="btn-outline-sm">إدارة المهام</a>
            </div>
            <div class="list-item-minimal">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="checkbox">
                    <span class="item-label">مراجعة طلبات الإجازة المعلقة</span>
                </div>
                <span class="status-late" style="font-size: 10px; padding: 2px 8px; border-radius: 10px;">أولوية عالية</span>
            </div>
            <div class="list-item-minimal">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="checkbox">
                    <span class="item-label">تحديث بيانات الموظفين الجدد</span>
                </div>
                <span class="status-pending" style="font-size: 10px; padding: 2px 8px; border-radius: 10px;">أولوية متوسطة</span>
            </div>
        </div>
    </div>
</div>
@endsection
