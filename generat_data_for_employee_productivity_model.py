import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import random

# ---------------------------------------
# 1️⃣ إعداد المتغيرات الأساسية
# ---------------------------------------
num_employees = 1000  # عدد الموظفين
min_tasks = 20        # أقل عدد مهام لكل موظف
max_tasks = 50        # أكثر عدد مهام لكل موظف

# ---------------------------------------
# 2️⃣ توليد بيانات الموظفين
# ---------------------------------------
employee_ids = range(1001, 1001 + num_employees)
all_tasks = []

for emp_id in employee_ids:
    num_tasks = np.random.randint(min_tasks, max_tasks+1)
    for _ in range(num_tasks):
        start_date = datetime(2026, np.random.randint(1,4), np.random.randint(1,28))  # بين Jan-Mar 2026
        duration_days = np.random.randint(1,10)  # مدة المهمة 1-10 أيام
        completed_at = start_date + timedelta(days=duration_days)
        due_date = start_date + timedelta(days=np.random.randint(1,10))  # موعد التسليم
        check_in_hour = random.randint(6,9)
        check_out_hour = check_in_hour + random.randint(6,9)  # ساعات العمل 6-9 ساعات
        avg_work_hours = check_out_hour - check_in_hour
        performance_score = np.random.randint(60,101)  # تقييم 60-100

        all_tasks.append({
            "employee_id": emp_id,
            "start_date": start_date,
            "completed_at": completed_at,
            "due_date": due_date,
            "work_hours": avg_work_hours,
            "performance_score": performance_score
        })

# ---------------------------------------
# 3️⃣ إنشاء DataFrame للبيانات الخام وحفظها
# ---------------------------------------
raw_df = pd.DataFrame(all_tasks)
raw_df.to_csv("employee_tasks_raw.csv", index=False)
print("✅ البيانات الخام تم حفظها في employee_tasks_raw.csv")

# ---------------------------------------
# 4️⃣ حساب Features لكل موظف
# ---------------------------------------
features = raw_df.groupby("employee_id").agg(
    tasks_completed=("completed_at","count"),
    avg_task_duration=("completed_at", lambda x: (x - raw_df.loc[x.index,"start_date"]).dt.days.mean()),
    late_tasks=("completed_at", lambda x: ((x - raw_df.loc[x.index,"due_date"]).dt.days > 0).sum()),
    avg_work_hours=("work_hours","mean"),
    performance_score=("performance_score","mean")
).reset_index()

features["late_ratio"] = features["late_tasks"] / features["tasks_completed"]

# ---------------------------------------
# 5️⃣ حساب Productivity Score
# ---------------------------------------
features["productivity_score"] = (
    features["tasks_completed"] * 0.4 +
    (1 - features["late_ratio"]) * 30 +
    features["performance_score"] * 0.3
)

# ---------------------------------------
# 6️⃣ حفظ Dataset النهائي للتدريب
# ---------------------------------------
features.to_csv("employee_productivity_dataset.csv", index=False)
print("✅ Dataset جاهز وتم تصديره إلى employee_productivity_dataset.csv")
print(features.head())