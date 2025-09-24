<?php
include_once 'includes/auth.php';

$current_user = $auth->get_current_user();
$is_logged_in = $auth->is_logged_in();

// Check for any response messages
$response_message = '';
$response_type = '';
if (isset($response) && !empty($response)) {
    $response_message = $response['message'] ?? '';
    $response_type = $response['success'] ? 'success' : 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect - Smart Garbage Collection</title>
    <link rel="icon" href="R.png" type="image/png">
    <meta name="description" content="Schedule pickups, track services, and keep your community clean with our modern waste management platform.">
    <meta name="keywords" content="garbage collection, waste management, smart city, recycling">
    <meta name="author" content="Chris Ribiz">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        primaryDark: '#059669',
                        secondary: '#F59E0B',
                        dark: '#1F2937',
                        light: '#F9FAFB'
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .hero-pattern {
            background-color: #f0fdf4;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2310b981' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .mobile-menu {
            transition: all 0.3s ease;
        }
        .auth-modal {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        .auth-modal.active {
            opacity: 1;
            visibility: visible;
        }
        .auth-tab {
            cursor: pointer;
            padding: 0.75rem 1rem;
            border-bottom: 3px solid transparent;
            font-size: 0.9rem;
        }
        .auth-tab.active {
            border-bottom: 3px solid #10B981;
            color: #10B981;
            font-weight: 600;
        }
        .user-type-btn {
            transition: all 0.3s ease;
            border: 2px solid #E5E7EB;
            padding: 0.75rem;
        }
        .user-type-btn.active {
            border-color: #10B981;
            background-color: #F0FDF4;
        }
        .step-indicator {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #E5E7EB;
            color: #6B7280;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .step-indicator.active {
            background-color: #10B981;
            color: white;
        }
        .step-indicator.completed {
            background-color: #10B981;
            color: white;
        }
        .password-strength-meter {
            height: 5px;
            background-color: #E5E7EB;
            border-radius: 3px;
            margin-top: 8px;
        }
        .password-strength-meter div {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: 500;
        }
        .alert-success {
            background-color: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        .alert-error {
            background-color: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .auth-modal .modal-container {
                width: 95%;
                margin: 1rem auto;
                max-height: 90vh;
                overflow-y: auto;
            }
            .auth-tab {
                padding: 0.5rem;
                font-size: 0.8rem;
            }
            .user-type-btn {
                flex-direction: column;
                text-align: center;
                padding: 0.5rem;
            }
            .step-indicators {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .step-indicator {
                width: 24px;
                height: 24px;
                font-size: 0.75rem;
            }
        }

        @media (max-height: 700px) {
            .auth-modal .modal-container {
                max-height: 85vh;
                overflow-y: auto;
            }
            .auth-form-container {
                padding: 1rem;
            }
            .auth-form-container h3 {
                font-size: 1.25rem;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Response Message Display -->
    <?php if (!empty($response_message)): ?>
    <div class="fixed top-4 right-4 z-50 max-w-sm <?php echo $response_type === 'success' ? 'alert-success' : 'alert-error'; ?> alert">
        <div class="flex items-center justify-between">
            <span><?php echo htmlspecialchars($response_message); ?></span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg">&times;</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center mr-3">
                    <i class="fas fa-trash-alt text-white"></i>
                </div>
                <span class="text-2xl font-bold text-primary">Eco<span class="text-dark">Collect</span></span>
            </div>
            
            <nav class="hidden md:flex space-x-8">
                <a href="#" class="text-dark hover:text-primary font-medium">Home</a>
                <a href="#" class="text-dark hover:text-primary font-medium">Services</a>
                <a href="#" class="text-dark hover:text-primary font-medium">Pricing</a>
                <a href="#" class="text-dark hover:text-primary font-medium">Schedule</a>
                <a href="#" class="text-dark hover:text-primary font-medium">Contact</a>
            </nav>
            
            <div class="flex items-center space-x-4">
                <?php if ($is_logged_in && $current_user): ?>
                    <span class="text-dark font-medium hidden md:inline">Welcome, <?php echo htmlspecialchars($current_user['name']); ?>!</span>
                    <a href="#" id="logout-btn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition duration-300">Logout</a>
                <?php else: ?>
                    <a href="#" id="login-btn" class="text-dark hover:text-primary font-medium hidden md:block">Login</a>
                    <a href="#" id="signup-btn" class="bg-primary hover:bg-primaryDark text-white px-4 py-2 rounded-lg font-medium transition duration-300">Sign Up</a>
                <?php endif; ?>
                <button id="mobile-menu-button" class="md:hidden text-dark" title="Open mobile menu">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu hidden md:hidden bg-white px-4 pb-4">
            <div class="flex flex-col space-y-3">
                <a href="#" class="text-dark hover:text-primary font-medium py-2">Home</a>
                <a href="#" class="text-dark hover:text-primary font-medium py-2">Services</a>
                <a href="#" class="text-dark hover:text-primary font-medium py-2">Pricing</a>
                <a href="#" class="text-dark hover:text-primary font-medium py-2">Schedule</a>
                <a href="#" class="text-dark hover:text-primary font-medium py-2">Contact</a>
                <?php if ($is_logged_in && $current_user): ?>
                    <span class="text-dark font-medium py-2">Welcome, <?php echo htmlspecialchars($current_user['name']); ?>!</span>
                    <a href="#" id="mobile-logout-btn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium text-center">Logout</a>
                <?php else: ?>
                    <a href="#" id="mobile-login-btn" class="text-dark hover:text-primary font-medium py-2">Login</a>
                    <a href="#" id="mobile-signup-btn" class="bg-primary hover:bg-primaryDark text-white px-4 py-2 rounded-lg font-medium text-center">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Authentication Modal -->
    <div id="auth-modal" class="auth-modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="modal-container bg-white rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <!-- Tabs -->
            <div class="flex border-b sticky top-0 bg-white z-10">
                <div id="login-tab" class="auth-tab active w-1/2 text-center">Login</div>
                <div id="signup-tab" class="auth-tab w-1/2 text-center">Sign Up</div>
            </div>
            
            <!-- Login Form -->
            <div id="login-form" class="auth-form-container p-4 sm:p-6">
                <h3 class="text-xl sm:text-2xl font-bold text-dark mb-4 sm:mb-6">Login to Your Account</h3>
                
                <!-- User Type Selection -->
                <div class="mb-4 sm:mb-6">
                    <label class="block text-gray-700 mb-2 sm:mb-3">I am a:</label>
                    <div class="flex space-x-2 sm:space-x-4">
                        <button type="button" class="user-type-btn active flex-1 py-2 sm:py-3 rounded-lg text-center flex flex-col items-center" data-type="user">
                            <i class="fas fa-user text-lg sm:text-xl mb-1 sm:mb-2"></i>
                            <p class="text-sm sm:text-base">User</p>
                        </button>
                        <button type="button" class="user-type-btn flex-1 py-2 sm:py-3 rounded-lg text-center flex flex-col items-center" data-type="driver">
                            <i class="fas fa-truck text-lg sm:text-xl mb-1 sm:mb-2"></i>
                            <p class="text-sm sm:text-base">Driver</p>
                        </button>
                    </div>
                </div>
                
                <form id="login-form-data" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="user_type" id="login-user-type" value="user">
                    <input type="hidden" name="ajax" value="true">
                    
                    <div class="mb-3 sm:mb-4">
                        <label class="block text-gray-700 mb-1 sm:mb-2 text-sm sm:text-base" for="login-email">Email Address</label>
                        <input type="email" id="login-email" name="email" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm sm:text-base" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-4 sm:mb-6">
                        <label class="block text-gray-700 mb-1 sm:mb-2 text-sm sm:text-base" for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm sm:text-base" placeholder="Enter your password" required>
                    </div>
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember-me" name="remember" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-xs sm:text-sm text-gray-700">Remember me</label>
                        </div>
                        <a href="#" class="text-xs sm:text-sm text-primary hover:underline">Forgot password?</a>
                    </div>
                    <button type="submit" class="w-full bg-primary hover:bg-primaryDark text-white py-2 sm:py-3 rounded-lg font-semibold transition duration-300 text-sm sm:text-base">Login</button>
                </form>
                <div class="mt-3 sm:mt-4 text-center">
                    <p class="text-gray-600 text-xs sm:text-sm">Don't have an account? <a href="#" id="switch-to-signup" class="text-primary hover:underline">Sign up</a></p>
                </div>
            </div>
            
            <!-- Signup Form -->
            <div id="signup-form" class="auth-form-container p-4 sm:p-6 hidden">
                <!-- Step Indicators -->
                <div class="flex justify-between mb-6 relative">
                    <div class="flex items-center">
                        <div class="step-indicator completed" id="step-1-indicator">1</div>
                        <div class="text-xs ml-2 text-primary font-medium hidden sm:block">Account Type</div>
                    </div>
                    <div class="flex items-center">
                        <div class="step-indicator" id="step-2-indicator">2</div>
                        <div class="text-xs ml-2 text-gray-500 hidden sm:block">Personal Info</div>
                    </div>
                    <div class="flex items-center">
                        <div class="step-indicator" id="step-3-indicator">3</div>
                        <div class="text-xs ml-2 text-gray-500 hidden sm:block">Verification</div>
                    </div>
                </div>
                
                <!-- Step 1: Account Type -->
                <div id="signup-step-1">
                    <h3 class="text-xl sm:text-2xl font-bold text-dark mb-4 sm:mb-6">Create an Account</h3>
                    <div class="mb-4 sm:mb-6">
                        <label class="block text-gray-700 mb-2 sm:mb-3 text-sm sm:text-base">Account Type:</label>
                        <div class="flex space-x-2 sm:space-x-4">
                            <button type="button" class="user-type-btn active flex-1 py-2 sm:py-3 rounded-lg text-center flex flex-col items-center" data-type="user">
                                <i class="fas fa-user text-lg sm:text-xl mb-1 sm:mb-2"></i>
                                <p class="text-sm sm:text-base">User</p>
                                <p class="text-xs text-gray-500 mt-1">Schedule pickups</p>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Driver accounts require special authorization. Contact us for driver registration.</p>
                    </div>
                    <button type="button" id="step-1-next" class="w-full bg-primary hover:bg-primaryDark text-white py-2 sm:py-3 rounded-lg font-semibold transition duration-300 text-sm sm:text-base">Continue</button>
                </div>
                
                <!-- Step 2: Personal Information -->
                <div id="signup-step-2" class="hidden">
                    <h3 class="text-xl sm:text-2xl font-bold text-dark mb-4 sm:mb-6">Personal Information</h3>
                    <form id="signup-form-data">
                        <input type="hidden" name="action" value="register">
                        <input type="hidden" name="ajax" value="true">
                        
                        <div class="mb-3 sm:mb-4">
                            <label class="block text-gray-700 mb-1 sm:mb-2 text-sm sm:text-base" for="signup-name">Full Name</label>
                            <input type="text" id="signup-name" name="full_name" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm sm:text-base" placeholder="Enter your full name" required>
                        </div>
                        <div class="mb-3 sm:mb-4">
                            <label class="block text-gray-700 mb-1 sm:mb-2 text-sm sm:text-base" for="signup-email">Email Address</label>
                            <input type="email" id="signup-email" name="email" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm sm:text-base" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3 sm:mb-4">
                            <label class="block text-gray-700 mb-1 sm:mb-2 text-sm sm:text-base" for="signup-phone">Phone Number</label>
                            <input type="tel" id="signup-phone" name="phone" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm sm:text-base" placeholder="Enter your phone number" required>
                        </div>
                        <div class="mb-3 sm:mb-4">
                            <label class="block text-gray-700 mb-1 sm:mb-2 text-sm sm:text-base" for="signup-password">Password</label>
                            <input type="password" id="signup-password" name="password" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm sm:text-base" placeholder="Create a password" required>
                            <div class="password-strength-meter mt-2">
                                <div id="password-strength-bar" class="bg-red-500" style="width: 0%"></div>
                            </div>
                            <p id="password-strength-text" class="text-xs text-gray-500 mt-1">Password strength: <span>Very weak</span></p>
                        </div>
                        <div class="mb-4 sm:mb-6">
                            <label class="block text-gray-700 mb-1 sm:mb-2 text-sm sm:text-base" for="signup-confirm-password">Confirm Password</label>
                            <input type="password" id="signup-confirm-password" name="confirm_password" class="w-full px-3 sm:px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm sm:text-base" placeholder="Confirm your password" required>
                        </div>
                        
                        <div class="flex space-x-2 sm:space-x-4">
                            <button type="button" id="step-2-back" class="w-1/3 border border-gray-300 text-gray-700 py-2 sm:py-3 rounded-lg font-semibold transition duration-300 text-sm sm:text-base">Back</button>
                            <button type="button" id="step-2-next" class="w-2/3 bg-primary hover:bg-primaryDark text-white py-2 sm:py-3 rounded-lg font-semibold transition duration-300 text-sm sm:text-base">Continue</button>
                        </div>
                    </form>
                </div>
                
                <!-- Step 3: Verification -->
                <div id="signup-step-3" class="hidden">
                    <h3 class="text-xl sm:text-2xl font-bold text-dark mb-4 sm:mb-6">Account Verification</h3>
                    <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-blue-50 rounded-lg">
                        <p class="text-blue-800 text-xs sm:text-sm">We've sent a 6-digit verification code to your email. Please check your inbox and enter the code below.</p>
                    </div>
                    <form id="verification-form-data" method="POST">
                        <input type="hidden" name="action" value="verify_email">
                        <input type="hidden" name="ajax" value="true">
                        <input type="hidden" name="email" id="verification-email">
                        
                        <div class="mb-4 sm:mb-6">
                            <label class="block text-gray-700 mb-2 text-sm sm:text-base">Verification Code</label>
                            <div class="flex space-x-2 justify-center" id="verification-code-container">
                                <input type="text" maxlength="1" class="w-10 h-10 sm:w-12 sm:h-12 text-center border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-xl" data-index="0" required>
                                <input type="text" maxlength="1" class="w-10 h-10 sm:w-12 sm:h-12 text-center border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-xl" data-index="1" required>
                                <input type="text" maxlength="1" class="w-10 h-10 sm:w-12 sm:h-12 text-center border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-xl" data-index="2" required>
                                <input type="text" maxlength="1" class="w-10 h-10 sm:w-12 sm:h-12 text-center border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-xl" data-index="3" required>
                                <input type="text" maxlength="1" class="w-10 h-10 sm:w-12 sm:h-12 text-center border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-xl" data-index="4" required>
                                <input type="text" maxlength="1" class="w-10 h-10 sm:w-12 sm:h-12 text-center border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-xl" data-index="5" required>
                            </div>
                            <input type="hidden" name="verification_code" id="verification-code-input">
                        </div>
                        
                        <div class="mb-3 sm:mb-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="terms" name="terms" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded" required>
                                <label for="terms" class="ml-2 block text-xs sm:text-sm text-gray-700">I agree to the <a href="#" class="text-primary hover:underline">Terms</a> and <a href="#" class="text-primary hover:underline">Privacy Policy</a></label>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2 sm:space-x-4">
                            <button type="button" id="step-3-back" class="w-1/3 border border-gray-300 text-gray-700 py-2 sm:py-3 rounded-lg font-semibold transition duration-300 text-sm sm:text-base">Back</button>
                            <button type="submit" class="w-2/3 bg-primary hover:bg-primaryDark text-white py-2 sm:py-3 rounded-lg font-semibold transition duration-300 text-sm sm:text-base">Verify Account</button>
                        </div>
                    </form>
                    
                    <div class="mt-3 sm:mt-4 text-center">
                        <p class="text-gray-600 text-xs sm:text-sm">Didn't receive the code? <a href="#" id="resend-code" class="text-primary hover:underline">Resend</a></p>
                    </div>
                </div>
                
                <div class="mt-3 sm:mt-4 text-center hidden" id="signup-login-link">
                    <p class="text-gray-600 text-xs sm:text-sm">Already have an account? <a href="#" id="switch-to-login" class="text-primary hover:underline">Login</a></p>
                </div>
            </div>
            
            <!-- Close Button -->
            <button id="close-auth-modal" class="absolute top-2 right-2 sm:top-4 sm:right-4 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero-pattern py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-dark mb-6">Smart Garbage Collection for a Cleaner City</h1>
                <p class="text-xl text-gray-600 mb-10">Schedule pickups, track services, and keep your community clean with our modern waste management platform.</p>
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <?php if (!$is_logged_in): ?>
                        <a href="#" id="hero-signup-btn" class="bg-primary hover:bg-primaryDark text-white px-8 py-4 rounded-lg font-semibold text-lg shadow-lg transition duration-300">Get Started</a>
                    <?php endif; ?>
                    <a href="#" class="border-2 border-primary text-primary hover:bg-primary hover:text-white px-8 py-4 rounded-lg font-semibold text-lg transition duration-300">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-dark mb-12">How It Works</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-light p-8 rounded-xl text-center shadow-sm hover:shadow-md transition duration-300">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-calendar-check text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-dark mb-4">Schedule Pickups</h3>
                    <p class="text-gray-600">Easily schedule garbage collection times that work for you through our simple platform.</p>
                </div>
                
                <div class="bg-light p-8 rounded-xl text-center shadow-sm hover:shadow-md transition duration-300">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-map-marker-alt text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-dark mb-4">Track Services</h3>
                    <p class="text-gray-600">Monitor collection trucks in real-time and receive notifications about service status.</p>
                </div>
                
                <div class="bg-light p-8 rounded-xl text-center shadow-sm hover:shadow-md transition duration-300">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-recycle text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-dark mb-4">Eco-Friendly</h3>
                    <p class="text-gray-600">Our optimized routes and schedules reduce carbon emissions and promote sustainability.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-primary">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-white mb-6">Ready to make your city cleaner?</h2>
            <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">Join thousands of satisfied residents and businesses using EcoCollect for efficient waste management.</p>
            <?php if (!$is_logged_in): ?>
                <a href="#" id="cta-signup-btn" class="bg-white text-primary hover:bg-gray-100 px-8 py-4 rounded-lg font-semibold text-lg shadow-lg transition duration-300">Sign Up Now</a>
            <?php else: ?>
                <a href="#" class="bg-white text-primary hover:bg-gray-100 px-8 py-4 rounded-lg font-semibold text-lg shadow-lg transition duration-300">Schedule a Pickup</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center mr-3">
                            <i class="fas fa-trash-alt text-white"></i>
                        </div>
                        <span class="text-2xl font-bold text-white">Eco<span class="text-primary">Collect</span></span>
                    </div>
                    <p class="text-gray-400 mb-4">Smart waste management solutions for modern cities.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Quick Links</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Services</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Pricing</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Schedule</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Services</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white">Residential Pickup</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Commercial Solutions</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Recycling Programs</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Hazardous Waste</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Composting</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Contact Us</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt text-primary mt-1 mr-3"></i>
                            <span class="text-gray-400">123 Green Street, Eco City</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-phone text-primary mt-1 mr-3"></i>
                            <span class="text-gray-400">+1 (555) 123-4567</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope text-primary mt-1 mr-3"></i>
                            <span class="text-gray-400">info@ecocollect.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2025 EcoCollect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu functionality
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Authentication modal functionality
        const authModal = document.getElementById('auth-modal');
        const loginBtn = document.getElementById('login-btn');
        const signupBtn = document.getElementById('signup-btn');
        const mobileLoginBtn = document.getElementById('mobile-login-btn');
        const mobileSignupBtn = document.getElementById('mobile-signup-btn');
        const heroSignupBtn = document.getElementById('hero-signup-btn');
        const ctaSignupBtn = document.getElementById('cta-signup-btn');
        const closeAuthModal = document.getElementById('close-auth-modal');
        const loginTab = document.getElementById('login-tab');
        const signupTab = document.getElementById('signup-tab');
        const loginForm = document.getElementById('login-form');
        const signupForm = document.getElementById('signup-form');
        const switchToSignup = document.getElementById('switch-to-signup');
        const switchToLogin = document.getElementById('switch-to-login');
        
        // Signup steps
        const signupStep1 = document.getElementById('signup-step-1');
        const signupStep2 = document.getElementById('signup-step-2');
        const signupStep3 = document.getElementById('signup-step-3');
        const step1Next = document.getElementById('step-1-next');
        const step2Back = document.getElementById('step-2-back');
        const step2Next = document.getElementById('step-2-next');
        const step3Back = document.getElementById('step-3-back');
        const step1Indicator = document.getElementById('step-1-indicator');
        const step2Indicator = document.getElementById('step-2-indicator');
        const step3Indicator = document.getElementById('step-3-indicator');
        const signupLoginLink = document.getElementById('signup-login-link');
        
        // User type selection
        let userType = 'user';
        const userTypeButtons = document.querySelectorAll('.user-type-btn');
        const loginUserTypeInput = document.getElementById('login-user-type');
        
        // Function to show login form
        function showLoginForm() {
            loginTab.classList.add('active');
            signupTab.classList.remove('active');
            loginForm.classList.remove('hidden');
            signupForm.classList.add('hidden');
            resetSignupForm();
        }

        // Function to show signup form
        function showSignupForm() {
            signupTab.classList.add('active');
            loginTab.classList.remove('active');
            signupForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
        }

        // Reset signup form to step 1
        function resetSignupForm() {
            signupStep1.classList.remove('hidden');
            signupStep2.classList.add('hidden');
            signupStep3.classList.add('hidden');
            step1Indicator.classList.add('completed');
            step2Indicator.classList.remove('active', 'completed');
            step3Indicator.classList.remove('active', 'completed');
            signupLoginLink.classList.add('hidden');
            
            // Reset form fields
            document.getElementById('signup-form-data').reset();
            document.getElementById('verification-form-data').reset();
            document.getElementById('password-strength-bar').style.width = '0%';
            document.getElementById('password-strength-text').querySelector('span').textContent = 'Very weak';
        }

        // Open modal with login form
        function openLoginModal() {
            authModal.classList.add('active');
            showLoginForm();
        }

        // Open modal with signup form
        function openSignupModal() {
            authModal.classList.add('active');
            showSignupForm();
            resetSignupForm();
        }

        // Event listeners for login buttons
        if (loginBtn) loginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openLoginModal();
        });

        if (mobileLoginBtn) mobileLoginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('mobile-menu').classList.add('hidden');
            openLoginModal();
        });

        // Event listeners for signup buttons
        if (signupBtn) signupBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openSignupModal();
        });

        if (mobileSignupBtn) mobileSignupBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('mobile-menu').classList.add('hidden');
            openSignupModal();
        });

        if (heroSignupBtn) heroSignupBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openSignupModal();
        });

        if (ctaSignupBtn) ctaSignupBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openSignupModal();
        });

        // Close modal
        closeAuthModal.addEventListener('click', function() {
            authModal.classList.remove('active');
        });

        // Click outside modal to close
        authModal.addEventListener('click', function(e) {
            if (e.target === authModal) {
                authModal.classList.remove('active');
            }
        });

        // Switch between login and signup forms
        loginTab.addEventListener('click', showLoginForm);
        signupTab.addEventListener('click', function() {
            showSignupForm();
            resetSignupForm();
        });
        switchToSignup.addEventListener('click', function(e) {
            e.preventDefault();
            showSignupForm();
            resetSignupForm();
        });
        switchToLogin.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginForm();
        });

        // User type selection (only for login)
        userTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Only allow selection if we're in the login form
                if (loginForm.classList.contains('hidden')) return;
                
                userTypeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                userType = this.getAttribute('data-type');
                loginUserTypeInput.value = userType;
            });
        });

        // Signup form steps
        step1Next.addEventListener('click', function() {
            signupStep1.classList.add('hidden');
            signupStep2.classList.remove('hidden');
            step2Indicator.classList.add('active');
        });

        step2Back.addEventListener('click', function() {
            signupStep2.classList.add('hidden');
            signupStep1.classList.remove('hidden');
            step2Indicator.classList.remove('active');
        });

        step2Next.addEventListener('click', function() {
            // Basic form validation
            const name = document.getElementById('signup-name').value;
            const email = document.getElementById('signup-email').value;
            const phone = document.getElementById('signup-phone').value;
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-confirm-password').value;
            
            if (!name || !email || !phone || !password || !confirmPassword) {
                showAlert('Please fill in all required fields', 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                showAlert('Passwords do not match', 'error');
                return;
            }
            
            if (password.length < 8) {
                showAlert('Password must be at least 8 characters long', 'error');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address', 'error');
                return;
            }
            
            // Phone validation (basic)
            const phoneRegex = /^[\+]?[1-9][0-9]{7,14}$/;
            if (!phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''))) {
                showAlert('Please enter a valid phone number', 'error');
                return;
            }
            
            // AJAX registration
            const formData = new FormData(document.getElementById('signup-form-data'));
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('verification-email').value = email;
                    signupStep2.classList.add('hidden');
                    signupStep3.classList.remove('hidden');
                    step2Indicator.classList.add('completed');
                    step3Indicator.classList.add('active');
                    signupLoginLink.classList.remove('hidden');
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred during registration. Please try again.', 'error');
            });
        });

        step3Back.addEventListener('click', function() {
            signupStep3.classList.add('hidden');
            signupStep2.classList.remove('hidden');
            step3Indicator.classList.remove('active');
            step2Indicator.classList.add('active');
            signupLoginLink.classList.add('hidden');
        });

        // Login form submission
        document.getElementById('login-form-data').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        authModal.classList.remove('active');
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred during login. Please try again.', 'error');
            });
        });

        // Verification form submission
        document.getElementById('verification-form-data').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Combine verification code inputs
            const codeInputs = document.querySelectorAll('#verification-code-container input');
            const verificationCode = Array.from(codeInputs).map(input => input.value).join('');
            
            if (verificationCode.length !== 6) {
                showAlert('Please enter the complete 6-digit verification code', 'error');
                return;
            }
            
            const formData = new FormData(this);
            formData.set('verification_code', verificationCode);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        authModal.classList.remove('active');
                        showLoginForm();
                        resetSignupForm();
                    }, 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred during verification. Please try again.', 'error');
            });
        });

        // Resend verification code
        document.getElementById('resend-code').addEventListener('click', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('verification-email').value;
            if (!email) {
                showAlert('Email not found. Please restart the registration process.', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'resend_verification');
            formData.append('email', email);
            formData.append('ajax', 'true');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.message, data.success ? 'success' : 'error');
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while resending the code. Please try again.', 'error');
            });
        });

        // Logout functionality
        const logoutBtn = document.getElementById('logout-btn');
        const mobileLogoutBtn = document.getElementById('mobile-logout-btn');
        
        function handleLogout() {
            const formData = new FormData();
            formData.append('action', 'logout');
            formData.append('ajax', 'true');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred during logout. Please try again.', 'error');
            });
        }
        
        if (logoutBtn) logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleLogout();
        });
        
        if (mobileLogoutBtn) mobileLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleLogout();
        });

        // Password strength meter
        const passwordInput = document.getElementById('signup-password');
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('password-strength-text').querySelector('span');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check password strength
            if (password.length >= 8) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            if (/[^A-Za-z0-9]/.test(password)) strength += 25;
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            
            // Update strength text
            if (strength === 0) {
                strengthBar.className = 'bg-red-500';
                strengthText.textContent = 'Very weak';
            } else if (strength <= 25) {
                strengthBar.className = 'bg-red-500';
                strengthText.textContent = 'Weak';
            } else if (strength <= 50) {
                strengthBar.className = 'bg-yellow-500';
                strengthText.textContent = 'Fair';
            } else if (strength <= 75) {
                strengthBar.className = 'bg-blue-500';
                strengthText.textContent = 'Good';
            } else {
                strengthBar.className = 'bg-green-500';
                strengthText.textContent = 'Strong';
            }
        });

        // Auto-advance verification code inputs
        const verificationInputs = document.querySelectorAll('#verification-code-container input');
        verificationInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && index < verificationInputs.length - 1) {
                    verificationInputs[index + 1].focus();
                }
                
                // Auto-submit when all fields are filled
                const allFilled = Array.from(verificationInputs).every(input => input.value.length === 1);
                if (allFilled) {
                    document.getElementById('verification-form-data').dispatchEvent(new Event('submit'));
                }
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    verificationInputs[index - 1].focus();
                }
            });
        });

        // Alert function
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `fixed top-4 right-4 z-50 max-w-sm alert alert-${type}`;
            alertDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg">&times;</button>
                </div>
            `;
            document.body.appendChild(alertDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Close mobile menu when clicking on a link
        const mobileLinks = document.querySelectorAll('#mobile-menu a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                document.getElementById('mobile-menu').classList.add('hidden');
            });
        });

        // Prevent form submission on Enter key in verification inputs
        document.querySelectorAll('#verification-code-container input').forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>