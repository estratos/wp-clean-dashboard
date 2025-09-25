<?php
/**
 * Plugin Name: Clean Dashboard for WooCommerce
 * Description: Oculta todos los widgets del dashboard excepto los de WooCommerce. Incluye panel de configuración.
 * Version: 1.3
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
        
        // Cargar traducciones
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Añadir enlace de configuración
        add_action('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
        
        // Inicializar menú de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        
        // Limpiar dashboard con prioridad alta (se ejecuta después)
        add_action('wp_dashboard_setup', array($this, 'clean_dashboard'), 999);
        
        // Widget de Elementor con prioridad alta
        add_action('wp_dashboard_setup', array($this, 'remove_elementor_widget'), 999);
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
        
        // Campo para widgets de WooCommerce (solo si WooCommerce está activo)
        if ($this->is_woocommerce_active()) {
            add_settings_field(
                'woocommerce_widgets', 
                __('Widgets de WooCommerce a Mostrar', 'clean-dashboard-wc'), 
                array($this, 'woocommerce_widgets_render'), 
                'clean_dashboard_wc', 
                'clean_dashboard_wc_section'
            );
        }
        
        // Campo para upsells
        add_settings_field(
            'hide_upsells', 
            __('Ocultar Promociones y Upsells', 'clean-dashboard-wc'), 
            array($this, 'hide_upsells_render'), 
            'clean_dashboard_wc', 
            'clean_dashboard_wc_section'
        );
        
        // Campo para Elementor (solo si Elementor está activo)
        if ($this->is_elementor_active()) {
            add_settings_field(
                'hide_elementor_widget', 
                __('Ocultar Widget de Elementor', 'clean-dashboard-wc'), 
                array($this, 'hide_elementor_widget_render'), 
                'clean_dashboard_wc', 
                'clean_dashboard_wc_section'
            );
        }
        
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
     * Verificar si WooCommerce está activo
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Verificar si Elementor está activo
     */
    private function is_elementor_active() {
        return did_action('elementor/loaded');
    }
    
    /**
     * Descripción de la sección
     */
    public function settings_section_callback() {
        echo __('Configura qué elementos quieres mostrar u ocultar en el dashboard de WordPress.', 'clean-dashboard-wc');
        
        // Mostrar estado de dependencias
        echo '<div style="margin-top: 15px; padding: 10px; background: #f0f0f1; border-radius: 4px;">';
        echo '<strong>' . __('Estado de dependencias:', 'clean-dashboard-wc') . '</strong><br>';
        echo '- WooCommerce: ' . ($this->is_woocommerce_active() ? '✅ Activo' : '❌ Inactivo') . '<br>';
        echo '- Elementor: ' . ($this->is_elementor_active() ? '✅ Activo' : '❌ Inactivo');
        echo '</div>';
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
        <p class="description"><?php _e('Nota: Algunos widgets pueden requerir permisos específicos para mostrarse correctamente.', 'clean-dashboard-wc'); ?></p>
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
     * Campo para ocultar widget de Elementor
     */
    public function hide_elementor_widget_render() {
        $options = get_option('clean_dashboard_wc_options');
        $value = isset($options['hide_elementor_widget']) ? $options['hide_elementor_widget'] : 1;
        ?>
        <input type="checkbox" name="clean_dashboard_wc_options[hide_elementor_widget]" value="1" <?php checked(1, $value, true); ?>>
        <label for="clean_dashboard_wc_options[hide_elementor_widget]"><?php _e('Ocultar widget "Conoce Elementor"', 'clean-dashboard-wc'); ?></label>
        <p class="description"><?php _e('Remove el widget de promoción de Elementor Pro del dashboard', 'clean-dashboard-wc'); ?></p>
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
                
                <?php if (!$this->is_woocommerce_active()): ?>
                <div style="color: #d63638; background: #fcf0f1; padding: 10px; border-radius: 4px; margin-top: 10px;">
                    <strong>⚠️ <?php _e('Aviso importante:', 'clean-dashboard-wc'); ?></strong><br>
                    <?php _e('WooCommerce no está activo. Los widgets de WooCommerce no estarán disponibles.', 'clean-dashboard-wc'); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('clean_dashboard_wc');
                do_settings_sections('clean_dashboard_wc');
                submit_button();
                ?>
            </form>
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
     * Remover widget de Elementor específicamente
     */
    public function remove_elementor_widget() {
        if (!$this->should_apply_cleaning() || !$this->is_elementor_active()) {
            return;
        }
        
        $options = get_option('clean_dashboard_wc_options');
        
        if (isset($options['hide_elementor_widget']) && $options['hide_elementor_widget']) {
            // Métodos múltiples para asegurar que se oculta
            remove_meta_box('e-dashboard-overview', 'dashboard', 'normal');
            add_action('admin_head', array($this, 'hide_elementor_css'));
        }
    }
    
    /**
     * CSS para ocultar widget de Elementor
     */
    public function hide_elementor_css() {
        echo '<style>
            #e-dashboard-overview,
            .e-dashboard-overview,
            [class*="elementor-dashboard-overview"] {
                display: none !important;
            }
        </style>';
    }
    
    /**
     * Limpiar el dashboard - MÉTODO MEJORADO
     */
    public function clean_dashboard() {
        if (!$this->should_apply_cleaning()) {
            return;
        }
        
        $options = get_option('clean_dashboard_wc_options');
        
        // 1. Primero ocultar widgets de WordPress si está activado
        if (isset($options['hide_wp_widgets']) && $options['hide_wp_widgets']) {
            $this->remove_wordpress_widgets();
        }
        
        // 2. Manejar widgets de WooCommerce si está activo
        if ($this->is_woocommerce_active()) {
            $this->handle_woocommerce_widgets($options);
        }
        
        // 3. Ocultar upsells si está activado
        if (isset($options['hide_upsells']) && $options['hide_upsells']) {
            $this->hide_upsells();
        }
    }
    
    /**
     * Remover widgets de WordPress
     */
    private function remove_wordpress_widgets() {
        $widgets_to_remove = array(
            'dashboard_primary' => 'side',           // Noticias de WordPress
            'dashboard_secondary' => 'side',         // Otras noticias
            'dashboard_quick_press' => 'side',       // Borrador rápido
            'dashboard_recent_drafts' => 'side',     // Borradores recientes
            'dashboard_activity' => 'normal',        // Actividad reciente
            'dashboard_right_now' => 'normal',       // Ahora mismo
            'dashboard_site_health' => 'normal',     // Estado del sitio
        );
        
        foreach ($widgets_to_remove as $widget => $context) {
            remove_meta_box($widget, 'dashboard', $context);
        }
        
        // Widgets de plugins comunes
        $plugin_widgets = array(
            'wpseo-dashboard-overview' => 'normal',  // Yoast SEO
            'jetpack_summary_widget' => 'normal',    // Jetpack
        );
        
        foreach ($plugin_widgets as $widget => $context) {
            remove_meta_box($widget, 'dashboard', $context);
        }
    }
    
    /**
     * Manejar widgets de WooCommerce
     */
    private function handle_woocommerce_widgets($options) {
        global $wp_meta_boxes;
        
        // Widgets de WooCommerce disponibles
        $woocommerce_widgets = array(
            'status' => 'woocommerce_dashboard_status',
            'reviews' => 'woocommerce_dashboard_recent_reviews', 
            'orders' => 'woocommerce_dashboard_recent_orders'
        );
        
        // Si no hay configuración específica, mantener todos los widgets de WC
        if (!isset($options['woocommerce_widgets']) || empty($options['woocommerce_widgets'])) {
            return; // Mantener todos los widgets de WooCommerce
        }
        
        // Remover widgets de WooCommerce no seleccionados
        foreach ($woocommerce_widgets as $key => $widget_id) {
            if (!in_array($key, $options['woocommerce_widgets'])) {
                remove_meta_box($widget_id, 'dashboard', 'normal');
            }
        }
        
        // Asegurar que los widgets seleccionados se mantengan
        foreach ($options['woocommerce_widgets'] as $selected_widget) {
            if (isset($woocommerce_widgets[$selected_widget])) {
                $widget_id = $woocommerce_widgets[$selected_widget];
                // El widget ya debería estar cargado por WooCommerce
            }
        }
    }
    
    /**
     * Ocultar upsells y promociones
     */
    private function hide_upsells() {
        add_action('admin_head', array($this, 'hide_upsells_css'));
        
        // Remover menús de upsell
        remove_submenu_page('woocommerce', 'wc-addons');
        
        // Remover notificaciones de WooCommerce
        add_filter('woocommerce_helper_suppress_admin_notices', '__return_true');
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
}

// Inicializar el plugin
new CleanWooCommerceDashboard();

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
        'hide_elementor_widget' => 1,
        'apply_to_roles' => array('administrator', 'shop_manager')
    );
    
    add_option('clean_dashboard_wc_options', $default_options);
}
?>
