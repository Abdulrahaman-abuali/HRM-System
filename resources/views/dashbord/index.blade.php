@extends('layout.app')

@section('title')
 لوحة التحكم - نظام إدارة الموارد البشرية
@endsection

@section('content')
<main class="main-content">

                <!-- الإحصائيات -->
                <section class="section section-stats">
                    <div class="section-header">
                        <h2 class="section-title">إحصائيات سريعة</h2>
                    </div>
                    <div class="grid grid-4 stats-grid">
                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">إجمالي الموظفين</h3>
                                <span class="stat-icon stat-icon-primary">👥</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">42</p>
                                <p class="stat-caption">موظف نشط في الشركة</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">الحضور اليوم</h3>
                                <span class="stat-icon stat-icon-success">🕒</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">38</p>
                                <p class="stat-caption">موظف مسجل حضورهم اليوم</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">طلبات الإجازة المعلقة</h3>
                                <span class="stat-icon stat-icon-warning">📅</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">5</p>
                                <p class="stat-caption">في انتظار اعتماد الموارد البشرية</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">إشعارات النظام</h3>
                                <span class="stat-icon stat-icon-info">🔔</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">9</p>
                                <p class="stat-caption">تنبيهات تحتاج إلى مراجعة</p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- الحضور اليوم + آخر النشاطات -->
                <section class="section section-grid">
                    <div class="grid grid-2">
                        <!-- الحضور اليوم -->
                        <article class="card">
                            <header class="card-header">
                                <div class="card-header-main">
                                    <h2 class="card-title">الحضور اليوم</h2>
                                    <p class="card-subtitle">سجل الحضور والانصراف لليوم الحالي</p>
                                </div>
                                <div class="card-header-actions">
                                    <button type="button" class="btn btn-sm btn-outline">عرض الكل</button>
                                </div>
                            </header>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>الموظف</th>
                                                <th>القسم</th>
                                                <th>وقت الدخول</th>
                                                <th>وقت الخروج</th>
                                                <th>الحالة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>محمد صالح</td>
                                                <td>تطوير البرمجيات</td>
                                                <td>08:10 ص</td>
                                                <td>—</td>
                                                <td><span class="badge badge-success">حاضر</span></td>
                                            </tr>
                                            <tr>
                                                <td>سارة إبراهيم</td>
                                                <td>تحليل النظم</td>
                                                <td>08:25 ص</td>
                                                <td>—</td>
                                                <td><span class="badge badge-success">حاضر</span></td>
                                            </tr>
                                            <tr>
                                                <td>خالد يوسف</td>
                                                <td>الدعم الفني</td>
                                                <td>—</td>
                                                <td>—</td>
                                                <td><span class="badge badge-danger">غائب</span></td>
                                            </tr>
                                            <tr>
                                                <td>ليث عبد الله</td>
                                                <td>تطوير البرمجيات</td>
                                                <td>09:05 ص</td>
                                                <td>—</td>
                                                <td><span class="badge badge-warning">متأخر</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </article>

                        <!-- آخر النشاطات -->
                        <article class="card">
                            <header class="card-header">
                                <div class="card-header-main">
                                    <h2 class="card-title">آخر نشاطات النظام</h2>
                                    <p class="card-subtitle">سجل العمليات الأخيرة في النظام</p>
                                </div>
                                <div class="card-header-actions">
                                    <button type="button" class="btn btn-sm btn-outline">عرض السجل الكامل</button>
                                </div>
                            </header>
                            <div class="card-body">
                                <ul class="activity-list">
                                    <li class="activity-item">
                                        <div class="activity-icon activity-icon-success">✓</div>
                                        <div class="activity-content">
                                            <p class="activity-text">
                                                تمت إضافة موظف جديد: <span class="activity-strong">أحمد سعيد</span> في قسم تطوير البرمجيات.
                                            </p>
                                            <span class="activity-meta">منذ 10 دقائق</span>
                                        </div>
                                    </li>
                                    <li class="activity-item">
                                        <div class="activity-icon activity-icon-info">ℹ</div>
                                        <div class="activity-content">
                                            <p class="activity-text">
                                                اعتماد طلب إجازة لموظف: <span class="activity-strong">سارة إبراهيم</span> لمدة 3 أيام.
                                            </p>
                                            <span class="activity-meta">منذ 30 دقيقة</span>
                                        </div>
                                    </li>
                                    <li class="activity-item">
                                        <div class="activity-icon activity-icon-warning">!</div>
                                        <div class="activity-content">
                                            <p class="activity-text">
                                                وجود <span class="activity-strong">5</span> طلبات إجازة جديدة بانتظار الموافقة.
                                            </p>
                                            <span class="activity-meta">منذ ساعة واحدة</span>
                                        </div>
                                    </li>
                                    <li class="activity-item">
                                        <div class="activity-icon activity-icon-primary">★</div>
                                        <div class="activity-content">
                                            <p class="activity-text">
                                                تم إنشاء تقرير الحضور الشهري لقسم <span class="activity-strong">الدعم الفني</span>.
                                            </p>
                                            <span class="activity-meta">منذ ساعتين</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- الإجازات / الرواتب / مهام الموارد البشرية -->
                <section class="section section-grid">
                    <div class="grid grid-3">
                        <!-- أحدث طلبات الإجازة -->
                        <article class="card">
                            <header class="card-header">
                                <div class="card-header-main">
                                    <h2 class="card-title">أحدث طلبات الإجازة</h2>
                                    <p class="card-subtitle">متابعة حالة طلبات الإجازة الجديدة</p>
                                </div>
                                <div class="card-header-actions">
                                    <button type="button" class="btn btn-sm btn-outline">كل الطلبات</button>
                                </div>
                            </header>
                            <div class="card-body">
                                <ul class="list list-leaves">
                                    <li class="list-item">
                                        <div class="list-main">
                                            <span class="list-title">سارة إبراهيم</span>
                                            <span class="badge badge-warning">قيد المراجعة</span>
                                        </div>
                                        <div class="list-meta">
                                            <span class="list-text">إجازة سنوية • 3 أيام</span>
                                            <span class="list-date">من 05 إلى 07 ديسمبر</span>
                                        </div>
                                    </li>
                                    <li class="list-item">
                                        <div class="list-main">
                                            <span class="list-title">محمد صالح</span>
                                            <span class="badge badge-success">معتمدة</span>
                                        </div>
                                        <div class="list-meta">
                                            <span class="list-text">إجازة مرضية • يوم واحد</span>
                                            <span class="list-date">04 ديسمبر 2025</span>
                                        </div>
                                    </li>
                                    <li class="list-item">
                                        <div class="list-main">
                                            <span class="list-title">ليث عبد الله</span>
                                            <span class="badge badge-danger">مرفوضة</span>
                                        </div>
                                        <div class="list-meta">
                                            <span class="list-text">إجازة طارئة • يومان</span>
                                            <span class="list-date">01 ديسمبر 2025</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </article>

                        <!-- تنبيهات الرواتب -->
                        <article class="card">
                            <header class="card-header">
                                <div class="card-header-main">
                                    <h2 class="card-title">تنبيهات الرواتب</h2>
                                    <p class="card-subtitle">متابعة حالة كشوف الرواتب الحالية</p>
                                </div>
                                <div class="card-header-actions">
                                    <button type="button" class="btn btn-sm btn-outline">إدارة الرواتب</button>
                                </div>
                            </header>
                            <div class="card-body">
                                <ul class="list list-payroll">
                                    <li class="list-item">
                                        <div class="list-main">
                                            <span class="list-title">رواتب شهر نوفمبر 2025</span>
                                            <span class="badge badge-success">مدفوعة</span>
                                        </div>
                                        <div class="list-meta">
                                            <span class="list-text">تم دفع رواتب جميع الموظفين</span>
                                            <span class="list-date">30 نوفمبر 2025</span>
                                        </div>
                                    </li>
                                    <li class="list-item">
                                        <div class="list-main">
                                            <span class="list-title">رواتب شهر ديسمبر 2025</span>
                                            <span class="badge badge-warning">قيد التجهيز</span>
                                        </div>
                                        <div class="list-meta">
                                            <span class="list-text">لم يتم اعتماد الكشوف النهائية بعد</span>
                                            <span class="list-date">آخر تحديث: منذ 3 ساعات</span>
                                        </div>
                                    </li>
                                    <li class="list-item">
                                        <div class="list-main">
                                            <span class="list-title">مراجعة البدلات والخصومات</span>
                                            <span class="badge badge-info">مطلوب مراجعة</span>
                                        </div>
                                        <div class="list-meta">
                                            <span class="list-text">بعض الأقسام تحتوي على خصومات غير معتمدة</span>
                                            <span class="list-date">منذ يوم واحد</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </article>

                        <!-- مهام مسؤول الموارد البشرية -->
                        <article class="card">
                            <header class="card-header">
                                <div class="card-header-main">
                                    <h2 class="card-title">مهام الموارد البشرية اليوم</h2>
                                    <p class="card-subtitle">قائمة بالمهام التي يُفضل إنجازها اليوم</p>
                                </div>
                                <div class="card-header-actions">
                                    <button type="button" class="btn btn-sm btn-outline">إدارة المهام</button>
                                </div>
                            </header>
                            <div class="card-body">
                                <ul class="task-list">
                                    <li class="task-item">
                                        <label class="task-checkbox">
                                            <input type="checkbox" class="task-input">
                                            <span class="task-label">
                                                مراجعة طلبات الإجازة المعلقة (5 طلبات)
                                            </span>
                                        </label>
                                        <span class="task-badge task-badge-high">أولوية عالية</span>
                                    </li>
                                    <li class="task-item">
                                        <label class="task-checkbox">
                                            <input type="checkbox" class="task-input">
                                            <span class="task-label">
                                                تحديث بيانات الموظفين الجدد في النظام
                                            </span>
                                        </label>
                                        <span class="task-badge task-badge-medium">أولوية متوسطة</span>
                                    </li>
                                    <li class="task-item">
                                        <label class="task-checkbox">
                                            <input type="checkbox" class="task-input">
                                            <span class="task-label">
                                                إعداد تقرير الحضور الأسبوعي للإدارة
                                            </span>
                                        </label>
                                        <span class="task-badge task-badge-low">أولوية منخفضة</span>
                                    </li>
                                    <li class="task-item">
                                        <label class="task-checkbox">
                                            <input type="checkbox" class="task-input">
                                            <span class="task-label">
                                                مراجعة تقييمات الأداء المبدئية لفريق التطوير
                                            </span>
                                        </label>
                                        <span class="task-badge task-badge-medium">أولوية متوسطة</span>
                                    </li>
                                </ul>
                            </div>
                        </article>
                    </div>
                </section>

            </main>
@endsection