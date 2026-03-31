@extends('layout.app')

@section('content')
<style>
    /* تنسيق فقاعات الدردشة لتبدو تحت بعضها */
    .chat-container { display: flex; flex-direction: column; gap: 8px; padding: 5px; }
    .chat-bubble {
        padding: 10px 15px;
        border-radius: 10px;
        font-size: 0.85rem;
        line-height: 1.4;
        max-width: 100%;
        position: relative;
    }
    /* تنسيق الموظف (دائماً الأول) */
    .chat-employee { background: #f8fafc; color: #475569; border-right: 4px solid #cbd5e1; }
    /* تنسيق مدير القسم (الثاني) */
    .chat-dept { background: #fffaf0; color: #c2410c; border-right: 4px solid #fb923c; }
    /* تنسيق مدير النظام (الأخير) */
    .chat-admin { background: #fef2f2; color: #b91c1c; border-right: 4px solid #f87171; }

    .chat-label { display: block; font-weight: bold; font-size: 0.7rem; margin-bottom: 4px; text-transform: uppercase; }
</style>

    <div class="main">
        <header class="main-header">
            <div class="header-left">
                <h1 class="page-title">إدارة الإجازات</h1>
                <p class="page-subtitle">متابعة طلبات الإجازة واعتمادها لموظفي النظام</p>
            </div>
        </header>

        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
            @endif

            {{-- 1. قسم تقديم طلب لنفسي + متابعة حالات طلباتي الشخصية --}}
            @if(auth()->user()->role?->name === 'مدير القسم')
            <section class="section" style="margin-bottom: 30px;">
                <div class="row" style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div class="col-md-5" style="flex: 1; min-width: 300px;">
                        <article class="card shadow-sm" style="border-top: 4px solid #6366f1; height: 100%;">
                            <header class="card-header" style="background: #f8fafc; padding: 15px;">
                                <h2 style="font-size: 1rem; margin: 0; color: #312e81;">✍️ تقديم طلب إجازة لنفسي</h2>
                            </header>
                            <div class="card-body" style="padding: 20px;">
                                <form action="{{ route('leaves.store') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label style="font-weight: bold; font-size: 0.9rem;">نوع الإجازة:</label>
                                        <select name="leave_type_id" class="form-control" required style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                                            <option value="">اختر النوع...</option>
                                            @foreach(\App\Models\LeaveType::all() as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="display: flex; gap: 10px;" class="mb-3">
                                        <div style="flex: 1;">
                                            <label style="font-weight: bold; font-size: 0.9rem;">من تاريخ:</label>
                                            <input type="date" name="start_date" class="form-control" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                        </div>
                                        <div style="flex: 1;">
                                            <label style="font-weight: bold; font-size: 0.9rem;">إلى تاريخ:</label>
                                            <input type="date" name="end_date" class="form-control" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label style="font-weight: bold; font-size: 0.9rem;">السبب:</label>
                                        <textarea name="reason" class="form-control" rows="2" placeholder="اكتب سبب الإجازة هنا..." style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="width: 100%; background: #6366f1; border: none; padding: 10px; border-radius: 5px; font-weight: bold; color: white;">إرسال الطلب للمدير العام</button>
                                </form>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-7" style="flex: 1.5; min-width: 300px;">
                        <article class="card shadow-sm" style="border-top: 4px solid #10b981; height: 100%;">
                            <header class="card-header" style="background: #f8fafc; padding: 15px;">
                                <h2 style="font-size: 1rem; margin: 0; color: #065f46;">📊 حالة طلباتي الأخيرة</h2>
                            </header>
                            <div class="card-body" style="padding: 0;">
                                <table class="table" style="width: 100%; text-align: right;">
                                    <thead style="background: #f1f5f9;">
                                        <tr>
                                            <th style="padding: 12px;">النوع</th>
                                            <th style="padding: 12px;">التاريخ</th>
                                            <th style="padding: 12px;">الحالة / التبرير</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($myRequests as $req)
                                        <tr style="border-bottom: 1px solid #eee;">
                                            <td>{{ $req->leaveType->name }}</td>
                                            <td>{{ $req->start_date }}</td>
                                            <td>
                                                <span class="badge" style="padding: 4px 8px; border-radius: 8px; background: {{ $req->status == 'approved' ? '#dcfce7' : ($req->status == 'pending' ? '#fef3c7' : ($req->status == 'pending_admin' ? '#e0f2fe' : '#fef2f2')) }}; color: {{ $req->status == 'approved' ? '#166534' : ($req->status == 'pending' ? '#92400e' : ($req->status == 'pending_admin' ? '#0369a1' : '#991b1b')) }};">
                                                    @if($req->status == 'pending') قيد الانتظار @elseif($req->status == 'pending_admin') موافقة مبدئية @elseif($req->status == 'approved') مقبولة @else مرفوضة @endif
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" style="text-align: center; padding: 20px;">لم تقم بتقديم أي طلبات بعد.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
            @endif

            {{-- 2. كروت الإحصائيات --}}
            <section class="section">
                <div class="grid grid-4">
                    <article class="card stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-title">طلبات قيد الانتظار</h3>
                            <span class="stat-icon stat-icon-warning">⏳</span>
                        </div>
                        <div class="stat-card-body"><p class="stat-value">{{ $stats['pending'] }}</p></div>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-title">إجازات معتمدة</h3>
                            <span class="stat-icon stat-icon-success">✔</span>
                        </div>
                        <div class="stat-card-body"><p class="stat-value">{{ $stats['approved'] }}</p></div>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-title">إجازات مرفوضة</h3>
                            <span class="stat-icon stat-icon-danger">✖</span>
                        </div>
                        <div class="stat-card-body"><p class="stat-value">{{ $stats['rejected'] }}</p></div>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-title">إجمالي الطلبات</h3>
                            <span class="stat-icon stat-icon-info">📅</span>
                        </div>
                        <div class="stat-card-body"><p class="stat-value">{{ $stats['total'] }}</p></div>
                    </article>
                </div>
            </section>

            {{-- 3. جدول طلبات الإجازة الواردة (تم تعديل عمود الأسباب) --}}
            <section class="section">
                <article class="card">
                    <header class="card-header">
                        <h2 class="card-title">طلبات الإجازة الواردة</h2>
                    </header>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>الموظف</th>
                                        <th>نوع الإجازة</th>
                                        <th>الفترة (المدة)</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $leave)
                                        @php
                                            $start = \Carbon\Carbon::parse($leave->start_date);
                                            $end = \Carbon\Carbon::parse($leave->end_date);
                                            $days = $start->diffInDays($end) + 1;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</strong></td>
                                            <td><span class="badge badge-info">{{ $leave->leaveType->name }}</span></td>

                                            {{-- عمود سجل الدردشة المرتب --}}
                                            <td>
                                                <div class="small">{{ $leave->start_date }}
                                                     - {{ $leave->end_date }}
                                                </div>

                                                <div class="text-muted small">({{ $days }} أيام)</div>
                                            </td>

                                            <td>
                                                @if($leave->status == 'pending') <span class="badge badge-warning">قيد الانتظار</span>
                                                @elseif($leave->status == 'pending_admin') <span class="badge badge-primary" style="background:#007bff; color:#fff;">موافقة مبدئية</span>
                                                @elseif($leave->status == 'rejected_by_dept') <span class="badge" style="background:#f97316; color:#fff;">مرفوضة من القسم</span>
                                                @elseif($leave->status == 'approved') <span class="badge badge-success">معتمدة</span>
                                                @else <span class="badge badge-danger">مرفوضة نهائياً</span>
                                                @endif
                                            </td>
                                            <td>
                                            <div class="action-buttons" style="display:flex; gap:8px; align-items: center;">

    {{-- 💬 زر السبب (لون أزرق سماوي للمعلومات) --}}
    <button type="button"
            class="btn btn-sm"
            style="background-color: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; font-weight: bold; border-radius: 6px; padding: 5px 12px;"
            onclick="showChatHistory('{{ $leave->employee->first_name }}', '{{ addslashes($leave->reason) }}')">
        💬 السبب
    </button>

    @php
        $isManager = auth()->user()->role?->name === 'مدير النظام';
        $isDeptHead = auth()->user()->role?->name === 'مدير القسم';
    @endphp

    @if(($isDeptHead && $leave->status == 'pending') || ($isManager && in_array($leave->status, ['pending', 'pending_admin', 'rejected_by_dept'])))

        {{-- ✅ زر الموافقة / الاعتماد النهائي (لون أخضر زمردي) --}}
        <form action="{{ route('admin.leaves.status', $leave->id) }}" method="POST" style="margin:0;">
            @csrf
            <input type="hidden" name="status" value="approved">
            <button type="submit" class="btn btn-sm text-white"
                    style="background-color: #10b981; border: none; font-weight: bold; border-radius: 6px; padding: 5px 15px; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">
                {{ $isManager ? 'اعتماد نهائي' : 'موافقة' }}
            </button>
        </form>

        {{-- ❌ زر الرفض (يبقى باللون الأحمر) --}}
        <button type="button" class="btn btn-sm btn-danger"
                style="font-weight: bold; border-radius: 6px; padding: 5px 15px;"
                onclick="confirmReject({{ $leave->id }}, '{{ $isManager ? 'نهائي' : 'مبدئي' }}')">
            رفض
        </button>

        <form id="reject-form-{{ $leave->id }}" action="{{ route('admin.leaves.status', $leave->id) }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="status" value="rejected">
            <input type="hidden" name="reject_reason" id="reason-input-{{ $leave->id }}">
        </form>
    @else
        <span class="badge bg-light text-muted" style="border: 1px dashed #ccc; padding: 6px 12px;">تمت المعالجة</span>
    @endif
</div>
                                        </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </div>

    @section('script')
    <script>
        function confirmReject(id, type) {
            Swal.fire({
                title: 'تأكيد الرفض الـ' + type,
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
</script>
@endsection
@endsection
