            <div class="hero-section">
                <div class="vtc-stat-card">
                    <img src="<?php echo $api_vtc_imagen; ?>" class="vtc-logo-big" alt="Logo">
                    <div class="vtc-big-number"><?php echo $api_vtc_miembros; ?></div>
                    <div style="color: #666; font-size: 0.8rem; letter-spacing: 1px; margin-top: 5px;"><?php echo $t['stats_total']; ?></div>
                    <div style="margin-top: 20px; padding: 5px 15px; background: rgba(0,255,157,0.1); border: 1px solid var(--accent-green); color: var(--accent-green); border-radius: 20px; font-size: 0.7rem; font-weight: bold;"><?php echo $t['stats_verified']; ?></div>
                </div>
                <?php if ($evento_destacado): ?>
                    <div class="featured-event-card" style="background-image: url('<?php echo $evento_destacado['banner']; ?>'); background-size: cover;">
                        <div class="event-overlay"></div>
                        <a href="<?php echo $evento_destacado['url']; ?>" target="_blank" class="btn-event">Ver en TMP <i class="fas fa-external-link-alt"></i></a>
                        <div class="event-content">
                            <span class="tag-hosted"><?php echo $t['event_official']; ?></span>
                            <h2 class="event-title"><?php echo $evento_destacado['nombre']; ?></h2>
                            <div class="event-details">
                                <div><i class="far fa-calendar-alt"></i> <?php echo date('d M', $evento_destacado['fecha']); ?></div>
                                <div><i class="far fa-clock"></i> <?php echo date('H:i', $evento_destacado['fecha']); ?> UTC</div>
                                <div><i class="fas fa-server"></i> <?php echo $evento_destacado['server']; ?></div>
                                <div><i class="fas fa-map-marker-alt"></i> <?php echo $evento_destacado['salida']; ?> <i class="fas fa-arrow-right"></i> <?php echo $evento_destacado['llegada']; ?></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="featured-event-card" style="align-items: center; justify-content: center; background: rgba(255,255,255,0.02);">
                        <h3 style="font-family: 'Kanit'; color: #666;">Sin eventos propios</h3>
                    </div>
                <?php endif; ?>
            </div>
            <div class="attending-section-title"><?php echo $t['event_agenda']; ?> <span>// <?php echo $t['event_invited']; ?></span></div>
            <div class="attending-grid">
                <?php if (!empty($eventos_asistencia)): ?>
                    <?php foreach ($eventos_asistencia as $evt): ?>
                        <div class="attending-card-visual" style="background-image: url('<?php echo $evt['banner'] ? $evt['banner'] : 'assets/img/header.png'; ?>');">
                            <div class="att-overlay"></div>
                            <div class="att-content">
                                <span class="att-organizer">Organiza: <?php echo $evt['organiza']; ?></span>
                                <h3 class="att-title"><?php echo substr($evt['nombre'], 0, 30) . (strlen($evt['nombre']) > 30 ? '...' : ''); ?></h3>
                                <div class="att-meta">
                                    <span><i class="far fa-calendar-alt"></i> <?php echo date('d M - H:i', $evt['fecha']); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo $evt['salida']; ?> <i class="fas fa-arrow-right"></i> <?php echo $evt['llegada']; ?></span>
                                </div>
                            </div>
                            <a href="<?php echo $evt['url']; ?>" target="_blank" style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:3;"></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; padding: 30px; color: #555; border: 1px dashed #333; text-align: center;"><?php echo $t['event_none']; ?></div>
                <?php endif; ?>
            </div>