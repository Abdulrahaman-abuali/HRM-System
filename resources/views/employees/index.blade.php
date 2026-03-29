@extends('layout.app')

@section('title', 'إدارة الموظفين')

@section('content')
<main class="main-content">

    <section class="section">
        <div class="page-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <form action="{{ route('employees.index') }}" method="GET" class="search-bar" style="display: flex; gap: 10px;">
                <input type="text" name="search" class="form-control" placeholder="ابحث عن موظف..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">بحث</button>
            </form>

            @if(auth()->user()->role?->name === 'مدير النظام')
                <button type="button" class="btn btn-primary" onclick="window.location='{{ route('employees.create') }}'">
                    إضافة موظف جديد
                </button>
            @endif
        </div>
    </section>

    <section class="section">
        <article class="card">
            <header class="card-header">
                <h2 class="card-title">
                    {{ auth()->user()->role?->name === 'مدير القسم' ? 'قائمة موظفي القسم' : 'قائمة الموظفين' }}
                </h2>
            </header>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover employee-table">
                        <thead>
                            <tr>
                                <th>رقم الموظف</th>
                                <th>الاسم الكامل</th>
                                <th>القسم</th>
                                <th>المسمى الوظيفي</th>
                                <th>تاريخ التعيين</th>
                                <th>العمر</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                <tr>
                                    <td>{{ $employee->id }}</td>
                                    <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                    <td>{{ $employee->department->name ?? '-' }}</td>
                                    <td>{{ $employee->jobTitle->name ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($employee->hire_date)->format('d-m-Y') }}</td>
                                    <td>
                                        {{ $employee->birth_date ? \Carbon\Carbon::parse($employee->birth_date)->age : '-' }}
                                    </td>
                                    <td>
                                        @if($employee->status == 'نشط')
                                            <span class="badge badge-success">نشط</span>
                                        @elseif($employee->status == 'في إجازة')
                                            <span class="badge badge-warning">في إجازة</span>
                                        @elseif($employee->status == 'موقوف')
                                            <span class="badge badge-danger">موقوف</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $employee->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons" style="display: flex; gap: 5px;">
                                            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-outline">عرض</a>

                                            @if(auth()->user()->role?->name === 'مدير النظام')
                                                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline">تعديل</a>
                                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف الموظف؟')">حذف</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align: center;">لا يوجد موظفين</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </article>
    </section>
</main>
@endsection
