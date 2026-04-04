@extends('layout.app')

@section('title')
    تقييم الأداء
@endsection

@section('content')
    <div class="main">
            <!-- الهيدر العلوي -->
            <header class="main-header">
                <div class="header-left">
                    <h1 class="page-title">تقييم الأداء (بالذكاء الاصطناعي)</h1>
                    <p class="page-subtitle">
                        متابعة تقييم أداء الموظفين وتحليل إنتاجيتهم ديناميكياً
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
                        <h2 class="section-title">ملخص تقييمات الأداء الحالية للصفحة</h2>
                    </div>
                    <div class="grid grid-4">
                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">تقييم ممتاز</h3>
                                <span class="stat-icon stat-icon-success">★</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">{{ $stats['excellent'] }}</p>
                                <p class="stat-caption">موظفون بتقييم ممتاز</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">تقييم جيد جداً</h3>
                                <span class="stat-icon stat-icon-primary">☆</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">{{ $stats['very_good'] }}</p>
                                <p class="stat-caption">موظفون بتقييم جيد جداً</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">تقييم مقبول</h3>
                                <span class="stat-icon stat-icon-warning">≋</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">{{ $stats['acceptable'] }}</p>
                                <p class="stat-caption">موظفون بتقييم مقبول</p>
                            </div>
                        </article>

                        <article class="card stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-title">تقييم ضعيف</h3>
                                <span class="stat-icon stat-icon-danger">!</span>
                            </div>
                            <div class="stat-card-body">
                                <p class="stat-value">{{ $stats['weak'] }}</p>
                                <p class="stat-caption">يحتاجون إلى خطة تطوير</p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- الفلاتر والإجراءات -->
                <section class="section">
                    <form method="GET" action="{{ route('performance') }}">
                        <div class="performance-header">
                            <div class="grid grid-4">
                                <div class="form-group">
                                    <label class="form-label" for="filterDepartment">القسم</label>
                                    <select id="filterDepartment" name="department" class="form-control">
                                        <option value="">كل الأقسام</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ $departmentFilter == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="filterRating">مستوى الأداء</label>
                                    <select id="filterRating" name="rating" class="form-control">
                                        <option value="">كل المستويات</option>
                                        <option value="excellent" {{ $ratingFilter == 'excellent' ? 'selected' : '' }}>ممتاز</option>
                                        <option value="very-good" {{ $ratingFilter == 'very-good' ? 'selected' : '' }}>جيد جداً</option>
                                        <option value="acceptable" {{ $ratingFilter == 'acceptable' ? 'selected' : '' }}>مقبول</option>
                                        <option value="weak" {{ $ratingFilter == 'weak' ? 'selected' : '' }}>ضعيف</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="filterName">بحث بالاسم</label>
                                    <input type="text" id="filterName" name="name" value="{{ $nameFilter }}" class="form-control" placeholder="اكتب اسم الموظف...">
                                </div>
                                <div class="form-group d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100" style="margin-top: 25px;">
                                        بحث وتصفية
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- جدول تقييم الأداء -->
                <section class="section">
                    <article class="card">
                        <header class="card-header">
                            <div class="card-header-main">
                                <h2 class="card-title">سجل تقييمات الأداء بالذكاء الاصطناعي</h2>
                                <p class="card-subtitle">
                                    معتمد على خوارزميات التنبؤ بالإنتاجية والتوصية بالتحسين
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
                                            <th>مستوى الأداء</th>
                                            <th>مستوى المهارة (AI)</th>
                                            <th>نسبة الإنجاز (AI)</th>
                                            <th>المقيّم</th>
                                            <th>إجراءات الإدارة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($evaluatedEmployees as $emp)
                                        <tr>
                                            <td>{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                            <td>{{ $emp->department->name ?? 'غير محدد' }}</td>
                                            <td>
                                                <span class="badge badge-performance badge-performance-{{ $emp->ai_evaluation['rating_class'] }}">
                                                    {{ $emp->ai_evaluation['status_label'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span style="font-weight: bold; color: #4f46e5;">{{ $emp->ai_evaluation['skill_level'] }}</span>
                                            </td>
                                            <td style="min-width: 200px;">
                                                <div class="progress" style="margin-bottom: 2px;">
                                                    <div class="progress-bar">
                                                        <div class="progress-fill" style="width: {{ $emp->ai_evaluation['productivity_score'] }}%; background-color: {{ $emp->ai_evaluation['productivity_score'] < 50 ? '#ef4444' : ($emp->ai_evaluation['productivity_score'] < 70 ? '#f59e0b' : '#10b981') }};"></div>
                                                    </div>
                                                    <span class="progress-value">{{ $emp->ai_evaluation['productivity_score'] }}%</span>
                                                </div>
                                            </td>
                                            <td>🤖 النظام الذكي</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="{{ route('employees.evaluation', $emp->id) }}" class="btn btn-sm btn-outline" style="text-decoration: none;">
                                                        تقرير التقييم الشامل
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center" style="padding: 20px;">لا يوجد بيانات للعرض. تأكد من إعدادات الفلترة.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- الترقيم -->
                        @if($employees->hasPages())
                        <div class="card-footer" style="padding: 15px; border-top: 1px solid #eee;">
                            {{ $employees->withQueryString()->links() }}
                        </div>
                        @endif
                    </article>
                </section>

            </main>
        </div>
@endsection
