<?php
/**
 * Plugin Name: Clean Dashboard for WooCommerce
 * Description: Oculta todos los widgets del dashboard excepto los de WooCommerce
 * Version: 1.0
 * Author: Estratos
 */

if (!defined('ABSPATH')) {
    exit;
}

class CleanWooCommerceDashboard {
    
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'clean_dashboard'));
        add_action('admin_menu', array($this, 'remove_menu_pages'));
        add_action('admin_init', array($this, 'remove_dashboard_meta'));
    }
    
    /**
     * Limpia el dashboard de widgets no deseados
     */
    public function clean_dashboard() {
        global $wp_meta_boxes;
        
        // Widgets que SÍ queremos mantener (WooCommerce)
        $allowed_widgets = array(
            'woocommerce_dashboard_status',
            'woocommerce_dashboard_recent_reviews',
            'woocommerce_network_orders',
            'wc_admin_dashboard_setup'
        );
        
        // Remover todos los widgets excepto los permitidos
        foreach ($wp_meta_boxes['dashboard'] as $context => $priority_array) {
            foreach ($priority_array as $priority => $widgets) {
                foreach ($widgets as $widget_id => $widget_data) {
                    if (!in_array($widget_id, $allowed_widgets)) {
                        remove_meta_box($widget_id, 'dashboard', $context);
                    }
                }
            }
        }
        
        // Remover widgets específicos de WordPress
        $widgets_to_remove = array(
            'dashboard_primary',           // Noticias de WordPress
            'dashboard_secondary',         // Otras noticias
            'dashboard_quick_press',       // Borrador rápido
            'dashboard_recent_drafts',     // Borradores recientes
            'dashboard_activity',          // Actividad reciente
            'dashboard_right_now',         // Ahora mismo
            'dashboard_site_health',       // Estado del sitio
            'wpseo-dashboard-overview',    // Yoast SEO
            'jetpack_summary_widget',      // Jetpack
        );
        
        foreach ($widgets_to_remove as $widget) {
            remove_meta_box($widget, 'dashboard', 'normal');
            remove_meta_box($widget, 'dashboard', 'side');
            remove_meta_box($widget, 'dashboard', 'column3');
            remove_meta_box($widget, 'dashboard', 'column4');
        }
    }
    
    /**
     * Remover páginas de menú no deseadas
     */
    public function remove_menu_pages() {
        // Solo aplicar para usuarios que no son administradores
        if (!current_user_can('manage_options')) {
            remove_menu_page('index.php'); // Dashboard
            remove_menu_page('edit-comments.php'); // Comentarios
        }
        
        // Elementos a remover para todos los usuarios
        remove_submenu_page('index.php', 'update-core.php'); // Actualizaciones
        
        // Remover elementos de WooCommerce no esenciales
        remove_submenu_page('woocommerce', 'wc-addons'); // Marketplace de WooCommerce
    }
    
    /**
     * Remover meta boxes adicionales
     */
    public function remove_dashboard_meta() {
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');
        
        // Deshabilitar noticias de WordPress
        add_filter('pre_option_default_category', '__return_zero');
        add_action('admin_head', array($this, 'hide_news_widgets_css'));
    }
    
    /**
     * CSS adicional para ocultar elementos
     */
    public function hide_news_widgets_css() {
        echo '<style>
            /* Ocultar elementos de noticias y promociones */
            .woocommerce-message[data-message-id="wc_connect_install_notice"],
            .woocommerce-message[data-message-id="wc_connect_services_notice"],
            #wp-admin-bar-wp-logo,
            #dashboard-widgets-wrap .welcome-panel,
            .woocommerce-marketing-notifications-panel {
                display: none !important;
            }
            
            /* Asegurar que solo se vean los widgets de WooCommerce */
            #woocommerce_dashboard_status,
            #woocommerce_dashboard_recent_reviews {
                display: block !important;
            }
        </style>';
    }
}

new CleanWooCommerceDashboard();

/**
 * Deshabilitar noticias y eventos de WordPress
 */
add_filter('pre_site_transient_update_core', '__return_null');
add_filter('pre_site_transient_update_plugins', '__return_null');
add_filter('pre_site_transient_update_themes', '__return_null');

/**
 * Deshabilitar verificaciones de actualización para plugins específicos
 */
add_filter('site_transient_update_plugins', function($value) {
    if (isset($value->response)) {
        // Remover notificaciones de actualización de plugins no esenciales
        $plugins_to_ignore = array(
            'jetpack/jetpack.php',
            'akismet/akismet.php',
        );
        
        foreach ($plugins_to_ignore as $plugin) {
            if (isset($value->response[$plugin])) {
                unset($value->response[$plugin]);
            }
        }
    }
    return $value;
});

?>
