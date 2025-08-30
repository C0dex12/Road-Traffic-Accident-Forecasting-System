<?php
// Include database connection
include "C:/xampp/htdocs/thesis-system/Database/connection.php";

$message = ""; // Variable to store success or error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize input
    $email = trim($_POST['email']);
    $username_or_badge = trim($_POST['username_or_badge']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check for empty fields
    if (empty($email) || empty($username_or_badge) || empty($first_name) || empty($last_name) || empty($password) || empty($confirm_password)) {
        $message = "<p class='text-red-600 font-semibold'>All fields are required!</p>";
    } elseif ($password !== $confirm_password) {
        $message = "<p class='text-red-600 font-semibold'>Passwords do not match!</p>";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email or username already exists
        $check_query = "SELECT * FROM users WHERE email = ? OR username_or_badge = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $email, $username_or_badge);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "<p class='text-red-600 font-semibold'>Email or Username/Badge Number already exists!</p>";
        } else {
            // Insert user data
            $query = "INSERT INTO users (email, username_or_badge, first_name, middle_name, last_name, password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssss", $email, $username_or_badge, $first_name, $middle_name, $last_name, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>
                    alert('Registration successful! Redirecting to login page...');
                    window.location.href = 'log_in.php';
                </script>";
                exit();
            } else {
                $message = "<p class='text-red-600 font-semibold'>Something went wrong. Try again later!</p>";
            }
        }

        // Close statement
        $stmt->close();
    }

    // Close database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNP Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/lucide-icons/dist/umd/lucide.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background: url('/img/philippines-flag-wavy-abstract-background-vector-illustration-philippines-flag-wavy-abstract-background-layout-vector-illustration-224439724.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .overlay {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(5px);
        }
        input:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.2);
        }
        .icon {
            display: inline-flex;
            vertical-align: middle;
        }
        .transition-all {
            transition: all 0.2s ease;
        }
        .section-title {
            font-weight: 600;
            color: #1e3a8a;
            border-bottom: 1px solid rgba(30, 58, 138, 0.2);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="h-screen flex justify-center items-center p-4">
    <div class="overlay p-8 rounded-xl shadow-2xl w-full max-w-4xl border border-blue-900/10">
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-full bg-blue-900 flex items-center justify-center text-white">
                <i data-lucide="shield" class="w-10 h-10"></i>
            </div>
        </div>

        <h2 class="text-2xl font-bold text-blue-900 mb-6 text-center">User Registration</h2>

        <!-- Display Success/Error Message -->
        <div class="mb-4 text-center">
            <?php if (!empty($message)) echo $message; ?>
        </div>

        <form action="" method="POST" onsubmit="return validatePasswords()" class="space-y-6">
            <!-- Personal Information Section -->
            <div>
                <h3 class="section-title flex items-center gap-1.5">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    Personal Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- First Name -->
                    <div class="space-y-2">
                        <label class="block text-blue-900 font-medium">First Name</label>
                        <input type="text" name="first_name" class="w-full p-2.5 border border-blue-900/30 rounded-lg transition-all" placeholder="Enter first name" autocomplete="off" required>
                    </div>

                    <!-- Middle Name -->
                    <div class="space-y-2">
                        <label class="block text-blue-900 font-medium">Middle Name</label>
                        <input type="text" name="middle_name" class="w-full p-2.5 border border-blue-900/30 rounded-lg transition-all" placeholder="Enter middle name" autocomplete="off">
                    </div>

                    <!-- Last Name -->
                    <div class="space-y-2">
                        <label class="block text-blue-900 font-medium">Last Name</label>
                        <input type="text" name="last_name" class="w-full p-2.5 border border-blue-900/30 rounded-lg transition-all" placeholder="Enter last name" autocomplete="off" required>
                    </div>
                </div>
            </div>

            <!-- Account Information Section -->
            <div>
                <h3 class="section-title flex items-center gap-1.5">
                    <i data-lucide="user-circle" class="w-5 h-5"></i>
                    Account Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="flex items-center gap-1.5 text-blue-900 font-medium">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            Email
                        </label>
                        <input type="email" name="email" class="w-full p-2.5 border border-blue-900/30 rounded-lg transition-all" placeholder="Enter your email" required autocomplete="off" autofocus>
                    </div>

                    <!-- Username/Badge Number -->
                    <div class="space-y-2">
                        <label class="flex items-center gap-1.5 text-blue-900 font-medium">
                            <i data-lucide="badge" class="w-4 h-4"></i>
                            Username/Badge Number
                        </label>
                        <input type="text" name="username_or_badge" class="w-full p-2.5 border border-blue-900/30 rounded-lg transition-all" placeholder="Enter username or badge number" required autocomplete="off">
                    </div>
                </div>
            </div>

            <!-- Security Section -->
            <div>
                <h3 class="section-title flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                    Security
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Password -->
                    <div class="space-y-2">
                        <label class="block text-blue-900 font-medium">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" class="w-full p-2.5 border border-blue-900/30 rounded-lg pr-10 transition-all" placeholder="Enter your password" required autocomplete="off">
                            <button type="button" onclick="togglePassword('password')" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-blue-900/70 hover:text-blue-900 transition-all">
                                <i data-lucide="eye" class="w-5 h-5 password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Re-Type Password -->
                    <div class="space-y-2">
                        <label class="block text-blue-900 font-medium">Re-Type Password</label>
                        <div class="relative">
                            <input type="password" id="confirmPassword" name="confirm_password" class="w-full p-2.5 border border-blue-900/30 rounded-lg pr-10 transition-all" placeholder="Re-enter your password" required autocomplete="off">
                            <button type="button" onclick="togglePassword('confirmPassword')" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-blue-900/70 hover:text-blue-900 transition-all">
                                <i data-lucide="eye" class="w-5 h-5 confirm-password-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <p id="error-message" class="text-red-600 font-semibold mt-2 hidden">Passwords do not match!</p>
            </div>

            <!-- Register Button -->
            <div class="pt-4">
                <button type="submit" class="w-full bg-blue-900 text-white p-3 rounded-lg hover:bg-blue-800 transition-all font-medium text-lg">Register</button>
            </div>

            <!-- Back to Login -->
            <p class="mt-4 text-blue-900 text-center">
                Already have an account?
                <a href="/log_in.php" class="text-yellow-600 font-semibold hover:underline transition-all">Log In here</a>
            </p>
        </form>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        function togglePassword(id) {
            let input = document.getElementById(id);
            let icon = input.nextElementSibling.querySelector('i');

            if (input.type === "password") {
                input.type = "text";
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = "password";
                icon.setAttribute('data-lucide', 'eye');
            }

            // Refresh icons
            lucide.createIcons();
        }

        function validatePasswords() {
            let password = document.getElementById("password").value;
            let confirmPassword = document.getElementById("confirmPassword").value;
            let errorMessage = document.getElementById("error-message");

            if (password !== confirmPassword) {
                errorMessage.classList.remove("hidden");
                return false;
            } else {
                errorMessage.classList.add("hidden");
                return true;
            }
        }
    </script>
</body>
</html>
