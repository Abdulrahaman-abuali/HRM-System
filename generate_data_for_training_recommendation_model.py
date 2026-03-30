import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import random

# ---------------------------------------
# 1️⃣ إعداد المتغيرات الأساسية
# ---------------------------------------
np.random.seed(42)

num_employees = 5000
min_tasks = 20
max_tasks = 50
departments = ["IT", "HR", "Finance", "Marketing", "Operations"]

# ---------------------------------------
# 2️⃣ توليد بيانات الموظفين
# ---------------------------------------
employee_ids = np.arange(1001, 1001 + num_employees)

all_data = []

for emp_id in employee_ids:
    num_tasks = np.random.randint(min_tasks, max_tasks+1)
    for _ in range(num_tasks):
        start_date = datetime(2026, np.random.randint(1,4), np.random.randint(1,28))
        duration_days = np.random.randint(1,10)
        completed_at = start_date + timedelta(days=duration_days)
        due_date = start_date + timedelta(days=np.random.randint(1,10))
        check_in_hour = random.randint(6,9)
        check_out_hour = check_in_hour + random.randint(6,9)
        avg_work_hours = check_out_hour - check_in_hour
        performance_score = np.random.randint(60,101)

        all_data.append({
            "employee_id": emp_id,
            "start_date": start_date,
            "completed_at": completed_at,
            "due_date": due_date,
            "work_hours": avg_work_hours,
            "performance_score": performance_score
        })

# ---------------------------------------
# 3️⃣ إنشاء DataFrame للمهام الخام وحفظها
# ---------------------------------------
raw_df = pd.DataFrame(all_data)
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
# 6️⃣ إضافة department
# ---------------------------------------
features["department"] = np.random.choice(departments, size=len(features))

# ---------------------------------------
# 7️⃣ اشتقاق skill_level
# ---------------------------------------
def get_skill_level(score):
    if score < 40:
        return "Junior"
    elif score < 70:
        return "Mid"
    else:
        return "Senior"

features["skill_level"] = features["productivity_score"].apply(get_skill_level)

# ---------------------------------------
# 8️⃣ إنشاء recommended_training
# ---------------------------------------
def recommend_training(row):
    if row["late_ratio"] > 0.4:
        return "Time Management"
    if row["performance_score"] < 70:
        return "Technical Skills"
    if row["avg_task_duration"] > 6:
        return "Productivity Improvement"
    if row["skill_level"] == "Junior":
        return "Basic Professional Skills"
    if row["skill_level"] == "Senior":
        return "Leadership Training"
    return "Communication Skills"

features["recommended_training"] = features.apply(recommend_training, axis=1)

# ---------------------------------------
# 9️⃣ حفظ Dataset النهائي للتدريب
# ---------------------------------------
features.to_csv("employee_training_dataset.csv", index=False)
print("✅ Dataset جاهز وتم حفظه في employee_training_dataset.csv")

print(features.head())