<?php
header('Content-Type: application/json');

if (!isset($_FILES['accident_file'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit();
}

$uploadedFile = $_FILES['accident_file']['tmp_name'];

if (!is_uploaded_file($uploadedFile)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file upload']);
    exit();
}

$python = "\"C:/Users/JURG CHARLES/AppData/Local/Programs/Python/Python310/python.exe\"";
$scriptPath = "C:/xampp/htdocs/thesis-system/implementation_algo/algo.py";
$timestamp = time();

// Ensure the reports directory exists
if (!is_dir("reports")) {
    mkdir("reports", 0755, true);
}

// Process the file with Python
$escapedFile = escapeshellarg($uploadedFile);
$escapedScript = escapeshellarg($scriptPath);

// Pass both file path and timestamp to Python script
$command = "$python $escapedScript $escapedFile $timestamp 2>&1";
$output = shell_exec($command);

// Log output for debugging
file_put_contents("error_log_{$timestamp}.txt", $output);

// Paths to expected generated files
$reportPath = "reports/report_summary_{$timestamp}.xlsx";
$chartPath = "reports/accident_chart_{$timestamp}.png";

// Wait for files to be generated (max 30 seconds)
$maxWait = 30;
$waited = 0;
while ((!file_exists($reportPath) || !file_exists($chartPath)) && $waited < $maxWait) {
    sleep(1);
    $waited++;
}

$reportExists = file_exists($reportPath);
$chartExists = file_exists($chartPath);

if ($reportExists && $chartExists) {
    echo json_encode(['success' => true, 'timestamp' => $timestamp]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to generate report files',
        'debug' => [
            'report_exists' => $reportExists,
            'chart_exists' => $chartExists,
            'waited' => $waited
        ]
    ]);
}
?>
