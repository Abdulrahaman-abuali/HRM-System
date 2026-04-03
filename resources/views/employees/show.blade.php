@extends('layout.app')

@section('title')
    تفاصيل الموظف
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
                            <h2 class="employee-name">{{ $employee->first_name }} {{ $employee->last_name }}</h2>
                            <p class="employee-position">
                                {{ $employee->jobTitle->name }} - {{ $employee->department->name }}
                            </p>
                            <p class="employee-id">
                                رقم الموظف: <span class="employee-id-value">{{ $employee->id }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="employee-header-meta">
                        @if ($employee->status == 1)
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
                                <span class="info-value">{{ $employee->first_name }} {{ $employee->last_name }}</span>
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
                                <span class="info-value">{{ $employee->address }}</span>
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
                                <span class="info-value">{{ $employee->manager->first_name ?? 'لا يوجد مدير مباشر' }}
                                    {{ $employee->manager->last_name ?? '' }}</span>
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
                            @php
                                $basic = $employee->basic_salary ?? 0;
                                $housingPercent = $employee->housing_percentage ?? 10;
                                $transportPercent = $employee->transport_percentage ?? 5;

                                $housingAmount = $basic * ($housingPercent / 100);
                                $transportAmount = $basic * ($transportPercent / 100);
                                $totalAllowances = $housingAmount + $transportAmount;

                                $socialInsurance = $basic * 0.06;
                                // ملاحظة: الضريبة تحتاج دالة، سنستخدم قيمة تقريبية هنا
                                $incomeTax = 0;
                                if ($basic * 12 > 120000) {
                                    $incomeTax = $basic * 12 > 240000 ? $basic * 0.13 : $basic * 0.1;
                                }
                                $totalDeductions = $socialInsurance + $incomeTax;

                                $netSalary = $basic + $totalAllowances - $totalDeductions;
                            @endphp
                            <div class="info-row">
                                <span class="info-label">الراتب الأساسي:</span>
                                <span class="info-value">{{ number_format($basic, 2) }} ريال</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">بدل السكن ({{ $housingPercent }}%):</span>
                                <span class="info-value">{{ number_format($housingAmount, 2) }} ريال</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">بدل المواصلات ({{ $transportPercent }}%):</span>
                                <span class="info-value">{{ number_format($transportAmount, 2) }} ريال</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">إجمالي البدلات:</span>
                                <span class="info-value">{{ number_format($totalAllowances, 2) }} ريال</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">التأمينات الاجتماعية (6%):</span>
                                <span class="info-value">{{ number_format($socialInsurance, 2) }} ريال</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">ضريبة الدخل:</span>
                                <span class="info-value">{{ number_format($incomeTax, 2) }} ريال</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">إجمالي الخصومات:</span>
                                <span class="info-value">{{ number_format($totalDeductions, 2) }} ريال</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">صافي الراتب:</span>
                                <span class="info-value info-value-strong">{{ number_format($netSalary, 2) }} ريال</span>
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
                            <a href="{{ route('employees.evaluation', $employee->id) }}" class="btn btn-outline">
                                التقييم بالذكاء الاصطناعي (AI)
                            </a>
                            <button type="button" class="btn btn-danger">
                                إيقاف الموظف
                            </button>
                        </footer>

                    </article>
                </section>

    </main>
@endsection
