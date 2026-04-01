@extends('layout.app')

@section('title', 'سجل النشاطات')

@section('content')
<div class="main">
    <main class="main-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h1 class="page-title">سجل النشاطات</h1>
                    <p class="page-subtitle">جميع العمليات التي تمت في النظام</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>التاريخ والوقت</th>
                                    <th>المستخدم</th>
                                    <th>النشاط</th>
                                    <th>النوع</th>
                                </tr>
                                </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            @if($activity->user)
                                                {{ $activity->user->name }}
                                            @else
                                                نظام
                                            @endif
                                        </td>
                                        <td>{!! $activity->description !!}</td>
                                        <td>
                                            @if($activity->type == 'activity-icon-success')
                                                <span class="badge badge-success">✅ نجاح</span>
                                            @elseif($activity->type == 'activity-icon-warning')
                                                <span class="badge badge-warning">⚠️ تنبيه</span>
                                            @else
                                                <span class="badge badge-info">ℹ️ عام</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center;">لا توجد نشاطات مسجلة</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $activities->links() }}
                </div>
            </div>
        </section>
    </main>
</div>

<style>
    .badge-success { background: #d1fae5; color: #059669; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
    .badge-warning { background: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
    .badge-info { background: #e0e7ff; color: #4f46e5; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
</style>
@endsection
