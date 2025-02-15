<?php 


function rvbs_add_all_script() {

    // add the jquery support 
    wp_enqueue_script('jquery');
    // Add Bootstrap JS with dependencies
    // 
    wp_enqueue_script('rvbs-bootstrap-js', plugin_dir_url(__FILE__) . '../assets/js/rvbs-bootstrap.min.js', array('jquery'), PLUGIN_VER, true);
    wp_enqueue_script('rvbs-search-rv', plugin_dir_url(__FILE__) . '../assets/js/rvbs-search-rv.js', array('jquery'), PLUGIN_VER, true);

    wp_localize_script('rvbs-search-rv','rvbs_serch_rv',[
        'ajax_url' => admin_url('admin-ajax.php'),
        'action' => 'load_more_posts'
    ]);


}
add_action('wp_enqueue_scripts', 'rvbs_add_all_script');



?>