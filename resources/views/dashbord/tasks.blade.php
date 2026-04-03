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
                @elseif(auth()->user()->role?->name === 'مدير القسم')
                    <h2 class="section-title">إدارة مهام القسم 👥</h2>
                    <p style="color: #64748b;">متابعة مهام موظفي قسمك وإسناد أعمال جديدة</p>
                @else
                    <h2 class="section-title">مهامي الشخصية 📋</h2>
                    <p style="color: #64748b;">قائمة الأعمال المطلوب منك إنجازها</p>
                @endif
            </div>

            @if(in_array(auth()->user()->role?->name, ['مدير النظام', 'مدير القسم']))
                <button class="btn btn-primary" onclick="document.getElementById('taskModal').style.display='flex'">
                    + إضافة مهمة جديدة
                </button>
            @endif
        </header>

        {{-- إحصائيات سريعة --}}
        <div class="grid grid-4" style="margin-bottom: 30px;">
            <div class="card" style="border-right: 5px solid #4f46e5; padding: 15px;">
                <span style="font-size: 13px; color: #64748b;">{{ in_array(auth()->user()->role?->name, ['مدير النظام', 'مدير القسم']) ? 'إجمالي المهام المفتوحة' : 'مهامي المتبقية' }}</span>
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
                        @if(in_array(auth()->user()->role?->name, ['مدير النظام', 'مدير القسم'])) <th>الموظف المسؤول</th> @endif
                        <th>اسم المهمة</th>
                        <th>مصدر المهمة</th>
                        <th>تاريخ الإنشاء</th>
                        <th>موعد التسليم</th>
                        <th style="width: 160px;">حالة المهمة</th>
                        <th>التفاصيل/السبب</th>
                        @if(in_array(auth()->user()->role?->name, ['مدير النظام', 'مدير القسم'])) <th>إجراءات</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $index => $task)
@php
    // جلب بيانات منشئ المهمة
    $creator = \App\Models\User::find($task->added_by);
    $creatorRole = $creator->role->name ?? 'مدير النظام';

    // صلاحيات المستخدم الحالي
    $currentUser = auth()->user();
    $isSystemAdmin = $currentUser->role?->name === 'مدير النظام';

    // فحص المصدر بدقة:
    // إذا كان added_by فارغ أو المنشئ هو مدير نظام، تظهر "إدارة عليا"
    $isHighAdminTask = is_null($task->added_by) || $creatorRole === 'مدير النظام';

    // صلاحية الحذف:
    // المدير العام يحذف كل شيء.
    // مدير القسم يحذف فقط ما أنشأه هو بنفسه (أي الـ added_by يساوي معرفه الحالي)
    $canDelete = $isSystemAdmin || ($task->added_by == $currentUser->id);
@endphp

