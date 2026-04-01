@extends('reports.layout')

@section('content')
    <div style="margin-bottom: 2rem;">
        <h3>البيانات الوظيفية</h3>
        <table class="report-table">
            <tr><th style="width:30%">الاسم الكامل</th><td>{{ $data->first_name }} {{ $data->last_name }}</td></tr>
            <tr><th>القسم</th><td>{{ $data->department->name ?? '---' }}</td></tr>
            <tr><th>المسمى الوظيفي</th><td>{{ $data->jobTitle->name ?? '---' }}</td></tr>
            <tr><th>المدير المباشر</th><td>{{ $data->manager->first_name ?? 'الإدارة العليا' }} {{ $data->manager->last_name ?? '' }}</td></tr>
            <tr><th>تاريخ التعيين</th><td>{{ $data->hire_date ?? '-' }}</td></tr>
            <tr><th>الحالة</th>
                <td><span class="badge {{ $data->status == 'active' ? 'badge-success' : 'badge-warning' }}">
                    {{ $data->status == 'active' ? 'على رأس العمل' : 'موقوف' }}
                </span></td>
            </tr>
        </table>
    </div>

    <div>
        <h3>المعلومات الشخصية</h3>
        <table class="report-table">
            <tr><th>الهاتف</th><td>{{ $data->phone ?? '---' }}</td></tr>
            <tr><th>البريد الإلكتروني</th><td>{{ $data->email ?? '---' }}</td></tr>
            <tr><th>تاريخ الميلاد</th><td>{{ $data->birth_date ?? '---' }} (العمر: {{ $data->age ?? '-' }} سنة)</td></tr>
            <tr><th>العنوان</th><td>{{ $data->address ?? '---' }}</td></tr>
            <tr><th>نوع التوظيف</th><td>{{ $data->employment_type == 'full-time' ? 'دوام كامل' : 'تعاقد / جزئي' }}</td></tr>
        </table>
    </div>
@endsection

@section('signatures')
    <footer class="footer-sigs">
        <div class="sig-box">توقيع الموظف</div>
        <div class="sig-box">توقيع مسؤول السجلات</div>
        <div class="sig-box">اعتماد مدير الموارد البشرية</div>
    </footer>
@endsection
