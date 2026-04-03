@extends('layout.app')

@section('content')
<style>
    .tab-btn { background: none; border: none; padding: 8px 16px; font-size: 14px; cursor: pointer; border-radius: 8px; transition: all 0.2s; }
    .tab-btn.active { background: #4f46e5; color: white; }
    .tab-btn:hover:not(.active) { background: #f1f5f9; }
    .badge { padding: 4px 8px; border-radius: 20px; font-size: 12px; }
    .badge-warning { background: #fef3c7; color: #d97706; }
    .badge-success { background: #d1fae5; color: #059669; }
    .badge-danger { background: #fee2e2; color: #dc2626; }
    .badge-info { background: #e0e7ff; color: #4f46e5; }
    .badge-secondary { background: #e2e8f0; color: #475569; }
    .btn-outline { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 6px; cursor: pointer; }
    .btn-outline:hover { background: #e2e8f0; }
</style>

<div class="main">
    <header class="main-header">
        <div class="header-left">
            <h1 class="page-title">إدارة الطلبات</h1>
            <p class="page-subtitle">متابعة طلبات الإجازة واعتمادها لموظفي النظام</p>
        </div>
    </header>

    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom:20px;">{{ session('error') }}</div>
        @endif

        @php $userRole = auth()->user()->role?->name; @endphp

        {{-- إحصائيات سريعة --}}
        <section class="section">
            <div class="grid grid-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">إجازات قيد الانتظار</h3>
                        <span class="stat-icon stat-icon-warning">⏳</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ $leaveStats['pending'] ?? 0 }}</p>
                    </div>
                </article>

                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">إجازات معتمدة</h3>
                        <span class="stat-icon stat-icon-success">✔</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ $leaveStats['approved'] ?? 0 }}</p>
                    </div>
                </article>

                {{-- إحصائيات القروض تظهر فقط لمدير النظام --}}
                @if($userRole === 'مدير النظام')
                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">قروض قيد الانتظار</h3>
                        <span class="stat-icon stat-icon-warning">💰</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ $loanStats['pending'] ?? 0 }}</p>
                    </div>
                </article>

                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">قروض معتمدة</h3>
                        <span class="stat-icon stat-icon-success">✔</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ $loanStats['approved'] ?? 0 }}</p>
                    </div>
                </article>
                @endif
            </div>
        </section>

        {{-- تبويبات الطلبات --}}
        <section class="section">
            <article class="card">
                <div class="card-header">
                    <div class="tabs" style="display: flex; gap: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
                        <button class="tab-btn active" onclick="showTab('leave')">📅 طلبات الإجازات</button>
                        @if($userRole === 'مدير النظام')
                        <button class="tab-btn" onclick="showTab('loan')">💰 طلبات القروض</button>
                        @endif
                    </div>
                </div>

                {{-- تبويب طلبات الإجازات --}}
                <div id="tab-leave" class="tab-content active">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الموظف</th>
                                    <th>نوع الإجازة</th>
                                    <th>الفترة</th>
                                    <th>المدة</th>
                                    <th>السبب</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                                </thead>
                            <tbody>
                                @forelse($leaveRequests ?? [] as $leave)
                                    @php
                                        $start = \Carbon\Carbon::parse($leave->start_date);
                                        $end = \Carbon\Carbon::parse($leave->end_date);
                                        $days = $start->diffInDays($end) + 1;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</strong></td>
                                        <td><span class="badge badge-info">{{ $leave->leaveType->name ?? '-' }}</span></td>
                                        <td>{{ $leave->start_date }} إلى {{ $leave->end_date }}</td>
                                        <td>{{ $days }} أيام</td>
                                        <td>
                                            @if($leave->reason)
                                                <button type="button" class="btn btn-sm btn-outline" onclick="showChatHistory('{{ $leave->employee->first_name }}', '{{ addslashes($leave->reason) }}')">
                                                    عرض السبب
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($leave->status == 'pending')
                                                <span class="badge badge-warning">قيد الانتظار</span>
                                            @elseif($leave->status == 'pending_admin')
                                                <span class="badge badge-info">موافقة مبدئية</span>
                                            @elseif($leave->status == 'rejected_by_dept')
                                                <span class="badge badge-warning">رفض مبدئي - بانتظار الاعتماد</span>
                                            @elseif($leave->status == 'approved')
                                                <span class="badge badge-success">معتمدة</span>
                                            @elseif($leave->status == 'rejected')
                                                <span class="badge badge-danger">مرفوضة نهائياً</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $leave->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons" style="display:flex; gap:5px;">
                                                @if(in_array($leave->status, ['pending', 'pending_admin', 'rejected_by_dept']))
                                                    <form action="{{ route('admin.leaves.status', $leave->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="btn btn-sm btn-success">موافقة</button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmReject({{ $leave->id }})">رفض</button>
                                                    <form id="reject-form-{{ $leave->id }}" action="{{ route('admin.leaves.status', $leave->id) }}" method="POST" style="display:none;">
                                                        @csrf
                                                        <input type="hidden" name="status" value="rejected">
                                                        <input type="hidden" name="reject_reason" id="reason-input-{{ $leave->id }}">
                                                    </form>
                                                @else
                                                    <span class="text-muted">تمت المعالجة</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center;">لا توجد طلبات إجازات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- تبويب طلبات القروض (يظهر فقط لمدير النظام) --}}
                @if($userRole === 'مدير النظام')
                <div id="tab-loan" class="tab-content" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>

                                    <th>الموظف</th>
                                    <th>المبلغ</th>
                                    <th>عدد الأشهر</th>
                                    <th>القسط الشهري</th>
                                    <th>السبب</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                                </thead>
                            <tbody>
                                @forelse($loanRequests ?? [] as $request)
                                    <tr>
                                        <td>{{ $request->employee->first_name }} {{ $request->employee->last_name }}</td>
                                        <td>{{ number_format($request->amount, 2) }} ريال</td>
                                        <td>{{ $request->months }} شهر</td>
                                        <td>{{ number_format($request->monthly_installment, 2) }} ريال</td>
                                        <td>
                                            @if($request->reason)
                                                <button type="button" class="btn btn-sm btn-outline" onclick="showLoanReason('{{ $request->employee->first_name }}', '{{ addslashes($request->reason) }}', '{{ number_format($request->amount, 2) }}')">
                                                    عرض السبب
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status == 'pending')
                                                <span class="badge badge-warning">قيد المراجعة</span>
                                            @elseif($request->status == 'approved')
                                                <span class="badge badge-success">موافق عليه</span>
                                            @else
                                                <span class="badge badge-danger">مرفوض</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons" style="display:flex; gap:5px;">
                                                @if($request->status == 'pending')
                                                    <form action="{{ route('loans.approve', $request->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">موافقة</button>
                                                    </form>
                                                    <form action="{{ route('loans.reject', $request->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger">رفض</button>
                                                    </form>
                                                @else
                                                    <span class="text-muted">تمت المعالجة</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center;">لا توجد طلبات قروض</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </article>
        </section>
    </main>
