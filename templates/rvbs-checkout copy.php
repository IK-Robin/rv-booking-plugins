<?php
/*
Template Name: Checkout
*/

// Check if the theme is block-based (Full Site Editing)
$is_fse_theme = wp_is_block_theme();

// Start session
if (!session_id()) {
    session_start();
}

// Get cart data from session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_total = 0;

// Redirect to cart if empty
if (empty($cart)) {
    wp_redirect(home_url('/shopping-cart/'));
    exit;
}

// Calculate cart total and gather order summary data
$order_items = [];
foreach ($cart as $item) {
    $post_id = $item['post_id'];
    $price = floatval(get_post_meta($post_id, '_rv_lots_price', true)) ?: 20.00;
    $check_in = new DateTime($item['check_in']);
    $check_out = new DateTime($item['check_out']);
    $nights = $check_in->diff($check_out)->days ?: 1;
    $subtotal = $price * $nights;
    $cart_total += $subtotal;

    $order_items[] = [
        'title' => $item['room_title'],
        'details' => sprintf(
            '%s (%d Nights)',
            $check_in->format('D, M d') . ' - ' . $check_out->format('D, M d'),
            $nights
        ),
        'guests' => sprintf(
            '%d Adults',
            $item['adults']
        ),
        'subtotal' => $subtotal,
        'lot_id' => $post_id, // Assuming post_id is the lot_id
        'post_id' => $post_id
    ];
}

// Add campground fees
$campground_fees = 5.00;
$total_charges = $cart_total + $campground_fees;

