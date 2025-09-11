<?php
require_once 'config.php';

// Initialize messages
$success_message = '';
$error_message = '';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    try {
        // Validate inputs
        $username = sanitize_input($_POST['username'] ?? '');
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = sanitize_input($_POST['role'] ?? 'user');

        // Required fields
        if (empty($username) || empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
            throw new Exception("All fields are required.");
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Validate password
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long.");
        }
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }

        // Validate role (restrict admin role)
        if ($role === 'admin' && (!isset($_POST['admin_key']) || $_POST['admin_key'] !== 'SECRET_ADMIN_KEY')) {
            throw new Exception("Invalid admin key. Cannot register as admin.");
        }

        // Check for duplicate username or email
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $stmt->close();

        if ($count > 0) {
            throw new Exception("Username or email already exists.");
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $sql = "INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $password_hash, $full_name, $email, $role);
        
        if ($stmt->execute()) {
            $success_message = "Registration successful! Please <a href='login.php'>log in</a>.";
        } else {
            throw new Exception("Error registering user: " . $conn->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-user-plus"></i> Sign Up</h2>
    </div>
    
    <div class="card-body">
        <!-- Display messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <!-- Sign Up Form -->
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input type="text" id="username" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-control" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="role">Role <span class="required">*</span></label>
                <select id="role" name="role" class="form-control">
                    <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="admin_key">Admin Key (required for admin role)</label>
                <input type="text" id="admin_key" name="admin_key" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Sign Up
            </button>
            <p class="mt-3">Already have an account? <a href="login.php">Log in</a></p>
        </form>
    </div>
</div>

<style>
.required {
    color: #dc3545;
}
.form-control {
    padding: 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    width: 100%;
}
.form-group {
    margin-bottom: 1.5rem;
}
.mt-3 {
    margin-top: 1rem;
}
</style>

<?php include 'includes/footer.php'; ?>