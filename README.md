#Clean Dashboard for WooCommerce# 🧹

A WordPress plugin that allows you to completely clean and customize the admin dashboard. Hide unwanted widgets, annoying promotions, and get full control over which elements to display.

✨ Key Features
🎯 Granular Widget Control
Automatic detection of all dashboard widgets

Individual selection per widget (WordPress, WooCommerce, other plugins)

Smart categorization with visual interface

Visible IDs for easy identification

🛒 WooCommerce Specialized
Maintains essential WooCommerce widgets

Hides annoying promotions and upsells

Compatible with WooCommerce stores

⚙️ Flexible Configuration
Apply by user roles

Smart default configuration

User-friendly interface

📦 Installation
Method 1: Direct Upload
Download the plugin ZIP file

Go to Plugins > Add New > Upload Plugin in your WordPress

Upload the ZIP file and activate the plugin

Method 2: Manual Installation
Extract the ZIP file

Upload the clean-woocommerce-dashboard folder to /wp-content/plugins/

Activate the plugin in Plugins > Installed Plugins

Method 3: Development
bash
cd wp-content/plugins/
git clone [repository-url] clean-woocommerce-dashboard
🚀 Quick Start
Basic Configuration
Activate the plugin in Plugins > Installed Plugins

Go to Settings > Clean Dashboard

Select the widgets you want to keep visible

Save changes and reload the dashboard

Recommended WooCommerce Setup
✅ woocommerce_dashboard_status - Store status

✅ woocommerce_dashboard_recent_reviews - Recent reviews

✅ woocommerce_dashboard_recent_orders - Recent orders

❌ All other WordPress widgets

❌ Promotions and upsells

🎛️ Configuration Panel
Allowed Widgets
WooCommerce Widgets 🛒: Status, reviews, orders

WordPress Widgets ⚙️: Activity, news, drafts

Other Plugin Widgets 🔌: Wordfence, CTX Feed, etc.

Additional Options
Apply to roles: Select which user roles will see the cleaned dashboard

Hide promotions: Remove upsells and plugin suggestions

🏗️ Project Structure
text
clean-woocommerce-dashboard/
├── clean-dashboard.php          # Main plugin file
├── languages/                   # Translations (optional)
│   └── clean-dashboard-wc-es_ES.po
├── assets/                      # Resources (optional)
│   ├── css/
│   └── js/
└── README.md                    # This file
🔧 Advanced Customization
Available Filters
php
// Modify default allowed widgets
add_filter('clean_dashboard_default_widgets', function($widgets) {
    $widgets[] = 'my_custom_widget';
    return $widgets;
});

// Modify allowed roles
add_filter('clean_dashboard_allowed_roles', function($roles) {
    $roles[] = 'editor';
    return $roles;
});
Available Hooks
php
// Before cleaning the dashboard
add_action('clean_dashboard_before_cleanup', function() {
    // Your code here
});

// After cleaning the dashboard  
add_action('clean_dashboard_after_cleanup', function() {
    // Your code here
});
🐛 Troubleshooting
Widgets Not Hiding
Verify the plugin is activated

Check configuration in Settings > Clean Dashboard

Ensure your user role is selected

"Error loading widget" Message
The plugin uses correct priorities to load after WooCommerce

Verify WooCommerce is updated

Not All Widgets Detected
Save configuration and reload the page

Some widgets may load dynamically

📋 Changelog
Version 2.0 (Current)
✅ Automatic detection of all widgets

✅ Individual widget selection

✅ Categorized and improved interface

✅ Support for Wordfence, CTX Feed and other plugins

Version 1.3
✅ Fixed WooCommerce widget errors

✅ Improved execution priority

✅ Dependency detection

Version 1.2
✅ Support for hiding Elementor widget

✅ Improved status notifications

🤝 Contributing
Contributions are welcome. Please:

Fork the project

Create a feature branch (git checkout -b feature/AmazingFeature)

Commit your changes (git commit -m 'Add some AmazingFeature')

Push to the branch (git push origin feature/AmazingFeature)

Open a Pull Request

📝 License
This project is licensed under the GPL v2 or later license. See the LICENSE file for details.

🙋‍♂️ Support
If you need help:

Check the troubleshooting section above

Open an issue on GitHub

Contact the developer

🏆 Credits
Developed with ❤️ for the WordPress and WooCommerce community.

Like this plugin? Give it a star ⭐ on GitHub!

📞 Need Customizations?
If you need specific functionalities for your project, contact me to develop a customized version of the plugin.

Clean dashboard, focused mind! 🚀
