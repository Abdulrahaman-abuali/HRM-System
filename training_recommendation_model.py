import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report
import joblib


df = pd.read_csv("employee_training_dataset.csv")

#تجهيز Features وTarget
X = df[[
    "tasks_completed",
    "avg_task_duration",
    "late_ratio",
    "avg_work_hours",
    "performance_score",
    "productivity_score"
]]


#Target
y = df["recommended_training"]


#تقسيم البيانات للتدريب والاختبار
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.3, random_state=42
)

#تدريب المودل
model = RandomForestClassifier(n_estimators=200, random_state=42)
model.fit(X_train, y_train)

#اختبار المودل
y_pred = model.predict(X_test)
accuracy = accuracy_score(y_test, y_pred)
print("✅ Accuracy:", accuracy)
print("\nClassification Report:\n", classification_report(y_test, y_pred))

#حفظ المودل
joblib.dump(model, "training_recommendation_model.pkl")
print("✅ Model saved as training_recommendation_model.pkl")

# Feature Importance
importance = model.feature_importances_
features_columns = X.columns

print("\nFeature Importance for Training Recommendation:\n")
for f, score in zip(features_columns, importance):
    print(f"{f} : {score:.3f}")

# دالة لتفسير توصية التدريب لأي موظف جديد
def explain_training_prediction(employee_data):
    pred = model.predict(employee_data)[0]
    feat_importance = pd.DataFrame({
        "Feature": employee_data.columns,
        "Value": employee_data.iloc[0].values,
        "Importance": importance
    }).sort_values(by="Importance", ascending=False)
    
    print(f"\nRecommended Training: {pred}\n")
    print("Factors contributing most to this recommendation:")
    print(feat_importance)


#تجربة التنبؤ على موظف جديد
new_employee = pd.DataFrame([{
    "tasks_completed": 30,
    "avg_task_duration": 5,
    "late_ratio": 0.35,
    "avg_work_hours": 7,
    "performance_score": 80,
    "productivity_score": 65
}])

explain_training_prediction(new_employee)