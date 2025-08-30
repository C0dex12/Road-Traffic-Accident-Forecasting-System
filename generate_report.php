<?php
$python = "\"C:/Users/JURG CHARLES/AppData/Local/Programs/Python/Python310/python.exe\"";
$scriptPath = "C:/xampp/htdocs/thesis-system/implementation_algo/algo.py";

// Check if this is the results page (after processing)
if (isset($_GET['show_results']) && isset($_GET['timestamp'])) {
    $timestamp = $_GET['timestamp'];

    // Paths to expected generated files
    $htmlDashboardPath = "reports/dashboard_{$timestamp}.html";
    $pdfReportPath = "reports/traffic_analysis_report_{$timestamp}.pdf";
    $logPath = "error_log_{$timestamp}.txt";

    // Check if files exist
    $dashboardExists = file_exists($htmlDashboardPath);
    $pdfExists = file_exists($pdfReportPath);

    // Get Python output for debugging
    $output = '';
    if (file_exists($logPath)) {
        $output = file_get_contents($logPath);
    }

    // Check if we have HTML dashboard content in the output
    $dashboardHtml = '';
    if (strpos($output, 'DASHBOARD_HTML_START') !== false && strpos($output, 'DASHBOARD_HTML_END') !== false) {
        $start = strpos($output, 'DASHBOARD_HTML_START') + strlen('DASHBOARD_HTML_START');
        $end = strpos($output, 'DASHBOARD_HTML_END');
        $dashboardHtml = trim(substr($output, $start, $end - $start));

        // Save the HTML to a file for future reference
        if (!empty($dashboardHtml)) {
            file_put_contents($htmlDashboardPath, $dashboardHtml);
            $dashboardExists = true;
        }
    }

    // Additional check for SUCCESS message in output
    $processingSuccess = strpos($output, 'SUCCESS: Dashboard generated') !== false;
    $hasChartData = strpos($output, 'Charts created as base64 images') !== false ||
                   strpos($dashboardHtml, 'data:image/png;base64') !== false;

    // Display the results page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Traffic Analysis Dashboard</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .dashboard-container {
                width: 100%;
                height: 100vh;
                border: none;
                overflow: hidden;
            }
            .tab-button {
                transition: all 0.3s ease;
            }
            .tab-button.active {
                background-color: #3B82F6;
                color: white;
            }
            .dashboard-frame {
                border: none;
                width: 100%;
                height: calc(100vh - 200px);
                min-height: 600px;
            }
            .pdf-download-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                transition: all 0.3s ease;
            }
            .pdf-download-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body class="bg-gray-100">
        <div class="min-h-screen">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center py-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Traffic Analysis Dashboard</h1>
                            <p class="text-sm text-gray-600">Generated on <?= date('F j, Y \a\t g:i A', $timestamp) ?></p>
                        </div>
                        <div class="flex space-x-4">
                            <button onclick="toggleView('dashboard')" id="dashboard-tab" class="tab-button active px-4 py-2 rounded-lg font-medium">
                                📊 Dashboard
                            </button>
                            <button onclick="toggleView('pdf')" id="pdf-tab" class="tab-button px-4 py-2 rounded-lg font-medium bg-gray-200 text-gray-700">
                                📄 PDF Reports
                            </button>
                            <button onclick="toggleView('debug')" id="debug-tab" class="tab-button px-4 py-2 rounded-lg font-medium bg-gray-200 text-gray-700">
                                🔧 Debug Info
                            </button>
                            <a href="officer.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                ← Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-7xl mx-auto">
                <?php if (!$dashboardExists || empty($dashboardHtml)): ?>
                    <!-- Error State -->
                    <div class="p-8">
                        <div class="bg-white shadow-xl rounded-2xl p-8 text-center">
                            <h2 class="text-2xl font-bold text-red-700 mb-4">❌ Dashboard Generation Failed</h2>
                            <p class="text-red-600 font-semibold mb-4">Python script did not generate the expected dashboard.</p>

                            <div class="mt-6 bg-yellow-50 border border-yellow-200 p-4 rounded-lg text-left">
                                <h3 class="font-bold text-yellow-800 mb-2">Debugging Information:</h3>
                                <div class="text-sm text-yellow-700 space-y-1">
                                    <p>Dashboard file: <?= $dashboardExists ? '✅ Found' : '❌ Not found' ?></p>
                                    <p>PDF Report: <?= $pdfExists ? '✅ Generated' : '❌ Not found' ?></p>
                                    <p>HTML content: <?= !empty($dashboardHtml) ? '✅ Generated (' . strlen($dashboardHtml) . ' chars)' : '❌ Missing' ?></p>
                                    <p>Processing success: <?= $processingSuccess ? '✅ Success message found' : '❌ No success message' ?></p>
                                    <p>Chart data: <?= $hasChartData ? '✅ Chart data detected' : '❌ No chart data found' ?></p>
                                    <p>Timestamp: <?= $timestamp ?></p>
                                    <p>Log file: <?= file_exists($logPath) ? '✅ Available (' . filesize($logPath) . ' bytes)' : '❌ Missing' ?></p>
                                </div>
                            </div>

                            <?php if (!empty($output)): ?>
                                <div class="mt-4">
                                    <h4 class="font-bold text-gray-800 mb-2">Python Script Output (Last 2000 chars):</h4>
                                    <pre class="bg-gray-100 text-left p-4 text-sm overflow-auto rounded max-h-64"><?= htmlentities(substr($output, -2000)) ?></pre>
                                </div>
                            <?php endif; ?>

                            <div class="mt-6 space-x-4">
                                <button onclick="retryGeneration()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition duration-200">
                                    🔄 Retry Generation
                                </button>
                                <button onclick="showFullLog()" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition duration-200">
                                    📄 Show Full Log
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Dashboard View -->
                    <div id="dashboard-view" class="dashboard-view">
                        <div class="bg-white shadow-sm">
                            <div class="p-4 border-b">
                                <div class="flex justify-between items-center">
                                    <h2 class="text-lg font-semibold text-gray-900">Interactive Traffic Analysis</h2>
                                    <div class="flex space-x-2">
                                        <button onclick="refreshDashboard()" class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                                            🔄 Refresh
                                        </button>
                                        <button onclick="exportDashboard()" class="px-3 py-1 bg-purple-600 text-white rounded text-sm hover:bg-purple-700">
                                            📥 Export HTML
                                        </button>
                                        <?php if ($pdfExists): ?>
                                            <a href="<?= $pdfReportPath ?>" target="_blank" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                                📄 View PDF
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="fullscreen()" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700">
                                            ⛶ Fullscreen
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Dashboard iframe -->
                            <div class="relative">
                                <iframe
                                    id="dashboard-frame"
                                    class="dashboard-frame"
                                    onload="dashboardLoaded()"
                                    sandbox="allow-scripts allow-same-origin allow-forms"
                                ></iframe>

                                <!-- Loading overlay -->
                                <div id="loading-overlay" class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                                        <p class="mt-4 text-gray-600">Loading dashboard...</p>
                                        <p class="mt-2 text-sm text-gray-500">Initializing charts and data...</p>
                                    </div>
                                </div>

                                <!-- Error overlay for iframe issues -->
                                <div id="iframe-error" class="absolute inset-0 bg-red-50 border-2 border-red-200 rounded-lg p-8 text-center hidden">
                                    <h3 class="text-lg font-bold text-red-700 mb-2">Dashboard Loading Error</h3>
                                    <p class="text-red-600 mb-4">The dashboard failed to load properly in the iframe.</p>
                                    <button onclick="openInNewTab()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                        Open in New Tab
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PDF Reports View -->
                    <div id="pdf-view" class="pdf-view hidden p-8">
                        <div class="bg-white shadow-xl rounded-2xl p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">📄 PDF Reports & Export Options</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                <!-- Comprehensive PDF Report -->
                                <div class="pdf-download-card rounded-xl shadow-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold">📊 Full Analysis Report</h3>
                                            <p class="text-sm opacity-90 mt-1">Complete 8-page PDF with all insights</p>
                                        </div>
                                        <div class="text-3xl opacity-75">📋</div>
                                    </div>

                                    <?php if ($pdfExists): ?>
                                        <div class="space-y-3">
                                            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                                                <p class="text-xs opacity-90">File Size: <?= number_format(filesize($pdfReportPath) / 1024, 1) ?> KB</p>
                                                <p class="text-xs opacity-90">Pages: 8</p>
                                                <p class="text-xs opacity-90">Generated: <?= date('M j, Y H:i', $timestamp) ?></p>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="<?= $pdfReportPath ?>" target="_blank" class="flex-1 bg-white text-purple-600 px-4 py-2 rounded-lg text-center font-medium hover:bg-gray-100 transition duration-200">
                                                    👁️ View
                                                </a>
                                                <a href="<?= $pdfReportPath ?>" download class="flex-1 bg-white bg-opacity-90 text-purple-600 px-4 py-2 rounded-lg text-center font-medium hover:bg-white transition duration-200">
                                                    💾 Download
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-white bg-opacity-20 rounded-lg p-3 text-center">
                                            <p class="text-sm">❌ PDF report not generated</p>
                                            <button onclick="regeneratePDF()" class="mt-2 bg-white text-purple-600 px-4 py-1 rounded text-sm hover:bg-gray-100">
                                                🔄 Regenerate
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Dashboard Export -->
                                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold">🖥️ Dashboard Export</h3>
                                            <p class="text-sm opacity-90 mt-1">Interactive dashboard as PDF</p>
                                        </div>
                                        <div class="text-3xl opacity-75">📱</div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="bg-white bg-opacity-20 rounded-lg p-3">
                                            <p class="text-xs opacity-90">Format: PDF</p>
                                            <p class="text-xs opacity-90">Content: Live dashboard snapshot</p>
                                            <p class="text-xs opacity-90">Quality: High resolution</p>
                                        </div>
                                        <button onclick="exportDashboardToPDF()" class="w-full bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition duration-200">
                                            📥 Export Dashboard
                                        </button>
                                    </div>
                                </div>

                                <!-- Data Export -->
                                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold">📊 Raw Data Export</h3>
                                            <p class="text-sm opacity-90 mt-1">Export processed data files</p>
                                        </div>
                                        <div class="text-3xl opacity-75">💾</div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="bg-white bg-opacity-20 rounded-lg p-3">
                                            <p class="text-xs opacity-90">Formats: CSV, Excel, JSON</p>
                                            <p class="text-xs opacity-90">Records: <?= $processingSuccess ? 'All processed data' : 'N/A' ?></p>
                                            <p class="text-xs opacity-90">Includes: Analysis results</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="exportCSV()" class="flex-1 bg-white text-green-600 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-100 transition duration-200">
                                                📄 CSV
                                            </button>
                                            <button onclick="exportExcel()" class="flex-1 bg-white text-green-600 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-100 transition duration-200">
                                                📊 Excel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Export History -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">📁 Export History</h3>
                                <div class="space-y-2">
                                    <?php if ($pdfExists): ?>
                                        <div class="flex items-center justify-between bg-white p-3 rounded-lg">
                                            <div class="flex items-center">
                                                <span class="text-red-500 mr-3">📄</span>
                                                <div>
                                                    <p class="font-medium">Full Analysis Report</p>
                                                    <p class="text-sm text-gray-500"><?= date('M j, Y H:i', $timestamp) ?></p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="<?= $pdfReportPath ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                                <a href="<?= $pdfReportPath ?>" download class="text-green-600 hover:text-green-800 text-sm">Download</a>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($dashboardExists): ?>
                                        <div class="flex items-center justify-between bg-white p-3 rounded-lg">
                                            <div class="flex items-center">
                                                <span class="text-blue-500 mr-3">🖥️</span>
                                                <div>
                                                    <p class="font-medium">Interactive Dashboard</p>
                                                    <p class="text-sm text-gray-500"><?= date('M j, Y H:i', $timestamp) ?></p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="<?= $htmlDashboardPath ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                                <a href="<?= $htmlDashboardPath ?>" download class="text-green-600 hover:text-green-800 text-sm">Download</a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Debug View -->
                    <div id="debug-view" class="debug-view hidden p-8">
                        <div class="bg-white shadow-xl rounded-2xl p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">🔧 Debug Information</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h3 class="font-bold text-blue-800 mb-2">Generation Status</h3>
                                    <div class="text-sm text-blue-700 space-y-1">
                                        <p><?= $processingSuccess ? '✅' : '❌' ?> Dashboard generated <?= $processingSuccess ? 'successfully' : 'with errors' ?></p>
                                        <p><?= !empty($dashboardHtml) ? '✅' : '❌' ?> HTML content extracted</p>
                                        <p><?= $pdfExists ? '✅' : '❌' ?> PDF report <?= $pdfExists ? 'generated' : 'missing' ?></p>
                                        <p><?= $hasChartData ? '✅' : '❌' ?> Chart data <?= $hasChartData ? 'found' : 'missing' ?></p>
                                        <p>✅ File saved to: <?= $htmlDashboardPath ?></p>
                                        <p>📊 Content size: <?= number_format(strlen($dashboardHtml)) ?> characters</p>
                                    </div>
                                </div>

                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h3 class="font-bold text-green-800 mb-2">File Information</h3>
                                    <div class="text-sm text-green-700 space-y-1">
                                        <p>Timestamp: <?= $timestamp ?></p>
                                        <p>Generated: <?= date('Y-m-d H:i:s', $timestamp) ?></p>
                                        <p>Dashboard file: <?= basename($htmlDashboardPath) ?></p>
                                        <p>PDF file: <?= $pdfExists ? basename($pdfReportPath) : 'Not generated' ?></p>
                                        <p>Log file: <?= basename($logPath) ?></p>
                                        <p>Log size: <?= file_exists($logPath) ? number_format(filesize($logPath)) . ' bytes' : 'N/A' ?></p>
                                        <?php if ($pdfExists): ?>
                                            <p>PDF size: <?= number_format(filesize($pdfReportPath)) ?> bytes</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($output)): ?>
                                <div class="mb-6">
                                    <h3 class="font-bold text-gray-800 mb-2">Python Script Output</h3>
                                    <div class="bg-gray-100 p-4 rounded border max-h-96 overflow-auto">
                                        <pre class="text-sm whitespace-pre-wrap"><?= htmlentities($output) ?></pre>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="flex flex-wrap gap-4">
                                <a href="<?= $htmlDashboardPath ?>" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    🔗 Open Dashboard in New Tab
                                </a>
                                <?php if ($pdfExists): ?>
                                    <a href="<?= $pdfReportPath ?>" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                        📄 Open PDF Report
                                    </a>
                                <?php endif; ?>
                                <button onclick="downloadLog()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                                    📄 Download Log
                                </button>
                                <button onclick="downloadDashboard()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                    💾 Download Dashboard HTML
                                </button>
                                <button onclick="validateDashboard()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                                    🔍 Validate Dashboard
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Store dashboard HTML in a script tag to avoid escaping issues -->
        <script type="text/html" id="dashboard-content">
