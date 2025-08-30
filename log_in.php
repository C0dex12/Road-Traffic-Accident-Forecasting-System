<?php
// Include database connection
include "C:/xampp/htdocs/thesis-system/Database/connection.php";

session_start();
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_badge = trim($_POST['username_or_badge']);
    $password = trim($_POST['password']);

    // Check if fields are empty
    if (empty($username_or_badge) || empty($password)) {
        $message = "<div class='p-4 mb-6 rounded-lg bg-red-50 border-l-4 border-red-500'>
                        <div class='flex'>
                            <div class='flex-shrink-0'>
                                <svg class='h-5 w-5 text-red-500' viewBox='0 0 20 20' fill='currentColor'>
                                    <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'/>
                                </svg>
                            </div>
                            <div class='ml-3'>
                                <p class='text-sm text-red-700 font-medium'>All fields are required!</p>
                            </div>
                        </div>
                    </div>";
    } else {
        // Fetch user data
        $query = "SELECT * FROM users WHERE username_or_badge = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username_or_badge);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username_or_badge'] = $user['username_or_badge'];
                $_SESSION['role'] = ctype_digit($username_or_badge) ? "officer" : "regular_user";

                // Redirect based on role
                if ($_SESSION['role'] === "officer") {
                    echo "<script>
                        alert('Login successful! Redirecting to Officer Dashboard...');
                        window.location.href = 'officer.php';
                    </script>";
                } else {
                    echo "<script>
                        alert('Login successful! Redirecting to Regular User Dashboard...');
                        window.location.href = 'user.php';
                    </script>";
                }
                exit();
            } else {
                $message = "<div class='p-4 mb-6 rounded-lg bg-red-50 border-l-4 border-red-500'>
                                <div class='flex'>
                                    <div class='flex-shrink-0'>
                                        <svg class='h-5 w-5 text-red-500' viewBox='0 0 20 20' fill='currentColor'>
                                            <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'/>
                                        </svg>
                                    </div>
                                    <div class='ml-3'>
                                        <p class='text-sm text-red-700 font-medium'>Incorrect password!</p>
                                    </div>
                                </div>
                            </div>";
            }
        } else {
            $message = "<div class='p-4 mb-6 rounded-lg bg-red-50 border-l-4 border-red-500'>
                            <div class='flex'>
                                <div class='flex-shrink-0'>
                                    <svg class='h-5 w-5 text-red-500' viewBox='0 0 20 20' fill='currentColor'>
                                        <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'/>
                                    </svg>
                                </div>
                                <div class='ml-3'>
                                    <p class='text-sm text-red-700 font-medium'>User not found!</p>
                                </div>
                            </div>
                        </div>";
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNP Log In</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: url('/img/philippines-flag-wavy-abstract-background-vector-illustration-philippines-flag-wavy-abstract-background-layout-vector-illustration-224439724.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow:
                0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05),
                0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        .input-field {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            background-color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .link-hover {
            position: relative;
        }

        .link-hover::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #eab308;
            transition: width 0.3s ease;
        }

        .link-hover:hover::after {
            width: 100%;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: #1e3a8a;
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-icon {
            transition: all 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body class="min-h-screen flex justify-center items-center p-4 bg-gray-100">
    <div class="w-full max-w-md animate-fadeIn">
        <!-- Logo Section -->
        <div class="flex justify-center mb-6">
            <div class="bg-blue-900 rounded-full p-3 shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                </svg>
            </div>
        </div>

        <!-- Card -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 p-6 text-white">
                <h2 class="text-2xl font-bold text-center">Welcome Back</h2>
                <p class="text-blue-100 text-center text-sm mt-1">Sign in to your account</p>
            </div>

            <!-- Card Body -->
            <div class="p-8">
                <!-- Error Message -->
                <?php if (!empty($message)) echo $message; ?>

                <form action="" method="POST" class="space-y-6">
                    <!-- Username/Badge Field -->
                    <div class="space-y-2">
                        <label for="username_or_badge" class="form-label">
                            <i class="fa-solid fa-user text-blue-800 mr-2"></i>Username/Badge Number
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">
                                    <i class="fa-solid fa-id-card"></i>
                                </span>
                            </div>
                            <input
                                type="text"
                                id="username_or_badge"
                                name="username_or_badge"
                                class="input-field w-full pl-10 pr-4 py-3 rounded-lg"
                                placeholder="Enter your username or badge number"
                                required
                                autocomplete="off"
                                autofocus
                            >
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label for="password" class="form-label">
                            <i class="fa-solid fa-lock text-blue-800 mr-2"></i>Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="input-field w-full pl-10 pr-10 py-3 rounded-lg"
                                placeholder="Enter your password"
                                required
                                autocomplete="off"
                            >
                            <button
                                type="button"
                                onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-800 input-icon"
                            >
                                <i class="fa-solid fa-eye" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="flex justify-end">
                        <a href="#" class="text-sm text-blue-800 hover:text-blue-700 link-hover">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Login Button -->
                    <button
                        type="submit"
                        class="btn-primary w-full py-3 px-4 rounded-lg text-white font-medium"
                    >
                        Sign In
                    </button>
                </form>

                <!-- Footer Links -->
                <div class="mt-8 text-center space-y-3">
                    <p class="text-gray-700">
                        Don't have an account?
                        <a href="/register.php" class="text-yellow-600 font-semibold hover:text-yellow-700 link-hover ml-1">
                            Register here
                        </a>
                    </p>
                    <p class="text-gray-700">
                        <a href="/landing_page.php" class="text-yellow-600 font-semibold hover:text-yellow-700 link-hover">
                            Return Home
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            let passwordInput = document.getElementById("password");
            let eyeIcon = document.getElementById("eye-icon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>
