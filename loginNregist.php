<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekC!ng</title>
    <link rel="stylesheet" href="style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
    </style>
</head>

<body>

    <div class="container">
        <img src="img/logo.png" alt="Logo CekC!ng">

        <div class="form-login" id="loginForm">
            <h1>Log In</h1>
            <form action="login.php" method="post">
                <div class="isi_form">
                    <input type="text" name="email" id="loginEmail" placeholder="Email" required>
                </div>
                <div class="isi_form">
                    <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                </div>
                <p class="lupa"><a href="#">Forgot Password?</a></p>
                <button type="submit">Login</button>
                <p class="regist-link">
                    <a href="#" id="toRegister">Don't have an account?</a>
                </p>
            </form>
        </div>

        <div class="form-regist" id="registForm">
            <h1>Register</h1>
            <form action="register.php" method="post">
                <div class="isi_form">
                    <input type="text" name="username" id="regUsername" placeholder="Username" required>
                </div>
                <div class="isi_form">
                    <input type="text" name="email" id="regEmail" placeholder="Email" required>
                </div>
                <div class="isi_form">
                    <input type="password" name="password" id="regPassword" placeholder="Password" required>
                </div>
                <button type="submit">Register</button>
                <p class="regist-link">
                    <a href="#" id="toLogin">Already have an account?</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const registForm = document.getElementById('registForm');
        const toRegister = document.getElementById('toRegister');
        const toLogin = document.getElementById('toLogin');

        // Ketika klik "Don't have an account?"
        toRegister.addEventListener('click', function(e) {
            e.preventDefault();
            loginForm.style.display = 'none';
            registForm.style.display = 'flex'; // UBAH DARI 'block' KE 'flex'
        });

        // Ketika klik "Already have an account?"
        toLogin.addEventListener('click', function(e) {
            e.preventDefault();
            registForm.style.display = 'none';
            loginForm.style.display = 'flex'; // UBAH DARI 'block' KE 'flex'
        });
    </script>

</body>

</html>