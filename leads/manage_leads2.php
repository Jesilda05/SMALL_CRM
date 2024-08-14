<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure that only lead managers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'LeadManager') {
    header('Location: ../login.php');
    exit();
}

$lead_manager_id = (int)$_SESSION['user_id'];
$sql = "SELECT * FROM leads WHERE lead_manager_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $lead_manager_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Include header -->
<?php include('header2.php'); ?>

<h3>Manage Leads</h3>

<!-- Display leads in a table -->
<table border="1">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <a href="edit_lead2.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="delete_lead2.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No leads found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>

<?php
$stmt->close();
$conn->close();
?>
