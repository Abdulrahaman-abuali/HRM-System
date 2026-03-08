@extends('layout.app')

@section('title')
تفاصيل الموظق
@endsection

@section('content')
 <main class="main-content">

                <section class="section">
                    <article class="card employee-profile">
                        <!-- هيدر الملف الشخصي -->
                        <header class="employee-header">
                            <div class="employee-header-main">
                                <div class="employee-avatar">م</div>
                                <div class="employee-basic-info">
                                    <h2 class="employee-name">{{ $employee->first_name}} {{ $employee->last_name }}</h2>
                                    <p class="employee-position">
                                        {{ $employee->jobTitle->name }} - {{ $employee->department->name }}
                                    </p>
                                    <p class="employee-id">
                                        رقم الموظف: <span class="employee-id-value">{{ $employee->id }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="employee-header-meta">
                                @if ($employee->status==1)
                                            <div class="employee-status">
                                    <span class="badge badge-success">
                                        نشط
                                    </span>
                                </div>

                                        @else
                                        <div class="employee-status">
                                    <span class="badge badge-danger">
                                        موقف
                                    </span>
                                </div>

                                        @endif

                                <div class="employee-dates">
                                    <span class="employee-date-label">تاريخ التعيين:</span>
                                    <span class="employee-date-value">{{ $employee->hire_date }}</span>
                                </div>
                            </div>
                        </header>

                        <!-- شبكة المعلومات -->
                        <div class="employee-info-grid">

                            <!-- معلومات شخصية -->
                            <section class="info-section">
                                <header class="info-section-header">
                                    <h3 class="info-section-title">المعلومات الشخصية</h3>
                                </header>
                                <div class="info-section-body">
                                    <div class="info-row">
                                        <span class="info-label">الاسم الكامل:</span>
                                        <span class="info-value">{{ $employee->first_name}} {{ $employee->last_name }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">البريد الإلكتروني:</span>
                                        <span class="info-value">{{ $employee->email }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">رقم الجوال:</span>
                                        <span class="info-value">+967 {{ $employee->phone }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">العنوان:</span>
                                        <span class="info-value">صنعاء - حي الجامعة - شارع النصر</span>
                                    </div>
                                </div>
                            </section>

                            <!-- معلومات وظيفية -->
                            <section class="info-section">
                                <header class="info-section-header">
                                    <h3 class="info-section-title">المعلومات الوظيفية</h3>
                                </header>
                                <div class="info-section-body">
                                    <div class="info-row">
                                        <span class="info-label">القسم:</span>
                                        <span class="info-value">{{ $employee->department->name }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">المسمى الوظيفي:</span>
                                        <span class="info-value">{{ $employee->jobTitle->name }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">مدير مباشر:</span>
                                        <span class="info-value">{{ $employee->manager->first_name ?? 'لا يوجد مدير مباشر' }} {{ $employee->manager->last_name ?? '' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">نوع التوظيف:</span>
                                        <span class="info-value">{{ $employee->employment_type }}</span>
                                    </div>
                                </div>
                            </section>

                            <!-- معلومات مالية -->
                            <section class="info-section">
                                <header class="info-section-header">
                                    <h3 class="info-section-title">المعلومات المالية</h3>
                                </header>
                                <div class="info-section-body">
                                    <div class="info-row">
                                        <span class="info-label">الراتب الأساسي:</span>
                                        <span class="info-value">{{ $employee->salaries->last()->basic_salary ?? 0 }} ريال</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">البدلات:</span>
                                        <span class="info-value">50,000 ريال</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">الخصومات الدورية:</span>
                                        <span class="info-value">10,000 ريال</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">صافي الراتب:</span>
                                        <span class="info-value info-value-strong">440,000 ريال</span>
                                    </div>
                                </div>
                            </section>

                            <!-- إحصائيات الحضور -->
                            <section class="info-section">
                                <header class="info-section-header">
                                    <h3 class="info-section-title">إحصائيات الحضور</h3>
                                </header>
                                <div class="info-section-body">
                                    <div class="info-row">
                                        <span class="info-label">أيام الحضور هذا الشهر:</span>
                                        <span class="info-value">20 يوم</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">أيام الغياب:</span>
                                        <span class="info-value">1 يوم</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">مرات التأخر:</span>
                                        <span class="info-value">3 مرات</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">التقييم العام للانضباط:</span>
                                        <span class="info-value badge badge-info">جيد جداً</span>
                                    </div>
                                </div>
                            </section>

                        </div>

                        <!-- أزرار العمليات -->
                        <footer class="employee-actions">
                            <button type="button" class="btn btn-primary">
                                تعديل المعلومات
                            </button>
                            <button type="button" class="btn btn-outline">
                                عرض سجل الحضور
                            </button>
                            <button type="button" class="btn btn-outline">
                                عرض طلبات الإجازة
                            </button>
                            <button type="button" class="btn btn-outline">
                                عرض تقييمات الأداء
                            </button>
                            <button type="button" class="btn btn-danger">
                                إيقاف الموظف
                            </button>
                        </footer>

                    </article>
                </section>

            </main>
@endsection
