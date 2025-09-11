<?php
require_once 'config.php';
require_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('proposals.php');
}

$project_id = (int)$_GET['id'];

$sql_project = "SELECT p.*, c.customer_name, c.email, c.phone, c.address
                FROM projects p
                JOIN customers c ON p.customer_id = c.customer_id
                WHERE p.project_id = ?";
$stmt_project = $conn->prepare($sql_project);
$stmt_project->bind_param("i", $project_id);
$stmt_project->execute();
$result_project = $stmt_project->get_result();

if ($result_project->num_rows === 0) {
    redirect('proposals.php');
}

$project = $result_project->fetch_assoc();
$stmt_project->close();

$sql_materials = "SELECT * FROM materials WHERE project_id = ?";
$stmt_materials = $conn->prepare($sql_materials);
$stmt_materials->bind_param("i", $project_id);
$stmt_materials->execute();
$result_materials = $stmt_materials->get_result();
$stmt_materials->close();

$sql_activities = "SELECT * FROM activities WHERE project_id = ?";
$stmt_activities = $conn->prepare($sql_activities);
$stmt_activities->bind_param("i", $project_id);
$stmt_activities->execute();
$result_activities = $stmt_activities->get_result();
$stmt_activities->close();

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-file-alt"></i> Project Proposal #<?php echo $project['project_id']; ?></h2>
        <div>
            <a href="edit_proposal.php?id=<?php echo $project_id; ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="preview_proposal.php?id=<?php echo $project_id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-file-pdf"></i> Preview
            </a>
            <a href="proposals.php" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (isset($_GET['created']) || isset($_GET['updated'])): ?>
            <div class="alert alert-success">
                Proposal <?php echo isset($_GET['created']) ? 'created' : 'updated'; ?> successfully!
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h3>Customer Information</h3>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($project['customer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($project['email'] ?: 'N/A'); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($project['phone'] ?: 'N/A'); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($project['address'] ?: 'N/A'); ?></p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h3>Project Details</h3>
            </div>
            <div class="card-body">
                <p><strong>Capacity:</strong> <?php echo $project['project_capacity']; ?> KW</p>
                <p><strong>Date:</strong> <?php echo date('d-m-Y', strtotime($project['project_date'])); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($project['project_location']); ?></p>
                <p><strong>Status:</strong> <span class="badge <?php echo ($project['status'] == 'Completed') ? 'badge-success' : 'badge-warning'; ?>"><?php echo $project['status']; ?></span></p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h3>Project Cost</h3>
            </div>
            <div class="card-body">
                <p><strong>Effective Cost:</strong> ₹<?php echo number_format($project['effective_cost'], 2); ?></p>
                <p><strong>Subsidy Amount:</strong> ₹<?php echo number_format($project['subsidy_amount'], 2); ?></p>
                <p><strong>Net Landing Cost:</strong> ₹<?php echo number_format($project['net_landing_cost'], 2); ?></p>
                <p><strong>DISCOM Meter Charge:</strong> <?php echo $project['discom_meter_charge']; ?></p>
                <p><strong>Transportation Charge:</strong> <?php echo $project['transportation_charge']; ?></p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h3>Material Description</h3>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($material = $result_materials->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($material['description']); ?></td>
                                    <td><?php echo htmlspecialchars($material['unit']); ?></td>
                                    <td><?php echo $material['quantity']; ?></td>
                                    <td><?php echo htmlspecialchars($material['size'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($material['manufacturer'] ?: 'N/A'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Activities/Services</h3>
            </div>
            <div class="card-body">
                <?php while ($activity = $result_activities->fetch_assoc()): ?>
                    <div class="mb-3">
                        <h4><?php echo htmlspecialchars($activity['activity_name']); ?></h4>
                        <p><?php echo nl2br(htmlspecialchars($activity['activity_details'])); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>