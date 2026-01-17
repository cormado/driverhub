<?php
require 'includes/i18n.php';
require 'includes/auth_logic.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,300;0,600;1,800&family=Montserrat:wght@400;600;800&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/logo.png">

    <link rel="stylesheet" href="assets/css/login.css">
    <script src="assets/js/login.js"></script>
</head>

<body>

    <div id="preloader">
        <div class="loader-circle"></div>
        <div class="loader-text">Loading...</div>
    </div>

    <header id="header">
        <div class="logo-text">VINTARA<span>.</span></div>
        <div class="header-controls">
            <?php if ($lang == 'es'): ?>
                <button onclick="cambiarIdioma('en')" class="lang-btn"><i class="fas fa-globe"></i> EN</button>
            <?php else: ?>
                <button onclick="cambiarIdioma('es')" class="lang-btn"><i class="fas fa-globe"></i> ES</button>
            <?php endif; ?>

            <a href="/" class="back-btn"><i class="fas fa-arrow-left"></i> <?php echo $t['back_btn']; ?></a>
        </div>
    </header>

    <div class="login-wrapper">
        <div class="glass-login">
            <div
                style="position: absolute; top: 15px; left: 20px; color: #555; font-family: 'Kanit'; font-size: 0.7rem; letter-spacing: 2px;">
                <?php echo $t['secure_tag']; ?>
            </div>

            <h2 class="login-title"><?php echo $t['login_title_1']; ?> <span><?php echo $t['login_title_2']; ?></span>
            </h2>

            <?php if ($error): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username"><?php echo $t['label_user']; ?></label>
                    <div class="input-group-inner">
                        <input type="text" id="username" name="username" class="form-input trigger-explosion"
                            placeholder="<?php echo $t['ph_user']; ?>" required autocomplete="off">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password"><?php echo $t['label_pass']; ?></label>
                    <div class="input-group-inner">
                        <input type="password" id="password" name="password" class="form-input trigger-explosion"
                            placeholder="<?php echo $t['ph_pass']; ?>" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>

                <button type="submit" class="submit-btn"><?php echo $t['btn_connect']; ?></button>
            </form>
        </div>
    </div>

    <footer>
        <p style="opacity: 0.5;"><?php echo $t['footer_rights']; ?></p>
        <div class="social-links">
            <a href="https://www.tiktok.com/@chapulinesvtc" target="_blank"><i class="fab fa-tiktok"></i></a>
            <a href="https://discord.gg/UY42pmqvnw" target="_blank"><i class="fab fa-discord"></i></a>
            <a href="https://www.youtube.com/@chapulinesvtc" target="_blank"><i class="fab fa-youtube"></i></a>
        </div>
        <div class="footer-links">
            <a href="<?php echo $t['url_terms']; ?>"><?php echo $t['link_terms']; ?></a>
            <a href="<?php echo $t['url_privacy']; ?>"><?php echo $t['link_privacy']; ?></a>
        </div>
    </footer>

</body>

</html>