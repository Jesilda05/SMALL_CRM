<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure only lead managers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'LeadManager') {
    header('Location: ../login.php');
    exit();
}

$lead_manager_id = (int)$_SESSION['user_id'];
$error = $success = '';

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['id'];

    // Fetch the lead data
    $sql = "SELECT * FROM leads WHERE id = ? AND lead_manager_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $id, $lead_manager_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $lead = $result->fetch_assoc();

        // Update the lead if POST request is made
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
            $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
            $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
            $status = filter_var(trim($_POST['status']), FILTER_SANITIZE_STRING);

            if (empty($name) || empty($email) || empty($phone) || empty($status)) {
                $error = "Please fill in all fields.";
            } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $name)) {
                $error = "Name can only contain letters, spaces, and basic punctuation.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } elseif (!preg_match("/^\d{10}$/", $phone)) {
                $error = "Phone number must be 10 digits.";
            } else {
                $update_sql = "UPDATE leads SET name = ?, email = ?, phone = ?, status = ? WHERE id = ? AND lead_manager_id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param('sssiii', $name, $email, $phone, $status, $id, $lead_manager_id);

                if ($stmt->execute()) {
                    $success = "Lead updated successfully.";
                } else {
                    $error = "Error updating lead: " . $stmt->error;
                }
            }
        }
    } else {
        $error = "Lead not found.";
    }

    $stmt->close();
} else {
    header('Location: manage_leads2.php');
    exit();
}

$conn->close();
?>

<!-- Include header -->
<?php include('header2.php'); ?>

<h3>Edit Lead</h3>

<!-- Display success/error messages -->
<?php if (!empty($error)): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<!-- Edit form -->
<form action="edit_lead2.php?id=<?php echo $id; ?>" method="POST">
    Name: <input type="text" name="name" value="<?php echo htmlspecialchars($lead['name']); ?>" required><br>
    Email: <input type="email" name="email" value="<?php echo htmlspecialchars($lead['email']); ?>" required><br>
    Phone: <input type="text" name="phone" value="<?php echo htmlspecialchars($lead['phone']); ?>" required><br>
    Status:
    <select name="status" id="status" required>
        <option value="">Select a status</option>
        <option value="new" <?php echo ($lead['status'] === 'new') ? 'selected' : ''; ?>>NEW</option>
        <option value="in_progress" <?php echo ($lead['status'] === 'in_progress') ? 'selected' : ''; ?>>IN_PROGRESS</option>
        <option value="closed" <?php echo ($lead['status'] === 'closed') ? 'selected' : ''; ?>>CLOSED</option>
    </select><br>
    <button type="submit">Update Lead</button>
</form>

<?php include('footer.php'); ?>
