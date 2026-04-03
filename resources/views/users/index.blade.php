@extends('layout.app')

@section('title', 'إدارة المستخدمين والصلاحيات')

@section('content')
    <div class="main">
        <main class="main-content">
            <section class="section" style="margin-bottom: 1.5rem;">
                <article class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('users.index') }}"
                            style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">

                            <div style="flex: 2; min-width: 200px;">
                                <label class="form-label">بحث سريع</label>
                                <input type="text" name="search" class="form-control"
                                    placeholder="بحث بالاسم أو البريد..." value="{{ request('search') }}">
                            </div>

                            <div style="display: flex; gap: 0.5rem;">
                                <button type="submit" class="btn btn-primary">تصفية النتائج</button>
                                <a href="{{ route('users.index') }}" class="btn btn-outline">إعادة تعيين</a>
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section class="section">
                <article class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" style="width: 100%; border-collapse: collapse;">
                                <thead style="background: #f9fafb;">
                                    <tr>
                                        <th style="padding: 1rem; text-align: right;">المستخدم</th>
                                        <th style="padding: 1rem; text-align: right;">الدور</th>
                                        <th style="padding: 1rem; text-align: right;">القسم</th>
                                        <th style="padding: 1rem; text-align: right;">آخر ظهور</th>
                                        <th style="padding: 1rem; text-align: right;">الحالة</th>
                                        <th style="padding: 1rem; text-align: center;">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr style="border-bottom: 1px solid #edf2f7;">
                                            <td style="padding: 1rem;">
                                                <div style="display: flex; align-items: center; gap: 1rem;">
                                                    <div
                                                        style="width: 40px; height: 40px; border-radius: 10px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid #dbeafe;">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                    <div style="display: flex; flex-direction: column;">
                                                        <span
                                                            style="font-weight: 600; color: #111827;">{{ $user->name }}</span>
                                                        <span
                                                            style="font-size: 0.85rem; color: #6b7280;">{{ $user->email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="padding: 1rem;">
                                                <span
                                                    style="padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 500;
                                                background: #f0f9ff; color: #075985;">
                                                    {{ $user->role->name ?? 'بدون دور' }}
                                                </span>
                                            </td>
                                            <td style="padding: 1rem; color: #4b5563;">
                                                {{ $user->employee->department->name ?? 'غير محدد' }}
                                            </td>
                                            <td style="padding: 1rem; color: #6b7280; font-size: 0.9rem;">
                                                {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'لم يسجل دخول' }}
                                            </td>
                                            <td style="padding: 1rem;">
                                                @php
                                                    $isActive = $user->is_active == 1;
                                                    $bgColor = $isActive ? '#ecfdf5' : '#fef2f2';
                                                    $textColor = $isActive ? '#059669' : '#dc2626';
                                                    $statusText = $isActive ? 'نشط' : 'غير نشط';
                                                @endphp
                                                <span
                                                    style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; background: {{ $bgColor }}; color: {{ $textColor }};">
                                                    <span
                                                        style="width: 8px; height: 8px; border-radius: 50%; background: {{ $textColor }};"></span>
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td style="padding: 1rem; text-align: center;">
                                                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                                    <a href="{{ route('users.edit', $user->id) }}"
                                                        class="btn btn-sm btn-outline" title="تعديل الصلاحيات">
                                                        إدارة الصلاحية
                                                    </a>
                                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                        onsubmit="return confirm('هل أنت متأكد من حذف الحساب؟ لن يتم حذف بيانات الموظف.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            style="background: #fee2e2; color: #dc2626; border: none;">حذف</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="padding: 3rem; text-align: center; color: #9ca3af;">
                                                لا توجد سجلات مستخدمين مطابقة للبحث
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </div>
@endsection