<?= $dashboardHtml ?>
        </script>

        <script>
            // Tab switching
            function toggleView(view) {
                const dashboardView = document.getElementById('dashboard-view');
                const pdfView = document.getElementById('pdf-view');
                const debugView = document.getElementById('debug-view');
                const dashboardTab = document.getElementById('dashboard-tab');
                const pdfTab = document.getElementById('pdf-tab');
                const debugTab = document.getElementById('debug-tab');

                // Hide all views
                dashboardView.classList.add('hidden');
                pdfView.classList.add('hidden');
                debugView.classList.add('hidden');

                // Reset all tabs
                [dashboardTab, pdfTab, debugTab].forEach(tab => {
                    tab.classList.remove('active', 'bg-blue-600', 'text-white');
                    tab.classList.add('bg-gray-200', 'text-gray-700');
                });

                // Show selected view and activate tab
                if (view === 'dashboard') {
                    dashboardView.classList.remove('hidden');
                    dashboardTab.classList.add('active', 'bg-blue-600', 'text-white');
                    dashboardTab.classList.remove('bg-gray-200', 'text-gray-700');
                } else if (view === 'pdf') {
                    pdfView.classList.remove('hidden');
                    pdfTab.classList.add('active', 'bg-blue-600', 'text-white');
                    pdfTab.classList.remove('bg-gray-200', 'text-gray-700');
                } else {
                    debugView.classList.remove('hidden');
                    debugTab.classList.add('active', 'bg-blue-600', 'text-white');
                    debugTab.classList.remove('bg-gray-200', 'text-gray-700');
                }
            }

            // Load dashboard content into iframe
            function loadDashboard() {
                const frame = document.getElementById('dashboard-frame');
                const contentElement = document.getElementById('dashboard-content');

                if (contentElement && frame) {
                    const dashboardContent = contentElement.innerHTML;

                    // Create a blob URL for the dashboard content
                    const blob = new Blob([dashboardContent], { type: 'text/html' });
                    const url = URL.createObjectURL(blob);

                    frame.src = url;

                    // Clean up the blob URL after loading
                    frame.onload = function() {
                        URL.revokeObjectURL(url);
                        dashboardLoaded();
                    };
                } else {
                    console.error('Dashboard content not found');
                    document.getElementById('iframe-error').classList.remove('hidden');
                }
            }

            // Dashboard loaded
            function dashboardLoaded() {
                const overlay = document.getElementById('loading-overlay');
                const errorOverlay = document.getElementById('iframe-error');

                setTimeout(() => {
                    overlay.style.display = 'none';

                    // Check if dashboard actually loaded with data
                    const frame = document.getElementById('dashboard-frame');
                    try {
                        const frameDoc = frame.contentDocument || frame.contentWindow.document;
                        const hasCharts = frameDoc.querySelector('canvas') || frameDoc.querySelector('.chart-container');

                        if (!hasCharts) {
                            console.warn('No charts detected in dashboard');
                        }
                    } catch (e) {
                        console.error('Error checking dashboard content:', e);
                    }
                }, 1000);
            }

            // Refresh dashboard
            function refreshDashboard() {
                const overlay = document.getElementById('loading-overlay');
                overlay.style.display = 'flex';
                loadDashboard();
            }

            // Export dashboard
            function exportDashboard() {
                const contentElement = document.getElementById('dashboard-content');
                if (contentElement) {
                    const blob = new Blob([contentElement.innerHTML], { type: 'text/html' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'dashboard_<?= $timestamp ?>.html';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                } else {
                    alert('Dashboard content not available for export.');
                }
            }

            // PDF Export Functions
            function exportDashboardToPDF() {
                alert('Dashboard PDF export will be triggered from within the dashboard iframe.');
                // The dashboard itself has PDF export functionality
            }

            function exportCSV() {
                // Create a form to request CSV export
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'export_data.php';

                const timestampInput = document.createElement('input');
                timestampInput.type = 'hidden';
                timestampInput.name = 'timestamp';
                timestampInput.value = '<?= $timestamp ?>';

                const formatInput = document.createElement('input');
                formatInput.type = 'hidden';
                formatInput.name = 'format';
                formatInput.value = 'csv';

                form.appendChild(timestampInput);
                form.appendChild(formatInput);
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            function exportExcel() {
                // Create a form to request Excel export
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'export_data.php';

                const timestampInput = document.createElement('input');
                timestampInput.type = 'hidden';
                timestampInput.name = 'timestamp';
                timestampInput.value = '<?= $timestamp ?>';

                const formatInput = document.createElement('input');
                formatInput.type = 'hidden';
                formatInput.name = 'format';
                formatInput.value = 'excel';

                form.appendChild(timestampInput);
                form.appendChild(formatInput);
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            function regeneratePDF() {
                if (confirm('This will regenerate the PDF report. Continue?')) {
                    // Trigger PDF regeneration
                    window.location.href = 'regenerate_pdf.php?timestamp=<?= $timestamp ?>';
                }
            }

            // Open in new tab
            function openInNewTab() {
                window.open('<?= $htmlDashboardPath ?>', '_blank');
            }

            // Fullscreen
            function fullscreen() {
                const frame = document.getElementById('dashboard-frame');
                if (frame.requestFullscreen) {
                    frame.requestFullscreen();
                } else if (frame.webkitRequestFullscreen) {
                    frame.webkitRequestFullscreen();
                } else if (frame.msRequestFullscreen) {
                    frame.msRequestFullscreen();
                }
            }

            // Retry generation
            function retryGeneration() {
                if (confirm('This will regenerate the dashboard. Continue?')) {
                    window.location.href = 'officer.php';
                }
            }

            // Show full log
            function showFullLog() {
                const logContent = <?= json_encode($output) ?>;
                const newWindow = window.open('', '_blank');
                newWindow.document.write(`
                    <html>
                        <head><title>Full Python Log</title></head>
                        <body style="font-family: monospace; padding: 20px;">
                            <h2>Python Script Output</h2>
                            <pre style="white-space: pre-wrap; background: #f5f5f5; padding: 15px; border-radius: 5px;">${logContent}</pre>
                        </body>
                    </html>
                `);
                newWindow.document.close();
            }

            // Download log
            function downloadLog() {
                const logContent = <?= json_encode($output) ?>;
                const blob = new Blob([logContent], { type: 'text/plain' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'python_log_<?= $timestamp ?>.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }

            // Download dashboard HTML
            function downloadDashboard() {
                const contentElement = document.getElementById('dashboard-content');
                if (contentElement) {
                    const dashboardContent = contentElement.innerHTML;
                    const blob = new Blob([dashboardContent], { type: 'text/html' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'dashboard_<?= $timestamp ?>.html';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            }

            // Validate dashboard
            function validateDashboard() {
                const frame = document.getElementById('dashboard-frame');
                try {
                    const frameDoc = frame.contentDocument || frame.contentWindow.document;
                    const hasCharts = frameDoc.querySelector('canvas');
                    const hasData = frameDoc.querySelector('.chart-container') || frameDoc.body.textContent.includes('Total Records');
                    const hasErrors = frameDoc.querySelector('.error') || frameDoc.body.textContent.includes('Error');

                    let message = 'Dashboard Validation Results:\n\n';
                    message += `Charts detected: ${hasCharts ? 'Yes' : 'No'}\n`;
                    message += `Data present: ${hasData ? 'Yes' : 'No'}\n`;
                    message += `Errors found: ${hasErrors ? 'Yes' : 'No'}\n`;
                    message += `PDF Report: <?= $pdfExists ? 'Available' : 'Not generated' ?>\n`;

                    alert(message);
                } catch (e) {
                    alert('Validation failed: ' + e.message);
                }
            }

            // Initialize dashboard on page load
            document.addEventListener('DOMContentLoaded', function() {
                loadDashboard();
            });

            // Auto-hide loading overlay after 10 seconds
            setTimeout(() => {
                const overlay = document.getElementById('loading-overlay');
                if (overlay && overlay.style.display !== 'none') {
                    overlay.style.display = 'none';
                    console.warn('Loading overlay auto-hidden after timeout');
                }
            }, 10000);

            // Enhanced error handling for iframe
            window.addEventListener('message', function(event) {
                if (event.data && event.data.type === 'dashboard-error') {
                    console.error('Dashboard error:', event.data.message);
                    document.getElementById('iframe-error').classList.remove('hidden');
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}

// If this is the initial form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['accident_file'])) {
    $uploadedFile = $_FILES['accident_file']['tmp_name'];

    // Check if file is uploaded
    if (is_uploaded_file($uploadedFile)) {
        // Validate file type
        $fileInfo = pathinfo($_FILES['accident_file']['name']);
        $allowedExtensions = ['xlsx', 'xls', 'csv'];

        if (!in_array(strtolower($fileInfo['extension']), $allowedExtensions)) {
            echo "<script>alert('Invalid file type. Please upload an Excel (.xlsx, .xls) or CSV file.'); window.location.href = 'officer.php';</script>";
            exit();
        }

        // Check file size (max 50MB)
        if ($_FILES['accident_file']['size'] > 50 * 1024 * 1024) {
            echo "<script>alert('File too large. Maximum size is 50MB.'); window.location.href = 'officer.php';</script>";
            exit();
        }

        // Show enhanced loading animation
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Processing Report with PDF Generation</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .spinner { animation: spin 1.5s linear infinite; }
                @keyframes progress {
                    0% { width: 0%; }
                    100% { width: 100%; }
                }
                .progress-animation { animation: progress 45s ease-out forwards; }
                @keyframes dots {
                    0% { content: '.'; }
                    33% { content: '..'; }
                    66% { content: '...'; }
                }
                .dot-animation::after {
                    content: '.';
                    animation: dots 1.5s infinite;
                }
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
                .pulse { animation: pulse 2s infinite; }
            </style>
        </head>
        <body class="bg-gray-100 flex items-center justify-center min-h-screen">
            <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-4xl text-center">
                <h1 class="text-3xl font-bold text-blue-700 mb-6">Analyzing Traffic Data & Generating Reports</h1>

                <!-- Progress bar -->
                <div class="w-full h-3 bg-gray-200 rounded-full mb-8 overflow-hidden">
                    <div id="progress-bar" class="h-full bg-gradient-to-r from-blue-500 to-purple-600 progress-animation"></div>
                </div>

                <!-- Loading animation -->
                <div class="flex flex-col items-center justify-center mb-8">
                    <div class="relative w-32 h-32">
                        <div class="absolute inset-0 border-4 border-blue-200 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-transparent border-t-blue-600 rounded-full spinner"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>

                    <h2 id="status-text" class="text-xl font-semibold text-blue-700 mt-6">Processing your data<span class="dot-animation"></span></h2>
                    <p id="status-detail" class="text-gray-600 mt-2">Analyzing accident patterns and generating comprehensive reports</p>
                </div>

                <!-- Processing steps -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div id="step1" class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <div class="flex items-center mb-2">
                            <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white mr-2">1</div>
                            <h3 class="font-semibold text-blue-800">Data Processing</h3>
                        </div>
                        <p class="text-sm text-blue-700">Loading and cleaning accident data</p>
                    </div>

                    <div id="step2" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2">
                            <div class="w-6 h-6 rounded-full bg-gray-400 flex items-center justify-center text-white mr-2">2</div>
                            <h3 class="font-semibold text-gray-600">ML Analysis</h3>
                        </div>
                        <p class="text-sm text-gray-500">Running machine learning algorithms</p>
                    </div>

                    <div id="step3" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2">
                            <div class="w-6 h-6 rounded-full bg-gray-400 flex items-center justify-center text-white mr-2">3</div>
                            <h3 class="font-semibold text-gray-600">PDF Generation</h3>
                        </div>
                        <p class="text-sm text-gray-500">Creating comprehensive PDF report</p>
                    </div>

                    <div id="step4" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-2">
                            <div class="w-6 h-6 rounded-full bg-gray-400 flex items-center justify-center text-white mr-2">4</div>
                            <h3 class="font-semibold text-gray-600">Dashboard</h3>
                        </div>
                        <p class="text-sm text-gray-500">Building interactive dashboard</p>
                    </div>
                </div>

                <!-- Features being generated -->
                <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Reports Being Generated</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center">
                            <span class="text-red-500 mr-2 pulse">📄</span>
                            <span>8-page comprehensive PDF report</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-blue-500 mr-2 pulse">🖥️</span>
                            <span>Interactive HTML dashboard</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2 pulse">📊</span>
                            <span>Statistical analysis & charts</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-purple-500 mr-2 pulse">🤖</span>
                            <span>Machine learning insights</span>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-gray-500">Creating your comprehensive analysis with enhanced PDF reporting...</p>
            </div>

            <script>
                // Step activation logic
                const step1 = document.getElementById('step1');
                const step2 = document.getElementById('step2');
                const step3 = document.getElementById('step3');
                const step4 = document.getElementById('step4');

                function activateStep(step, text, detail) {
                    [step1, step2, step3, step4].forEach(s => {
                        s.classList.remove('bg-blue-50', 'border-blue-100');
                        s.classList.add('bg-gray-50', 'border-gray-200');
                        const stepNum = s.querySelector('div.w-6');
                        stepNum.classList.remove('bg-blue-600');
                        stepNum.classList.add('bg-gray-400');
                        const title = s.querySelector('h3');
                        title.classList.remove('text-blue-800');
                        title.classList.add('text-gray-600');
                        const desc = s.querySelector('p');
                        desc.classList.remove('text-blue-700');
                        desc.classList.add('text-gray-500');
                    });

                    step.classList.remove('bg-gray-50', 'border-gray-200');
                    step.classList.add('bg-blue-50', 'border-blue-100');
                    const stepNum = step.querySelector('div.w-6');
                    stepNum.classList.remove('bg-gray-400');
                    stepNum.classList.add('bg-blue-600');
                    const title = step.querySelector('h3');
                    title.classList.remove('text-gray-600');
                    title.classList.add('text-blue-800');
                    const desc = step.querySelector('p');
                    desc.classList.remove('text-gray-500');
                    desc.classList.add('text-blue-700');

                    document.getElementById('status-text').innerHTML = text + '<span class="dot-animation"></span>';
                    document.getElementById('status-detail').textContent = detail;
                }

                setTimeout(() => activateStep(step2, "Running machine learning analysis", "Processing vehicle types, demographics, and risk factors"), 12000);
                setTimeout(() => activateStep(step3, "Generating PDF report", "Creating comprehensive 8-page analysis document"), 25000);
                setTimeout(() => activateStep(step4, "Building interactive dashboard", "Finalizing charts and interactive visualizations"), 35000);
            </script>
        </body>
        </html>
        <?php

        // Start processing in the background
        $escapedFile = escapeshellarg($uploadedFile);
        $escapedScript = escapeshellarg($scriptPath);
        $timestamp = time();

        // Ensure the reports directory exists
        if (!is_dir("reports")) {
            mkdir("reports", 0755, true);
        }

        // Run the Python script with enhanced error handling
        $command = "$python $escapedScript $escapedFile $timestamp 2>&1";

        // Set execution time limit
        set_time_limit(300); // 5 minutes

        $output = shell_exec($command);

        // Log output for debugging
        file_put_contents("error_log_{$timestamp}.txt", $output);

        // Check if Python script executed successfully
        if ($output === null) {
            file_put_contents("error_log_{$timestamp}.txt", "ERROR: Python script failed to execute. Command: $command");
        }

        // Wait a moment for processing
        sleep(3);

        // Redirect to results
        echo "<script>setTimeout(() => { window.location.href = 'generate_report.php?show_results=1&timestamp=$timestamp'; }, 8000);</script>";
        exit();
    } else {
        echo "<script>alert('No file uploaded or upload failed.'); window.location.href = 'officer.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-3xl text-center">
        <h1 class="text-3xl font-bold text-red-700 mb-4">❌ Error</h1>
        <p class="text-gray-700 mb-6">Invalid request. Please upload a file from the officer dashboard.</p>
        <a href="officer.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-800 transition duration-200">
            Back to Dashboard
        </a>
    </div>
</body>
</html>
