<?php 
if (!defined('ABSPATH')) {
    exit;
}
function rvbs_create_rv_lot_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rvbs_rv_lots';
    $charset_collate = $wpdb->get_charset_collate();

    // Check if the table already exists
    $table_exists = $wpdb->get_var($wpdb->prepare(
        "SHOW TABLES LIKE %s", $table_name
    ));

    if ($table_exists === $table_name) {
        return; // Table already exists, no need to create it again
    }

    // SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        post_id INT NOT NULL,
        user_id INT NULL,
        start_date DATE NULL,
        end_date DATE NULL,
        is_available TINYINT(1) NOT NULL DEFAULT 1,
        status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
        is_trash TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY post_id (post_id)
    ) $charset_collate;";

    // Include the upgrade.php file for dbDelta()
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Execute the SQL query
    dbDelta($sql);

    // Check for errors
    if (!empty($wpdb->last_error)) {
        error_log('Error creating RV bookings table: ' . $wpdb->last_error);
    } else {
        error_log('RV bookings table created successfully.');
    }

    // Add foreign key constraint separately (if needed)
    $foreign_key_sql = "ALTER TABLE $table_name
        ADD CONSTRAINT fk_post_id
        FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(ID)
        ON DELETE CASCADE;";

    // Execute the foreign key query
    $wpdb->query($foreign_key_sql);

    // Check for errors in foreign key creation
    if (!empty($wpdb->last_error)) {
        error_log('Error adding foreign key constraint: ' . $wpdb->last_error);
    } else {
        error_log('Foreign key constraint added successfully.');
    }
}



// second database  record how many time a user has booked a post or how many times a rv lot has been booked

function rvbs_create_rv_lot_bookings_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rvbs_bookings';
    $charset_collate = $wpdb->get_charset_collate();

    // Check if the table already exists
    $table_exists = $wpdb->get_var($wpdb->prepare(
        "SHOW TABLES LIKE %s", $table_name
    ));

    if ($table_exists === $table_name) {
        return; // Table already exists, no need to create it again
    }

    // SQL query to create the booking table
    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        lot_id INT NOT NULL,
        user_id INT NULL,
        check_in DATE NOT NULL,
        check_out DATE NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY lot_id (lot_id),
        CONSTRAINT fk_lot_id FOREIGN KEY (lot_id) REFERENCES {$wpdb->prefix}rvbs_rv_lots(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    if (!empty($wpdb->last_error)) {
        error_log('Error creating RV lot bookings table: ' . $wpdb->last_error);
    } else {
        error_log('RV lot bookings table created successfully.');
    }
}

function rvbs_create_rv_lot_tables() {
    rvbs_create_rv_lot_table(); // Creates the rvbs_rv_lots table
    rvbs_create_rv_lot_bookings_table(); // Creates the rvbs_bookings table
}





?>