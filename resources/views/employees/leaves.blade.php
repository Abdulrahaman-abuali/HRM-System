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
    .btn-outline { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 6px; cursor: pointer; font-size: 12px; transition: all 0.2s; }
    .btn-outline:hover { background: #e2e8f0; }
    .text-muted { color: #6b7280; }
</style>

<div class="main-content">
    <header class="main-header">
        <div class="header-left">
            <h1 class="page-title">الطلبات</h1>
            <p class="page-subtitle">يمكنك تقديم طلبات إجازة أو قرض جديد ومتابعة حالة طلباتك السابقة</p>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px; padding: 10px; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 20px; padding: 10px; border-radius: 5px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- تبويبات الطلبات --}}
    <section class="section">
        <article class="card">
            <div class="card-header">
                <div class="tabs" style="display: flex; gap: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
                    <button class="tab-btn active" onclick="showTab('leave')">📅 طلبات الإجازات</button>
                    <button class="tab-btn" onclick="showTab('loan')">💰 طلبات القروض</button>
                </div>
            </div>

            {{-- تبويب طلبات الإجازات --}}
            <div id="tab-leave" class="tab-content active">
                {{-- نموذج تقديم إجازة --}}
                <div style="margin-bottom: 30px; padding: 20px; background: #f8fafc; border-radius: 12px;">
                    <h3>تقديم طلب إجازة جديد</h3>
                    <form action="{{ route('leaves.store') }}" method="POST">
                        @csrf
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div class="form-group">
                                <label class="form-label">نوع الإجازة</label>
                                <select name="leave_type_id" class="form-control" required>
                                    <option value="">-- اختر نوع الإجازة --</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">من تاريخ</label>
                                <input type="date" name="start_date" class="form-control" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">إلى تاريخ</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 15px;">
                            <label class="form-label">السبب (اختياري)</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="اشرح سبب طلب الإجازة هنا..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 15px;">إرسال الطلب</button>
                    </form>
                </div>

                {{-- جدول طلبات الإجازات السابقة --}}
                <h3>سجل طلبات الإجازات</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            32
                                <th>النوع</th>
                                <th>من</th>
                                <th>إلى</th>
                                <th>السبب</th>
                                <th>الحالة</th>
                                <th>تاريخ التقديم</th>
                            </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                                <tr>
                                    <td>{{ $leave->leaveType->name ?? 'غير محدد' }}</td>
                                    <td>{{ $leave->start_date }}</td>
                                    <td>{{ $leave->end_date }}</td>
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
                                            <span class="badge badge-warning">رفض مبدئي</span>
                                        @elseif($leave->status == 'approved')
                                            <span class="badge badge-success">مقبولة</span>
                                        @elseif($leave->status == 'rejected')
                                            <span class="badge badge-danger">مرفوضة</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $leave->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $leave->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" style="text-align: center;">لا توجد طلبات إجازة سابقة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- تبويب طلبات القروض --}}
            <div id="tab-loan" class="tab-content" style="display: none;">
                {{-- نموذج تقديم قرض --}}
                <div style="margin-bottom: 30px; padding: 20px; background: #f8fafc; border-radius: 12px;">
                    <h3>تقديم طلب قرض جديد</h3>
                    <form action="{{ route('loans.store') }}" method="POST">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label class="form-label">المبلغ المطلوب (ر.ي)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required min="1000">
                                <small>الحد الأدنى 1,000 ريال</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">عدد الأشهر</label>
                                <select name="months" class="form-control" required>
                                    <option value="">اختر عدد الأشهر</option>
                                    @for($i = 1; $i <= 24; $i++)
                                        <option value="{{ $i }}">{{ $i }} شهر</option>
                                    @endfor
                                </select>
                                <small>الحد الأقصى 24 شهراً</small>
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label class="form-label">سبب القرض (اختياري)</label>
                                <textarea name="reason" class="form-control" rows="2" placeholder="اذكر سبب طلب القرض..."></textarea>
                            </div>
                        </div>
                        <div id="installment_preview" class="alert alert-info" style="display: none; margin-top: 15px;">
                            القسط الشهري المتوقع: <strong><span id="monthly_installment">0</span> ريال</strong>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 15px;">إرسال الطلب</button>
                    </form>
                </div>

                {{-- جدول طلبات القروض السابقة --}}
                <h3>سجل طلبات القروض</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            32
                                <th>المبلغ</th>
                                <th>عدد الأشهر</th>
                                <th>القسط الشهري</th>
                                <th>السبب</th>
                                <th>الحالة</th>
                                <th>تاريخ التقديم</th>
                            </thead>
                        <tbody>
                            @forelse($loanRequests as $request)
                                <tr>
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
                                        @elseif($request->status == 'rejected')
                                            <span class="badge badge-danger">مرفوض</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $request->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" style="text-align: center;">لا توجد طلبات قروض سابقة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </article>
    </section>
</div>

@section('script')
<script>
    function showTab(tab) {
        document.getElementById('tab-leave').style.display = 'none';
        document.getElementById('tab-loan').style.display = 'none';
        document.getElementById('tab-' + tab).style.display = 'block';
        const btns = document.querySelectorAll('.tab-btn');
        btns.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
    }

    // حساب القسط الشهري
    const amountInput = document.querySelector('#tab-loan input[name="amount"]');
    const monthsSelect = document.querySelector('#tab-loan select[name="months"]');
    const previewDiv = document.getElementById('installment_preview');
    const installmentSpan = document.getElementById('monthly_installment');

    function calculateInstallment() {
        const amount = parseFloat(amountInput?.value) || 0;
        const months = parseInt(monthsSelect?.value) || 0;
        if (amount > 0 && months > 0) {
            const installment = amount / months;
            installmentSpan.innerText = installment.toLocaleString(undefined, {minimumFractionDigits: 2});
            previewDiv.style.display = 'block';
        } else {
            previewDiv.style.display = 'none';
        }
    }
    if (amountInput && monthsSelect) {
        amountInput.addEventListener('input', calculateInstallment);
        monthsSelect.addEventListener('change', calculateInstallment);
    }

    // عرض سجل السبب (نفس تصميم مدير القسم)
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
                label = "طلبك الأصلي";
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

    // عرض سبب طلب القرض (نفس تصميم مدير القسم)
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
</script>
@endsection
@endsection 
