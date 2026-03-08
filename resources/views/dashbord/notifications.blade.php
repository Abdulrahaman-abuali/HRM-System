@extends('layout.app')

@section('title')
    الاشعارات
@endsection

@section('content')
    <div class="main">
            <!-- الهيدر العلوي -->
            <header class="main-header">
                <div class="header-left">
                    <h1 class="page-title">الإشعارات والتنبيهات</h1>
                    <p class="page-subtitle">
                        متابعة جميع الإشعارات الصادرة من وحدات نظام الموارد البشرية
                    </p>
                </div>
                <div class="header-right">
                    <div class="header-user">
                        <div class="header-user-avatar">م</div>
                        <div class="header-user-info">
                            <span class="header-user-name">مدير النظام</span>
                            <span class="header-user-role">إدارة الموارد البشرية</span>
                        </div>
                        <span class="header-notifications-badge">9</span>
                    </div>
                </div>
            </header>

            <!-- محتوى الصفحة -->
            <main class="main-content">

                <!-- ملخص الإشعارات -->
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
                                <p class="stat-value">6</p>
                                <p class="stat-caption">إشعارات تحتاج إلى مراجعة</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">مهمة</h3>
                                <span class="stat-icon stat-icon-danger">!</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">3</p>
                                <p class="stat-caption">إنذارات أو تنبيهات عالية الأهمية</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">إشعارات النظام</h3>
                                <span class="stat-icon stat-icon-info">ℹ</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">5</p>
                                <p class="stat-caption">رسائل صادرة آلياً من النظام</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">إجمالي الإشعارات</h3>
                                <span class="stat-icon stat-icon-primary">🔔</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">24</p>
                                <p class="stat-caption">خلال آخر 7 أيام</p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- الفلاتر والإجراءات -->
                <section class="section">
                    <div class="notifications-header">
                        <div class="grid grid-4">
                            <div class="form-group">
                                <label class="form-label" for="filterNotificationType">نوع الإشعار</label>
                                <select id="filterNotificationType" class="form-control">
                                    <option value="">كل الأنواع</option>
                                    <option value="important">مهم</option>
                                    <option value="system">نظام</option>
                                    <option value="reminder">تذكير</option>
                                    <option value="info">معلومات</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="filterNotificationStatus">الحالة</label>
                                <select id="filterNotificationStatus" class="form-control">
                                    <option value="">الكل</option>
                                    <option value="unread">غير مقروء</option>
                                    <option value="read">مقروء</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="filterFromDate">من تاريخ</label>
                                <input
                                    type="date"
                                    id="filterFromDate"
                                    class="form-control"
                                >
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="filterToDate">إلى تاريخ</label>
                                <input
                                    type="date"
                                    id="filterToDate"
                                    class="form-control"
                                >
                            </div>
                        </div>

                        <div class="notifications-actions">
                            <button type="button" class="btn btn-primary">
                                وضع الجميع كمقروء
                            </button>
                            <button type="button" class="btn btn-outline">
                                حذف جميع الإشعارات
                            </button>
                            <button type="button" class="btn btn-outline">
                                إرسال إشعار جديد
                            </button>
                            <button type="button" class="btn btn-outline">
                                إعدادات الإشعارات
                            </button>
                        </div>
                    </div>
                </section>

                <!-- قائمة الإشعارات -->
                <section class="section">
                    <article class="card">
                        <header class="card-header">
                            <div class="card-header-main">
                                <h2 class="card-title">قائمة الإشعارات</h2>
                                <p class="card-subtitle">
                                    عرض جميع الإشعارات حسب أحدث وقت إرسال
                                </p>
                            </div>
                        </header>
                        <div class="card-body">
                            <ul class="notifications-list">

                                <!-- إشعار مهم غير مقروء -->
                                <li class="notification-item notification-unread">
                                    <div class="notification-main">
                                        <div class="notification-icon notification-icon-important">!</div>
                                        <div class="notification-content">
                                            <div class="notification-header-row">
                                                <span class="notification-title">
                                                    تأخير في تسجيل حضور بعض الموظفين
                                                </span>
                                                <span class="badge badge-notification badge-notification-important">
                                                    مهم
                                                </span>
                                            </div>
                                            <p class="notification-text">
                                                تم رصد 3 حالات تأخير في الحضور اليوم في قسم الدعم الفني، يرجى المراجعة واتخاذ القرار المناسب.
                                            </p>
                                            <div class="notification-meta">
                                                <span class="notification-time">منذ 5 دقائق</span>
                                                <span class="notification-source">من: نظام الحضور والانصراف</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-actions">
                                        <button type="button" class="btn btn-sm btn-primary">
                                            عرض
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline">
                                            وضع كمقروء
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost">
                                            حذف
                                        </button>
                                    </div>
                                </li>

                                <!-- إشعار نظام غير مقروء -->
                                <li class="notification-item notification-unread">
                                    <div class="notification-main">
                                        <div class="notification-icon notification-icon-system">ℹ</div>
                                        <div class="notification-content">
                                            <div class="notification-header-row">
                                                <span class="notification-title">
                                                    إتمام معالجة كشوف رواتب شهر نوفمبر 2025
                                                </span>
                                                <span class="badge badge-notification badge-notification-system">
                                                    نظام
                                                </span>
                                            </div>
                                            <p class="notification-text">
                                                تم الانتهاء من إنشاء كشوف رواتب شهر نوفمبر 2025 لجميع الأقسام، يرجى مراجعتها قبل الاعتماد النهائي.
                                            </p>
                                            <div class="notification-meta">
                                                <span class="notification-time">منذ 20 دقيقة</span>
                                                <span class="notification-source">من: نظام الرواتب</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-actions">
                                        <button type="button" class="btn btn-sm btn-primary">
                                            عرض
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline">
                                            وضع كمقروء
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost">
                                            حذف
                                        </button>
                                    </div>
                                </li>

                                <!-- إشعار تذكير غير مقروء -->
                                <li class="notification-item notification-unread">
                                    <div class="notification-main">
                                        <div class="notification-icon notification-icon-reminder">⏰</div>
                                        <div class="notification-content">
                                            <div class="notification-header-row">
                                                <span class="notification-title">
                                                    تذكير بمراجعة طلبات الإجازة المعلقة
                                                </span>
                                                <span class="badge badge-notification badge-notification-reminder">
                                                    تذكير
                                                </span>
                                            </div>
                                            <p class="notification-text">
                                                يوجد حالياً 5 طلبات إجازة في حالة "قيد الانتظار" تحتاج إلى اعتماد أو رفض.
                                            </p>
                                            <div class="notification-meta">
                                                <span class="notification-time">منذ ساعة واحدة</span>
                                                <span class="notification-source">من: نظام الإجازات</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-actions">
                                        <button type="button" class="btn btn-sm btn-primary">
                                            عرض
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline">
                                            وضع كمقروء
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost">
                                            حذف
                                        </button>
                                    </div>
                                </li>

                                <!-- إشعار معلومات مقروء -->
                                <li class="notification-item">
                                    <div class="notification-main">
                                        <div class="notification-icon notification-icon-info">i</div>
                                        <div class="notification-content">
                                            <div class="notification-header-row">
                                                <span class="notification-title">
                                                    إضافة موظف جديد إلى قسم تطوير البرمجيات
                                                </span>
                                                <span class="badge badge-notification badge-notification-info">
                                                    معلومات
                                                </span>
                                            </div>
                                            <p class="notification-text">
                                                تم إضافة الموظف "أحمد سعيد" إلى قسم تطوير البرمجيات بمسمى "مطور برمجيات".
                                            </p>
                                            <div class="notification-meta">
                                                <span class="notification-time">منذ 3 ساعات</span>
                                                <span class="notification-source">من: إدارة الموظفين</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-actions">
                                        <button type="button" class="btn btn-sm btn-outline">
                                            عرض
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost">
                                            حذف
                                        </button>
                                    </div>
                                </li>

                                <!-- إشعار مهم مقروء -->
                                <li class="notification-item">
                                    <div class="notification-main">
                                        <div class="notification-icon notification-icon-important">!</div>
                                        <div class="notification-content">
                                            <div class="notification-header-row">
                                                <span class="notification-title">
                                                    تجاوز حد الغياب المسموح لأحد الموظفين
                                                </span>
                                                <span class="badge badge-notification badge-notification-important">
                                                    مهم
                                                </span>
                                            </div>
                                            <p class="notification-text">
                                                تجاوز الموظف "ليث عبد الله" عدد أيام الغياب المسموح بها خلال الشهر الحالي.
                                            </p>
                                            <div class="notification-meta">
                                                <span class="notification-time">منذ يوم واحد</span>
                                                <span class="notification-source">من: نظام الحضور والانصراف</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-actions">
                                        <button type="button" class="btn btn-sm btn-outline">
                                            عرض
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost">
                                            حذف
                                        </button>
                                    </div>
                                </li>

                                <!-- إشعار نظام قديم -->
                                <li class="notification-item">
                                    <div class="notification-main">
                                        <div class="notification-icon notification-icon-system">ℹ</div>
                                        <div class="notification-content">
                                            <div class="notification-header-row">
                                                <span class="notification-title">
                                                    تحديث في إعدادات تقييم الأداء
                                                </span>
                                                <span class="badge badge-notification badge-notification-system">
                                                    نظام
                                                </span>
                                            </div>
                                            <p class="notification-text">
                                                تم تعديل معايير تقييم الأداء لقسم تطوير البرمجيات وفقاً لتوجيهات الإدارة.
                                            </p>
                                            <div class="notification-meta">
                                                <span class="notification-time">منذ 3 أيام</span>
                                                <span class="notification-source">من: نظام تقييم الأداء</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-actions">
                                        <button type="button" class="btn btn-sm btn-outline">
                                            عرض
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost">
                                            حذف
                                        </button>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </article>
                </section>

            </main>
        </div>
@endsection