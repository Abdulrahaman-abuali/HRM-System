@extends('layout.app')

@section('content')
<div class="main-content">
    <header class="main-header">
        <div class="header-left">
            <h1 class="page-title">طلبات الإجازة</h1>
            <p class="page-subtitle">يمكنك تقديم طلب جديد ومتابعة حالة طلباتك السابقة</p>
        </div>
    </header>

    {{-- رسائل النجاح أو الخطأ --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px; padding: 10px; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- نموذج طلب جديد --}}
    <section class="section">
        <article class="card">
            <div class="card-header"><h2 class="card-title">تقديم طلب إجازة</h2></div>
            <div class="card-body">
                <form action="{{ route('leaves.store') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">نوع الإجازة</label>
                            {{-- تم التعديل لاستخدام leave_type_id بدلاً من type --}}
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
        </article>
    </section>

    {{-- جدول الطلبات السابقة --}}
    <section class="section" style="margin-top: 20px;">
        <article class="card">
            <div class="card-header"><h2 class="card-title">سجل الطلبات</h2></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>النوع</th>
                                <th>من</th>
                                <th>إلى</th>
                                <th>الحالة</th>
                                <th>تاريخ التقديم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                            <tr>
                                {{-- تم التعديل لجلب اسم النوع من العلاقة leaveType --}}
                                <td>{{ $leave->leaveType->name ?? 'غير محدد' }}</td>
                                <td>{{ $leave->start_date }}</td>
                                <td>{{ $leave->end_date }}</td>
                                <td>
                                    @if($leave->status == 'pending')
                                        <span class="badge badge-warning">قيد الانتظار</span>
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
                            <tr><td colspan="5" style="text-align: center;">لا توجد طلبات سابقة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </article>
    </section>
</div>
@endsection
