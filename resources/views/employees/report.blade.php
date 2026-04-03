@extends('reports.layout')

@section('content')
    <div style="overflow-x: auto;">
        <table class="report-table" style="min-width: 1000px;">
            <thead>
                32
                    <th>#</th>
                    <th>رقم الموظف</th>
                    <th>الاسم الكامل</th>
                    <th>القسم</th>
                    <th>المسمى الوظيفي</th>
                    <th>المدير المباشر</th>
                    <th>الهاتف</th>
                    <th>الحالة</th>
                    <th>تاريخ التعيين</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp
                @foreach($data as $emp)
                    @php
                        $manager = $emp->actual_manager ?? ($emp->manager ?? null);
                    @endphp
                    <tr>
                        <td>{{ $counter++ }}</td>
                        <td>ID-{{ $emp->id }}</td>
                        <td>{{ $emp->first_name }} {{ $emp->last_name }}</td>
                        <td>{{ $emp->department->name ?? '---' }}</td>
                        <td>{{ $emp->jobTitle->name ?? '---' }}</td>
                        <td>{{ $manager ? $manager->first_name.' '.$manager->last_name : 'لا يوجد' }}</td>
                        <td>{{ $emp->phone ?? '---' }}</td>
                        <td>
                            <span class="badge {{ $emp->status == 'active' ? 'badge-success' : 'badge-warning' }}">
                                {{ $emp->status == 'active' ? 'على رأس العمل' : 'موقوف' }}
                            </span>
                        </td>
                        <td>{{ $emp->hire_date ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
