<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CS425 Test login</title>
</head>
<body>

<form name="form" action="/api/login" method="POST">
    <label for="username">Username: </label>
    <input type="text" id="username" name="username" value=""><br>

    <label for="password">Password: </label>
    <input type="password" id="password" name="password" value=""><br>

    <input type="submit" name="submit" value="Login">
</form>

</body>
</html>