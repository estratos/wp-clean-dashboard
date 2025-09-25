Clean Dashboard for WooCommerce 🧹
Un plugin de WordPress que te permite limpiar y personalizar completamente el dashboard administrativo. Oculta widgets no deseados, promociones molestas y te da control total sobre qué elementos mostrar.

✨ Características Principales
🎯 Control Granular de Widgets
Detección automática de todos los widgets del dashboard

Selección individual por widget (WordPress, WooCommerce, otros plugins)

Categorización inteligente con interfaz visual

IDs visibles para fácil identificación

🛒 Especializado para WooCommerce
Mantiene widgets esenciales de WooCommerce

Oculta promociones y upsells molestos

Compatible con tiendas WooCommerce

⚙️ Configuración Flexible
Aplicación por roles de usuario

Configuración por defecto inteligente

Interfaz amigable y fácil de usar

📦 Instalación
Método 1: Subida directa
Descarga el archivo ZIP del plugin

Ve a Plugins > Añadir nuevo > Subir plugin en tu WordPress

Sube el archivo ZIP y activa el plugin

Método 2: Instalación manual
Descomprime el archivo ZIP

Sube la carpeta clean-woocommerce-dashboard a /wp-content/plugins/

Activa el plugin en Plugins > Plugins instalados

Método 3: Desarrollo
bash
cd wp-content/plugins/
git clone [url-del-repositorio] wp-clean-dashboard
🚀 Uso Rápido
Configuración básica
Activa el plugin en Plugins > Plugins instalados

Ve a Ajustes > Clean Dashboard

Selecciona los widgets que quieres mantener visibles

Guarda los cambios y recarga el dashboard

Configuración recomendada para WooCommerce
✅ woocommerce_dashboard_status - Estado de la tienda

✅ woocommerce_dashboard_recent_reviews - Reseñas recientes

✅ woocommerce_dashboard_recent_orders - Pedidos recientes

❌ Todos los demás widgets de WordPress

❌ Promociones y upsells

🎛️ Panel de Configuración
Widgets Permitidos
Widgets de WooCommerce 🛒: Estado, reseñas, pedidos

Widgets de WordPress ⚙️: Actividad, noticias, borradores

Widgets de otros plugins 🔌: Wordfence, CTX Feed, etc.

Opciones Adicionales
Aplicar a roles: Selecciona qué roles de usuario verán el dashboard limpio

Ocultar promociones: Elimina upsells y sugerencias de plugins

🏗️ Estructura del Proyecto
text
wp-clean-dashboard/
├── clean-dashboard.php          # Archivo principal del plugin
├── languages/                   # Traducciones (opcional)
│   └── clean-dashboard-wc-es_ES.po
├── assets/                      # Recursos (opcional)
│   ├── css/
│   └── js/
└── readme.txt                   # Este archivo
🔧 Personalización Avanzada
Filtros disponibles
php
// Modificar widgets permitidos por defecto
add_filter('clean_dashboard_default_widgets', function($widgets) {
    $widgets[] = 'mi_widget_personalizado';
    return $widgets;
});

// Modificar roles permitidos
add_filter('clean_dashboard_allowed_roles', function($roles) {
    $roles[] = 'editor';
    return $roles;
});
Hooks disponibles
php
// Antes de limpiar el dashboard
add_action('clean_dashboard_before_cleanup', function() {
    // Tu código aquí
});

// Después de limpiar el dashboard  
add_action('clean_dashboard_after_cleanup', function() {
    // Tu código aquí
});
🐛 Solución de Problemas
Widgets no se ocultan
Verifica que el plugin esté activado

Revisa la configuración en Ajustes > Clean Dashboard

Asegúrate de que tu rol de usuario esté seleccionado

Error "Error loading widget"
El plugin usa prioridades correctas para cargar después de WooCommerce

Verifica que WooCommerce esté actualizado

No se detectan todos los widgets
Guarda la configuración y recarga la página

Algunos widgets pueden cargarse dinámicamente

📋 Changelog
Versión 2.0 (Actual)
✅ Detección automática de todos los widgets

✅ Selección individual por widget

✅ Interfaz categorizada y mejorada

✅ Soporte para Wordfence, CTX Feed y otros plugins

Versión 1.3
✅ Corrección de errores en widgets de WooCommerce

✅ Mejor prioridad de ejecución

✅ Detección de dependencias

Versión 1.2
✅ Soporte para ocultar widget de Elementor

✅ Notificaciones de estado mejoradas

🤝 Contribuir
Las contribuciones son bienvenidas. Por favor:

Haz un fork del proyecto

Crea una rama para tu feature (git checkout -b feature/AmazingFeature)

Commit tus cambios (git commit -m 'Add some AmazingFeature')

Push a la rama (git push origin feature/AmazingFeature)

Abre un Pull Request

📝 Licencia
Este proyecto está licenciado bajo la licencia GPL v2 o posterior. Ver el archivo LICENSE para más detalles.

🙋‍♂️ Soporte
Si necesitas ayuda:

Revisa la sección de solución de problemas arriba

Abre un issue en GitHub

Contacta al desarrollador

🏆 Créditos
Desarrollado por Estratos con ❤️ para la comunidad de WordPress y WooCommerce.

¿Te gusta este plugin? ¡Dale una estrella ⭐ en GitHub!

📞 ¿Necesitas personalizaciones?
Si necesitas funcionalidades específicas para tu proyecto, contáctame para desarrollar una versión personalizada del plugin.

¡Dashboard limpio, mente enfocada! 🚀

