            <div class="form-add-event">
                <h3 style="font-family:'Kanit'; margin-bottom:15px; color:white;">AGREGAR NUEVO EVENTO</h3>
                <form method="POST">
                    <label style="color:#888;">Event ID:</label>
                    <input type="number" name="add_event_id" class="input-event" placeholder="Ej: 12345" required>
                    <button type="submit" class="btn-add">Guardar</button>
                </form>
                <?php if ($mensaje_accion) echo "<p style='margin-top:10px; color:var(--accent-green);'>$mensaje_accion</p>"; ?>
            </div>
            <?php if ($discord_template): ?><div style="margin-bottom:40px;">
                    <h4 style="font-family:'Kanit'; color:var(--accent-purple);">PLANTILLA DISCORD:</h4>
                    <div class="discord-box"><?php echo $discord_template; ?></div>
                </div><?php endif; ?>
            <div class="attending-section-title">Eventos Guardados</div>
            <?php foreach ($lista_eventos_db as $evt_db): ?>
                <div style="background:#111; padding:15px; border-left:3px solid #333; display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="color:#666;">ID: <?php echo $evt_db['event_id']; ?></span>
                    <a href="?view=events&del_event=<?php echo $evt_db['event_id']; ?>&lang=<?php echo $lang; ?>" style="color:#ff0055;">ELIMINAR</a>
                </div>
            <?php endforeach; ?>