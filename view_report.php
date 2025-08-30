<?php
$timestamp = $_GET['ts'] ?? null;
if (!$timestamp) {
    echo "Invalid report access.";
    exit();
}

$reportPath = "reports/report_summary_{$timestamp}.xlsx";
$chartPath = "reports/accident_chart_{$timestamp}.png";
$reportExists = file_exists($reportPath);
$chartExists = file_exists($chartPath);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generated Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
<div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-3xl text-center">
    <h1 class="text-2xl font-bold text-green-700">📊 Generated Accident Report</h1>

    <?php if (!$reportExists || !$chartExists): ?>
        <p class="mt-4 text-red-600 font-semibold">❌ Missing report or chart. Check the error logs.</p>
    <?php else: ?>
        <p class="mt-4 text-gray-700">✅ Report generated successfully!</p>
        <img src="<?= $chartPath ?>" alt="Chart" class="mt-6 w-full rounded shadow">
        <a href="<?= $reportPath ?>" download class="mt-6 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-800 transition">
            📥 Download Excel Report
        </a>
    <?php endif; ?>

    <a href="officer.php" class="block mt-8 text-blue-600 hover:underline">← Back to Dashboard</a>
</div>
</body>
</html>
