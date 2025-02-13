<?php
var_dump('d');

// Hook to include custom template from plugin
add_filter('theme_page_templates', 'my_custom_plugin_add_template');

function my_custom_plugin_add_template($templates) {
    $templates['my-custom-template.php'] = 'My Custom Template';
    return $templates;
}

add_filter('template_include', 'my_custom_plugin_load_template');

function my_custom_plugin_load_template($template) {
    if (is_page('my-custom-page')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/my-custom-template.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
?>