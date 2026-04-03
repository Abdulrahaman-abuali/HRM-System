@extends('reports.layout')

@section('content')
    <div style="overflow-x: auto;">
        <table class="report-table" style="min-width: 1400px;">
            <thead>
                32
                    <th>#</th>
                    <th>رقم الموظف</th>
                    <th>الاسم الكامل</th>
                    <th>الشهر</th>
                    <th>الأساسي</th>
                    <th>بدل السكن</th>
                    <th>بدل المواصلات</th>
                    <th>المكافآت</th>
                    <th>التأمينات</th>
                    <th>ضريبة الدخل</th>
                    <th>القروض</th>
                    <th>الجزاءات</th>
                    <th>أيام الغياب</th>
                    <th>خصم الغياب</th>
                    <th>دقائق التأخير</th>
                    <th>خصم التأخير</th>
                    <th>الصافي</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; $totals = []; @endphp
                @foreach($data as $row)
                    @php
                        $basic = $row->basic_salary ?? 0;
                        $housing = $basic * (($row->housing_percentage ?? 0)/100);
                        $transport = $basic * (($row->transport_percentage ?? 0)/100);
                        $bonuses = $row->bonuses ?? 0;
                        $insurance = $basic * 0.06;
                        $tax = $basic * (($row->tax_percentage ?? 0)/100);
                        $loans = $row->loan_installments ?? 0;
                        $penalties = $row->penalties ?? 0;
                        $absenceDays = $row->absence_days ?? 0;
                        $lateMinutes = $row->late_minutes ?? 0;
                        $absenceDed = ($basic/30) * $absenceDays;
                        $lateDed = ($basic/30/8/60) * $lateMinutes;
                        $net = $basic + $housing + $transport + $bonuses - ($insurance + $tax + $loans + $penalties + $absenceDed + $lateDed);
                    @endphp
                    <tr>
                        <td>{{ $counter++ }}</td>
                        <td>EMP-{{ $row->employee->id ?? '' }}</td>
                        <td>{{ $row->employee->first_name ?? '' }} {{ $row->employee->last_name ?? '' }}</td>
                        <td>{{ $row->month ?? '' }}</td>
                        <td>{{ number_format($basic, 2) }}</td>
                        <td>{{ number_format($housing, 2) }}</td>
                        <td>{{ number_format($transport, 2) }}</td>
                        <td>{{ number_format($bonuses, 2) }}</td>
                        <td>{{ number_format($insurance, 2) }}</td>
                        <td>{{ number_format($tax, 2) }}</td>
                        <td>{{ number_format($loans, 2) }}</td>
                        <td>{{ number_format($penalties, 2) }}</td>
                        <td style="text-align:center">{{ $absenceDays }}</td>
                        <td>{{ number_format($absenceDed, 2) }}</td>
                        <td style="text-align:center">{{ $lateMinutes }}</td>
                        <td>{{ number_format($lateDed, 2) }}</td>
                        <td style="font-weight:bold">{{ number_format($net, 2) }}</td>
                        <td>
                            <span class="badge {{ $row->status == 'مدفوع' ? 'badge-success' : 'badge-warning' }}">
                                {{ $row->status ?? 'معلق' }}
                            </span>
                        </td>
                    </tr>
                    @php
                        $totals['basic'] = ($totals['basic'] ?? 0) + $basic;
                        $totals['housing'] = ($totals['housing'] ?? 0) + $housing;
                        $totals['transport'] = ($totals['transport'] ?? 0) + $transport;
                        $totals['bonuses'] = ($totals['bonuses'] ?? 0) + $bonuses;
                        $totals['insurance'] = ($totals['insurance'] ?? 0) + $insurance;
                        $totals['tax'] = ($totals['tax'] ?? 0) + $tax;
                        $totals['loans'] = ($totals['loans'] ?? 0) + $loans;
                        $totals['penalties'] = ($totals['penalties'] ?? 0) + $penalties;
                        $totals['absence'] = ($totals['absence'] ?? 0) + $absenceDed;
                        $totals['late'] = ($totals['late'] ?? 0) + $lateDed;
                        $totals['net'] = ($totals['net'] ?? 0) + $net;
                    @endphp
                @endforeach
            </tbody>
            @if(count($data))
                <tfoot>
                    <tr style="background:#f1f5f9; font-weight:bold;">
                        <td colspan="4">الإجمالي الكلي</td>
                        <td>{{ number_format($totals['basic'], 2) }}</td>
                        <td>{{ number_format($totals['housing'], 2) }}</td>
                        <td>{{ number_format($totals['transport'], 2) }}</td>
                        <td>{{ number_format($totals['bonuses'], 2) }}</td>
                        <td>{{ number_format($totals['insurance'], 2) }}</td>
                        <td>{{ number_format($totals['tax'], 2) }}</td>
                        <td>{{ number_format($totals['loans'], 2) }}</td>
                        <td>{{ number_format($totals['penalties'], 2) }}</td>
                        <td>---</td>
                        <td>{{ number_format($totals['absence'], 2) }}</td>
                        <td>---</td>
                        <td>{{ number_format($totals['late'], 2) }}</td>
                        <td style="background:#4f46e5; color:#fff;">{{ number_format($totals['net'], 2) }}</td>
                        <td>---</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection

@section('signatures')
    <footer class="footer-sigs">
        <div class="sig-box">توقيع المحاسب المالي</div>
        <div class="sig-box">اعتماد مدير الموارد البشرية</div>
    </footer>
@endsection
