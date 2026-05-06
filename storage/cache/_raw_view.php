        <div class="glass-card" style="max-width: 900px; margin: 0 auto;">
            <div style="margin-bottom: 30px;">
                <h2 style="font-weight: 800; margin: 0;">🛠️ Herramientas del Sistema</h2>
                <p style="color: var(--text-muted); margin-top: 5px;">Administración y mantenimiento del sistema.</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                <a href="tools.php?action=diagnostic" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">🔍</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Diagnóstico del Sistema</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Verifica PHP, extensiones, permisos y conexión a base de datos.</p>
                    </div>
                </a>

                <a href="run_migrations.php" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">🗄️</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Sincronizar Esquema BD</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Aplica migraciones pendientes y crea tablas faltantes.</p>
                    </div>
                </a>

                <a href="tools.php?action=export" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">💾</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Exportar Datos (JSON)</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Descarga un respaldo completo de clientes, facturas y productos.</p>
                    </div>
                </a>

                <a href="fix_permissions.php" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">🔐</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Corregir Permisos</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Restablece permisos de escritura en carpetas críticas del sistema.</p>
                    </div>
                </a>

                <a href="cleanup.php" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#ef4444'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">🧹</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Limpiar Sistema</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Elimina archivos temporales, duplicados y plantillas repetidas.</p>
                    </div>
                </a>

                <a href="read_log.php" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">📋</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Ver Log de Errores</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Revisa los últimos errores registrados por el sistema.</p>
                    </div>
                </a>

            </div>
        </div>
        