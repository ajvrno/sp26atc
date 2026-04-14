<!-- This file is a template for the database configuration. Copy this file and rename it to db_config.php,
 then fill in the appropriate values for your local MySQL database. Make sure to add your db_config.php file
 in .gitignore so your MySQL credentials are not committed to the repository. -->

<?php
$host = 'localhost';
$username = 'root';
$password = ''; // Put local MySQL password here
$dbname = 'asc_dropin_tutoring';
$db = mysqli_connect($host, $username, $password, $dbname);
?>