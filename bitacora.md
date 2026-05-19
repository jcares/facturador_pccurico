## Historial de Solución de Problemas de Redirección

### 2026-05-10
- Se detectó un bucle de redirección infinito (ERR_TOO_MANY_REDIRECTS) al acceder a https://facturador.pccurico.cl/index.php después del login
- El error estaba relacionado con el cambio en la estructura del FTP: ahora el sitio se encuentra en `Y:\facturador.pccurico.cl` en lugar de `Y:\`
- Se actualizó la bitácora para reflejar la nueva ruta de producción
- Se verificó que el archivo index.php tenía el contenido correcto para el bootstrap del sistema
- La causa probable del bucle de redirección es:
  1. Un conflicto entre las reglas de redirección en el archivo .htaccess de producción
  2. O un problema con la detección de sesión/autenticación que causa un ciclo entre index.php y login.php

### Próximos pasos para resolver:
1. Revisar el archivo .htaccess en `Y:\facturador.pccurico.cl\` para identificar reglas de redirección conflictivas
2. Verificar el funcionamiento de la autenticación y sesiones en el entorno de producción
3. Asegurarse de que el enrutamiento web.php esté correctamente configurado