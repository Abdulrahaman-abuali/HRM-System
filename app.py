from flask import Flask, request, jsonify
import joblib
import pandas as pd
import traceback

app = Flask(__name__)

# Load models
try:
    productivity_model = joblib.load('employee_productivity_model.pkl')
    training_model = joblib.load('training_recommendation_model.pkl')
except Exception as e:
    print(f"Error loading models: {e}")
    productivity_model = None
    training_model = None

@app.route('/predict_productivity', methods=['POST'])
def predict_productivity():
    try:
        data = request.json
        # Expecting: tasks_completed, avg_task_duration, late_ratio, avg_work_hours, performance_score
        df = pd.DataFrame([data])
        
        # Ensure we only use the features the model expects
        features = ["tasks_completed", "avg_task_duration", "late_ratio", "avg_work_hours", "performance_score"]
        df = df[features]
        
        if productivity_model:
            score = productivity_model.predict(df)[0]
        else:
            score = 65.0 # Mock fallback

        return jsonify({"productivity_score": float(score)})
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

@app.route('/recommend_training', methods=['POST'])
def recommend_training():
    try:
        data = request.json
        # Expecting string department and skill_level but model might not need them
        
        # The training model expects: tasks_completed, avg_task_duration, late_ratio, avg_work_hours, performance_score, productivity_score
        df = pd.DataFrame([data])
        features = ["tasks_completed", "avg_task_duration", "late_ratio", "avg_work_hours", "performance_score", "productivity_score"]
        df = df[features]

        if training_model:
            recommendation = training_model.predict(df)[0]
        else:
            recommendation = "Communication Skills" # Mock fallback
            
        return jsonify({
            "recommended_training": str(recommendation),
            "features": data
        })
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(port=5000)
