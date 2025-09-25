<?php
/**
 * Plugin Name: Clean Dashboard for WooCommerce
 * Description: Oculta todos los widgets del dashboard excepto los de WooCommerce. Incluye panel de configuración.
 * Version: 1.1
 * Author: Estratos
 * Text Domain: clean-dashboard-wc
 */

if (!defined('ABSPATH')) {
    exit;
}

class CleanWooCommerceDashboard {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('clean_dashboard_wc_options');
        
        add_action('wp_dashboard_setup', array($this, 'clean_dashboard'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
        
        // Cargar traducciones
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Cargar traducciones
     */
    public function load_textdomain() {
        load_plugin_textdomain('clean-dashboard-wc', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Añadir enlace de configuración en la lista de plugins
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=clean-dashboard-wc') . '">' . __('Configuración', 'clean-dashboard-wc') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_options_page(
            'Clean Dashboard WC', 
            'Clean Dashboard', 
            'manage_options', 
            'clean-dashboard-wc', 
            array($this, 'options_page')
        );
    }
    
    /**
     * Inicializar configuraciones
     */
    public function settings_init() {
        register_setting('clean_dashboard_wc', 'clean_dashboard_wc_options');
        
        // Sección principal
        add_settings_section(
            'clean_dashboard_wc_section', 
            __('Configuración del Dashboard', 'clean-dashboard-wc'), 
            array($this, 'settings_section_callback'), 
            'clean_dashboard_wc'
        );
        
        // Campo para widgets de WordPress
        add_settings_field(
            'hide_wp_widgets', 
            __('Ocultar Widgets de WordPress', 'clean-dashboard-wc'), 
            array($this, 'hide_wp_widgets_render'), 
            'clean_dashboard_wc', 
            'clean_dashboard_wc_section'
        );
        
        // Campo para widgets de WooCommerce
        add_settings_field(
            'woocommerce_widgets', 
            __('Widgets de WooCommerce a Mostrar', 'clean-dashboard-wc'), 
            array($this, 'woocommerce_widgets_render'), 
            'clean_dashboard_wc', 
            'clean_dashboard_wc_section'
        );
        
        // Campo para upsells
        add_settings_field(
            'hide_upsells', 
            __('Ocultar Promociones y Upsells', 'clean-dashboard-wc'), 
            array($this, 'hide_upsells_render'), 
            'clean_dashboard_wc', 
            'clean_dashboard_wc_section'
        );
        
        // Campo para roles
        add_settings_field(
            'apply_to_roles', 
            __('Aplicar a Roles', 'clean-dashboard-wc'), 
            array($this, 'apply_to_roles_render'), 
            'clean_dashboard_wc', 
            'clean_dashboard_wc_section'
        );
    }
    
    /**
     * Descripción de la sección
     */
    public function settings_section_callback() {
        echo __('Configura qué elementos quieres mostrar u ocultar en el dashboard de WordPress.', 'clean-dashboard-wc');
    }
    
    /**
     * Campo para ocultar widgets de WordPress
     */
    public function hide_wp_widgets_render() {
        $options = get_option('clean_dashboard_wc_options');
        $value = isset($options['hide_wp_widgets']) ? $options['hide_wp_widgets'] : 1;
        ?>
        <input type="checkbox" name="clean_dashboard_wc_options[hide_wp_widgets]" value="1" <?php checked(1, $value, true); ?>>
        <label for="clean_dashboard_wc_options[hide_wp_widgets]"><?php _e('Ocultar todos los widgets nativos de WordPress', 'clean-dashboard-wc'); ?></label>
        <p class="description"><?php _e('Actividad, Noticias, Borradores rápidos, etc.', 'clean-dashboard-wc'); ?></p>
        <?php
    }
    
    /**
     * Campo para widgets de WooCommerce
     */
    public function woocommerce_widgets_render() {
        $options = get_option('clean_dashboard_wc_options');
        $selected = isset($options['woocommerce_widgets']) ? $options['woocommerce_widgets'] : array('status', 'reviews');
        ?>
        <fieldset>
            <label>
                <input type="checkbox" name="clean_dashboard_wc_options[woocommerce_widgets][]" value="status" <?php checked(in_array('status', $selected), true); ?>>
                <?php _e('Estado de WooCommerce', 'clean-dashboard-wc'); ?>
            </label><br>
            
            <label>
                <input type="checkbox" name="clean_dashboard_wc_options[woocommerce_widgets][]" value="reviews" <?php checked(in_array('reviews', $selected), true); ?>>
                <?php _e('Reseñas Recientes', 'clean-dashboard-wc'); ?>
            </label><br>
            
            <label>
                <input type="checkbox" name="clean_dashboard_wc_options[woocommerce_widgets][]" value="orders" <?php checked(in_array('orders', $selected), true); ?>>
                <?php _e('Pedidos Recientes', 'clean-dashboard-wc'); ?>
            </label>
        </fieldset>
        <?php
    }
    
    /**
     * Campo para ocultar upsells
     */
    public function hide_upsells_render() {
        $options = get_option('clean_dashboard_wc_options');
        $value = isset($options['hide_upsells']) ? $options['hide_upsells'] : 1;
        ?>
        <input type="checkbox" name="clean_dashboard_wc_options[hide_upsells]" value="1" <?php checked(1, $value, true); ?>>
        <label for="clean_dashboard_wc_options[hide_upsells]"><?php _e('Ocultar promociones y sugerencias de plugins', 'clean-dashboard-wc'); ?></label>
        <p class="description"><?php _e('Marketplace de WooCommerce, extensiones sugeridas, etc.', 'clean-dashboard-wc'); ?></p>
        <?php
    }
    
    /**
     * Campo para roles de usuario
     */
    public function apply_to_roles_render() {
        $options = get_option('clean_dashboard_wc_options');
        $selected = isset($options['apply_to_roles']) ? $options['apply_to_roles'] : array('administrator', 'shop_manager');
        
        $roles = get_editable_roles();
        ?>
        <fieldset>
            <?php foreach ($roles as $role_id => $role): ?>
                <label>
                    <input type="checkbox" name="clean_dashboard_wc_options[apply_to_roles][]" value="<?php echo esc_attr($role_id); ?>" 
                        <?php checked(in_array($role_id, $selected), true); ?>>
                    <?php echo esc_html($role['name']); ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>
        <p class="description"><?php _e('Selecciona los roles de usuario a los que aplicar la limpieza del dashboard.', 'clean-dashboard-wc'); ?></p>
        <?php
    }
    
    /**
     * Página de opciones
     */
    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Clean Dashboard for WooCommerce', 'clean-dashboard-wc'); ?></h1>
            
            <div style="background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #0073aa;">
                <h3><?php _e('Estado del Plugin', 'clean-dashboard-wc'); ?></h3>
                <p><?php _e('El plugin está activo y limpiando el dashboard según la configuración actual.', 'clean-dashboard-wc'); ?></p>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('clean_dashboard_wc');
                do_settings_sections('clean_dashboard_wc');
                submit_button();
                ?>
            </form>
            
            <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
                <h3><?php _e('¿Qué hace este plugin?', 'clean-dashboard-wc'); ?></h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php _e('Elimina widgets innecesarios del dashboard', 'clean-dashboard-wc'); ?></li>
                    <li><?php _e('Mantiene solo los widgets esenciales de WooCommerce', 'clean-dashboard-wc'); ?></li>
                    <li><?php _e('Oculta promociones y upsells molestos', 'clean-dashboard-wc'); ?></li>
                    <li><?php _e('Mejora la experiencia del usuario en el admin', 'clean-dashboard-wc'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Verificar si aplicar la limpieza al usuario actual
     */
    private function should_apply_cleaning() {
        if (!is_admin()) return false;
        
        $user = wp_get_current_user();
        $options = get_option('clean_dashboard_wc_options');
        $allowed_roles = isset($options['apply_to_roles']) ? $options['apply_to_roles'] : array('administrator', 'shop_manager');
        
        return array_intersect($allowed_roles, $user->roles);
    }
    
    /**
     * Limpiar el dashboard
     */
    public function clean_dashboard() {
        if (!$this->should_apply_cleaning()) {
            return;
        }
        
        global $wp_meta_boxes;
        $options = get_option('clean_dashboard_wc_options');
        
        // Widgets permitidos basados en configuración
        $allowed_widgets = array();
        
        if (isset($options['woocommerce_widgets'])) {
            if (in_array('status', $options['woocommerce_widgets'])) {
                $allowed_widgets[] = 'woocommerce_dashboard_status';
            }
            if (in_array('reviews', $options['woocommerce_widgets'])) {
                $allowed_widgets[] = 'woocommerce_dashboard_recent_reviews';
            }
            if (in_array('orders', $options['woocommerce_widgets'])) {
                $allowed_widgets[] = 'woocommerce_network_orders';
            }
        }
        
        $allowed_widgets[] = 'wc_admin_dashboard_setup';
        
        // Remover widgets no deseados
        if (isset($wp_meta_boxes['dashboard'])) {
            foreach ($wp_meta_boxes['dashboard'] as $context => $priority_array) {
                foreach ($priority_array as $priority => $widgets) {
                    foreach ($widgets as $widget_id => $widget_data) {
                        if (!in_array($widget_id, $allowed_widgets)) {
                            remove_meta_box($widget_id, 'dashboard', $context);
                        }
                    }
                }
            }
        }
        
        // Remover widgets específicos si está activada la opción
        if (isset($options['hide_wp_widgets']) && $options['hide_wp_widgets']) {
            $wp_widgets_to_remove = array(
                'dashboard_primary', 'dashboard_secondary', 'dashboard_quick_press',
                'dashboard_recent_drafts', 'dashboard_activity', 'dashboard_right_now',
                'dashboard_site_health', 'wpseo-dashboard-overview', 'jetpack_summary_widget',
            );
            
            foreach ($wp_widgets_to_remove as $widget) {
                remove_meta_box($widget, 'dashboard', 'normal');
                remove_meta_box($widget, 'dashboard', 'side');
            }
        }
        
        // Ocultar upsells si está activada la opción
        if (isset($options['hide_upsells']) && $options['hide_upsells']) {
            add_action('admin_head', array($this, 'hide_upsells_css'));
            $this->remove_upsell_menus();
        }
    }
    
    /**
     * CSS para ocultar upsells
     */
    public function hide_upsells_css() {
        echo '<style>
            .woocommerce-message[data-message-id*="connect"],
            .woocommerce-message[data-message-id*="marketing"],
            .woocommerce-marketing-notifications-panel,
            a[href*="wc-addons"],
            .woocommerce-admin-page .woocommerce-store-alerts {
                display: none !important;
            }
        </style>';
    }
    
    /**
     * Remover menús de upsell
     */
    private function remove_upsell_menus() {
        remove_submenu_page('woocommerce', 'wc-addons');
    }
}

// Inicializar el plugin
new CleanWooCommerceDashboard();

/**
 * Función para verificar el estado del plugin
 */
function clean_dashboard_wc_plugin_status() {
    $options = get_option('clean_dashboard_wc_options');
    if ($options) {
        return '<span style="color: green;">✓ ' . __('Configurado', 'clean-dashboard-wc') . '</span>';
    }
    return '<span style="color: orange;">⚙️ ' . __('Necesita configuración', 'clean-dashboard-wc') . '</span>';
}

/**
 * Acción al activar el plugin
 */
register_activation_hook(__FILE__, 'clean_dashboard_wc_activate');
function clean_dashboard_wc_activate() {
    // Configuración por defecto
    $default_options = array(
        'hide_wp_widgets' => 1,
        'woocommerce_widgets' => array('status', 'reviews'),
        'hide_upsells' => 1,
        'apply_to_roles' => array('administrator', 'shop_manager')
    );
    
    add_option('clean_dashboard_wc_options', $default_options);
}
?>
