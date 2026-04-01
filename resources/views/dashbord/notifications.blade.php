@extends('layout.app')

@section('title', 'الإشعارات')

@section('content')

    <style>
        /* 1. التعتيم الكامل للخلفية وإخفاء القائمة الجانبية برمجياً */
        #sendGeneralNotification,
        #sendSpecificNotification {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background-color: rgba(26, 26, 46, 0.9) !important;
            backdrop-filter: blur(10px);
            z-index: 999999 !important;
            display: none;
            align-items: center;
            justify-content: center;
        }

        #sendGeneralNotification.show,
        #sendSpecificNotification.show {
            display: flex !important;
        }

        /* 2. تصميم النموذج البنفسجي */
        .modal-content {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5) !important;
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

        .btn-send-specific {
            background: #8b5cf6 !important;
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

        /* تنسيق جميع المودالات */
        #sendGeneralNotification,
        #sendSpecificNotification,
        #sendDepartmentNotification,
        #sendDepartmentSpecificModal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background-color: rgba(26, 26, 46, 0.9) !important;
            backdrop-filter: blur(10px);
            z-index: 999999 !important;
            display: none;
            align-items: center;
            justify-content: center;
        }

        #sendGeneralNotification.show,
        #sendSpecificNotification.show,
        #sendDepartmentNotification.show,
        #sendDepartmentSpecificModal.show {
            display: flex !important;
        }

        /* تنسيق محتوى المودال */
        .modal-content {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5) !important;
            background: #fff;
            width: 100%;
            max-width: 550px;
            overflow: hidden;
            direction: rtl;
            margin: auto !important;
            position: relative !important;
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

        .btn-send-specific {
            background: #8b5cf6 !important;
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
                                    <option value="important" {{ request('type') == 'important' ? 'selected' : '' }}>مهم
                                    </option>
                                    <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>تنبيه
                                        (حضور)</option>
                                    <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>نجاح
                                        (رواتب)</option>
                                    <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>معلومات (مهام)
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-control" onchange="this.form.submit()">
                                    <option value="">الكل</option>
                                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>غير مقروء
                                    </option>
                                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>مقروء
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">من تاريخ</label>
                                <input type="date" name="from_date" value="{{ request('from_date') }}"
                                    class="form-control" onchange="this.form.submit()">
                            </div>
                            <div class="form-group">
                                <label class="form-label">إلى تاريخ</label>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control"
                                    onchange="this.form.submit()">
                            </div>
                        </div>
                    </form>

                    <div class="notifications-actions" style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="{{ route('notifications.readAll') }}" class="btn btn-primary">وضع الجميع كمقروء</a>

                        <form action="{{ route('notifications.deleteAll') }}" method="POST"
                            onsubmit="return confirm('هل أنت متأكد من حذف الكل؟')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline">حذف الكل</button>
                        </form>

                        @if (auth()->user()->role && auth()->user()->role->name == 'مدير النظام')
                            {{-- أزرار مدير النظام --}}
                            <button type="button" class="btn btn-general" onclick="openGeneralNotificationModal()">
                                إرسال إعلان عام 📢
                            </button>
                            <button type="button" class="btn btn-general" style="background: #8b5cf6;"
                                onclick="openSpecificNotificationModal()">
                                إرسال إشعار خاص 👤
                            </button>
                        @endif

                        @if (auth()->user()->role && auth()->user()->role->name == 'مدير القسم')
                            {{-- أزرار مدير القسم --}}
                            <button type="button" class="btn btn-general" style="background: #10b981;"
                                onclick="openDepartmentNotificationModal()">
                                إرسال إعلان للقسم 📢
                            </button>
                            <button type="button" class="btn btn-general" style="background: #8b5cf6;"
                                onclick="openDepartmentSpecificModal()">
                                إرسال إشعار لموظف بالقسم 👤
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
                                        <div
                                            class="notification-icon
                                    {{ $noti->type == 'important' ? 'notification-icon-important' : '' }}
                                    {{ $noti->type == 'system' ? 'notification-icon-system' : '' }}
                                    {{ $noti->type == 'reminder' ? 'notification-icon-reminder' : '' }}">

                                            @if ($noti->source == 'إعلان عام')
                                                📢
                                            @elseif($noti->source == 'إشعار خاص')
                                                👤
                                            @elseif($noti->source == 'نظام الحضور')
                                                ⏰
                                            @elseif($noti->source == 'نظام الرواتب')
                                                💰
                                            @elseif($noti->source == 'نظام الإجازات')
                                                📅
                                            @else
                                                🔔
                                            @endif
                                        </div>

                                        <div class="notification-content">
                                            <div class="notification-header-row"
                                                style="display: flex; align-items: center; gap: 10px;">
                                                <span class="notification-title">{{ $noti->title }}</span>

                                                @if ($noti->type == 'important')
                                                    <span class="badge"
                                                        style="background-color: #e74c3c; color: white; padding: 2px 8px; border-radius: 5px; font-size: 0.75rem;">مهم</span>
                                                @elseif($noti->type == 'reminder')
                                                    <span class="badge"
                                                        style="background-color: #f1c40f; color: #333; padding: 2px 8px; border-radius: 5px; font-size: 0.75rem;">تذكير</span>
                                                @elseif($noti->type == 'system')
                                                    <span class="badge"
                                                        style="background-color: #3498db; color: white; padding: 2px 8px; border-radius: 5px; font-size: 0.75rem;">من
                                                        النظام</span>
                                                @endif
                                            </div>

                                            <p class="notification-text">{{ $noti->text }}</p>
                                            <div class="notification-meta">
                                                <span
                                                    class="notification-time">{{ $noti->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="notification-actions" style="display: flex; gap: 8px;">
                                        @php
                                            $targetRoute = '#';
                                            if ($noti->source == 'نظام الإجازات') {
                                                $targetRoute = route('attendance');
                                            } elseif ($noti->source == 'نظام الرواتب') {
                                                $targetRoute = route('payroll.index');
                                            } elseif ($noti->source == 'إدارة المهام') {
                                                $targetRoute = route('tasks.admin');
                                            }
                                        @endphp

                                        @if (auth()->user()->role && auth()->user()->role->name == 'مدير النظام')
                                            @if ($targetRoute != '#')
                                                <a href="{{ $targetRoute }}" class="btn btn-sm"
                                                    style="background-color: #6f42c1; color: white; border-radius: 5px;">
                                                    عرض التفاصيل
                                                </a>
                                            @endif
                                        @endif

                                        @if (!$noti->is_read)
                                            <a href="{{ route('notifications.read', $noti->id) }}"
                                                class="btn btn-sm btn-outline">قراءة</a>
                                        @endif

                                        <form action="{{ route('notifications.destroy', $noti->id) }}" method="POST"
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

    {{-- مودال الإعلان العام --}}
    <div id="sendGeneralNotification" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="margin: 0;">📢 إرسال إعلان عام للموظفين</h5>
                <button type="button" onclick="closeGeneralNotificationModal()"
                    style="background: none; border: none; font-size: 1.5rem; color: white; cursor: pointer;">&times;</button>
            </div>
            <form action="{{ route('notifications.sendGeneral') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">عنوان الإعلان</label>
                        <input type="text" name="title" class="form-control"
                            placeholder="مثلاً: تنبيه بخصوص إجازة العيد" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نص الرسالة</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="اكتب تفاصيل الإعلان هنا بكل وضوح..."
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-send">إرسال الإعلان الآن 🚀</button>
                    <button type="button" class="btn btn-cancel"
                        onclick="closeGeneralNotificationModal()">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
    {{-- مودال الإشعار الخاص لموظف واحد مع بحث تلقائي --}}
    <div id="sendSpecificNotification" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="margin: 0;">👤 إرسال إشعار خاص لموظف</h5>
                <button type="button" onclick="closeSpecificNotificationModal()"
                    style="background: none; border: none; font-size: 1.5rem; color: white; cursor: pointer;">&times;</button>
            </div>
            <form action="{{ route('notifications.sendToEmployee') }}" method="POST" id="specificNotificationForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">اختر الموظف</label>
                        <div style="position: relative;">
                            <input type="text" id="employeeSearchInput" class="form-control"
                                placeholder="ابحث باسم الموظف..." autocomplete="off" onkeyup="filterEmployees()"
                                onfocus="showSuggestions()">
                            <input type="hidden" name="employee_id" id="selectedEmployeeId" required>
                            <div id="employeeSuggestions"
                                style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 8px; max-height: 250px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                        <small class="text-muted" style="display: block; margin-top: 5px;">يمكنك كتابة اسم الموظف أو جزء
                            منه</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">عنوان الإشعار</label>
                        <input type="text" name="title" class="form-control" placeholder="مثلاً: اجتماع هام"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">نص الرسالة</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="أدخل نص الإشعار هنا..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-send-specific">إرسال الإشعار الآن 🚀</button>
                    <button type="button" class="btn btn-cancel"
                        onclick="closeSpecificNotificationModal()">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
    {{-- مودال إرسال إعلان للقسم (لمدير القسم) --}}
    <div id="sendDepartmentNotification" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="margin: 0;">📢 إرسال إعلان لقسم
                    {{ auth()->user()->employee->department->name ?? '' }}</h5>
                <button type="button" onclick="closeDepartmentNotificationModal()"
                    style="background: none; border: none; font-size: 1.5rem; color: white; cursor: pointer;">&times;</button>
            </div>
            <form action="{{ route('notifications.sendToDepartment') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">عنوان الإعلان</label>
                        <input type="text" name="title" class="form-control" placeholder="مثلاً: اجتماع القسم"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نص الرسالة</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="أدخل نص الإعلان هنا..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-send" style="background: #10b981 !important;">إرسال الإعلان
                        للقسم 🚀</button>
                    <button type="button" class="btn btn-cancel"
                        onclick="closeDepartmentNotificationModal()">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مودال إرسال إشعار خاص لموظف في القسم (لمدير القسم) --}}
    <div id="sendDepartmentSpecificModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="margin: 0;">👤 إرسال إشعار لموظف بالقسم</h5>
                <button type="button" onclick="closeDepartmentSpecificModal()"
                    style="background: none; border: none; font-size: 1.5rem; color: white; cursor: pointer;">&times;</button>
            </div>
            <form action="{{ route('notifications.sendToEmployeeDepartment') }}" method="POST"
                id="departmentSpecificForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">اختر الموظف</label>
                        <div style="position: relative;">
                            <input type="text" id="deptEmployeeSearchInput" class="form-control"
                                placeholder="ابحث باسم الموظف..." autocomplete="off" onkeyup="filterDeptEmployees()"
                                onfocus="showDeptSuggestions()">
                            <input type="hidden" name="employee_id" id="selectedDeptEmployeeId" required>
                            <div id="deptEmployeeSuggestions"
                                style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 8px; max-height: 250px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                        <small class="text-muted" style="display: block; margin-top: 5px;">يمكنك كتابة اسم الموظف أو جزء
                            منه</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">عنوان الإشعار</label>
                        <input type="text" name="title" class="form-control" placeholder="مثلاً: اجتماع هام"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">نص الرسالة</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="أدخل نص الإشعار هنا..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-send-specific">إرسال الإشعار الآن 🚀</button>
                    <button type="button" class="btn btn-cancel" onclick="closeDepartmentSpecificModal()">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    @php
        // جلب بيانات الموظفين من الخادم لاستخدامها في JavaScript
        $allEmployees = \App\Models\Employee::with('department')
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'department' => $emp->department->name ?? 'بدون قسم',
                ];
            });

        $deptEmployees = [];
        if (auth()->user()->role?->name == 'مدير القسم' && auth()->user()->employee) {
            $deptEmployees = \App\Models\Employee::where('department_id', auth()->user()->employee->department_id)
                ->with('department')
                ->get()
                ->map(function ($emp) {
                    return [
                        'id' => $emp->id,
                        'name' => $emp->first_name . ' ' . $emp->last_name,
                        'department' => $emp->department->name ?? 'بدون قسم',
                    ];
                });
        }
    @endphp
    <script>
        // بيانات الموظفين من الخادم
        let employees = @json($allEmployees);

        // بيانات موظفي القسم (لمدير القسم)
        let departmentEmployees = @json($deptEmployees);

        // البحث عن الموظفين (للمدير العام)
        function filterEmployees() {
            let input = document.getElementById('employeeSearchInput');
            if (!input) return;
            let filter = input.value.toLowerCase();
            let suggestionsDiv = document.getElementById('employeeSuggestions');

            if (filter.length === 0) {
                if (suggestionsDiv) suggestionsDiv.style.display = 'none';
                return;
            }

            let filtered = employees.filter(emp =>
                emp.name.toLowerCase().includes(filter) ||
                emp.department.toLowerCase().includes(filter)
            );

            if (filtered.length === 0) {
                if (suggestionsDiv) {
                    suggestionsDiv.innerHTML =
                        '<div style="padding: 12px; text-align: center; color: #999;">لا توجد نتائج</div>';
                    suggestionsDiv.style.display = 'block';
                }
                return;
            }

            let html = '';
            filtered.forEach(emp => {
                let highlightedName = emp.name.replace(new RegExp(`(${filter})`, 'gi'),
                    '<strong style="color: #6f42c1;">$1</strong>');
                html += `
                <div onclick="selectEmployee(${emp.id}, '${emp.name.replace(/'/g, "\\'")}')"
                     style="padding: 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;"
                     onmouseover="this.style.backgroundColor='#f8f9fa'"
                     onmouseout="this.style.backgroundColor='white'">
                    <div style="font-weight: 500;">${highlightedName}</div>
                    <div style="font-size: 12px; color: #6c757d;">${emp.department}</div>
                </div>
            `;
            });

            if (suggestionsDiv) {
                suggestionsDiv.innerHTML = html;
                suggestionsDiv.style.display = 'block';
            }
        }

        function showSuggestions() {
            let input = document.getElementById('employeeSearchInput');
            if (input && input.value.length > 0) {
                filterEmployees();
            }
        }

        function selectEmployee(id, name) {
            document.getElementById('employeeSearchInput').value = name;
            document.getElementById('selectedEmployeeId').value = id;
            let suggestionsDiv = document.getElementById('employeeSuggestions');
            if (suggestionsDiv) suggestionsDiv.style.display = 'none';
        }

        // البحث في موظفي القسم (لمدير القسم)
        function filterDeptEmployees() {
            let input = document.getElementById('deptEmployeeSearchInput');
            if (!input) return;
            let filter = input.value.toLowerCase();
            let suggestionsDiv = document.getElementById('deptEmployeeSuggestions');

            if (filter.length === 0) {
                if (suggestionsDiv) suggestionsDiv.style.display = 'none';
                return;
            }

            let filtered = departmentEmployees.filter(emp =>
                emp.name.toLowerCase().includes(filter) ||
                emp.department.toLowerCase().includes(filter)
            );

            if (filtered.length === 0) {
                if (suggestionsDiv) {
                    suggestionsDiv.innerHTML =
                        '<div style="padding: 12px; text-align: center; color: #999;">لا توجد نتائج</div>';
                    suggestionsDiv.style.display = 'block';
                }
                return;
            }

            let html = '';
            filtered.forEach(emp => {
                let highlightedName = emp.name.replace(new RegExp(`(${filter})`, 'gi'),
                    '<strong style="color: #6f42c1;">$1</strong>');
                html += `
                <div onclick="selectDeptEmployee(${emp.id}, '${emp.name.replace(/'/g, "\\'")}')"
                     style="padding: 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background 0.2s;"
                     onmouseover="this.style.backgroundColor='#f8f9fa'"
                     onmouseout="this.style.backgroundColor='white'">
                    <div style="font-weight: 500;">${highlightedName}</div>
                    <div style="font-size: 12px; color: #6c757d;">${emp.department}</div>
                </div>
            `;
            });

            if (suggestionsDiv) {
                suggestionsDiv.innerHTML = html;
                suggestionsDiv.style.display = 'block';
            }
        }

        function showDeptSuggestions() {
            let input = document.getElementById('deptEmployeeSearchInput');
            if (input && input.value.length > 0) {
                filterDeptEmployees();
            }
        }

        function selectDeptEmployee(id, name) {
            document.getElementById('deptEmployeeSearchInput').value = name;
            document.getElementById('selectedDeptEmployeeId').value = id;
            let suggestionsDiv = document.getElementById('deptEmployeeSuggestions');
            if (suggestionsDiv) suggestionsDiv.style.display = 'none';
        }

        // إغلاق الاقتراحات عند الضغط خارج الحقل
        document.addEventListener('click', function(event) {
            let suggestionsDiv = document.getElementById('employeeSuggestions');
            let input = document.getElementById('employeeSearchInput');
            if (input && suggestionsDiv && event.target !== input && !suggestionsDiv.contains(event.target)) {
                suggestionsDiv.style.display = 'none';
            }

            let deptSuggestionsDiv = document.getElementById('deptEmployeeSuggestions');
            let deptInput = document.getElementById('deptEmployeeSearchInput');
            if (deptInput && deptSuggestionsDiv && event.target !== deptInput && !deptSuggestionsDiv.contains(event
                    .target)) {
                deptSuggestionsDiv.style.display = 'none';
            }
        });

        // مودال الإعلان العام
        function openGeneralNotificationModal() {
            let modal = document.getElementById('sendGeneralNotification');
            if (modal) modal.style.display = 'flex';
        }

        function closeGeneralNotificationModal() {
            let modal = document.getElementById('sendGeneralNotification');
            if (modal) modal.style.display = 'none';
        }

        // مودال الإشعار الخاص (مدير النظام)
        function openSpecificNotificationModal() {
            let input = document.getElementById('employeeSearchInput');
            let selectedId = document.getElementById('selectedEmployeeId');
            let suggestions = document.getElementById('employeeSuggestions');
            let form = document.getElementById('specificNotificationForm');

            if (input) input.value = '';
            if (selectedId) selectedId.value = '';
            if (suggestions) suggestions.style.display = 'none';
            if (form) form.reset();

            let modal = document.getElementById('sendSpecificNotification');
            if (modal) modal.style.display = 'flex';
        }

        function closeSpecificNotificationModal() {
            let modal = document.getElementById('sendSpecificNotification');
            if (modal) modal.style.display = 'none';
        }

        // مودال إعلان القسم (مدير القسم)
        function openDepartmentNotificationModal() {
            let modal = document.getElementById('sendDepartmentNotification');
            if (modal) modal.style.display = 'flex';
        }

        function closeDepartmentNotificationModal() {
            let modal = document.getElementById('sendDepartmentNotification');
            if (modal) modal.style.display = 'none';
        }

        // مودال إشعار خاص للقسم (مدير القسم)
        function openDepartmentSpecificModal() {
            let input = document.getElementById('deptEmployeeSearchInput');
            let selectedId = document.getElementById('selectedDeptEmployeeId');
            let suggestions = document.getElementById('deptEmployeeSuggestions');
            let form = document.getElementById('departmentSpecificForm');

            if (input) input.value = '';
            if (selectedId) selectedId.value = '';
            if (suggestions) suggestions.style.display = 'none';
            if (form) form.reset();

            let modal = document.getElementById('sendDepartmentSpecificModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeDepartmentSpecificModal() {
            let modal = document.getElementById('sendDepartmentSpecificModal');
            if (modal) modal.style.display = 'none';
        }

        // التحقق قبل إرسال النموذج
        document.getElementById('specificNotificationForm')?.addEventListener('submit', function(e) {
            let employeeId = document.getElementById('selectedEmployeeId')?.value;
            if (!employeeId) {
                e.preventDefault();
                alert('الرجاء اختيار موظف من القائمة');
                return false;
            }
        });

        document.getElementById('departmentSpecificForm')?.addEventListener('submit', function(e) {
            let employeeId = document.getElementById('selectedDeptEmployeeId')?.value;
            if (!employeeId) {
                e.preventDefault();
                alert('الرجاء اختيار موظف من القائمة');
                return false;
            }
        });

        // إغلاق المودال عند الضغط خارج المحتوى
        window.onclick = function(event) {
            let generalModal = document.getElementById('sendGeneralNotification');
            let specificModal = document.getElementById('sendSpecificNotification');
            let deptModal = document.getElementById('sendDepartmentNotification');
            let deptSpecificModal = document.getElementById('sendDepartmentSpecificModal');

            if (event.target == generalModal) generalModal.style.display = 'none';
            if (event.target == specificModal) specificModal.style.display = 'none';
            if (event.target == deptModal) deptModal.style.display = 'none';
            if (event.target == deptSpecificModal) deptSpecificModal.style.display = 'none';
        }
    </script>

@endsection
