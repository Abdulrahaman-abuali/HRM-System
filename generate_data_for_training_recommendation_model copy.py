import pandas as pd
import numpy as np

np.random.seed(42)

# عدد الموظفين
num_employees = 5000

employee_id = np.arange(1001, 1001 + num_employees)

tasks_completed = np.random.randint(5, 50, num_employees)

avg_task_duration = np.random.uniform(2, 8, num_employees)

late_tasks = np.random.randint(0, 20, num_employees)

avg_work_hours = np.random.uniform(5, 9, num_employees)

performance_score = np.random.uniform(60, 95, num_employees)

late_ratio = late_tasks / tasks_completed

# حساب productivity_score
productivity_score = (
    tasks_completed * 0.6
    - late_ratio * 30
    - avg_task_duration * 2
    + performance_score * 0.2
)

# إنشاء dataframe
df = pd.DataFrame({
    "employee_id": employee_id,
    "tasks_completed": tasks_completed,
    "avg_task_duration": avg_task_duration,
    "late_ratio": late_ratio,
    "avg_work_hours": avg_work_hours,
    "performance_score": performance_score,
    "productivity_score": productivity_score
})

# -----------------------------
# إضافة department
# -----------------------------

departments = ["IT", "HR", "Finance", "Marketing", "Operations"]

df["department"] = np.random.choice(departments, num_employees)

# -----------------------------
# اشتقاق skill_level
# -----------------------------

def get_skill_level(score):
    if score < 40:
        return "Junior"
    elif score < 70:
        return "Mid"
    else:
        return "Senior"

df["skill_level"] = df["productivity_score"].apply(get_skill_level)

# -----------------------------
# حفظ الداتا
# -----------------------------

df.to_csv("employee_dataset_with_department.csv", index=False)

print(df.head())

print("\nDataset saved as employee_dataset_with_department.csv")