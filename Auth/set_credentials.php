<?php
session_start();
if (!isset($_SESSION['id_Number']) || !isset($_SESSION['IC_Number'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Credentials</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="container">
        <h2>Set Your Username & Password</h2>
        <form action="save_credentials.php" method="POST" enctype="multipart/form-data">
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class='bx bx-hide' id="togglePassword"></i>
            </div>
            <div class="show-password">
                <input type="checkbox" id="showPassword" style="margin-right: 8px;">
                <label for="showPassword">Show Password</label>
            </div>
            <div class="input-box">
                <input type="file" name="picture" id="picture" accept="image/*">
                <i class='bx bx-image'></i>
            </div>
            <button type="submit" class="btn">Save</button>
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePassword');
        const showPasswordCheck = document.getElementById('showPassword');

        toggleIcon.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            toggleIcon.classList.toggle('bx-show');
            toggleIcon.classList.toggle('bx-hide');
        });

        showPasswordCheck.addEventListener('change', () => {
            passwordInput.type = showPasswordCheck.checked ? 'text' : 'password';
            toggleIcon.classList.toggle('bx-show', showPasswordCheck.checked);
            toggleIcon.classList.toggle('bx-hide', !showPasswordCheck.checked);
        });
    </script>
</body>
</html>

