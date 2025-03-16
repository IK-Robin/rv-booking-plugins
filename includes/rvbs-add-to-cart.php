<?php 



// Start the session if not already started
function start_custom_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'start_custom_session', 1);