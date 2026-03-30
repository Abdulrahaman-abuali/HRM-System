{{-- @extends('layout.app') --}}

@section('content')
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

            <section class="section">
                <div class="grid grid-4">
                    <article class="card stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-title">طلبات قيد الانتظار</h3>
                            <span class="stat-icon stat-icon-warning">⏳</span>
                        </div>
                        <div class="stat-card-body">
                            <p class="stat-value">{{ $stats['pending'] }}</p>
                        </div>
                    </article>

                    <article class="card stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-title">إجازات معتمدة</h3>
                            <span class="stat-icon stat-icon-success">✔</span>
                        </div>
                        <div class="stat-card-body">
                            <p class="stat-value">{{ $stats['approved'] }}</p>
                        </div>
                    </article>

                    <article class="card stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-title">إجازات مرفوضة</h3>
                            <span class="stat-icon stat-icon-danger">✖</span>
                        </div>
                        <div class="stat-card-body">
                            <p class="stat-value">{{ $stats['rejected'] }}</p>
                        </div>
                    </article>

                    <article class="card stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-title">إجمالي الطلبات</h3>
                            <span class="stat-icon stat-icon-info">📅</span>
                        </div>
                        <div class="stat-card-body">
                            <p class="stat-value">{{ $stats['total'] }}</p>
                        </div>
                    </article>
                </div>
            </section>

            <section class="section">
                <article class="card">
                    <header class="card-header">
                        <h2 class="card-title">طلبات الإجازة الواردة</h2>
                    </header>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الموظف</th>
                                        <th>نوع الإجازة</th>
                                        <th>الفترة</th>
                                        <th>المدة</th>
                                        <th>حالة الطلب</th>
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
                                            <td>{{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</td>
                                            <td><span class="badge badge-info">{{ $leave->leaveType->name }}</span></td>
                                            <td>{{ $leave->start_date }} إلى {{ $leave->end_date }}</td>
                                            <td>{{ $days }} أيام</td>
                                            <td>
                                                @if($leave->status == 'pending')
                                                    <span class="badge badge-warning">قيد الانتظار</span>
                                                @elseif($leave->status == 'approved')
                                                    <span class="badge badge-success">معتمدة</span>
                                                @else
                                                    <span class="badge badge-danger">مرفوضة</span>
                                                @endif
                                            </td>
                                            <td>
    <div class="action-buttons" style="display:flex; gap:5px;">
        <button type="button"
                class="btn btn-sm btn-info"
                onclick="showReason('{{ $leave->employee->first_name }}', '{{ $leave->reason }}')">
            عرض السبب
        </button>

        @if($leave->status == 'pending')
            <form action="{{ route('admin.leaves.status', $leave->id) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="btn btn-sm btn-success">موافقة</button>
            </form>

            <form action="{{ route('admin.leaves.status', $leave->id) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <button type="submit" class="btn btn-sm btn-danger">رفض</button>
            </form>
        @else
            <span class="text-muted">تمت المعالجة</span>
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
    function showReason(name, reason) {
        // إذا كان السبب فارغاً
        const message = reason ? reason : "لا يوجد سبب مذكور لهذا الطلب.";

        Swal.fire({
            title: 'سبب إجازة الموظف: ' + name,
            text: message,
            icon: 'info',
            confirmButtonText: 'إغلاق',
            confirmButtonColor: '#3085d6',
            background: '#fff',
            customClass: {
                title: 'text-right',
                content: 'text-right'
            }
        });
    }
</script>
@endsection
@endsection
