<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'تقرير' }}</title>
    <style>
        /* تنسيقات مشتركة لجميع التقارير */
        body {
            font-family: 'DejaVu Sans', 'Tajawal', sans-serif;
            background: #fff;
            margin: 0;
            padding: 20px;
        }

        .report-container {
            max-width: 1200px;
            margin: auto;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .floating-buttons {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .footer-sigs {
            margin-top: 3rem;
            display: flex;
            justify-content: space-around;
        }

        .sig-box {
            text-align: center;
            width: 250px;
            border-top: 1px dashed #6b7280;
            padding-top: 10px;
            font-weight: bold;
        }

        /* تنسيقات الجداول المشتركة */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 6px;
            text-align: right;
            vertical-align: middle;
        }

        .report-table th {
            background: #f1f5f9;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fffbeb; color: #b45309; }

        /* الطباعة */
        @media print {
            .no-print { display: none !important; }
            @page { size: landscape; margin: 0.5cm; }
            body { margin: 0; padding: 0; }
            .report-container { max-width: 100%; padding: 0; }
            .report-header { border: 1px solid #ddd; background: #fff; padding: 0.5rem; }
            .report-table { font-size: 0.65rem; }
            .report-table th, .report-table td {
                border: 1px solid #aaa;
                padding: 4px 4px;
                white-space: nowrap;
            }
            .badge-success, .badge-warning {
                background: #fff !important;
                border: 1px solid #000;
                color: #000 !important;
            }
            .footer-sigs { margin-top: 2rem; }
            .sig-box { border-top: 1px solid #000; }
        }
    </style>
</head>
<body>

    {{-- أزرار الطباعة والتصدير --}}
    <div class="no-print floating-buttons">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
            🖨️ طباعة
        </button>
        <form action="{{ request()->url() }}" method="GET" style="margin:0;">
            @foreach(request()->except('export_excel') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <input type="hidden" name="export_excel" value="1">
            <button type="submit" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                📊 تصدير Excel
            </button>
        </form>
    </div>

    <div class="report-container">
        {{-- الهيدر المشترك --}}
        <div class="report-header">
            <div>
                <h1 style="color: #4f46e5; margin: 0;">نظام إدارة الموارد البشرية</h1>
                <p style="margin: 5px 0 0 0;">{{ $subtitle ?? 'التقارير الرسمية' }}</p>
            </div>
            <div style="text-align: center;">
                <h2 style="font-size: 1.2rem; margin: 0;">{{ $title }}</h2>
                @if(isset($period))
                    <p style="margin: 5px 0 0 0;">الفترة: {{ $period }}</p>
                @endif
            </div>
            <div style="text-align: left;">
                <div>تاريخ الاستخراج: {{ date('Y-m-d') }}</div>
                <p style="margin-top: 5px;">المسؤول: {{ auth()->user()->name ?? 'نظام آلي' }}</p>
            </div>
        </div>

        {{-- المحتوى الرئيسي (يُملأ من الصفحات الفرعية) --}}
        @yield('content')

        {{-- تذييل التوقيعات المشترك (يمكن استبداله في الصفحة الفرعية) --}}
        @hasSection('signatures')
            @yield('signatures')
        @else
            <footer class="footer-sigs">
                <div class="sig-box">توقيع مسؤول القسم</div>
                <div class="sig-box">اعتماد المدير</div>
            </footer>
        @endif
    </div>

</body>
</html>
