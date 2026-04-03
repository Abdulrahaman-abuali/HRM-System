@extends('layout.app')

@section('title', 'لوحة الموظف')

@section('content')
    @php
        use App\Models\AttendanceRecord;

        $authUser = auth()->user();
        $employee = $authUser?->employee;
        $today = now()->toDateString();
        $userRole = $authUser->role?->name; // الحصول على دور المستخدم

        $todayRecord = null;
        if ($employee) {
            $todayRecord = AttendanceRecord::where('employee_id', $employee->id)->where('date', $today)->first();
        }

        $hasCheckIn = (bool) $todayRecord?->check_in;
        $hasCheckOut = (bool) $todayRecord?->check_out;

        // منطق تحديد نص الحالة والشارة
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
        $firstName = $authUser?->name ? explode(' ', $authUser->name)[0] ?? $authUser->name : 'زميلنا';
    @endphp

    <main class="main-content">
        @if (session('success'))
            <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; border-radius: 8px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- بطاقة الترحيب الرئيسية (في الأعلى مباشرة) --}}
        <section class="section employee-welcome-section" style="margin-bottom: 30px;">
            <article class="card welcome-banner"
                style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; border: none; border-radius: 15px; overflow: hidden;">
                <div class="card-body" style="padding: 40px;">
                    <div class="welcome-content"
                        style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;">
                        <div class="welcome-text">
                            <h2 style="font-size: 2.2rem; margin: 0 0 10px 0; font-weight: 800;">أهلاً بك،
                                {{ $firstName }}!</h2>
                            <p style="font-size: 1.1rem; opacity: 0.9; max-width: 500px; line-height: 1.5;">
                                يمكنك من هنا متابعة إجازاتك، حضورك، ورواتبك الشهرية بكل سهولة.
                            </p>
                        </div>

                        <div class="welcome-stats" style="display: flex; gap: 40px;">
                            <div class="stat-item">
                                <span style="display: block; font-size: 0.85rem; opacity: 0.8; margin-bottom: 5px;">تاريخ
                                    اليوم</span>
                                <span id="displayDate" style="font-size: 1.1rem; font-weight: 600;"></span>
                            </div>
                            <div class="stat-item">
                                <span style="display: block; font-size: 0.85rem; opacity: 0.8; margin-bottom: 5px;">حالة
                                    الدوام</span>
                                <span class="{{ $attendanceBadgeClass }}"
                                    style="display: inline-block; padding: 4px 16px; border-radius: 20px; font-weight: 600; font-size: 0.9rem; background: rgba(255,255,255,0.2); color: #fff;">
                                    {{ $attendanceText }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        {{-- ⚠️ قسم تقديم الطلبات - يظهر فقط لمدير القسم --}}
        @if ($userRole === 'مدير القسم')
            <section class="section" style="margin-bottom: 30px;">
                <div class="row" style="display: flex; gap: 20px; flex-wrap: wrap;">
                    {{-- نموذج تقديم إجازة --}}
                    <div class="col-md-5" style="flex: 1; min-width: 300px;">
                        <article class="card shadow-sm"
                            style="border-top: 4px solid #6366f1; height: 100%; border-radius: 10px;">
                            <header class="card-header"
                                style="background: #f8fafc; padding: 15px; border-radius: 10px 10px 0 0;">
                                <h2 style="font-size: 1rem; margin: 0; color: #312e81;">✍️ تقديم طلب إجازة</h2>
                            </header>
                            <div class="card-body" style="padding: 20px;">
                                <form action="{{ route('leaves.store') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label style="font-weight: bold; font-size: 0.9rem;">نوع الإجازة:</label>
                                        <select name="leave_type_id" class="form-control" required
                                            style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                                            <option value="">اختر النوع...</option>
                                            @foreach (\App\Models\LeaveType::all() as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="display: flex; gap: 10px;" class="mb-3">
                                        <div style="flex: 1;">
                                            <label style="font-weight: bold; font-size: 0.9rem;">من تاريخ:</label>
                                            <input type="date" name="start_date" class="form-control" required
                                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                        </div>
                                        <div style="flex: 1;">
                                            <label style="font-weight: bold; font-size: 0.9rem;">إلى تاريخ:</label>
                                            <input type="date" name="end_date" class="form-control" required
                                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label style="font-weight: bold; font-size: 0.9rem;">السبب:</label>
                                        <textarea name="reason" class="form-control" rows="2" placeholder="اكتب سبب الإجازة هنا..."
                                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"
                                        style="width: 100%; background: #6366f1; border: none; padding: 10px; border-radius: 5px; font-weight: bold; color: white;">إرسال
                                        طلب الإجازة</button>
                                </form>
                            </div>
                        </article>
                    </div>

                    {{-- نموذج تقديم قرض --}}
                    <div class="col-md-5" style="flex: 1; min-width: 300px;">
                        <article class="card shadow-sm"
                            style="border-top: 4px solid #10b981; height: 100%; border-radius: 10px;">
                            <header class="card-header"
                                style="background: #f8fafc; padding: 15px; border-radius: 10px 10px 0 0;">
                                <h2 style="font-size: 1rem; margin: 0; color: #065f46;">💰 تقديم طلب قرض</h2>
                            </header>
                            <div class="card-body" style="padding: 20px;">
                                <form action="{{ route('loans.store') }}" method="POST">
                                    @csrf
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                        <div class="form-group">
                                            <label style="font-weight: bold; font-size: 0.9rem;">المبلغ المطلوب
                                                (ر.ي)</label>
                                            <input type="number" step="0.01" name="amount" class="form-control"
                                                required min="1000"
                                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                            <small>الحد الأدنى 1,000 ريال</small>
                                        </div>
                                        <div class="form-group">
                                            <label style="font-weight: bold; font-size: 0.9rem;">عدد الأشهر</label>
                                            <select name="months" class="form-control" required
                                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                                <option value="">اختر عدد الأشهر</option>
                                                @for ($i = 1; $i <= 24; $i++)
                                                    <option value="{{ $i }}">{{ $i }} شهر</option>
                                                @endfor
                                            </select>
                                            <small>الحد الأقصى 24 شهراً</small>
                                        </div>
                                        <div class="form-group" style="grid-column: span 2;">
                                            <label style="font-weight: bold; font-size: 0.9rem;">سبب القرض (اختياري)</label>
                                            <textarea name="reason" class="form-control" rows="2" placeholder="اذكر سبب طلب القرض..."></textarea>
                                        </div>
                                    </div>
                                    <div id="installment_preview" class="alert alert-info"
                                        style="display: none; margin-top: 15px; padding: 8px; background: #e0e7ff; border-radius: 5px;">
                                        القسط الشهري المتوقع: <strong><span id="monthly_installment">0</span> ريال</strong>
                                    </div>
                                    <button type="submit" class="btn btn-primary"
                                        style="width: 100%; background: #10b981; border: none; padding: 10px; border-radius: 5px; font-weight: bold; color: white; margin-top: 15px;">إرسال
                                        طلب القرض</button>
                                </form>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
            {{-- جدول حالة الطلبات الأخيرة (يظهر فقط لمدير القسم) --}}
            <section class="section">
                <article class="card shadow-sm" style="border-radius: 10px;">
                    <header class="card-header" style="background: #f8fafc; padding: 15px; border-radius: 10px 10px 0 0;">
                        <h3 style="font-size: 1rem; color: #334155;">📋 حالة طلباتي الأخيرة</h3>
                    </header>
                    <div class="card-body">
                        <table class="table" style="width: 100%; text-align: right;">
                            <thead style="background: #f1f5f9;">
                                <tr>
                                <th style="padding: 12px;">النوع</th>
                                <th style="padding: 12px;">التفاصيل</th>
                                <th style="padding: 12px;">التاريخ</th>
                                <th style="padding: 12px;">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myRequests ?? [] as $req)
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 12px;">
                                            @if ($req->type == 'leave')
                                                📅 إجازة
                                            @else
                                                💰 قرض
                                            @endif
                                        </td>
                                        <td style="padding: 12px;">
                                            @if ($req->type == 'leave')
                                                {{ $req->details }} ({{ $req->start_date }} - {{ $req->end_date }})
                                            @else
                                                {{ $req->details }}
                                            @endif
                                        </td>
                                        <td style="padding: 12px;">
                                            {{ \Carbon\Carbon::parse($req->date)->format('Y-m-d') }}</td>
                                        <td style="padding: 12px;">
                                            @if ($req->status == 'pending')
                                                <span class="badge badge-warning">قيد المراجعة</span>
                                            @elseif($req->status == 'approved')
                                                <span class="badge badge-success">موافق عليه</span>
                                            @elseif($req->status == 'rejected')
                                                <span class="badge badge-danger">مرفوض</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $req->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 20px;">لم تقم بتقديم أي
                                            طلبات بعد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        @endif
    </main>

    <script>
        // حساب القسط الشهري للقرض
        const amountInput = document.querySelector('input[name="amount"]');
        const monthsSelect = document.querySelector('select[name="months"]');
        const previewDiv = document.getElementById('installment_preview');
        const installmentSpan = document.getElementById('monthly_installment');

        function calculateInstallment() {
            const amount = parseFloat(amountInput?.value) || 0;
            const months = parseInt(monthsSelect?.value) || 0;

            if (amount > 0 && months > 0) {
                const installment = amount / months;
                installmentSpan.innerText = installment.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                });
                previewDiv.style.display = 'block';
            } else {
                previewDiv.style.display = 'none';
            }
        }

        if (amountInput && monthsSelect) {
            amountInput.addEventListener('input', calculateInstallment);
            monthsSelect.addEventListener('change', calculateInstallment);
        }
    </script>

    <style>
        .badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
        }

        .badge-warning {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-success {
            background: #d1fae5;
            color: #059669;
        }

        .badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .alert-info {
            background: #e0e7ff;
            padding: 10px;
            border-radius: 8px;
            color: #4f46e5;
        }
    </style>
@endsection

@section('script')
    <script>
        (function() {
            const dateEl = document.getElementById('displayDate');
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };

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
