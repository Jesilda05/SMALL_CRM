<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure only lead managers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'LeadManager') {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['id'];
    $lead_manager_id = (int)$_SESSION['user_id'];

    $sql = "DELETE FROM leads WHERE id = ? AND lead_manager_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $id, $lead_manager_id);

    if ($stmt->execute()) {
        header('Location: manage_leads2.php?success=Lead deleted successfully.');
    } else {
        header('Location: manage_leads2.php?error=Error deleting lead.');
    }

   
    $stmt->close();
} else {
    header('Location: manage_leads2.php?error=Invalid lead ID.');
    exit();
}

$conn->close();
?>
