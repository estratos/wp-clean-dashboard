<?php
/**
 * Plugin Name: Clean Dashboard for WooCommerce
 * Description: Oculta todos los widgets del dashboard excepto los de WooCommerce. Incluye panel de configuración.
 * Version: 2.0
 * Author: Tu Nombre
 * Text Domain: clean-dashboard-wc
 */

if (!defined('ABSPATH')) {
    exit;
}

class CleanWooCommerceDashboard {
    
    private $options;
    private $detected_widgets = array();
    
    public function __construct() {
        $this->options = get_option('clean_dashboard_wc_options');
        
        // Cargar traducciones
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Añadir enlace de configuración
        add_action('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
        
        // Inicializar menú de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        
        // Detectar widgets disponibles
        add_action('wp_dashboard_setup', array($this, 'detect_widgets'), 1);
        
        // Limpiar dashboard con prioridad alta (se ejecuta después)
        add_action('wp_dashboard_setup', array($this, 'clean_dashboard'), 999);
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
     * Detectar todos los widgets disponibles en el dashboard
     */
    public function detect_widgets() {
        global $wp_meta_boxes;
        
        if (isset($wp_meta_boxes['dashboard'])) {
            foreach ($wp_meta_boxes['dashboard'] as $context => $priority_array) {
                foreach ($priority_array as $priority => $widgets) {
                    foreach ($widgets as $widget_id => $widget_data) {
                        $this->detected_widgets[$widget_id] = array(
                            'title' => isset($widget_data['title']) ? $widget_data['title'] : $widget_id,
                            'context' => $context,
                            'priority' => $priority
                        );
                    }
                }
            }
        }
        
        // Guardar widgets detectados en una opción transitoria para usar en el admin
        set_transient('clean_dashboard_detected_widgets', $this->detected_widgets, 60);
    }
    
    /**
     * Obtener widgets detectados
     */
    private function get_detected_widgets() {
        $widgets = get_transient('clean_dashboard_detected_widgets');
        return $widgets ? $widgets : array();
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
        
        // Campo para selección individual de widgets
        add_settings_field(
            'allowed_widgets', 
            __('Widgets Permitidos en el Dashboard', 'clean-dashboard-wc'), 
            array($this, 'allowed_widgets_render'), 
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
        
        // Campo para ocultar upsells
        add_settings_field(
            'hide_upsells', 
            __('Ocultar Promociones y Upsells', 'clean-dashboard-wc'), 
            array($this, 'hide_upsells_render'), 
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
     * Descripción de la sección
     */
    public function settings_section_callback() {
        echo __('Selecciona exactamente qué widgets quieres mostrar en el dashboard. Solo los widgets seleccionados serán visibles.', 'clean-dashboard-wc');
        
        $detected_widgets = $this->get_detected_widgets();
        $widgets_count = count($detected_widgets);
        
        echo '<div style="margin-top: 15px; padding: 10px; background: #f0f0f1; border-radius: 4px;">';
        echo '<strong>' . __('Widgets detectados:', 'clean-dashboard-wc') . '</strong> ' . $widgets_count . '<br>';
        echo '<strong>' . __('WooCommerce:', 'clean-dashboard-wc') . '</strong> ' . ($this->is_woocommerce_active() ? '✅ Activo' : '❌ Inactivo');
        echo '</div>';
        
        // Mostrar lista de widgets detectados
        if ($widgets_count > 0) {
            echo '<div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px; font-size: 12px;">';
            echo '<strong>Widgets disponibles:</strong><br>';
            $widget_names = array();
            foreach ($detected_widgets as $widget_id => $widget_data) {
                $widget_names[] = $widget_data['title'] . ' (' . $widget_id . ')';
            }
            echo implode(', ', array_slice($widget_names, 0, 10));
            if ($widgets_count > 10) echo '... y ' . ($widgets_count - 10) . ' más';
            echo '</div>';
        }
    }
    
    /**
     * Campo para selección individual de widgets
     */
    public function allowed_widgets_render() {
        $options = get_option('clean_dashboard_wc_options');
        $selected_widgets = isset($options['allowed_widgets']) ? $options['allowed_widgets'] : array();
        $detected_widgets = $this->get_detected_widgets();
        
        // Widgets recomendados (WooCommerce)
        $recommended_widgets = array(
            'woocommerce_dashboard_status',
            'woocommerce_dashboard_recent_reviews',
            'woocommerce_dashboard_recent_orders'
        );
        
        // Categorizar widgets
        $wordpress_widgets = array();
        $woocommerce_widgets = array();
        $plugin_widgets = array();
        
        foreach ($detected_widgets as $widget_id => $widget_data) {
            $category = 'plugin';
            
            if (strpos($widget_id, 'dashboard_') === 0) {
                $category = 'wordpress';
            } elseif (strpos($widget_id, 'woocommerce_') === 0) {
                $category = 'woocommerce';
            } elseif (strpos($widget_id, 'wc_') === 0) {
                $category = 'woocommerce';
            }
            
            ${$category . '_widgets'}[$widget_id] = $widget_data;
        }
        
        // Ordenar alfabéticamente
        ksort($wordpress_widgets);
        ksort($woocommerce_widgets);
        ksort($plugin_widgets);
        ?>
        
        <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 15px; background: #fafafa;">
            
            <?php if (!empty($woocommerce_widgets)): ?>
            <div style="margin-bottom: 20px;">
                <h4>🛒 <?php _e('Widgets de WooCommerce', 'clean-dashboard-wc'); ?></h4>
                <?php foreach ($woocommerce_widgets as $widget_id => $widget_data): ?>
                <label style="display: block; margin: 5px 0; padding: 5px; background: white; border-radius: 3px;">
                    <input type="checkbox" name="clean_dashboard_wc_options[allowed_widgets][]" 
                           value="<?php echo esc_attr($widget_id); ?>" 
                           <?php checked(in_array($widget_id, $selected_widgets), true); ?>
                           <?php if (in_array($widget_id, $recommended_widgets)) echo 'style="border-color: #0073aa;"'; ?>>
                    <strong><?php echo esc_html($widget_data['title']); ?></strong>
                    <code style="font-size: 11px; color: #666; margin-left: 10px;"><?php echo esc_html($widget_id); ?></code>
                    <?php if (in_array($widget_id, $recommended_widgets)): ?>
                    <span style="color: #0073aa; font-size: 11px; margin-left: 5px;">(recomendado)</span>
                    <?php endif; ?>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($wordpress_widgets)): ?>
            <div style="margin-bottom: 20px;">
                <h4>⚙️ <?php _e('Widgets de WordPress', 'clean-dashboard-wc'); ?></h4>
                <?php foreach ($wordpress_widgets as $widget_id => $widget_data): ?>
                <label style="display: block; margin: 5px 0; padding: 5px; background: white; border-radius: 3px;">
                    <input type="checkbox" name="clean_dashboard_wc_options[allowed_widgets][]" 
                           value="<?php echo esc_attr($widget_id); ?>" 
                           <?php checked(in_array($widget_id, $selected_widgets), true); ?>>
                    <strong><?php echo esc_html($widget_data['title']); ?></strong>
                    <code style="font-size: 11px; color: #666; margin-left: 10px;"><?php echo esc_html($widget_id); ?></code>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($plugin_widgets)): ?>
            <div style="margin-bottom: 20px;">
                <h4>🔌 <?php _e('Widgets de Otros Plugins', 'clean-dashboard-wc'); ?></h4>
                <?php foreach ($plugin_widgets as $widget_id => $widget_data): ?>
                <label style="display: block; margin: 5px 0; padding: 5px; background: white; border-radius: 3px;">
                    <input type="checkbox" name="clean_dashboard_wc_options[allowed_widgets][]" 
                           value="<?php echo esc_attr($widget_id); ?>" 
                           <?php checked(in_array($widget_id, $selected_widgets), true); ?>>
                    <strong><?php echo esc_html($widget_data['title']); ?></strong>
                    <code style="font-size: 11px; color: #666; margin-left: 10px;"><?php echo esc_html($widget_id); ?></code>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (empty($detected_widgets)): ?>
            <p style="color: #666; font-style: italic;">
                <?php _e('No se detectaron widgets. Guarda la configuración y recarga la página para detectar los widgets disponibles.', 'clean-dashboard-wc'); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <p class="description">
            <?php _e('Solo los widgets seleccionados serán visibles en el dashboard. Los widgets no seleccionados serán ocultados.', 'clean-dashboard-wc'); ?>
        </p>
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
                <p><?php _e('Selecciona los widgets específicos que quieres mostrar en el dashboard.', 'clean-dashboard-wc'); ?></p>
                
                <?php 
                $detected_widgets = $this->get_detected_widgets();
                $options = get_option('clean_dashboard_wc_options');
                $selected_count = isset($options['allowed_widgets']) ? count($options['allowed_widgets']) : 0;
                ?>
                
                <p><strong><?php _e('Widgets detectados:', 'clean-dashboard-wc'); ?></strong> <?php echo count($detected_widgets); ?></p>
                <p><strong><?php _e('Widgets permitidos:', 'clean-dashboard-wc'); ?></strong> <?php echo $selected_count; ?></p>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('clean_dashboard_wc');
                do_settings_sections('clean_dashboard_wc');
                submit_button('Guardar Configuración');
                ?>
            </form>
            
            <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
                <h3>💡 <?php _e('Cómo usar este plugin', 'clean-dashboard-wc'); ?></h3>
                <ol>
                    <li><?php _e('Selecciona los widgets que quieres mantener visibles', 'clean-dashboard-wc'); ?></li>
                    <li><?php _e('Guarda la configuración', 'clean-dashboard-wc'); ?></li>
                    <li><?php _e('Recarga la página del dashboard para ver los cambios', 'clean-dashboard-wc'); ?></li>
                    <li><?php _e('Los widgets no seleccionados serán ocultados automáticamente', 'clean-dashboard-wc'); ?></li>
                </ol>
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
     * Limpiar el dashboard basado en la selección del usuario
     */
    public function clean_dashboard() {
        if (!$this->should_apply_cleaning()) {
            return;
        }
        
        $options = get_option('clean_dashboard_wc_options');
        $allowed_widgets = isset($options['allowed_widgets']) ? $options['allowed_widgets'] : array();
        
        global $wp_meta_boxes;
        
        // Si no hay widgets permitidos definidos, usar configuración por defecto
        if (empty($allowed_widgets)) {
            $allowed_widgets = $this->get_default_allowed_widgets();
        }
        
        // Remover todos los widgets excepto los permitidos
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
        
        // Ocultar upsells si está activado
        if (isset($options['hide_upsells']) && $options['hide_upsells']) {
            $this->hide_upsells();
        }
    }
    
    /**
     * Obtener widgets permitidos por defecto
     */
    private function get_default_allowed_widgets() {
        $default_widgets = array();
        
        // Widgets de WooCommerce por defecto
        if ($this->is_woocommerce_active()) {
            $default_widgets = array(
                'woocommerce_dashboard_status',
                'woocommerce_dashboard_recent_reviews',
                'woocommerce_dashboard_recent_orders'
            );
        }
        
        return $default_widgets;
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
        'allowed_widgets' => array(),
        'hide_upsells' => 1,
        'apply_to_roles' => array('administrator', 'shop_manager')
    );
    
    add_option('clean_dashboard_wc_options', $default_options);
}

/**
 * Limpiar transitorios al desactivar el plugin
 */
register_deactivation_hook(__FILE__, 'clean_dashboard_wc_deactivate');
function clean_dashboard_wc_deactivate() {
    delete_transient('clean_dashboard_detected_widgets');
}
?>