// List of countries (abbreviated for brevity; include all as in previous responses)
$countries = [
    'AF' => 'Afghanistan',
    'AX' => 'Aland Islands',
    'AL' => 'Albania',
    'DZ' => 'Algeria',
    'AS' => 'American Samoa',
    'AD' => 'Andorra',
    'AO' => 'Angola',
    'AI' => 'Anguilla',
    'AQ' => 'Antarctica',
    'AG' => 'Antigua and Barbuda',
    'AR' => 'Argentina',
    'AM' => 'Armenia',
    'AW' => 'Aruba',
    'AU' => 'Australia',
    'AT' => 'Austria',
    'AZ' => 'Azerbaijan',
    'BS' => 'Bahamas',
    'BH' => 'Bahrain',
    'BD' => 'Bangladesh',
    'BB' => 'Barbados',
    'BY' => 'Belarus',
    'BE' => 'Belgium',
    'BZ' => 'Belize',
    'BJ' => 'Benin',
    'BM' => 'Bermuda',
    'BT' => 'Bhutan',
    'BO' => 'Bolivia',
    'BQ' => 'Bonaire, Sint Eustatius and Saba',
    'BA' => 'Bosnia and Herzegovina',
    'BW' => 'Botswana',
    'BV' => 'Bouvet Island',
    'BR' => 'Brazil',
    'IO' => 'British Indian Ocean Territory',
    'BN' => 'Brunei Darussalam',
    'BG' => 'Bulgaria',
    'BF' => 'Burkina Faso',
    'BI' => 'Burundi',
    'KH' => 'Cambodia',
    'CM' => 'Cameroon',
    'CA' => 'Canada',
    'CV' => 'Cape Verde',
    'KY' => 'Cayman Islands',
    'CF' => 'Central African Republic',
    'TD' => 'Chad',
    'CL' => 'Chile',
    'CN' => 'China',
    'CX' => 'Christmas Island',
    'CC' => 'Cocos (Keeling) Islands',
    'CO' => 'Colombia',
    'KM' => 'Comoros',
    'CG' => 'Congo',
    'CD' => 'Congo, Democratic Republic of the',
    'CK' => 'Cook Islands',
    'CR' => 'Costa Rica',
    'CI' => 'Côte d\'Ivoire',
    'HR' => 'Croatia',
    'CU' => 'Cuba',
    'CW' => 'Curaçao',
    'CY' => 'Cyprus',
    'CZ' => 'Czech Republic',
    'DK' => 'Denmark',
    'DJ' => 'Djibouti',
    'DM' => 'Dominica',
    'DO' => 'Dominican Republic',
    'EC' => 'Ecuador',
    'EG' => 'Egypt',
    'SV' => 'El Salvador',
    'GQ' => 'Equatorial Guinea',
    'ER' => 'Eritrea',
    'EE' => 'Estonia',
    'ET' => 'Ethiopia',
    'FK' => 'Falkland Islands (Malvinas)',
    'FO' => 'Faroe Islands',
    'FJ' => 'Fiji',
    'FI' => 'Finland',
    'FR' => 'France',
    'GF' => 'French Guiana',
    'PF' => 'French Polynesia',
    'TF' => 'French Southern Territories',
    'GA' => 'Gabon',
    'GM' => 'Gambia',
    'GE' => 'Georgia',
    'DE' => 'Germany',
    'GH' => 'Ghana',
    'GI' => 'Gibraltar',
    'GR' => 'Greece',
    'GL' => 'Greenland',
    'GD' => 'Grenada',
    'GP' => 'Guadeloupe',
    'GU' => 'Guam',
    'GT' => 'Guatemala',
    'GG' => 'Guernsey',
    'GN' => 'Guinea',
    'GW' => 'Guinea-Bissau',
    'GY' => 'Guyana',
    'HT' => 'Haiti',
    'HM' => 'Heard Island and McDonald Islands',
    'VA' => 'Holy See (Vatican City State)',
    'HN' => 'Honduras',
    'HK' => 'Hong Kong',
    'HU' => 'Hungary',
    'IS' => 'Iceland',
    'IN' => 'India',
    'ID' => 'Indonesia',
    'IR' => 'Iran, Islamic Republic of',
    'IQ' => 'Iraq',
    'IE' => 'Ireland',
    'IM' => 'Isle of Man',
    'IL' => 'Israel',
    'IT' => 'Italy',
    'JM' => 'Jamaica',
    'JP' => 'Japan',
    'JE' => 'Jersey',
    'JO' => 'Jordan',
    'KZ' => 'Kazakhstan',
    'KE' => 'Kenya',
    'KI' => 'Kiribati',
    'KP' => 'Korea, Democratic People\'s Republic of',
    'KR' => 'Korea, Republic of',
    'KW' => 'Kuwait',
    'KG' => 'Kyrgyzstan',
    'LA' => 'Lao People\'s Democratic Republic',
    'LV' => 'Latvia',
    'LB' => 'Lebanon',
    'LS' => 'Lesotho',
    'LR' => 'Liberia',
    'LY' => 'Libya',
    'LI' => 'Liechtenstein',
    'LT' => 'Lithuania',
    'LU' => 'Luxembourg',
    'MO' => 'Macao',
    'MK' => 'Macedonia, the former Yugoslav Republic of',
    'MG' => 'Madagascar',
    'MW' => 'Malawi',
    'MY' => 'Malaysia',
    'MV' => 'Maldives',
    'ML' => 'Mali',
    'MT' => 'Malta',
    'MH' => 'Marshall Islands',
    'MQ' => 'Martinique',
    'MR' => 'Mauritania',
    'MU' => 'Mauritius',
    'YT' => 'Mayotte',
    'MX' => 'Mexico',
    'FM' => 'Micronesia, Federated States of',
    'MD' => 'Moldova, Republic of',
    'MC' => 'Monaco',
    'MN' => 'Mongolia',
    'ME' => 'Montenegro',
    'MS' => 'Montserrat',
    'MA' => 'Morocco',
    'MZ' => 'Mozambique',
    'MM' => 'Myanmar',
    'NA' => 'Namibia',
    'NR' => 'Nauru',
    'NP' => 'Nepal',
    'NL' => 'Netherlands',
    'NC' => 'New Caledonia',
    'NZ' => 'New Zealand',
    'NI' => 'Nicaragua',
    'NE' => 'Niger',
    'NG' => 'Nigeria',
    'NU' => 'Niue',
    'NF' => 'Norfolk Island',
    'MP' => 'Northern Mariana Islands',
    'NO' => 'Norway',
    'OM' => 'Oman',
    'PK' => 'Pakistan',
    'PW' => 'Palau',
    'PS' => 'Palestinian Territory, Occupied',
    'PA' => 'Panama',
    'PG' => 'Papua New Guinea',
    'PY' => 'Paraguay',
    'PE' => 'Peru',
    'PH' => 'Philippines',
    'PN' => 'Pitcairn',
    'PL' => 'Poland',
    'PT' => 'Portugal',
    'PR' => 'Puerto Rico',
    'QA' => 'Qatar',
    'RE' => 'Réunion',
    'RO' => 'Romania',
    'RU' => 'Russian Federation',
    'RW' => 'Rwanda',
    'BL' => 'Saint Barthélemy',
    'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
    'KN' => 'Saint Kitts and Nevis',
    'LC' => 'Saint Lucia',
    'MF' => 'Saint Martin (French part)',
    'PM' => 'Saint Pierre and Miquelon',
    'VC' => 'Saint Vincent and the Grenadines',
    'WS' => 'Samoa',
    'SM' => 'San Marino',
    'ST' => 'Sao Tome and Principe',
    'SA' => 'Saudi Arabia',
    'SN' => 'Senegal',
    'RS' => 'Serbia',
    'SC' => 'Seychelles',
    'SL' => 'Sierra Leone',
    'SG' => 'Singapore',
    'SX' => 'Sint Maarten (Dutch part)',
    'SK' => 'Slovakia',
    'SI' => 'Slovenia',
    'SB' => 'Solomon Islands',
    'SO' => 'Somalia',
    'ZA' => 'South Africa',
    'GS' => 'South Georgia and the South Sandwich Islands',
    'SS' => 'South Sudan',
    'ES' => 'Spain',
    'LK' => 'Sri Lanka',
    'SD' => 'Sudan',
    'SR' => 'Suriname',
    'SJ' => 'Svalbard and Jan Mayen',
    'SZ' => 'Swaziland',
    'SE' => 'Sweden',
    'CH' => 'Switzerland',
    'SY' => 'Syrian Arab Republic',
    'TW' => 'Taiwan, Province of China',
    'TJ' => 'Tajikistan',
    'TZ' => 'Tanzania, United Republic of',
    'TH' => 'Thailand',
    'TL' => 'Timor-Leste',
    'TG' => 'Togo',
    'TK' => 'Tokelau',
    'TO' => 'Tonga',
    'TT' => 'Trinidad and Tobago',
    'TN' => 'Tunisia',
    'TR' => 'Turkey',
    'TM' => 'Turkmenistan',
    'TC' => 'Turks and Caicos Islands',
    'TV' => 'Tuvalu',
    'UG' => 'Uganda',
    'UA' => 'Ukraine',
    'AE' => 'United Arab Emirates',
    'GB' => 'United Kingdom',
    'US' => 'United States',
    'UM' => 'United States Minor Outlying Islands',
    'UY' => 'Uruguay',
    'UZ' => 'Uzbekistan',
    'VU' => 'Vanuatu',
    'VE' => 'Venezuela, Bolivarian Republic of',
    'VN' => 'Viet Nam',
    'VG' => 'Virgin Islands, British',
    'VI' => 'Virgin Islands, U.S.',
    'WF' => 'Wallis and Futuna',
    'EH' => 'Western Sahara',
    'YE' => 'Yemen',
    'ZM' => 'Zambia',
    'ZW' => 'Zimbabwe'
];
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    wp_head(); // Ensures styles & scripts are loaded

    // Load global styles for block themes (necessary for FSE)
    if ($is_fse_theme) {
        $global_styles = file_get_contents(get_template_directory() . '/style.css');
        echo '<style>' . $global_styles . '</style>';
    }
    ?>
