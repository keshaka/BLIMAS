<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

class AdminAuth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Create admin user (for initial setup)
    public function createAdmin($username, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $this->db->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
            return $stmt->execute([$username, $hashedPassword]);
        } catch (PDOException $e) {
            error_log("Error creating admin: " . $e->getMessage());
            return false;
        }
    }
    
    // Login function
    public function login($username, $password, $remember = false) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_login_time'] = time();
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie(ADMIN_COOKIE_NAME, $token, time() + ADMIN_COOKIE_LIFETIME, '/', '', true, true);
                    
                    // Store token in database (you might want to create a separate table for this)
                    $stmt = $this->db->prepare("UPDATE admin_users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$token, $user['id']]);
                }
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        // Check session
        if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_login_time'])) {
            // Check session timeout
            if (time() - $_SESSION['admin_login_time'] < SESSION_TIMEOUT) {
                $_SESSION['admin_login_time'] = time(); // Refresh session time
                return true;
            } else {
                $this->logout();
                return false;
            }
        }
        
        // Check remember me cookie
        if (isset($_COOKIE[ADMIN_COOKIE_NAME])) {
            $token = $_COOKIE[ADMIN_COOKIE_NAME];
            
            try {
                $stmt = $this->db->prepare("SELECT id, username FROM admin_users WHERE remember_token = ?");
                $stmt->execute([$token]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_login_time'] = time();
                    return true;
                }
            } catch (PDOException $e) {
                error_log("Remember me error: " . $e->getMessage());
            }
        }
        
        return false;
    }
    
    // Logout function
    public function logout() {
        // Clear session
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_login_time']);
        
        // Clear remember me cookie
        if (isset($_COOKIE[ADMIN_COOKIE_NAME])) {
            setcookie(ADMIN_COOKIE_NAME, '', time() - 3600, '/', '', true, true);
            
            // Clear token from database
            if (isset($_SESSION['admin_id'])) {
                try {
                    $stmt = $this->db->prepare("UPDATE admin_users SET remember_token = NULL WHERE id = ?");
                    $stmt->execute([$_SESSION['admin_id']]);
                } catch (PDOException $e) {
                    error_log("Logout error: " . $e->getMessage());
                }
            }
        }
    }
    
    // Get current admin info
    public function getCurrentAdmin() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username']
            ];
        }
        return null;
    }
    
    // Require admin login (redirect if not logged in)
    public function requireAdmin($redirectUrl = '/admin/login.php') {
        if (!$this->isLoggedIn()) {
            header("Location: $redirectUrl");
            exit();
        }
    }
}
?>