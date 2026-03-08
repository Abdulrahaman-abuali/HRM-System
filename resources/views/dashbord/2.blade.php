@extends('layout.app')

@section('title')
صفحة ادارةالموظفين
@endsection

@section('content')
<main class="main-content">

                <!-- شريط البحث والإجراءات -->
                <section class="section">
                    <div class="page-actions">
                        <div class="search-bar">
                            <input
                                type="text"
                                class="form-control"
                                placeholder="بحث عن موظف..."
                            >
                            <button type="button" class="btn btn-primary">
                                بحث
                            </button>
                        </div>
                        <button type="button" class="btn btn-primary">
                            <a href="{{ route('employees.create') }}" class="btn btn-primary">
    إضافة موظف جديد
</a>

                        </button>
                    </div>
                </section>

                <!-- جدول الموظفين -->
                <section class="section">
                    <article class="card">
                        <header class="card-header">
                            <div class="card-header-main">
                                <h2 class="card-title">قائمة الموظفين</h2>
                                <p class="card-subtitle">
                                    عرض جميع الموظفين المسجلين في النظام مع الحالة الوظيفية
                                </p>
                            </div>
                        </header>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover employee-table">
                                    <thead>
                                        <tr>
                                            <th>رقم الموظف</th>
                                            <th>الاسم</th>
                                            <th>القسم</th>
                                            <th>المسمى الوظيفي</th>
                                            <th>تاريخ التعيين</th>
                                            <th>الحالة</th>
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>EMP-001</td>
                                            <td>محمد صالح</td>
                                            <td>تطوير البرمجيات</td>
                                            <td>مبرمج أول</td>
                                            <td>01-01-2022</td>
                                            <td>
                                                <span class="badge badge-success">نشط</span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-ghost">
                                                        عرض
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline">
                                                        تعديل
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger">
                                                        حذف
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>EMP-002</td>
                                            <td>سارة إبراهيم</td>
                                            <td>تحليل النظم</td>
                                            <td>محللة نظم</td>
                                            <td>15-03-2021</td>
                                            <td>
                                                <span class="badge badge-warning">في إجازة</span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-ghost">
                                                        عرض
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline">
                                                        تعديل
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger">
                                                        حذف
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>EMP-003</td>
                                            <td>ليث عبد الله</td>
                                            <td>الدعم الفني</td>
                                            <td>أخصائي دعم</td>
                                            <td>10-09-2020</td>
                                            <td>
                                                <span class="badge badge-danger">موقوف</span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-ghost">
                                                        عرض
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline">
                    تعديل
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger">
                                                        حذف
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>EMP-004</td>
                                            <td>هند محمد</td>
                                            <td>إدارة المشاريع</td>
                                            <td>مديرة مشروع</td>
                                            <td>05-06-2019</td>
                                            <td>
                                                <span class="badge badge-success">نشط</span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-ghost">
                                                        عرض
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline">
                                                        تعديل
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger">
                                                        حذف
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
@endsection