</head>

<body <?php body_class(); ?>>
    <?php
    // Load the correct header
    if (!$is_fse_theme) {
        get_header(); // Classic Theme
    } else {
        echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->'); // Block Theme Header
    }
    ?>

    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            display: flex;
            gap: 40px;
        }
        .checkout-left {
            flex: 2;
        }
        .checkout-right {
            flex: 1;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #000;
        }
        h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #000;
        }
        .form-section {
            margin-bottom: 40px;
        }
        .form-section label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .form-section input,
        .form-section select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            background: #fff;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .form-section input:focus,
        .form-section select:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }
        .form-section input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
        .form-section .note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .form-section .checkbox-label {
            font-size: 14px;
            color: #333;
        }
        .form-section .checkbox-label a {
            color: #28a745;
            text-decoration: none;
        }
        .form-section .checkbox-label a:hover {
            text-decoration: underline;
        }
        .order-item {
            margin-bottom: 20px;
        }
        .order-item h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #000;
        }
        .order-item p {
            font-size: 14px;
            color: #666;
            margin: 3px 0;
        }
        .order-item .price {
            font-weight: 700;
            color: #000;
        }
        .promo-code {
            margin: 20px 0;
        }
        .promo-code input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            background: #fff;
        }
        .subtotal,
        .total-charges {
            font-size: 16px;
            font-weight: 700;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            color: #000;
        }
        .policy-section {
            margin-bottom: 40px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .policy-section h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #000;
        }
        .policy-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .policy-section li {
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
            position: relative;
            padding-left: 20px;
        }
        .policy-section li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #28a745;
            font-size: 18px;
        }
        .place-order-btn {
            display: block;
            width: 100%;
            background: #28a745;
            color: white;
            font-size: 16px;
            font-weight: 600;
            padding: 15px;
            border: none;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s;
        }
        .place-order-btn:hover {
            background: #218838;
        }
        .error-message, .success-message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
        }
    </style>

    <div class="checkout-container">
        <!-- Left Section: Guest Information and Policies -->
        <div class="checkout-left">
            <h1>Checkout</h1>

            <!-- Guest Information -->
            <div class="form-section">
                <h2>Guest Information</h2>
                <form id="checkout-form">
                    <div class="mb-3">
                        <label for="full_name">Full Name*</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="address_line_1">Address Line 1*</label>
                        <input type="text" id="address_line_1" name="address_line_1" required>
                    </div>
                    <div class="mb-3">
                        <label for="address_line_2">Address Line 2</label>
                        <input type="text" id="address_line_2" name="address_line_2">
                    </div>
                    <div class="mb-3">
                        <label for="country">Country*</label>
                        <select id="country" name="country" required>
                            <option value="">Select a country</option>
                            <?php foreach ($countries as $code => $name) : ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php echo $code === 'US' ? 'selected' : ''; ?>>
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="postal_code">Postal Code*</label>
                        <input type="text" id="postal_code" name="postal_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="email">Email Address*</label>
                        <input type="email" id="email" name="email" required>
                        <div class="note">Your order confirmation will be sent here</div>
                    </div>
                    <div class="mb-3">
                        <label for="phone">Phone Number*</label>
                        <input type="tel" id="phone" name="phone" placeholder="(###) ###-####" required>
                    </div>
                    <div class="mb-3">
                        <label class="checkbox-label">
                            <input type="checkbox" name="receive_texts">
                            Receive text alerts about this reservation. <a href="#">View Details</a>
                        </label>
                    </div>
                </form>
            </div>

            <!-- Reservation Policies -->
            <div class="policy-section">
                <h2>Reservation Policies</h2>
                <ul>
                    <li>Reservations are non-refundable unless canceled within 48 hours of booking.</li>
                    <li>Check-in time is 2:00 PM. Check-out time is 11:00 AM.</li>
                    <li>Pets are allowed but must be leashed at all times.</li>
                    <li>Quiet hours are from 10:00 PM to 7:00 AM.</li>
                    <li>Any damage to the property will be charged to the guest.</li>
                </ul>
            </div>

            <!-- Place Order Button -->
            <button type="submit" form="checkout-form" class="place-order-btn">Place Order</button>
        </div>

        <!-- Right Section: Order Summary -->
        <div class="checkout-right">
            <h2>Order Summary</h2>
            <?php foreach ($order_items as $item) : ?>
                <div class="order-item">
                    <h3><?php echo esc_html($item['title']); ?></h3>
                    <p>30/50 Amp Deluxe Back-In Full Hookup Lot - 69 •</p>
                    <p><?php echo esc_html($item['details']); ?></p>
                    <p><?php echo esc_html($item['guests']); ?></p>
                    <p class="price">$<?php echo number_format($item['subtotal'], 2); ?></p>
                </div>
            <?php endforeach; ?>
            <div class="promo-code">
                <input type="text" placeholder="Enter a promo code">
            </div>
            <div class="subtotal">
                <p>Subtotal: $<?php echo number_format($cart_total, 2); ?></p>
            </div>
            <div class="total-charges">
                <p>Total Charges: $<?php echo number_format($total_charges, 2); ?></p>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#checkout-form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'rvbs_process_checkout');
            formData.append('_ajax_nonce', rvbs_checkout.nonce);

            $.ajax({
                type: 'POST',
                url: rvbs_checkout.ajax_url,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('.place-order-btn').text('Processing...').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        $('#checkout-form').prepend('<div class="success-message">' + response.data.message + '</div>');
                        // Redirect to confirmation page
                        setTimeout(function() {
                            window.location.href = '<?php echo esc_url(home_url('/booking-confirmation/')); ?>?booking_id=' + response.data.booking_id;
                        }, 2000);
                    } else {
                        $('#checkout-form').prepend('<div class="error-message">Error: ' + (response.data.message || 'Failed to process booking') + '</div>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    $('#checkout-form').prepend('<div class="error-message">An error occurred. Please try again.</div>');
                },
                complete: function() {
                    $('.place-order-btn').text('Place Order').prop('disabled', false);
                }
            });
        });
    });
    </script>

    <?php
    if (!$is_fse_theme) {
        get_footer(); // Classic Theme
    } else {
        echo do_blocks('<!-- wp:template-part {"slug":"footer"} /-->'); // Block Theme Footer
    }
    wp_footer();
    ?>
</body>
</html>