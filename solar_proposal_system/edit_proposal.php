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

$sql_customers = "SELECT customer_id, customer_name FROM customers ORDER BY customer_name";
$result_customers = $conn->query($sql_customers);

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

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        
        $customer_id = isset($_POST['existing_customer']) ? (int)$_POST['existing_customer'] : null;
        
        if (empty($customer_id) && !empty($_POST['customer_name'])) {
            $customer_name = sanitize_input($_POST['customer_name']);
            $customer_email = sanitize_input($_POST['customer_email'] ?? '');
            $customer_phone = sanitize_input($_POST['customer_phone'] ?? '');
            $customer_address = sanitize_input($_POST['customer_address'] ?? '');
            
            $sql_insert_customer = "INSERT INTO customers (customer_name, email, phone, address) VALUES (?, ?, ?, ?)";
            $stmt_customer = $conn->prepare($sql_insert_customer);
            $stmt_customer->bind_param("ssss", $customer_name, $customer_email, $customer_phone, $customer_address);
            $stmt_customer->execute();
            
            $customer_id = $conn->insert_id;
            $stmt_customer->close();
        }
        
        $project_capacity = sanitize_input($_POST['project_capacity']);
        $project_date = sanitize_input($_POST['project_date']);
        $project_location = sanitize_input($_POST['project_location']);
        $effective_cost = sanitize_input($_POST['effective_cost']);
        $subsidy_amount = sanitize_input($_POST['subsidy_amount']);
        $net_landing_cost = sanitize_input($_POST['net_landing_cost']);
        $discom_meter_charge = sanitize_input($_POST['discom_meter_charge'] ?? 'INCLUDED');
        $transportation_charge = sanitize_input($_POST['transportation_charge'] ?? 'INCLUDED');
        $status = sanitize_input($_POST['status'] ?? 'Pending');
        
        $sql_update_project = "UPDATE projects SET customer_id = ?, project_capacity = ?, project_date = ?, project_location = ?, effective_cost = ?, subsidy_amount = ?, net_landing_cost = ?, discom_meter_charge = ?, transportation_charge = ?, status = ? WHERE project_id = ?";
        $stmt_project = $conn->prepare($sql_update_project);
        $stmt_project->bind_param("idssddssssi", $customer_id, $project_capacity, $project_date, $project_location, $effective_cost, $subsidy_amount, $net_landing_cost, $discom_meter_charge, $transportation_charge, $status, $project_id);
        $stmt_project->execute();
        $stmt_project->close();
        
        $sql_delete_materials = "DELETE FROM materials WHERE project_id = ?";
        $stmt_delete_materials = $conn->prepare($sql_delete_materials);
        $stmt_delete_materials->bind_param("i", $project_id);
        $stmt_delete_materials->execute();
        $stmt_delete_materials->close();
        
        if (isset($_POST['material_description']) && is_array($_POST['material_description'])) {
            $material_descriptions = $_POST['material_description'];
            $material_units = $_POST['material_unit'];
            $material_quantities = $_POST['material_quantity'];
            $material_sizes = $_POST['material_size'];
            $material_manufacturers = $_POST['material_manufacturer'];
            
            $sql_insert_material = "INSERT INTO materials (project_id, description, unit, quantity, size, manufacturer) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_material = $conn->prepare($sql_insert_material);
            
            for ($i = 0; $i < count($material_descriptions); $i++) {
                if (!empty($material_descriptions[$i])) {
                    $description = sanitize_input($material_descriptions[$i]);
                    $unit = sanitize_input($material_units[$i]);
                    $quantity = (int)$material_quantities[$i];
                    $size = sanitize_input($material_sizes[$i] ?? '');
                    $manufacturer = sanitize_input($material_manufacturers[$i] ?? '');
                    
                    $stmt_material->bind_param("ississ", $project_id, $description, $unit, $quantity, $size, $manufacturer);
                    $stmt_material->execute();
                }
            }
            
            $stmt_material->close();
        }
        
        $sql_delete_activities = "DELETE FROM activities WHERE project_id = ?";
        $stmt_delete_activities = $conn->prepare($sql_delete_activities);
        $stmt_delete_activities->bind_param("i", $project_id);
        $stmt_delete_activities->execute();
        $stmt_delete_activities->close();
        
        if (isset($_POST['activity_name']) && is_array($_POST['activity_name'])) {
            $activity_names = $_POST['activity_name'];
            $activity_details = $_POST['activity_details'];
            
            $sql_insert_activity = "INSERT INTO activities (project_id, activity_name, activity_details) VALUES (?, ?, ?)";
            $stmt_activity = $conn->prepare($sql_insert_activity);
            
            for ($i = 0; $i < count($activity_names); $i++) {
                if (!empty($activity_names[$i])) {
                    $name = sanitize_input($activity_names[$i]);
                    $details = sanitize_input($activity_details[$i]);
                    
                    $stmt_activity->bind_param("iss", $project_id, $name, $details);
                    $stmt_activity->execute();
                }
            }
            
            $stmt_activity->close();
        }
        
        $conn->commit();
        $success_message = "Proposal updated successfully!";
        
        redirect("view_proposal.php?id=" . $project_id . "&updated=1");
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error updating proposal: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-edit"></i> Edit Project Proposal #<?php echo $project_id; ?></h2>
    </div>
    
    <div class="card-body">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Customer Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Customer Type</label>
                        <div>
                            <label style="margin-right: 1rem;">
                                <input type="radio" name="customer_type" value="existing" checked> Existing Customer
                            </label>
                            <label>
                                <input type="radio" name="customer_type" value="new"> New Customer
                            </label>
                        </div>
                    </div>
                    
                    <div id="existing-customer-section">
                        <div class="form-group">
                            <label for="existing_customer">Select Customer</label>
                            <select id="existing_customer" name="existing_customer" required>
                                <option value="">-- Select Customer --</option>
                                <?php while ($customer = $result_customers->fetch_assoc()): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>" <?php echo $customer['customer_id'] == $project['customer_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($customer['customer_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div id="new-customer-section" style="display: none;">
                        <div class="form-group">
                            <label for="customer_name">Customer Name*</label>
                            <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($project['customer_name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_email">Email</label>
                            <input type="email" id="customer_email" name="customer_email" value="<?php echo htmlspecialchars($project['email']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_phone">Phone</label>
                            <input type="text" id="customer_phone" name="customer_phone" value="<?php echo htmlspecialchars($project['phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_address">Address</label>
                            <textarea id="customer_address" name="customer_address" rows="3"><?php echo htmlspecialchars($project['address']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Project Details</h3>
                </div>
                <div class="card-body">
                    <div class="project-details-grid">
                        <div class="form-group">
                            <label for="project_capacity">Project Capacity (KW)*</label>
                            <input type="number" id="project_capacity" name="project_capacity" step="0.01" value="<?php echo $project['project_capacity']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="project_date">Project Date*</label>
                            <input type="date" id="project_date" name="project_date" value="<?php echo $project['project_date']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="project_location">Project Location*</label>
                            <input type="text" id="project_location" name="project_location" value="<?php echo htmlspecialchars($project['project_location']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Pending" <?php echo $project['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo $project['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed" <?php echo $project['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?php echo $project['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Project Cost</h3>
                </div>
                <div class="card-body">
                    <div class="project-cost-grid">
                        <div class="form-group">
                            <label for="effective_cost">Effective Project Cost (₹)*</label>
                            <input type="number" id="effective_cost" name="effective_cost" step="0.01" value="<?php echo $project['effective_cost']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subsidy_amount">Subsidy by DBT to Client (₹)*</label>
                            <input type="number" id="subsidy_amount" name="subsidy_amount" step="0.01" value="<?php echo $project['subsidy_amount']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="net_landing_cost">Net Landing Cost to Client (₹)*</label>
                            <input type="number" id="net_landing_cost" name="net_landing_cost" step="0.01" value="<?php echo $project['net_landing_cost']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="discom_meter_charge">DISCOM Net Meter Charge</label>
                            <select id="discom_meter_charge" name="discom_meter_charge">
                                <option value="INCLUDED" <?php echo $project['discom_meter_charge'] == 'INCLUDED' ? 'selected' : ''; ?>>INCLUDED</option>
                                <option value="NOT INCLUDED" <?php echo $project['discom_meter_charge'] == 'NOT INCLUDED' ? 'selected' : ''; ?>>NOT INCLUDED</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="transportation_charge">Transportation and Freight Charge</label>
                            <select id="transportation_charge" name="transportation_charge">
                                <option value="INCLUDED" <?php echo $project['transportation_charge'] == 'INCLUDED' ? 'selected' : ''; ?>>INCLUDED</option>
                                <option value="NOT INCLUDED" <?php echo $project['transportation_charge'] == 'NOT INCLUDED' ? 'selected' : ''; ?>>NOT INCLUDED</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Material Description</h3>
                </div>
                <div class="card-body">
                    <div class="material-header">
                        <div>Description</div>
                        <div>Unit</div>
                        <div>Qty</div>
                        <div>Size</div>
                        <div>Manufacturer/Make/Details</div>
                        <div></div>
                    </div>
                    
                    <div id="materials-container">
                        <?php 
                        $i = 0;
                        while ($material = $result_materials->fetch_assoc()):
                            $i++;
                        ?>
                        <div class="material-row">
                            <input type="text" name="material_description[]" value="<?php echo htmlspecialchars($material['description']); ?>" required>
                            <input type="text" name="material_unit[]" value="<?php echo htmlspecialchars($material['unit']); ?>" required>
                            <input type="number" name="material_quantity[]" value="<?php echo $material['quantity']; ?>" required>
                            <input type="text" name="material_size[]" value="<?php echo htmlspecialchars($material['size']); ?>">
                            <input type="text" name="material_manufacturer[]" value="<?php echo htmlspecialchars($material['manufacturer']); ?>">
                            <span class="remove-material" title="Remove"><i class="fas fa-trash"></i></span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <input type="hidden" id="material-row-count" value="<?php echo $i; ?>">
                    
                    <button type="button" id="add-material-btn" class="btn btn-secondary mt-3">
                        <i class="fas fa-plus"></i> Add Material
                    </button>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Activities/Services</h3>
                </div>
                <div class="card-body">
                    <?php while ($activity = $result_activities->fetch_assoc()): ?>
                    <div class="form-group mb-3">
                        <label>Activity Name</label>
                        <input type="text" name="activity_name[]" value="<?php echo htmlspecialchars($activity['activity_name']); ?>" required>
                        <label class="mt-2">Details</label>
                        <textarea name="activity_details[]" rows="3"><?php echo htmlspecialchars($activity['activity_details']); ?></textarea>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary mr-2">
                    <i class="fas fa-save"></i> Update Proposal
                </button>
                <a href="view_proposal.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>