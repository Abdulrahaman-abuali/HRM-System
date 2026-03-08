@extends('layout.app')

@section('title', 'لوحة الموظف')

@section('content')
@php
    use App\Models\AttendanceRecord;

    $authUser = auth()->user();
    $employee = $authUser?->employee;
    $today = now()->toDateString();

    $todayRecord = null;
    if ($employee) {
        $todayRecord = AttendanceRecord::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();
    }

    $hasCheckIn = (bool)($todayRecord?->check_in);
    $hasCheckOut = (bool)($todayRecord?->check_out);

    // منطق تحديد نص الحالة والشارة (Badge) بناءً على سجلات الحضور
    if (!$employee) {
        $attendanceText = 'غير مرتبط بموظف';
        $attendanceBadgeClass = 'badge badge-danger';
        $statusDescription = 'لا يوجد ربط بين حسابك وبيانات الموظف حالياً.';
    } elseif (!$hasCheckIn) {
        $attendanceText = 'غير مسجل حضور';
        $attendanceBadgeClass = 'badge badge-danger';
        $statusDescription = 'لم يتم رصد دخولك للنظام اليوم بعد.';
    } elseif ($hasCheckIn && !$hasCheckOut) {
        $attendanceText = 'مسجل حضور اليوم';
        $attendanceBadgeClass = 'badge badge-success';
        $statusDescription = 'تم تسجيل حضورك تلقائياً عند الدخول. سيتم تسجيل الانصراف عند تسجيل الخروج.';
    } else {
        $attendanceText = 'حضور + انصراف';
        $attendanceBadgeClass = 'badge badge-success';
        $statusDescription = 'تم اكتمال دورة الحضور والانصراف لهذا اليوم بنجاح.';
    }

    // استخراج الاسم الأول للموظف
    $firstName = $authUser?->name ? (explode(' ', $authUser->name)[0] ?? $authUser->name) : 'زميلنا';
@endphp

<main class="main-content">
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; border-radius: 8px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- بطاقة الحالة اليومية --}}
    <section class="section" style="margin-bottom: 20px;">
        <article class="card shadow-sm" style="border-right: 5px solid #6366f1;">
            <div class="card-body" style="padding: 20px;">
                <h2 style="margin:0 0 8px 0; font-size: 1.1rem; color: #1f2937;">وضعية الحضور الحالية</h2>
                <div style="color:#4b5563; font-size: 0.95rem; line-height: 1.6;">
                    {{ $statusDescription }}
                </div>
            </div>
        </article>
    </section>

    {{-- بطاقة الترحيب الرئيسية المصممة بهوية الشركة --}}
    <section class="section employee-welcome-section">
        <article class="card welcome-banner" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; border: none; border-radius: 15px; overflow: hidden;">
            <div class="card-body" style="padding: 40px;">
                <div class="welcome-content" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;">
                    <div class="welcome-text">
                        <h2 style="font-size: 2.2rem; margin: 0 0 10px 0; font-weight: 800;">أهلاً بك، {{ $firstName }}!</h2>
                        <p style="font-size: 1.1rem; opacity: 0.9; max-width: 500px; line-height: 1.5;">
                            يمكنك من هنا متابعة إجازاتك، حضورك، ورواتبك الشهرية بكل سهولة.
                        </p>
                    </div>

                    <div class="welcome-stats" style="display: flex; gap: 40px;">
                        <div class="stat-item">
                            <span style="display: block; font-size: 0.85rem; opacity: 0.8; margin-bottom: 5px;">تاريخ اليوم</span>
                            <span id="displayDate" style="font-size: 1.1rem; font-weight: 600;"></span>
                        </div>
                        <div class="stat-item">
                            <span style="display: block; font-size: 0.85rem; opacity: 0.8; margin-bottom: 5px;">حالة الدوام</span>
                            <span class="{{ $attendanceBadgeClass }}" style="display: inline-block; padding: 4px 16px; border-radius: 20px; font-weight: 600; font-size: 0.9rem; background: rgba(255,255,255,0.2); color: #fff;">
                                {{ $attendanceText }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </section>
</main>
@endsection

@section('script')
<script>
    (function () {
        const dateEl = document.getElementById('displayDate');
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

        function updateTime() {
            const now = new Date();
            if (dateEl) {
                dateEl.textContent = now.toLocaleDateString('ar-EG', options);
            }
        }

        updateTime();
    })();
</script>
@endsection
