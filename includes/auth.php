<?php
session_start();
include_once 'config/database.php';
include_once 'functions.php';

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Add this missing method
    public function get_current_user() {
        if ($this->is_logged_in()) {
            try {
                if (!$this->conn) {
                    return null;
                }
                
                $query = "SELECT id, email, full_name, user_type, phone_number FROM users WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id", $_SESSION['user_id']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                }
            } catch(PDOException $e) {
                error_log("Get current user error: " . $e->getMessage());
            }
        }
        return null;
    }

    // ... rest of your existing Auth class methods remain the same ...
    // User registration
    public function register($name, $email, $password, $phone, $user_type = 'user') {
        try {
            if (!$this->conn) {
                return ["success" => false, "message" => "Database connection failed"];
            }

            // Check if email already exists
            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ["success" => false, "message" => "Email already exists. Please use a different email."];
            }

            // Validate phone number
            if (!validate_phone($phone)) {
                return ["success" => false, "message" => "Please enter a valid phone number"];
            }

            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $verification_code = generate_verification_code();
            $verification_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Insert user
            $query = "INSERT INTO users (email, password_hash, full_name, phone_number, user_type, verification_code, verification_code_expires_at) 
                     VALUES (:email, :password_hash, :full_name, :phone_number, :user_type, :verification_code, :verification_expires)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password_hash", $password_hash);
            $stmt->bindParam(":full_name", $name);
            $stmt->bindParam(":phone_number", $phone);
            $stmt->bindParam(":user_type", $user_type);
            $stmt->bindParam(":verification_code", $verification_code);
            $stmt->bindParam(":verification_expires", $verification_expires);

            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                $this->create_user_profile($user_id);
                
                // Store verification info in session for demo purposes
                $_SESSION['pending_verification'] = [
                    'email' => $email,
                    'code' => $verification_code,
                    'user_id' => $user_id
                ];
                
                return [
                    "success" => true, 
                    "message" => "Registration successful! Verification code: " . $verification_code . " (This would be sent via email in production)",
                    "user_id" => $user_id
                ];
            } else {
                return ["success" => false, "message" => "Registration failed. Please try again."];
            }
        } catch(PDOException $exception) {
            error_log("Registration error: " . $exception->getMessage());
            return ["success" => false, "message" => "System error. Please try again later."];
        }
    }

    private function create_user_profile($user_id) {
        try {
            $query = "INSERT INTO user_profiles (user_id, created_at) VALUES (:user_id, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            return true;
        } catch(PDOException $e) {
            error_log("Profile creation error: " . $e->getMessage());
            return false;
        }
    }

    // Verify email
    public function verify_email($email, $code) {
        try {
            if (!$this->conn) {
                return ["success" => false, "message" => "Database connection failed"];
            }

            $query = "SELECT id, verification_code, verification_code_expires_at 
                     FROM users 
                     WHERE email = :email AND verification_code_expires_at > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user['verification_code'] === $code) {
                    // Update user as verified
                    $query = "UPDATE users SET email_verified = TRUE, account_status = 'active', verification_code = NULL, verification_code_expires_at = NULL 
                             WHERE id = :id";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(":id", $user['id']);
                    
                    if ($stmt->execute()) {
                        unset($_SESSION['pending_verification']);
                        return ["success" => true, "message" => "Email verified successfully! You can now login."];
                    }
                }
            }
            
            return ["success" => false, "message" => "Invalid or expired verification code. Please try again."];
        } catch(PDOException $exception) {
            error_log("Verification error: " . $exception->getMessage());
            return ["success" => false, "message" => "System error during verification."];
        }
    }

    // User login
    public function login($email, $password, $user_type) {
        try {
            if (!$this->conn) {
                return ["success" => false, "message" => "Database connection failed"];
            }

            $query = "SELECT id, email, password_hash, full_name, user_type, account_status, email_verified 
                     FROM users 
                     WHERE email = :email AND user_type = :user_type";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":user_type", $user_type);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $user['password_hash'])) {
                    if ($user['account_status'] === 'active') {
                        if ($user['email_verified']) {
                            // Set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_name'] = $user['full_name'];
                            $_SESSION['user_type'] = $user['user_type'];
                            $_SESSION['logged_in'] = true;
                            $_SESSION['login_time'] = time();

                            // Update last login
                            $this->update_last_login($user['id']);

                            return ["success" => true, "message" => "Login successful! Welcome back, " . $user['full_name'] . "!"];
                        } else {
                            return ["success" => false, "message" => "Please verify your email address before logging in."];
                        }
                    } else {
                        return ["success" => false, "message" => "Your account is not active. Please contact support."];
                    }
                }
            }
            
            return ["success" => false, "message" => "Invalid email or password. Please try again."];
        } catch(PDOException $exception) {
            error_log("Login error: " . $exception->getMessage());
            return ["success" => false, "message" => "System error during login."];
        }
    }

    private function update_last_login($user_id) {
        try {
            $query = "UPDATE users SET last_login_at = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $user_id);
            $stmt->execute();
        } catch(PDOException $e) {
            error_log("Last login update error: " . $e->getMessage());
        }
    }

    // Check if user is logged in
    public function is_logged_in() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Logout
    public function logout() {
        session_unset();
        session_destroy();
        return ["success" => true, "message" => "Logged out successfully"];
    }

    // Resend verification code
    public function resend_verification($email) {
        try {
            if (!$this->conn) {
                return ["success" => false, "message" => "Database connection failed"];
            }

            $verification_code = generate_verification_code();
            $verification_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $query = "UPDATE users SET verification_code = :code, verification_code_expires_at = :expires 
                     WHERE email = :email AND email_verified = FALSE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":code", $verification_code);
            $stmt->bindParam(":expires", $verification_expires);
            $stmt->bindParam(":email", $email);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                $_SESSION['pending_verification']['code'] = $verification_code;
                return [
                    "success" => true, 
                    "message" => "Verification code resent: " . $verification_code . " (This would be sent via email in production)"
                ];
            }
            
            return ["success" => false, "message" => "Unable to resend verification code."];
        } catch(PDOException $exception) {
            error_log("Resend verification error: " . $exception->getMessage());
            return ["success" => false, "message" => "System error while resending verification code."];
        }
    }
}

