˜<?php
require_once 'config.php';
require_login();

// Get dashboard statistics
$sql_proposals = "SELECT COUNT(*) as total FROM projects";
$result_proposals = $conn->query($sql_proposals);
$total_proposals = $result_proposals->fetch_assoc()['total'];

$sql_customers = "SELECT COUNT(*) as total FROM customers";
$result_customers = $conn->query($sql_customers);
$total_customers = $result_customers->fetch_assoc()['total'];

$sql_capacity = "SELECT SUM(project_capacity) as total FROM projects";
$result_capacity = $conn->query($sql_capacity);
$total_capacity = $result_capacity->fetch_assoc()['total'] ?? 0;

$sql_revenue = "SELECT SUM(effective_cost) as total FROM projects";
$result_revenue = $conn->query($sql_revenue);
$total_revenue = $result_revenue->fetch_assoc()['total'] ?? 0;

$sql_recent = "SELECT p.*, c.customer_name 
               FROM projects p 
               JOIN customers c ON p.customer_id = c.customer_id 
               ORDER BY p.created_at DESC 
               LIMIT 5";
$result_recent = $conn->query($sql_recent);

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
        <div>
            <a href="new_proposal.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Proposal
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <div class="dashboard-cards">
            <div class="stats-card">
                <i class="fas fa-file-alt"></i>
                <h3><?php echo $total_proposals; ?></h3>
                <p>Total Proposals</p>
            </div>
            
            <div class="stats-card">
                <i class="fas fa-users"></i>
                <h3><?php echo $total_customers; ?></h3>
                <p>Total Customers</p>
            </div>
            
            <div class="stats-card">
                <i class="fas fa-solar-panel"></i>
                <h3><?php echo number_format($total_capacity, 2); ?> KW</h3>
                <p>Total Capacity</p>
            </div>
            
            <div class="stats-card">
                <i class="fas fa-rupee-sign"></i>
                <h3><?php echo number_format($total_revenue); ?></h3>
                <p>Total Project Value</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Recent Proposals</h2>
                <div>
                    <a href="proposals.php" class="btn btn-secondary btn-sm">
                        View All
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table>
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
                            <?php if ($result_recent->num_rows > 0): ?>
                                <?php while ($row = $result_recent->fetch_assoc()): ?>
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
    </div>
</div>

<style>
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stats-card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    text-align: center;
}

.stats-card i {
    font-size: 2.5rem;
    color: #007bff;
    margin-bottom: 1rem;
}

.stats-card h3 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #007bff;
}

.stats-card p {
    color: #6c757d;
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