</div>

@section('script')
<script>
    function confirmReject(id) {
        Swal.fire({
            title: 'تأكيد الرفض',
            text: 'يرجى كتابة سبب الرفض أدناه:',
            input: 'textarea',
            inputPlaceholder: 'اكتب تبرير الرفض هنا...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'تأكيد الرفض',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#d33',
            inputValidator: (value) => { if (!value) return 'يجب كتابة سبب الرفض!' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('reason-input-' + id).value = result.value;
                document.getElementById('reject-form-' + id).submit();
            }
        });
    }

    function showChatHistory(name, reasonText) {
        if (!reasonText || reasonText.trim() === "") {
            Swal.fire({
                title: 'سجل السبب',
                text: 'لا يوجد تبريرات مكتوبة لهذا الطلب بعد.',
                icon: 'info',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        let entries = reasonText.split('[!]').reverse();
        let chatHtml = '<div style="text-align: right; display: flex; flex-direction: column; gap: 15px; padding: 10px; max-height: 400px; overflow-y: auto;">';

        entries.forEach((entry) => {
            let content = entry.trim();
            if(!content) return;

            let style = "", label = "", icon = "";

            if (content.includes('مدير النظام')) {
                style = "background: #fef2f2; color: #b91c1c; border-right: 4px solid #f87171; align-self: flex-start;";
                label = "مدير النظام";
                icon = "🚩";
                content = content.replace(/🚫|رفض|\(مدير النظام\)|:/g, '').trim();
            } else if (content.includes('مدير القسم')) {
                style = "background: #fff7ed; color: #c2410c; border-right: 4px solid #fb923c; align-self: flex-start;";
                label = "مدير القسم";
                icon = "🔸";
                content = content.replace(/🚫|رفض|\(مدير القسم\)|:/g, '').trim();
            } else {
                style = "background: #f1f5f9; color: #475569; border-right: 4px solid #cbd5e1; align-self: flex-start;";
                label = "الموظف (الطلب الأصلي)";
                icon = "👤";
                content = content.replace(/📝|سبب الموظف|:/g, '').trim();
            }

            chatHtml += `
                <div style="padding: 12px; border-radius: 8px; ${style} width: 95%; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <span style="display: block; font-weight: bold; font-size: 0.75rem; margin-bottom: 5px; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 3px;">
                        ${icon} ${label}
                    </span>
                    <div style="font-size: 0.9rem; line-height: 1.6; word-wrap: break-word;">${content}</div>
                </div>
            `;
        });

        chatHtml += '</div>';

        Swal.fire({
            title: '<span style="color: #312e81;">💬 سجل السبب للطلب</span>',
            html: chatHtml,
            showCloseButton: true,
            showConfirmButton: false,
            width: '450px',
            background: '#fff'
        });
    }

    function showLoanReason(name, reason, amount) {
        const message = reason ? reason : "لا يوجد سبب مذكور لهذا الطلب.";
        Swal.fire({
            title: 'طلب قرض من: ' + name,
            html: `<strong>المبلغ:</strong> ${amount} ريال<br><br><strong>السبب:</strong> ${message}`,
            icon: 'info',
            confirmButtonText: 'إغلاق',
            confirmButtonColor: '#3085d6'
        });
    }

    function showTab(tab) {
        document.getElementById('tab-leave').style.display = 'none';
        const loanTab = document.getElementById('tab-loan');
        if (loanTab) loanTab.style.display = 'none';
        document.getElementById('tab-' + tab).style.display = 'block';

        const btns = document.querySelectorAll('.tab-btn');
        btns.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
    }
</script>
@endsection
@endsection
