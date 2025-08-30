<?php
include "C:/xampp/htdocs/thesis-system/Database/connection.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "officer") {
    header("Location: log_in.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT first_name, middle_name, last_name, username_or_badge, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

$full_name = $user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'];
$badge_number = $user['username_or_badge'];
$email = $user['email'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Loading spinner animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .spinner {
            display: inline-block;
            width: 1.5rem;
            height: 1.5rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }
        .file-selected {
            border-color: #3b82f6 !important;
            background-color: #eff6ff !important;
        }
        .file-name {
            color: #2563eb;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        .file-size {
            font-size: 0.75rem;
            color: #6b7280;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-72 h-screen bg-gradient-to-b from-blue-900 via-blue-800 to-blue-900 text-white shadow-xl">
            <div class="p-6 border-b border-blue-700 flex items-center justify-center space-x-3">
                <i class="fas fa-shield-alt text-2xl"></i>
                <h2 class="text-xl font-bold">Officer Dashboard</h2>
            </div>

            <div class="p-6 text-center border-b border-blue-700">
                <div class="w-24 h-24 mx-auto bg-blue-700 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-user text-4xl"></i>
                </div>
                <h3 class="text-lg font-semibold"><?php echo $full_name; ?></h3>
                <div class="bg-blue-700 rounded-full px-3 py-1 text-sm inline-block mt-1">
                    <i class="fas fa-id-badge mr-1"></i>
                    Badge #: <?php echo $badge_number; ?>
                </div>
                <p class="text-xs text-gray-300 mt-2"><?php echo $email; ?></p>
            </div>

            <div class="mt-6">
                <a href="officer_reports.php" class="flex items-center px-6 py-4 hover:bg-blue-800 transition-all">
                    <i class="fas fa-file-alt w-6"></i>
                    <span class="ml-3">Reports</span>
                </a>
                <a href="/landing_page.php" class="flex items-center px-6 py-4 text-red-300 hover:bg-red-700 hover:text-white transition-all duration-200 rounded-l-lg">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-3">Log Out</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="bg-gradient-to-r from-blue-800 to-blue-600 text-white p-6 rounded-xl shadow-lg mb-8">
                    <div class="flex items-center">
                        <div class="mr-4 bg-white bg-opacity-20 p-3 rounded-lg">
                            <i class="fas fa-user-shield text-3xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold">Welcome, Officer <?php echo $user['last_name']; ?>!</h1>
                            <p class="mt-1 text-blue-100">Manage your reports from here.</p>
                        </div>
                    </div>
                </div>

                <!-- Report Generator Section -->
                <div class="bg-white p-8 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition-all max-w-3xl mx-auto">
                    <div class="flex items-center mb-6">
                        <div class="bg-blue-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Accident-Prone Report</h2>
                            <p class="text-gray-500">Generate accident summary and visualization</p>
                        </div>
                    </div>

                    <form id="reportForm" action="generate_report.php" method="POST" enctype="multipart/form-data" target="_blank">
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Excel File:</label>
                            <div id="fileDropArea" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-all relative">
                                <div id="fileContent">
                                    <i class="fas fa-file-excel text-gray-400 text-3xl mb-3"></i>
                                    <p class="text-sm text-gray-500 mb-3">Drag and drop your .xlsx file here or click to browse</p>
                                    <button type="button" class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg text-sm font-medium pointer-events-none">
                                        Browse Files
                                    </button>
                                </div>
                                <div id="selectedFileContent" class="hidden">
                                    <i class="fas fa-file-excel text-blue-500 text-3xl mb-3"></i>
                                    <p id="fileName" class="file-name"></p>
                                    <p id="fileSize" class="file-size"></p>
                                    <p class="text-xs text-gray-500">Click to change file</p>
                                </div>
                                <input type="file" name="accident_file" id="fileInput" accept=".xlsx" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" style="pointer-events: auto;">
                            </div>
                        </div>

                        <button id="submitBtn" type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-all flex items-center justify-center">
                            <i class="fas fa-upload mr-2"></i>
                            Upload & Generate Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // File selection handling
        const fileInput = document.getElementById('fileInput');
        const fileDropArea = document.getElementById('fileDropArea');
        const fileContent = document.getElementById('fileContent');
        const selectedFileContent = document.getElementById('selectedFileContent');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const reportForm = document.getElementById('reportForm');
        const submitBtn = document.getElementById('submitBtn');

        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                updateFileInfo(this.files[0]);
            }
        });

        // Handle drag and drop
        fileDropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileDropArea.classList.add('border-blue-500');
        });

        fileDropArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            if (!fileInput.files || !fileInput.files[0]) {
                fileDropArea.classList.remove('border-blue-500');
            }
        });

        fileDropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                fileInput.files = e.dataTransfer.files;
                updateFileInfo(e.dataTransfer.files[0]);
            }
        });

        // Update file info display
        function updateFileInfo(file) {
            fileDropArea.classList.add('file-selected');
            fileContent.classList.add('hidden');
            selectedFileContent.classList.remove('hidden');

            fileName.textContent = file.name;
            const fileSizeKB = (file.size / 1024).toFixed(2);
            fileSize.textContent = `${fileSizeKB} KB`;
        }

        // Form submission with loading animation
        reportForm.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner"></div> Generating Report...';

            // The form will submit normally since we're using target="_blank"
            // This will allow the loading state to remain visible while the report generates

            // Reset button after a delay (in a real app, you might want to use AJAX instead)
            setTimeout(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-upload mr-2"></i> Upload & Generate Report';
            }, 5000); // Reset after 5 seconds - adjust as needed
        });
    </script>
</body>

</html>     
