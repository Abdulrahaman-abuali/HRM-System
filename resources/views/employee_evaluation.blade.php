@extends('layout.app')

@section('title')
تقييم الموظف بالذكاء الاصطناعي
@endsection

@section('content')
<main class="main-content">
    <section class="section">
        <article class="card employee-profile">
            <!-- Header -->
            <header class="employee-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
                <div class="employee-header-main" style="display: flex; gap: 20px;">
                    <div class="employee-avatar" style="width: 60px; height: 60px; background: #007bff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        {{ mb_substr($employee->first_name, 0, 1) }}
                    </div>
                    <div class="employee-basic-info">
                        <h2 class="employee-name" style="margin: 0; color: #333;">{{ $employee->first_name}} {{ $employee->last_name }}</h2>
                        <p class="employee-position" style="margin: 5px 0; color: #666;">
                            {{ $employee->jobTitle->name ?? 'غير محدد' }} - {{ $employee->department->name ?? 'غير محدد' }}
                        </p>
                    </div>
                </div>
                
                <div class="employee-productivity-score" style="text-align: center;">
                    <div style="font-size: 36px; font-weight: bold; color: {{ $productivity_score >= 60 ? ($productivity_score >= 80 ? '#28a745' : '#17a2b8') : '#dc3545' }};">
                        {{ $productivity_score }}%
                    </div>
                    <p style="margin: 0; font-size: 14px; color: #555;">مؤشر الإنتاجية والتقييم (AI)</p>
                </div>
            </header>

            <!-- Status and Training -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #444;">الحالة العامة</h3>
                    <div style="display: flex; gap: 10px; align-items: center; font-size: 18px;">
                        <strong>حالة التقييم:</strong> 
                        <span class="badge" style="padding: 8px 12px; border-radius: 4px; color: #fff; background-color: {{ $status == 'Needs Improvement' ? '#dc3545' : ($status == 'High Performer' ? '#28a745' : '#17a2b8') }}">
                            {{ $status == 'Needs Improvement' ? 'يحتاج إلى تحسين' : ($status == 'High Performer' ? 'أداء ممتاز' : 'جيد جداً') }}
                        </span>
                    </div>
                    <div style="margin-top: 15px; font-size: 16px;">
                        <strong>المستوى الفني:</strong> {{ $skill_level == 'Senior' ? 'متقدم (Senior)' : ($skill_level == 'Mid' ? 'متوسط (Mid)' : 'مبتدئ (Junior)') }}
                    </div>
                </div>

                <div style="background: #fff3cd; padding: 20px; border-radius: 8px; border: 1px solid #ffeeba;">
                    <h3 style="margin-top: 0; color: #856404;">التوصيات الذكية (AI)</h3>
                    @if($recommended_training)
                        <p style="color: #856404; font-size: 16px; margin: 0;">
                            بناءً على تحليل البيانات، يوصى بإلحاق الموظف بالدورة التدريبية التالية:
                            <br>
                            <br>
                            <strong style="background: #ffe8a1; padding: 4px 8px; border-radius: 4px;">🎯 {{ $recommended_training }}</strong>
                        </p>
                    @else
                        <p style="color: #856404; margin: 0;">نظراً لأن أداء الموظف جيد، لا توجد توصيات تدريبية إلزامية في الوقت الحالي.</p>
                    @endif
                </div>
            </div>

            <!-- Key Factors Table -->
            <h3 style="color: #444; border-bottom: 2px solid #ddd; padding-bottom: 10px;">العوامل المؤثرة (Key Factors)</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background: #f1f1f1;">
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">المعيار</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">القيمة المحسوبة</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">المهام المنجزة (Tasks Completed)</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $evaluationData['tasks_completed'] }} مهمة</td>
                        <td style="padding: 12px; border: 1px solid #ddd; color: #666;">إجمالي عدد المهام المكتملة للموظف.</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">متوسط مدة الإنجاز (Avg Task Duration)</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $evaluationData['avg_task_duration'] }} يوم</td>
                        <td style="padding: 12px; border: 1px solid #ddd; color: #666;">متوسط الأيام المستغرقة لإكمال المهمة (بين الإنشاء والاكتمال).</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">نسبة التأخير (Late Ratio)</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $evaluationData['late_ratio'] * 100 }}%</td>
                        <td style="padding: 12px; border: 1px solid #ddd; color: #666;">نسبة المهام التي تم تسليمها بعد موعد الاستحقاق (Due Date).</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">متوسط ساعات العمل (Avg Work Hours)</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $evaluationData['avg_work_hours'] }} ساعة/يوم</td>
                        <td style="padding: 12px; border: 1px solid #ddd; color: #666;">متوسط عدد الساعات الفعلية المتواجد بها الموظف استناداً لسجل الحضور.</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">تقييم الأداء العام (Performance Score)</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $evaluationData['performance_score'] }} من 100</td>
                        <td style="padding: 12px; border: 1px solid #ddd; color: #666;">متوسط درجات التقييم الدورية المسجلة مسبقاً من الإدارة.</td>
                    </tr>
                </tbody>
            </table>

            <footer class="employee-actions" style="margin-top: 30px; text-align: left;">
                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-outline" style="padding: 10px 20px; text-decoration: none; border: 1px solid #ccc; color: #333; border-radius: 4px;">
                    العودة لملف الموظف
                </a>
            </footer>

        </article>
    </section>
</main>
@endsection
