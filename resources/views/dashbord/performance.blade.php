@extends('layout.app')

@section('title')
    تقييم الاداء
@endsection

@section('content')
    <div class="main">
            <!-- الهيدر العلوي -->
            <header class="main-header">
                <div class="header-left">
                    <h1 class="page-title">تقييم الأداء</h1>
                    <p class="page-subtitle">
                        متابعة تقييم أداء الموظفين حسب الفترات والأقسام
                    </p>
                </div>
                <div class="header-right">
                    <div class="header-user">
                        <div class="header-user-avatar">م</div>
                        <div class="header-user-info">
                            <span class="header-user-name">مدير النظام</span>
                            <span class="header-user-role">إدارة الموارد البشرية</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- محتوى الصفحة -->
            <main class="main-content">

                <!-- ملخص التقييمات -->
                <section class="section">
                    <div class="section-header">
                        <h2 class="section-title">ملخص تقييمات الأداء</h2>
                    </div>
                    <div class="grid grid-4">
                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">تقييم ممتاز</h3>
                                <span class="stat-icon stat-icon-success">★</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">8</p>
                                <p class="stat-caption">موظفون بتقييم ممتاز</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">تقييم جيد جداً</h3>
                                <span class="stat-icon stat-icon-primary">☆</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">15</p>
                                <p class="stat-caption">موظفون بتقييم جيد جداً</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">تقييم مقبول</h3>
                                <span class="stat-icon stat-icon-warning">≋</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">6</p>
                                <p class="stat-caption">موظفون بتقييم مقبول</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">تقييم ضعيف</h3>
                                <span class="stat-icon stat-icon-danger">!</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">2</p>
                                <p class="stat-caption">يحتاجون إلى خطة تطوير</p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- الفلاتر والإجراءات -->
                <section class="section">
                    <div class="performance-header">
                        <div class="grid grid-4">
                            <div class="form-group">
                                <label class="form-label" for="filterPeriod">فترة التقييم</label>
                                <select id="filterPeriod" class="form-control">
                                    <option value="q4-2025" selected>الربع الرابع 2025</option>
                                    <option value="q3-2025">الربع الثالث 2025</option>
                                    <option value="q2-2025">الربع الثاني 2025</option>
                                    <option value="q1-2025">الربع الأول 2025</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="filterRating">مستوى الأداء</label>
                                <select id="filterRating" class="form-control">
                                    <option value="">كل المستويات</option>
                                    <option value="excellent">ممتاز</option>
                                    <option value="very-good">جيد جداً</option>
                                    <option value="acceptable">مقبول</option>
                                    <option value="weak">ضعيف</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="filterDepartment">القسم</label>
                                <select id="filterDepartment" class="form-control">
                                    <option value="">كل الأقسام</option>
                                    <option value="dev">تطوير البرمجيات</option>
                                    <option value="analysis">تحليل النظم</option>
                                    <option value="support">الدعم الفني</option>
                                    <option value="pm">إدارة المشاريع</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="filterName">بحث بالاسم</label>
                                <input
                                    type="text"
                                    id="filterName"
                                    class="form-control"
                                    placeholder="اكتب اسم الموظف..."
                                >
                            </div>
                        </div>

                        <div class="performance-actions">
                            <button type="button" class="btn btn-primary">
                                بدء تقييم جديد
                            </button>
                            <button type="button" class="btn btn-outline">
                                تقييم جماعي
                            </button>
                            <button type="button" class="btn btn-outline">
                                تصدير تقرير التقييم
                            </button>
                            <button type="button" class="btn btn-outline">
                                عرض إحصائيات الأداء
                            </button>
                        </div>
                    </div>
                </section>

                <!-- جدول تقييم الأداء -->
                <section class="section">
                    <article class="card">
                        <header class="card-header">
                            <div class="card-header-main">
                                <h2 class="card-title">سجل تقييمات الأداء</h2>
                                <p class="card-subtitle">
                                    عرض تقييم الأداء لكل موظف حسب الفترة المحددة
                                </p>
                            </div>
                        </header>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover performance-table">
                                    <thead>
                                        <tr>
                                            <th>الموظف</th>
                                            <th>القسم</th>
                                            <th>فترة التقييم</th>
                                            <th>مستوى الأداء</th>
                                            <th>نسبة الإنجاز</th>
                                            <th>آخر تحديث</th>
                                            <th>المقيّم</th>
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>محمد صالح</td>
                                            <td>تطوير البرمجيات</td>
                                            <td>الربع الرابع 2025</td>
                                            <td>
                                                <span class="badge badge-performance badge-performance-excellent">
                                                    ممتاز
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar">
                                                        <div class="progress-fill progress-90" data-progress="90"></div>
                                                    </div>
                                                    <span class="progress-value">90%</span>
                                                </div>
                                            </td>
                                            <td>01-12-2025</td>
                                            <td>هند محمد</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-ghost">
                                                        عرض
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline">
                                                        تقييم
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>سارة إبراهيم</td>
                                            <td>تحليل النظم</td>
                                            <td>الربع الرابع 2025</td>
                                            <td>
                                                <span class="badge badge-performance badge-performance-very-good">
                                                    جيد جداً
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar">
                                                        <div class="progress-fill progress-85" data-progress="85"></div>
                                                    </div>
                                                    <span class="progress-value">85%</span>
                                                </div>
                                            </td>
                                            <td>29-11-2025</td>
                                            <td>مدير الموارد البشرية</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-ghost">
                                                        عرض
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline">
                                                        تقييم
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>ليث عبد الله</td>
                                            <td>الدعم الفني</td>
                                            <td>الربع الرابع 2025</td>
                                            <td>
                                                <span class="badge badge-performance badge-performance-acceptable">
                                                    مقبول
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar">
                                                        <div class="progress-fill progress-65" data-progress="65"></div>
                                                    </div>
                                                    <span class="progress-value">65%</span>
                                                </div>
                                            </td>
                                            <td>28-11-2025</td>
                                            <td>مدير الدعم الفني</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-ghost">
                                                        عرض
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline">
                                                        تقييم
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>خالد يوسف</td>
                                            <td>الدعم الفني</td>
                                            <td>الربع الرابع 2025</td>
                                            <td>
                                                <span class="badge badge-performance badge-performance-weak">
                                                    ضعيف
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar">
                                                        <div class="progress-fill progress-45" data-progress="45"></div>
                                                    </div>
                                                    <span class="progress-value">45%</span>
                                                </div>
                                            </td>
                                            <td>27-11-2025</td>
                                            <td>مدير الموارد البشرية</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-ghost">
                                                        عرض
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline">
                                                        خطة تطوير
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>هند محمد</td>
                                            <td>إدارة المشاريع</td>
                                            <td>الربع الرابع 2025</td>
                                            <td>
                                                <span class="badge badge-performance badge-performance-very-good">
                                                    جيد جداً
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar">
                                                        <div class="progress-fill progress-88" data-progress="88"></div>
                                                    </div>
                                                    <span class="progress-value">88%</span>
                                                </div>
                                            </td>
                                            <td>30-11-2025</td>
                                            <td>المدير التنفيذي</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-ghost">
                                                        عرض
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline">
                                                        تقييم
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </article>
                </section>

            </main>
        </div>
@endsection
