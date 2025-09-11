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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Preview - Janeta Solar</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            background-color: #fff;
        }
        .preview-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .header-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header-section img {
            height: 80px;
        }
        .section-title {
            font-size: 1.5rem;
            color: #007bff;
            margin-bottom: 1rem;
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.5rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .info-grid p {
            margin: 0.5rem 0;
        }
        table {
            margin-bottom: 1.5rem;
        }
        .print-btn {
            text-align: center;
            margin-top: 2rem;
        }
        @media print {
            .print-btn, .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="header-section">
            <img src="images/logo.png" alt="Janeta Solar Logo">
            <h1>Janeta Solar</h1>
            <p>Project Proposal #<?php echo $project['project_id']; ?></p>
        </div>
        
        <div class="section-title">Customer Information</div>
        <div class="info-grid">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($project['customer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($project['email'] ?: 'N/A'); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($project['phone'] ?: 'N/A'); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($project['address'] ?: 'N/A'); ?></p>
        </div>
        
        <div class="section-title">Project Details</div>
        <div class="info-grid">
            <p><strong>Capacity:</strong> <?php echo $project['project_capacity']; ?> KW</p>
            <p><strong>Date:</strong> <?php echo date('d-m-Y', strtotime($project['project_date'])); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($project['project_location']); ?></p>
            <p><strong>Status:</strong> <?php echo $project['status']; ?></p>
        </div>
        
        <div class="section-title">Project Cost</div>
        <div class="info-grid">
            <p><strong>Effective Cost:</strong> ₹<?php echo number_format($project['effective_cost'], 2); ?></p>
            <p><strong>Subsidy Amount:</strong> ₹<?php echo number_format($project['subsidy_amount'], 2); ?></p>
            <p><strong>Net Landing Cost:</strong> ₹<?php echo number_format($project['net_landing_cost'], 2); ?></p>
            <p><strong>DISCOM Meter Charge:</strong> <?php echo $project['discom_meter_charge']; ?></p>
            <p><strong>Transportation Charge:</strong> <?php echo $project['transportation_charge']; ?></p>
        </div>
        
        <div class="section-title">Material Description</div>
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
        
        <div class="section-title">Activities/Services</div>
        <?php while ($activity = $result_activities->fetch_assoc()): ?>
            <div class="mb-3">
                <h4><?php echo htmlspecialchars($activity['activity_name']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($activity['activity_details'])); ?></p>
            </div>
        <?php endwhile; ?>
        
        <div class="print-btn no-print">
            <button id="generate-pdf" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Proposal
            </button>
            <a href="view_proposal.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <script src="js/scripts.js"></script>
</body>
</html>