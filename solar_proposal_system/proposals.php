<?php
require_once 'config.php';
require_login();

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

$sql = "SELECT p.*, c.customer_name 
        FROM projects p 
        JOIN customers c ON p.customer_id = c.customer_id 
        WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (c.customer_name LIKE ? OR p.project_location LIKE ? OR p.project_id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status)) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= 's';
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-file-alt"></i> All Proposals</h2>
        <div>
            <a href="new_proposal.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Proposal
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <form method="get" action="" class="mb-4">
            <div class="form-group" style="display: flex; gap: 1rem;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by customer, location, or ID" class="form-control" style="flex: 1;">
                <select name="status" class="form-control" style="width: 200px;">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="In Progress" <?php echo $status == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Completed" <?php echo $status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?php echo $status == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
        
        <div class="table-responsive">
            <table id="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Cost</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['project_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['project_location']); ?></td>
                                <td><?php echo $row['project_capacity']; ?> KW</td>
                                <td>₹<?php echo number_format($row['effective_cost']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['project_date'])); ?></td>
                                <td>
                                    <span class="badge <?php echo ($row['status'] == 'Completed') ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_proposal.php?id=<?php echo $row['project_id']; ?>" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_proposal.php?id=<?php echo $row['project_id']; ?>" class="btn btn-sm btn-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="preview_proposal.php?id=<?php echo $row['project_id']; ?>" class="btn btn-sm btn-primary" title="Preview">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No proposals found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.form-control {
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
}

.badge-success {
    background-color: #28a745;
    color: #fff;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}
</style>

<?php include 'includes/footer.php'; ?>