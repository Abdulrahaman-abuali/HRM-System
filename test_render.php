$controller = app()->make(\App\Http\Controllers\EmployeeEvaluationController::class);
$view = $controller->evaluate(1);
echo "HTML RENDERED SUCCESSFULLY. LENGTH: " . strlen($view->render()) . "\n";
