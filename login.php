<?php
// Ashley Rabino - Login Page

session_start();

$db = mysqli_connect('dbname');

if (mysqli_connect_errno()) {
    exit("Error - could not connect to MySQL: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = htmlspecialchars($_POST['username']);
    $pass = htmlspecialchars($_POST['pass']);

    // Retrieve user using prepared statement
    $stmt = mysqli_prepare($db, "SELECT user_id, username, password FROM USERS WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $user);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $user_id, $username, $hashed_password);

    if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_fetch($stmt);

        // Verify password
        if (password_verify($pass, $hashed_password)) {
            // Password is correct
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;


            header("Location: src/MainFeed.html");

            exit();

        } else {
            $message = "Invalid username or password.";
        }
    } else {
        $message = "Invalid username or password.";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Log In</title>
    <link rel="stylesheet" href="create_profile.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,600;1,600&family=Quicksand:wght@300..700&display=swap');
    </style>
</head>

<body>
    <!--Log In Form -->
    <div class="register">
        <h1>Log In</h1>

        <form id="form" action="log_in.php" method="post">
            <label for="username">Username</label><br>
            <input type="text" name="username" required><br>
            <label for="pass">Password</label><br>
            <input type="password" name="pass" required><br>

            <button type="submit">Log In</button>
        </form>
    </div>
</body>

</html>