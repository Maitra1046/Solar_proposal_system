<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Janeta Solar - Project Proposal System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/logo.png" alt="Janeta Solar Logo">
            <h1>Janeta Solar</h1>
        </div>
        <nav>
            <ul>
                <?php if (is_logged_in()): ?>
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="new_proposal.php"><i class="fas fa-plus-circle"></i> New Proposal</a></li>
                    <li><a href="proposals.php"><i class="fas fa-file-alt"></i> All Proposals</a></li>
                    <?php if (is_admin()): ?>
                        <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                        <li><a href="materials.php"><i class="fas fa-boxes"></i> Materials</a></li>
                        <li><a href="users.php"><i class="fas fa-user-cog"></i> Users</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container">