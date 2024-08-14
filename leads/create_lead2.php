<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure that only lead managers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'LeadManager') {
    header('Location: ../login.php');
    exit();
}

$err = "";
$success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $status = filter_var(trim($_POST['status']), FILTER_SANITIZE_STRING);
    $lead_manager_id = (int)$_SESSION['user_id'];

    if (empty($name) || empty($email) || empty($phone) || empty($status)) {
        $err = "Please fill in all fields.";
    } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $name)) {
        $err = "Name can only contain letters, spaces, and basic punctuation.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format.";
    } elseif (!preg_match("/^\d{10}$/", $phone)) {
        $err = "Phone number must be 10 digits.";
    } else {
        $sql = "INSERT INTO leads (name, email, phone, status, created_at, lead_manager_id) VALUES (?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssi', $name, $email, $phone, $status, $lead_manager_id);

        if ($stmt->execute()) {
            $success = "Lead created successfully!";
        } else {
            $err = "Error creating lead: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!-- Include header and form -->
<?php include('header2.php'); ?>
<h3>Create Lead</h3>


<?php if (!empty($err)): ?>
    <div class="error"><?php echo $err; ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<!-- Lead creation form -->
<form action="create_lead2.php" method="POST">
    Name: <input type="text" name="name" required><br>
    Email: <input type="email" name="email" required><br>
    Phone: <input type="text" name="phone" required><br>
    Status:<select name="status" id="status" required>
        <option value="">Select a status</option>
        <option value="new">NEW</option>
        <option value="in_progress">IN_PROGRESS</option>
        <option value="closed">CLOSED</option>
    </select><br>
    <button type="submit">Create Lead</button>
</form>

<?php include('footer.php'); ?>
