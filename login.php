<?php
// Ashley Rabino - Login Page for ASC Drop-in Tutoring Admin Interface

session_start();

require_once 'db_config.php';

if (mysqli_connect_errno()) {
    exit("Error - could not connect to MySQL: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = htmlspecialchars($_POST['email']);
    $pass = htmlspecialchars($_POST['pass']);
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // Retrieve user using prepared statement
    $stmt = mysqli_prepare($db, "SELECT admin_id, email, pass FROM admins WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $admins);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $admin_id, $email, $hashed_password);

    if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_fetch($stmt);

        // Verify password
        if (password_verify($pass, $hashed_password)) {
            // Password is correct
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['email'] = $email;


            header("Location: admin.php");

            exit();

        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "Invalid email or password.";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ASC Tutor Management</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,600;1,600&family=Quicksand:wght@300..700&display=swap');

        html {
            background-color: rgb(182, 182, 182);
            font-family: "Inter", sans-serif;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        h1 {
            font-size: 50px;
        }

        button {
            font-family: "Inter", sans-serif;
            width: 255px;
            height: 30px;
            margin-top: 20px;
            margin-left: 5px;
            background-color: #fdb515;
            border: 0.5px;
            border-radius: 5px;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
            transition-duration: 0.2s;
            cursor: pointer;
        }

        button:hover {
            background-color: #a67a05;
        }

        input {
            width: 250px;
            height: 30px;
            margin: 5px;
            border: 0.5px solid black;
            border-radius: 5px;
        }

        a {
            color: black;
            transition-duration: 0.2s;
            cursor: pointer;
        }

        a:hover {
            color: #a67a05;
        }

        .register {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-top: 75px;
            width: auto;
            height: 450px;
            background-color: whitesmoke;
            border-radius: 2em;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px,
                rgba(0, 0, 0, 0.3) 0px 30px 60px -30px,
                rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
            padding: 3em;
        }

        .create {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-top: 75px;
            background-color: rgb(199, 200, 202);
            border-radius: 2em;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px,
                rgba(0, 0, 0, 0.3) 0px 30px 60px -30px,
                rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
            padding: 3em;
        }

        .create h1 {
            text-align: center;
        }

        .confirm {
            background-color: rgb(199, 200, 202);
            padding: 50px;
            border-radius: 2em;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px,
                rgba(0, 0, 0, 0.3) 0px 30px 60px -30px,
                rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
        }


        #form {
            font-size: 15px;
        }

        #pronouns {
            font-family: "Inter", sans-serif;
        }
    </style>
</head>

<body>
    <!--Log In Form -->
    <div class="register">
        <img src="data/UMBC-vertical-logo-RGB-665x1024.png" alt="UMBC Logo" style="width: 100px; height: auto;">
        <h1>Log In</h1>

        <form id="form" action="login.php" method="post">
            <label for="email">Email</label><br>
            <input type="text" name="email" required><br>
            <label for="pass">Password</label><br>
            <input type="password" name="pass" required><br>

            <button type="submit">Log In</button>
        </form>
    </div>
</body>

</html>