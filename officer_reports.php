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

// Get all PDF files from the reports directory
$report_dir = "C:/xampp/htdocs/thesis-system/reports/";
$files = glob($report_dir . "*.pdf");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="officer.php" class="flex items-center px-6 py-4 hover:bg-blue-800 transition-all">
                    <i class="fas fa-tachometer-alt w-6"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="officer_reports.php" class="flex items-center px-6 py-4 bg-blue-700 transition-all">
                    <i class="fas fa-file-alt w-6"></i>
                    <span class="ml-3">Reports</span>
                </a>
                <a href="/landing_page.php" class="flex items-center px-6 py-4 text-red-300 hover:bg-red-700 hover:text-white transition-all duration-200 rounded-l-lg mt-auto">
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
                            <i class="fas fa-file-pdf text-3xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold">Accident Summary Reports</h1>
                            <p class="mt-1 text-blue-100">View and manage all generated accident reports</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-folder-open text-blue-600 text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Available PDF Reports</h2>
                                <p class="text-gray-500"><?php echo count($files); ?> reports found</p>
                            </div>
                        </div>
                        <div class="relative">
                            <input type="text" id="searchReports" placeholder="Search reports..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>

                    <?php if (count($files) > 0): ?>
                        <ul class="space-y-3 report-list">
                            <?php foreach ($files as $file):
                                $filename = basename($file);
                                $url = str_replace("C:/xampp/htdocs/thesis-system/", "", $file);
                                $date = date("F j, Y", filemtime($file)); ?>
                                <li class="flex items-center justify-between bg-gray-50 p-4 rounded-lg border border-gray-100 hover:bg-gray-100 transition-all">
                                    <div class="flex items-center">
                                        <div class="bg-red-100 p-2 rounded-lg mr-4">
                                            <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-800"><?php echo $filename; ?></span>
                                            <p class="text-xs text-gray-500 mt-1">Generated on <?php echo $date; ?></p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="<?php echo $url; ?>" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all flex items-center">
                                            <i class="fas fa-eye mr-2"></i> View
                                        </a>
                                        <a href="<?php echo $url; ?>" download class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-all flex items-center">
                                            <i class="fas fa-download mr-2"></i> Download
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <i class="fas fa-folder-open text-gray-400 text-4xl mb-3"></i>
                            <p class="text-gray-500 text-lg">No reports found.</p>
                            <p class="text-gray-400 mt-2">Generated reports will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('searchReports').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const reportItems = document.querySelectorAll('.report-list li');

            reportItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if(text.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
