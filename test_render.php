<?php
$controller = new \App\Http\Controllers\EmployeeEvaluationController();
$view = $controller->evaluate(1);
echo "HTML RENDERED SUCCESSFULLY. LENGTH: " . strlen($view->render()) . "\n";
