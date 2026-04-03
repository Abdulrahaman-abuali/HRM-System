@extends('reports.layout')

@section('content')
    <div style="overflow-x: auto;">
        <table class="report-table" style="min-width: 900px;">
            <thead>
                32
                    <th>#</th>
                    <th>رقم الموظف</th>
                    <th>الاسم الكامل</th>
                    <th>التاريخ</th>
                    <th>وقت الحضور</th>
                    <th>وقت الانصراف</th>
                    <th>الحالة</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $counter = 1;
                    $totalPresent = 0;
                    $totalAbsent = 0;
                    $totalLate = 0;
                @endphp

                @foreach($data as $row)
                    @php
                        // التحقق من وجود خاصية status، وإعطاء قيمة افتراضية
                        $status = $row->status ?? '';
                        $statusText = $status;

                        // معالجة الحالات المختلفة
                        if ($status == 'approved') $statusText = 'معتمدة';
                        elseif ($status == 'rejected') $statusText = 'مرفوضة';
                        elseif ($status == 'pending') $statusText = 'انتظار';
                        elseif ($status == 'حاضر') $statusText = 'حاضر';
                        elseif ($status == 'غائب') $statusText = 'غائب';
                        elseif ($status == 'تأخير') $statusText = 'تأخير';

                        // تحديد لون الحالة
                        $isPresent = in_array($status, ['حاضر', 'approved', 'present', 'active']);
                        $isAbsent = in_array($status, ['غائب', 'rejected', 'absent', 'inactive']);
                        $isLate = in_array($status, ['تأخير', 'pending', 'late']);

                        if ($isPresent) $totalPresent++;
                        elseif ($isAbsent) $totalAbsent++;
                        elseif ($isLate) $totalLate++;
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 8px;">{{ $counter++ }}</td>
                        <td style="padding: 8px;">EMP-{{ $row->employee_id ?? '---' }}</td>
                        <td style="padding: 8px; font-weight: 600;">{{ $row->employee_name ?? 'غير معروف' }}</td>
                        <td style="padding: 8px;">{{ $row->date ?? $row->start_date ?? '-' }}</td>
                        <td style="padding: 8px;">{{ $row->check_in ?? '--:--' }}</td>
                        <td style="padding: 8px;">{{ $row->check_out ?? '--:--' }}</td>
                        <td style="padding: 8px;">
                            <span class="badge {{ $isPresent ? 'badge-success' : ($isLate ? 'badge-warning' : 'badge-danger') }}">
                                {{ $statusText ?: 'غير محدد' }}
                            </span>
                        </td>
                        <td style="padding: 8px;">{{ $row->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            @if($data->count() > 0)
                <tfoot>
                    <tr style="background: #f1f5f9; font-weight: bold;">
                        <td colspan="6" style="padding: 8px;">إجمالي ملخص الفترة</td>
                        <td style="padding: 8px; color: #16a34a;">الحضور: {{ $totalPresent }}</td>
                        <td style="padding: 8px; color: #dc2626;">الغياب: {{ $totalAbsent }}</td>
                        <td style="padding: 8px; color: #f59e0b;">التأخير: {{ $totalLate }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection

@section('signatures')
    <footer class="footer-sigs">
        <div class="sig-box">توقيع مسؤول الحضور</div>
        <div class="sig-box">اعتماد مدير الموارد البشرية</div>
    </footer>
@endsection
