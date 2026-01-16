    <aside class="sidebar">
        <div class="brand">VINTARA <span>// HUB</span></div>
        <div class="lang-switcher-sidebar">
            <button onclick="cambiarIdioma('es')" class="lang-mini-btn <?php echo ($lang == 'es') ? 'active' : ''; ?>">ES</button>
            <button onclick="cambiarIdioma('en')" class="lang-mini-btn <?php echo ($lang == 'en') ? 'active' : ''; ?>">EN</button>
        </div>
        <div class="vtc-mini-widget">
            <img src="<?php echo $api_vtc_imagen; ?>" class="vtc-logo-img" alt="Logo">
            <div class="vtc-name"><?php echo $api_vtc_nombre; ?></div>
            <div class="vtc-stats">
                <div><b><?php echo $api_vtc_miembros; ?></b> <?php echo $t['nav_members']; ?></div>
                <div><b><?php echo $api_vtc_reclutamiento; ?></b> <?php echo $t['nav_recruiting']; ?></div>
            </div>
        </div>
        <?php if ($es_admin): ?>
            <div class="nav-category"><?php echo $t['nav_admin']; ?></div>
            <a href="?view=events&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'events') ? 'active' : ''; ?>"><i class="fas fa-calendar-plus"></i> <?php echo $t['nav_manage_events']; ?></a>
            <a href="?view=database&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'database') ? 'active' : ''; ?>"><i class="fas fa-database"></i> <?php echo $t['nav_users']; ?></a>
            <a href="?view=manageStore&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'manageStore') ? 'active' : ''; ?>"><i class="fas fa-store"></i> <?php echo $t['nav_store_manage']; ?></a>
        <?php endif; ?>
        <div class="nav-category"><?php echo $t['nav_menu']; ?></div>
        <a href="?view=dashboard&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'dashboard') ? 'active' : ''; ?>"><i class="fas fa-satellite-dish"></i> <?php echo $t['nav_ops']; ?></a>
        <a href="?view=workshop&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'workshop') ? 'active' : ''; ?>"><i class="fas fa-tools"></i> <?php echo $t['nav_workshop']; ?></a>
        <a href="?view=tickets&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'tickets') ? 'active' : ''; ?>"><i class="fas fa-headset"></i> <?php echo $t['nav_tickets']; ?></a>
        <a href="?view=store&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'store') ? 'active' : ''; ?>"><i class="fas fa-shopping-cart"></i> <?php echo $t['nav_store']; ?></a>
        <div class="user-credential">
            <img src="<?php echo $mi_avatar; ?>" class="user-avatar" alt="User">
            <div class="user-info">
                <h4><?php echo $_SESSION['username']; ?></h4>
                <span class="user-rank"><?php echo $mi_rango_vtc; ?></span>
                <span class="user-id">TMP: <?php echo $mi_tmp_id; ?></span>
            </div>
        </div>
    </aside>