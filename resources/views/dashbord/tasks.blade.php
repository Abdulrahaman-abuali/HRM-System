@extends('layout.app')

@section('title', 'إدارة المهام')

@section('content')
<div class="main">
    <main class="main-content">

        {{-- الهيدر: يتغير حسب الرتبة --}}
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                @if(auth()->user()->role?->name === 'مدير النظام')
                    <h2 class="section-title">إدارة مهام الشركة 🏢</h2>
                    <p style="color: #64748b;">مراقبة أداء الموظفين وتوزيع التكليفات</p>
                @else
                    <h2 class="section-title">مهامي الشخصية 📋</h2>
                    <p style="color: #64748b;">قائمة الأعمال المطلوب منك إنجازها</p>
                @endif
            </div>

            {{-- زر الإضافة يظهر فقط لمدير النظام --}}
            @if(auth()->user()->role?->name === 'مدير النظام')
                <button class="btn btn-primary" onclick="document.getElementById('taskModal').style.display='flex'">
                    + إضافة مهمة جديدة
                </button>
            @endif
        </header>

        {{-- إحصائيات سريعة --}}
        <div class="grid grid-4" style="margin-bottom: 30px;">
            <div class="card" style="border-right: 5px solid #4f46e5; padding: 15px;">
                <span style="font-size: 13px; color: #64748b;">{{ auth()->user()->role?->name === 'مدير النظام' ? 'إجمالي المهام المفتوحة' : 'مهامي المتبقية' }}</span>
                <h3 style="margin: 10px 0;">{{ $tasks->where('status', '!=', 'completed')->count() }}</h3>
            </div>
            <div class="card" style="border-right: 5px solid #10b981; padding: 15px;">
                <span style="font-size: 13px; color: #64748b;">المهام المكتملة</span>
                <h3 style="margin: 10px 0;">{{ $tasks->where('status', 'completed')->count() }}</h3>
            </div>
        </div>

        {{-- الجدول --}}
        <div class="card" style="overflow-x: auto;">
            <table class="table" style="width: 100%;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th>رقم المهمة</th>
                        @if(auth()->user()->role?->name === 'مدير النظام') <th>الموظف المسؤول</th> @endif
                        <th>اسم المهمة</th>
                        <th style="width: 160px;">حالة المهمة</th>
                        <th>تاريخ الإنشاء</th>
                        <th>موعد التسليم</th>
                        <th>تاريخ الإنهاء</th>
                        @if(auth()->user()->role?->name === 'مدير النظام') <th>إجراءات</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $index => $task) {{-- استخدمنا $index للحصول على الترقيم --}}
    <tr style="{{ $task->status == 'completed' ? 'background-color: #f0fdf4;' : '' }}">

        {{-- عرض الرقم التسلسلي للموظف والـ ID الحقيقي للمدير --}}
        <td>
            <strong>
                @if(auth()->user()->role?->name === 'مدير النظام')
                    ID {{ $task->id }}
                @else
                    ID {{ $index + 1 }} {{-- يبدأ من 1 --}}
                @endif
            </strong>
        </td>

        @if(auth()->user()->role?->name === 'مدير النظام')
            <td>{{ $task->employee->first_name ?? '---' }}</td>
        @endif

        <td>{{ $task->title }}</td>

        <td>
            @if(auth()->user()->role?->name === 'مدير النظام')
                {{-- عرض الحالة للمدير --}}
                <span class="badge" style="background: {{ $task->status == 'completed' ? '#dcfce7; color: #166534;' : ($task->status == 'in_progress' ? '#e0f2fe; color: #0369a1;' : '#f1f5f9; color: #475569;') }} padding: 5px 12px; border-radius: 15px; font-size: 11px;">
                    {{ $task->status == 'completed' ? '✅ مكتملة' : ($task->status == 'in_progress' ? '🔄 تنفيذ' : '⏳ انتظار') }}
                </span>
            @else
                {{-- نظام الأزرار الذكي للموظف --}}
                <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                    @csrf @method('PATCH')

                    @if($task->status == 'pending')
                        {{-- زر البدء بالتنفيذ --}}
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="btn btn-sm" style="background-color: #4f46e5; color: white; border: none; padding: 5px 15px; border-radius: 5px; cursor: pointer; width: 70%;">
                            ▶️ ابدأ التنفيذ
                        </button>
                    @elseif($task->status == 'in_progress')
                        {{-- زر الإنهاء --}}
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-sm" style="background-color: #10b981; color: white; border: none; padding: 5px 15px; border-radius: 5px; cursor: pointer; width: 70%;">
                            ✅ إكمال المهمة
                        </button>
                    @else
                        {{-- حالة الاكتمال --}}
                        <span style="color: #10b981; font-weight: bold; font-size: 12px; display: block; text-align: center;">تم الإنجاز ✨</span>
                    @endif
                </form>
            @endif
        </td>

        <td>{{ $task->created_at->format('Y-m-d') }}</td>
        <td style="color: {{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status != 'completed' ? '#ef4444' : 'inherit' }}">
            {{ $task->due_date }}
        </td>
        <td style="color: #10b981; font-weight: bold;">
            {{ $task->completed_at ? \Carbon\Carbon::parse($task->completed_at)->format('Y-m-d H:i') : '---' }}
        </td>

        @if(auth()->user()->role?->name === 'مدير النظام')
        <td>
            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('حذف المهمة؟')">
                @csrf @method('DELETE')
                <button type="submit" style="color: #ef4444; border: none; background: none; cursor: pointer;">🗑️ حذف</button>
            </form>
        </td>
        @endif
    </tr>
    @endforeach
                </tbody>
            </table>
        </div>
    </main>
</div>

{{-- نافذة إضافة مهمة (Modal) - للمدير فقط --}}
@if(auth()->user()->role?->name === 'مدير النظام')
<div id="taskModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 500px; padding: 25px; border-radius: 15px;">
        <h3 style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">إضافة مهمة جديدة 📝</h3>
        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">عنوان المهمة</label>
                <input type="text" name="title" class="form-control" placeholder="ما هي المهمة المطلوبة؟" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">إسناد إلى موظف (ابحث بالاسم)</label>
                <input type="text" id="employee_search" class="form-control" list="employees_list" placeholder="اكتب اسم الموظف..." oninput="updateEmployeeId(this.value)" required>
                <input type="hidden" name="employee_id" id="real_employee_id" required>
                <datalist id="employees_list">
                    @isset($employees)
                        @foreach($employees as $emp)
                            <option data-id="{{ $emp->id }}" value="{{ $emp->first_name }} {{ $emp->last_name }}">
                        @endforeach
                    @endisset
                </datalist>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">تاريخ التسليم</label>
                <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('taskModal').style.display='none'">إلغاء</button>
                <button type="submit" class="btn btn-primary" style="background: #4f46e5; padding: 10px 30px;">إرسال المهمة 🚀</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateEmployeeId(val) {
    var options = document.getElementById('employees_list').options;
    for (var i = 0; i < options.length; i++) {
        if (options[i].value === val) {
            document.getElementById('real_employee_id').value = options[i].getAttribute('data-id');
            break;
        }
    }
}
</script>
@endif
@endsection
