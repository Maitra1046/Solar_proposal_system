<?php
require_once 'config.php';
require_admin();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $description = sanitize_input($_POST['description']);
        $unit = sanitize_input($_POST['unit']);
        $quantity = (int)$_POST['quantity'];
        $size = sanitize_input($_POST['size'] ?? '');
        $manufacturer = sanitize_input($_POST['manufacturer'] ?? '');

        $sql = "INSERT INTO materials (description, unit, quantity, size, manufacturer) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiss", $description, $unit, $quantity, $size, $manufacturer);
        if ($stmt->execute()) {
            $success_message = "Material added successfully!";
        } else {
            $error_message = "Error adding material: " . $conn->error;
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'delete' && isset($_POST['material_id'])) {
        $material_id = (int)$_POST['material_id'];
        $sql = "DELETE FROM materials WHERE material_id = ? AND project_id IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $material_id);
        if ($stmt->execute()) {
            $success_message = "Material deleted successfully!";
        } else {
            $error_message = "Error deleting material: " . $conn->error;
        }
        $stmt->close();
    }
}

$sql = "SELECT * FROM materials WHERE project_id IS NULL ORDER BY description";
$result = $conn->query($sql);

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-boxes"></i> Manage Default Materials</h2>
    </div>
    
    <div class="card-body">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h3>Add New Material</h3>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="material-row">
                        <div class="form-group">
                            <input type="text" name="description" placeholder="Description" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="unit" placeholder="Unit" required>
                        </div>
                        <div class="form-group">
                            <input type="number" name="quantity" placeholder="Qty" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="size" placeholder="Size">
                        </div>
                        <div class="form-group">
                            <input type="text" name="manufacturer" placeholder="Manufacturer">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Material
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Material List</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Qty</th>
                                <th>Size</th>
                                <th>Manufacturer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td><?php echo htmlspecialchars($row['size'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['manufacturer'] ?: 'N/A'); ?></td>
                                        <td>
                                            <form method="post" action="" onsubmit="return confirmDelete();">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="material_id" value="<?php echo $row['material_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No materials found</td>
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
    return confirm("Are you sure you want to delete this material?");
}
</script>

<?php include 'includes/footer.php'; ?>