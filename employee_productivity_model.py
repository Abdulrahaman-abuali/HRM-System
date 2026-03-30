import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error
import joblib


data = pd.read_csv("employee_productivity_dataset.csv")

#تحديد Features و Target
X = data[[
    "tasks_completed",
    "avg_task_duration",
    "late_ratio",
    "avg_work_hours",
    "performance_score"
]]
y = data["productivity_score"]

#تقسيم البيانات للتدريب والاختبار
X_train, X_test, y_train, y_test = train_test_split(
    X, y,
    test_size=0.2,
    random_state=42
)

#تدريب المودل
model = RandomForestRegressor(
    n_estimators=200,
    random_state=42
)
model.fit(X_train, y_train)


#اختبار المودل
predictions = model.predict(X_test)
error = mean_absolute_error(y_test, predictions)
print("MAE:", error) #كلما كانت MAE صغيرة كان المودل أفضل


#حفظ المودل
joblib.dump(model, "employee_productivity_model.pkl")
print("Model saved")

#ميزة "العوامل المؤثرة على الإنتاجية" 
#نستخرج Feature Importance من المودل
importance = model.feature_importances_
features = X.columns
for f, score in zip(features, importance):
    print(f"{f} : {score}")


# التنبؤ بإنتاجية موظف جديد
new_employee = pd.DataFrame([{
    "tasks_completed":20,
    "avg_task_duration":3.2,
    "late_ratio":0.1,
    "avg_work_hours":7.5,
    "performance_score":85
}])
prediction = model.predict(new_employee)
print("Predicted Productivity:", prediction[0])