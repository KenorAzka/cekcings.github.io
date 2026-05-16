<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekC!ng</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    </style>
</head>

<body>

    <nav class="navbar">
        <img src="img/logo.png" alt="Logo CekC!ng" class="logo">
        <ul class="nav-links">
            <li><a href="#about">About</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Wishlist</a></li>
        </ul>
    </nav>

    <main class="main-content">

        <section id="home">
            <div class="main-image">
                <img src="img/orang.png" alt="Orang CekC!ng">
            </div>
            <div class="main-text">
                <h1>Pengen Tracking<br>Tabungan Wishlistmu?</h1>
                <p>Gunakan CekC!ng agar memudahkan mencatat tabungan wishlistmu!</p>
                <a href="loginNregist.php" class="btn">
                    <span>Start Now</span>
                    <span class="arrow">➔</span>
                </a>
            </div>
        </section>

        <section id="about">
            <div class="about-wrapper">
                <div class="about-title">
                    <h2>About<br>Us</h2>
                </div>

                <div class="about-content">
                    <p>CekC!ng adalah aplikasi pencatatan tabungan digital yang membantu pengguna mengelola pemasukan, pengeluaran, dan saldo tabungan secara praktis.</p>
                    <p>Aplikasi ini dirancang dengan tampilan sederhana agar memudahkan pengguna memantau keuangan sehari-hari secara lebih teratur dan efisien.</p>
                    <p>CekC!ng hadir sebagai solusi modern untuk membangun kebiasaan menabung melalui pencatatan yang mudah diakses kapan saja.</p>
                </div>
            </div>
        </section>

    </main>

    <script>
        const navbar = document.querySelector('.navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('shrunk');
            } else {
                navbar.classList.remove('shrunk');
            }
        });
    </script>
</body>

</html>