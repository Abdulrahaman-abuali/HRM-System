@extends('layout.app')

@section('title', 'لوحة تحكم القسم')

@section('content')
<main class="main-content">
    <div class="dashboard-header" style="margin-bottom: 2rem;">
        <h2>مرحباً بك، مدير قسم {{ auth()->user()->employee->department->name ?? '' }}</h2>
        <p>إليك ملخص أداء القسم لهذا اليوم</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">

        <div class="card shadow-sm" style="border-right: 5px solid #6366f1; padding: 20px; background: #fff; border-radius: 10px;">
            <h4 style="color: #64748b; font-size: 0.9rem;">إجمالي موظفي القسم</h4>
            <h2 style="color: #1e1b4b; margin: 10px 0;">{{ $stats['total_employees'] }}</h2>
            <span style="font-size: 0.8rem; color: #6366f1;">موظف مسجل</span>
        </div>

        <div class="card shadow-sm" style="border-right: 5px solid #10b981; padding: 20px; background: #fff; border-radius: 10px;">
            <h4 style="color: #64748b; font-size: 0.9rem;">حضور القسم اليوم</h4>
            <h2 style="color: #064e3b; margin: 10px 0;">{{ $stats['today_attendance'] }}</h2>
            <span style="font-size: 0.8rem; color: #10b981;">حاضرون الآن</span>
        </div>

        <div class="card shadow-sm" style="border-right: 5px solid #f59e0b; padding: 20px; background: #fff; border-radius: 10px;">
            <h4 style="color: #64748b; font-size: 0.9rem;">إجازات بانتظار الموافقة</h4>
            <h2 style="color: #78350f; margin: 10px 0;">{{ $stats['pending_leaves'] }}</h2>
            <a href="{{ route('admin.leaves.index') }}" style="font-size: 0.8rem; color: #f59e0b; text-decoration: none;">عرض الطلبات ←</a>
        </div>

        <div class="card shadow-sm" style="border-right: 5px solid #ef4444; padding: 20px; background: #fff; border-radius: 10px;">
            <h4 style="color: #64748b; font-size: 0.9rem;">المهام الجارية</h4>
            <h2 style="color: #7f1d1d; margin: 10px 0;">{{ $stats['unread_notifications'] }}</h2>
            <span style="font-size: 0.8rem; color: #ef4444;">مهمة قيد التنفيذ</span>
        </div>

    </div>

    <section class="section">
        <article class="card shadow-sm" style="border-radius: 10px;">
            <header class="card-header" style="background: #f8fafc; padding: 15px;">
                <h3 style="font-size: 1rem; color: #334155;">📋 حالة الموظفين (آخر التحديثات)</h3>
            </header>
            <div class="card-body">
                <p style="text-align: center; color: #94a3b8; padding: 20px;">يمكنك متابعة تفاصيل الحضور الكاملة من قائمة "تحضير القسم" الجانبية.</p>
            </div>
        </article>
    </section>
</main>
@endsection
