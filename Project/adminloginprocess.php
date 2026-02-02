<?php 
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $username = $_POST['username']; 
    $password = $_POST['password']; 
    
    $conn = mysqli_connect("localhost", "root", "", "project");  
    if(!$conn) { 
        die("Connection failed: " . mysqli_connect_error());  
    } 
    
    // Prepared statement to prevent SQL injection
    $sql = "SELECT * FROM admins WHERE username=? AND password=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) { 
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.html"); 
        exit(); 
    } else { 
        header("Location: adminlogin.html?error=1");
        exit();
    } 
    
    mysqli_close($conn); 
} 
?>