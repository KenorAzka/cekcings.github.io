<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekC!ng</title>
    <link rel="stylesheet" href="css/style.css?v=1.1">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="#"><img src="img/logo.png" alt="Logo CekC!ng" class="logo"></a>
        <ul class="nav-links">
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="#feedback">Feedback</a></li>
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

        <section id="contact">
            <div class="contact-container">
                <div class="contact-info">
                    <h2>Get in<br>Touch</h2>
                    <p>Punya pertanyaan seputar fitur CekC!ng atau ingin bekerja sama dengan kami? Jangan ragu untuk menghubungi tim kami melalui saluran di bawah ini.</p>
                    <div class="info-links">
                        <div class="info-item">
                            <span class="icon">📍</span>
                            <p>Surakarta, Indonesia</p>
                        </div>
                        <div class="info-item">
                            <span class="icon">✉️</span>
                            <p><a href="mailto:kendhani0@gmail.com">support@cekcing.my.id</a></p>
                        </div>
                    </div>
                </div>
                <div class="contact-box-wrapper">
                    <div class="contact-box">
                        <h3>Hubungi Kami</h3>
                        <div class="box-item">
                            <h4>Customer Service</h4>
                            <p>Senin - Jumat (09.00 - 17.00 WIB)</p>
                            <a href="https://wa.me/62882005641003" target="_blank" class="contact-btn">Chat via WhatsApp</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="feedback">
            <div class="feedback-wrapper">
                <div class="feedback-header">
                    <h2>Give Us<br>Feedback</h2>
                    <p>Kritik, saran, atau masukan Anda sangat berharga bagi perkembangan CekC!ng menjadi lebih baik lagi.</p>
                </div>
                <div class="feedback-card">
                    <form id="feedbackForm">
                        <div class="form-group">
                            <label for="pesanFeedback">Masukan / Kritik & Saran Anda</label>
                            <textarea name="pesan" id="pesanFeedback" placeholder="Tulis masukan Anda di sini secara detail..." required></textarea>
                        </div>
                        <a href="loginNregist.php" class="btn-submit" style="display: block; text-align: center; text-decoration: none; box-sizing: border-box;">
                            <span>Kirim Masukan</span>
                        </a>
                    </form>
                </div>
            </div>
        </section>
        <footer class="site-footer">
            <div class="footer-container">
                <div class="footer-brand">
                    <img src="img/logo.png" alt="Logo CekC!ng" class="footer-logo">
                    <p>Tracking tabungan wishlistmu dengan mudah dan teratur.</p>
                </div>

                <div class="footer-right">
                    <p class="footer-copyright">
                        &copy; 2026 CekC!ng App &mdash; All Rights Reserved.
                    </p>
                </div>
            </div>
        </footer>
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