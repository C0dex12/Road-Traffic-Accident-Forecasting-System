<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNP Traffic Accident Forecasting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ph: {
                            blue: '#0038A8', // Philippine flag blue
                            red: '#CE1126',  // Philippine flag red
                        },
                        navy: {
                            900: '#0A2463', // Deep navy blue
                            800: '#1E3A8A', // Navy blue
                        },
                        accent: {
                            yellow: '#FCD116', // Philippine flag yellow
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: url('/img/philippines-flag-wavy-abstract-background-vector-illustration-philippines-flag-wavy-abstract-background-layout-vector-illustration-224439724.jpg') no-repeat center center fixed ;
            background-size: cover;
        }
        .overlay {
            background: rgba(255, 255, 255, 0.8); /* Adjusted opacity to 0.8 */
            backdrop-filter: blur(2px); /* Added subtle blur for better readability */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3); /* Added subtle border */
        }
        .ph-flag-gradient {
            background: linear-gradient(to right, #0038A8, #CE1126);
        }
        .login-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.6s ease;
        }
        .login-btn:hover::before {
            left: 100%;
        }
        .login-btn:hover {
            background-color: #0038A8;
            color: white;
        }
    </style>
</head>
<body class="h-screen flex flex-col">

    <!-- Navbar with Philippine Flag Colors -->
    <nav class="ph-flag-gradient text-white p-4 shadow-lg flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-md">
                <div class="text-ph-blue font-bold text-sm">PNP</div>
            </div>
            <span class="text-xl font-bold tracking-wide">Traffic Accident Forecasting</span>
        </div>
        <div>
            <a href="/log_in.php" class="login-btn flex items-center gap-2 px-6 py-2.5 bg-accent-yellow text-ph-blue rounded-full transition-all duration-300 font-semibold shadow-lg border-2 border-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                Log In
            </a>
        </div>
    </nav>

    <!-- Hero Section with Adjusted Overlay -->
    <div class="flex-grow flex items-center justify-center p-6">
        <div class="overlay max-w-4xl mx-auto p-10 my-8">
            <div class="text-center">
                <h1 class="text-5xl font-bold text-ph-blue mb-6 leading-tight">Traffic Accident Forecasting System</h1>
                <div class="h-1 w-24 bg-ph-red mx-auto mb-8"></div>
                <p class="text-xl text-navy-800 mb-6 max-w-2xl mx-auto leading-relaxed">
                    Advanced analytics and predictive modeling to anticipate and prevent traffic accidents in Butuan City.
                </p>
            </div>
        </div>
    </div>

</body>
</html>