// Handle form submissions
$auth = new Auth();
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = sanitize_input($_POST['action']);
        
        switch ($action) {
            case 'register':
                $name = sanitize_input($_POST['full_name'] ?? '');
                $email = sanitize_input($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $phone = sanitize_input($_POST['phone'] ?? '');
                $user_type = 'user'; // Only user registration allowed for now
                
                if (empty($name) || empty($email) || empty($password) || empty($phone)) {
                    $response = ["success" => false, "message" => "All fields are required"];
                } elseif (!validate_email($email)) {
                    $response = ["success" => false, "message" => "Please enter a valid email address"];
                } elseif (!validate_password($password)) {
                    $response = ["success" => false, "message" => "Password must be at least 8 characters long"];
                } else {
                    $response = $auth->register($name, $email, $password, $phone, $user_type);
                }
                break;

            case 'verify_email':
                $email = sanitize_input($_POST['email'] ?? '');
                $code = sanitize_input($_POST['verification_code'] ?? '');
                
                if (empty($email) || empty($code)) {
                    $response = ["success" => false, "message" => "Email and verification code are required"];
                } else {
                    $response = $auth->verify_email($email, $code);
                }
                break;

            case 'login':
                $email = sanitize_input($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $user_type = sanitize_input($_POST['user_type'] ?? 'user');
                
                if (empty($email) || empty($password)) {
                    $response = ["success" => false, "message" => "Email and password are required"];
                } else {
                    $response = $auth->login($email, $password, $user_type);
                }
                break;

            case 'logout':
                $response = $auth->logout();
                break;

            case 'resend_verification':
                $email = sanitize_input($_POST['email'] ?? '');
                if (empty($email)) {
                    $response = ["success" => false, "message" => "Email is required"];
                } else {
                    $response = $auth->resend_verification($email);
                }
                break;
        }
        
        // Return JSON response for AJAX requests
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else {
            // Store response in session for non-AJAX requests
            $_SESSION['form_response'] = $response;
        }
    }
}

// Check for session response (for non-AJAX form submissions)
if (isset($_SESSION['form_response'])) {
    $response = $_SESSION['form_response'];
    unset($_SESSION['form_response']);
}
?>