<tr style="{{ $task->status == 'completed' ? 'background-color: #f0fdf4;' : ($task->status == 'rejected' ? 'background-color: #fef2f2;' : '') }}">

    {{-- عمود رقم المهمة --}}
    <td>
        <strong>
            @if($isSystemAdmin || $currentUser->role?->name === 'مدير القسم')
                ID {{ $task->id }}
            @else
                ID {{ $index + 1 }}
            @endif
        </strong>
    </td>

    @if($isSystemAdmin || $currentUser->role?->name === 'مدير القسم')
        <td>{{ $task->employee->first_name ?? '---' }}</td>
    @endif

    <td>{{ $task->title }}</td>

    {{-- ✅ تمييز المصدر بشكل دقيق --}}
    <td>
        @if($isHighAdminTask)
            <span class="badge" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 10px;">🏢 إدارة عليا</span>
        @else
            <span class="badge" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; font-size: 10px;">👤 رئيس قسم ({{ $creator->first_name ?? 'سابق' }})</span>
        @endif
    </td>

    {{-- عمود تاريخ الإنشاء وموعد التسليم (كما هما) --}}
    <td>{{ $task->created_at->format('Y-m-d') }}</td>
    <td style="color: {{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status != 'completed' ? '#ef4444' : 'inherit' }}">
        {{ $task->due_date }}
    </td>

    {{-- عمود حالة المهمة (كما هو) --}}
    <td>
        {{-- ... كود الـ badge أو أزرار الموظف ... --}}
        @if($isSystemAdmin || $currentUser->role?->name === 'مدير القسم')
            <span class="badge" style="background: {{ $task->status == 'completed' ? '#dcfce7; color: #166534;' : ($task->status == 'rejected' ? '#fee2e2; color: #991b1b;' : ($task->status == 'in_progress' ? '#e0f2fe; color: #0369a1;' : '#f1f5f9; color: #475569;')) }} padding: 5px 12px; border-radius: 15px; font-size: 11px;">
                @if($task->status == 'completed') ✅ مكتملة @elseif($task->status == 'rejected') ❌ مرفوضة @elseif($task->status == 'in_progress') 🔄 تنفيذ @else ⏳ انتظار @endif
            </span>
        @else
            {{-- أزرار الموظف --}}
            <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                @csrf @method('PATCH')
                @if($task->status == 'pending')
                    <div style="display: flex; gap: 5px;">
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="btn btn-sm" style="background-color: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 4px 8px;">▶️ ابدأ</button>
                        <button type="button" class="btn btn-sm" style="background-color: #ef4444; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 4px 8px;" onclick="rejectTask({{ $task->id }})">✖ رفض</button>
                    </div>
                @elseif($task->status == 'in_progress')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-sm" style="background-color: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer; width: 100%; padding: 4px 8px;">✅ إنهاء</button>
                @else
                    <span style="font-weight: bold; font-size: 11px;">{{ $task->status == 'rejected' ? '❌ مرفوضة' : '✨ منجزة' }}</span>
                @endif
            </form>
        @endif
    </td>

    {{-- عمود السبب --}}
    <td>
        @if($task->status == 'rejected')
            <small style="color: #991b1b; font-style: italic;">{{ $task->rejection_reason ?? 'لا يوجد سبب' }}</small>
        @else
            <small class="text-muted">---</small>
        @endif
    </td>

    {{-- ✅ عمود الإجراءات المصحح --}}
    @if($isSystemAdmin || $currentUser->role?->name === 'مدير القسم')
    <td>
        @if($canDelete)
            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('حذف المهمة؟')">
                @csrf @method('DELETE')
                <button type="submit" style="color: #ef4444; border: none; background: none; cursor: pointer;">🗑️ حذف</button>
            </form>
        @else
            <small class="text-muted" title="لا يمكنك حذف مهام الإدارة العليا">🔒 محمية</small>
        @endif
    </td>
    @endif
</tr>
@endforeach
                </tbody>
            </table>
        </div>
    </main>
</div>

{{-- نافذة إضافة مهمة (Modal) --}}
@if(in_array(auth()->user()->role?->name, ['مدير النظام', 'مدير القسم']))
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
                    @isset($departmentEmployees)
                        @foreach($departmentEmployees as $emp)
                            <option data-id="{{ $emp->id }}" value="{{ $emp->first_name }} {{ $emp->last_name }}">
                        @endforeach
                    @else
                        @isset($employees)
                            @foreach($employees as $emp)
                                <option data-id="{{ $emp->id }}" value="{{ $emp->first_name }} {{ $emp->last_name }}">
                            @endforeach
                        @endisset
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
@endif

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

function rejectTask(taskId) {
    Swal.fire({
        title: 'رفض المهمة',
        text: 'يرجى كتابة سبب رفض هذه المهمة:',
        input: 'textarea',
        inputPlaceholder: 'اكتب السبب هنا...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'تأكيد الرفض',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#ef4444',
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value) {
                let form = document.createElement('form');
                form.method = 'POST';
                // ✅ تصحيح المسار ليكون ديناميكياً لتجنب 404
                form.action = `{{ url('dashbord/tasks') }}/${taskId}/update-status`;

                let csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';

                let method = document.createElement('input');
                method.type = 'hidden'; method.name = '_method'; method.value = 'PATCH';

                let status = document.createElement('input');
                status.type = 'hidden'; status.name = 'status'; status.value = 'rejected';

                let reason = document.createElement('input');
                reason.type = 'hidden'; reason.name = 'rejection_reason'; reason.value = result.value;

                form.appendChild(csrf);
                form.appendChild(method);
                form.appendChild(status);
                form.appendChild(reason);
                document.body.appendChild(form);
                form.submit();
            } else {
                Swal.fire('خطأ', 'يجب كتابة سبب للرفض', 'error');
            }
        }
    });
}
</script>
@endsection
