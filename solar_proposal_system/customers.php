<?php
require_once 'config.php';
require_admin();

// Initialize messages
$success_message = '';
$error_message = '';

// Generate or validate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    try {
        if ($_POST['action'] === 'add') {
            // Validate required fields
            if (empty($_POST['customer_name'])) {
                throw new Exception("Customer name is required.");
            }

            $customer_name = sanitize_input($_POST['customer_name']);
            $email = sanitize_input($_POST['email'] ?? '');
            $phone = sanitize_input($_POST['phone'] ?? '');
            $address = sanitize_input($_POST['address'] ?? '');

            // Insert new customer
            $sql = "INSERT INTO customers (customer_name, email, phone, address) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $customer_name, $email, $phone, $address);
            
            if ($stmt->execute()) {
                $success_message = "Customer added successfully!";
            } else {
                throw new Exception("Error adding customer: " . $conn->error);
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'delete' && isset($_POST['customer_id'])) {
            $customer_id = (int)$_POST['customer_id'];

            // Check if customer is linked to any projects
            $sql_check = "SELECT COUNT(*) as count FROM projects WHERE customer_id = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("i", $customer_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $count = $result_check->fetch_assoc()['count'];
            $stmt_check->close();

            if ($count > 0) {
                throw new Exception("Cannot delete customer. They are linked to existing projects.");
            }

            // Delete customer
            $sql = "DELETE FROM customers WHERE customer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customer_id);
            
            if ($stmt->execute()) {
                $success_message = "Customer deleted successfully!";
            } else {
                throw new Exception("Error deleting customer: " . $conn->error);
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Fetch all customers
$sql = "SELECT * FROM customers ORDER BY customer_name";
$result = $conn->query($sql);

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-users"></i> Manage Customers</h2>
    </div>
    
    <div class="card-body">
        <!-- Display success or error messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <!-- Add New Customer Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Add New Customer</h3>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group">
                        <label for="customer_name">Customer Name <span class="required">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Customer
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Customer List -->
        <div class="card">
            <div class="card-header">
                <h3>Customer List</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['customer_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['address'] ?: 'N/A'); ?></td>
                                        <td>
                                            <form method="post" action="" onsubmit="return confirmDelete();">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="customer_id" value="<?php echo $row['customer_id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No customers found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this customer?");
}
</script>

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
</style>

<?php include 'includes/footer.php'; ?>