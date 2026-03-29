@extends('layout.app')

@section('title', 'الإشعارات')

@section('content')
 <style>
    /* 1. التعتيم الكامل للخلفية وإخفاء القائمة الجانبية برمجياً */
    #sendGeneralNotification {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: rgba(26, 26, 46, 0.9) !important; /* لون غامق مائل للبنفسجي */
        backdrop-filter: blur(10px); /* تأثير الضبابية الاحترافي */
        z-index: 999999 !important; /* أعلى من القائمة الجانبية */
        display: none;
        align-items: center;
        justify-content: center;
    }

    #sendGeneralNotification.show {
        display: flex !important;
    }

    /* 2. تصميم النموذج البنفسجي (نفس التصميم الأول) */
    .modal-content {
        border: none !important;
        border-radius: 20px !important;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5) !important;
        background: #fff;
        width: 100%;
        max-width: 550px;
        overflow: hidden;
        direction: rtl;
    }

    .modal-header {
        background: linear-gradient(45deg, #6f42c1, #8e44ad) !important;
        color: white !important;
        padding: 25px !important;
        border: none !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        padding: 30px !important;
        text-align: right;
    }

    .form-control {
        border: 2px solid #f0f0f0 !important;
        border-radius: 12px !important;
        padding: 12px !important;
        margin-top: 10px;
    }

    .form-control:focus {
        border-color: #6f42c1 !important;
        box-shadow: none !important;
    }

    .modal-footer {
        padding: 20px 30px !important;
        display: flex !important;
        flex-direction: row-reverse !important;
        gap: 15px !important;
        border-top: 1px solid #eee !important;
    }

    .btn-send {
        background: #6f42c1 !important;
        color: white !important;
        border: none !important;
        padding: 12px 25px !important;
        border-radius: 10px !important;
        font-weight: bold;
    }

    .btn-cancel {
        background: #f8f9fa !important;
        border: 1px solid #ddd !important;
        color: #666 !important;
        padding: 12px 25px !important;
        border-radius: 10px !important;
    }
</style>

<div class="main">
    <main class="main-content">
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">ملخص الإشعارات</h2>
            </div>
            <div class="grid grid-4">
                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">غير مقروءة</h3>
                        <span class="stat-icon stat-icon-warning">●</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ $stats['unread'] ?? 0 }}</p>
                        <p class="stat-caption">إشعارات تحتاج إلى مراجعة</p>
                    </div>
                </article>

                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">مهمة</h3>
                        <span class="stat-icon stat-icon-danger">!</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ $stats['important'] }}</p>
                        <p class="stat-caption">تنبيهات عالية الأهمية</p>
                    </div>
                </article>

                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">إشعارات النظام</h3>
                        <span class="stat-icon stat-icon-info">ℹ</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ $stats['system'] }}</p>
                        <p class="stat-caption">رسائل صادرة آلياً</p>
                    </div>
                </article>

                <article class="card stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-title">إجمالي الإشعارات</h3>
                        <span class="stat-icon stat-icon-primary">🔔</span>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-value">{{ $stats['total'] }}</p>
                        <p class="stat-caption">خلال آخر 7 أيام</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="section">
            <div class="notifications-header">
                <form action="{{ route('notifications') }}" method="GET">
                    <div class="grid grid-4">
                        <div class="form-group">
                            <label class="form-label">نوع الإشعار</label>
                            <select name="type" class="form-control" onchange="this.form.submit()">
                                <option value="">كل الأنواع</option>
                                <option value="important" {{ request('type') == 'important' ? 'selected' : '' }}>مهم</option>
                                <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>تنبيه (حضور)</option>
                                <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>نجاح (رواتب)</option>
                                <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>معلومات (مهام)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">الكل</option>
                                <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>غير مقروء</option>
                                <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>مقروء</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" onchange="this.form.submit()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" onchange="this.form.submit()">
                        </div>
                    </div>
                </form>

                <div class="notifications-actions" style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('notifications.readAll') }}" class="btn btn-primary">وضع الجميع كمقروء</a>

                    <form action="{{ route('notifications.deleteAll') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الكل؟')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline">حذف الكل</button>
                    </form>

                    @if(auth()->user()->role && auth()->user()->role->name == 'مدير النظام')
                    <button type="button" class="btn btn-general" data-bs-toggle="modal" data-bs-target="#sendGeneralNotification">
                        إرسال إعلان عام 📢
                    </button>
                    @endif
                </div>
            </div>
        </section>

        <section class="section">
            <article class="card">
                <header class="card-header">
                    <h2 class="card-title">قائمة الإشعارات</h2>
                </header>
                <div class="card-body">
                    <ul class="notifications-list">
    @forelse($notifications as $noti)
    <li class="notification-item {{ !$noti->is_read ? 'notification-unread' : '' }}">
        <div class="notification-main">
            <div class="notification-icon
                {{ $noti->type == 'important' ? 'notification-icon-important' : '' }}
                {{ $noti->type == 'system' ? 'notification-icon-system' : '' }}
                {{ $noti->type == 'reminder' ? 'notification-icon-reminder' : '' }}">

                @if($noti->source == 'إعلان عام') 📢
                @elseif($noti->source == 'نظام الحضور') ⏰
                @elseif($noti->source == 'نظام الرواتب') 💰
                @elseif($noti->source == 'نظام الإجازات') 📅
                @else 🔔
                @endif
            </div>

            <div class="notification-content">
                <div class="notification-header-row" style="display: flex; align-items: center; gap: 10px;">
                    <span class="notification-title">{{ $noti->title }}</span>

                    @if($noti->type == 'important')
                        <span class="badge" style="background-color: #e74c3c; color: white; padding: 2px 8px; border-radius: 5px; font-size: 0.75rem;">مهم</span>
                    @elseif($noti->type == 'reminder')
                        <span class="badge" style="background-color: #f1c40f; color: #333; padding: 2px 8px; border-radius: 5px; font-size: 0.75rem;">تذكير</span>
                    @elseif($noti->type == 'system')
                        <span class="badge" style="background-color: #3498db; color: white; padding: 2px 8px; border-radius: 5px; font-size: 0.75rem;">من النظام</span>
                    @endif
                </div>

                <p class="notification-text">{{ $noti->text }}</p>
                <div class="notification-meta">
                    <span class="notification-time">{{ $noti->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        <div class="notification-actions" style="display: flex; gap: 8px;">
            @php
                $targetRoute = '#';
                if($noti->source == 'نظام الإجازات') $targetRoute = route('attendance'); // تأكد من أسماء المسارات لديك
                elseif($noti->source == 'نظام الرواتب') $targetRoute = route('payroll.index'); // تأكد من أسماء المسارات لديك
                elseif($noti->source == 'إدارة المهام') $targetRoute = route('tasks.admin'); // تأكد من أسماء المسارات لديك
            @endphp


            @if(auth()->user()->role && auth()->user()->role->name == 'مدير النظام')
                @if($targetRoute != '#')
                    <a href="{{ $targetRoute }}" class="btn btn-sm" style="background-color: #6f42c1; color: white; border-radius: 5px;">
                        عرض التفاصيل
                    </a>
                @endif
            @endif

            @if(!$noti->is_read)
                <a href="{{ route('notifications.read', $noti->id) }}" class="btn btn-sm btn-outline">قراءة</a>
            @endif

           <form action="{{ route('notifications.destroy', $noti->id) }}"
                method="POST"
                style="display:inline;"
                onsubmit="return confirm('هل أنت متأكد من حذف هذا الإشعار؟ لا يمكن التراجع عن هذه الخطوة.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm"
                        style="background-color: transparent; color: #e74c3c; border: 1px solid #e74c3c; padding: 4px 10px; border-radius: 5px; transition: 0.3s;"
                        onmouseover="this.style.backgroundColor='#e74c3c'; this.style.color='white'"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='#e74c3c'">
                    حذف
                </button>
            </form>
        </div>
    </li>
    @empty
    <li style="text-align: center; padding: 20px;">لا توجد إشعارات حالياً.</li>
    @endforelse
</ul>
                </div>
            </article>
        </section>
    </main>
</div>

<div class="modal fade" id="sendGeneralNotification" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="margin: 0 !important; width: 100%; display: flex; justify-content: center;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="margin: 0;">📢 إرسال إعلان عام للموظفين</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="margin: 0;"></button>
            </div>
            <form action="{{ route('notifications.sendGeneral') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">عنوان الإعلان</label>
                        <input type="text" name="title" class="form-control" placeholder="مثلاً: تنبيه بخصوص إجازة العيد" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نص الرسالة</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="اكتب تفاصيل الإعلان هنا بكل وضوح..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-send">إرسال الإعلان الآن 🚀</button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // التأكد من أن المودال يعمل برمجياً
        var myModalEl = document.getElementById('sendGeneralNotification');
        var modal = new bootstrap.Modal(myModalEl);
    });
</script>
@endsection
