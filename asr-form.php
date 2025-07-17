<?php
/**
 * Plugin Name: ASR Price Sheet Calculator
 * Description: Adds a shortcode [asr_psc_form] to display a price sheet calculator form, and handles email notifications with admin-configurable settings.
 * Version: 1.0
 * Author: Faizan Ali
 * Author URI: https://www.fiverr.com/wpengineeers
 * Text Domain: asr-psc
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// 1. Admin Settings Page
add_action( 'admin_menu', 'asr_psc_add_admin_menu' );
add_action( 'admin_init', 'asr_psc_settings_init' );

function asr_psc_add_admin_menu() {
    add_options_page( 'ASR Price Sheet Settings', 'ASR Price Sheet', 'manage_options', 'asr_psc', 'asr_psc_options_page' );
}

function asr_psc_settings_init() {
    register_setting( 'asr_psc_settings', 'asr_psc_settings' );
    add_settings_section( 'asr_psc_section', __( 'Email Notification Settings', 'asr-psc' ), null, 'asr_psc' );
    add_settings_field( 'extra_emails', __( 'Extra Notification Emails', 'asr-psc' ), 'asr_psc_extra_emails_render', 'asr_psc', 'asr_psc_section' );
}

function asr_psc_extra_emails_render() {
    $options = get_option( 'asr_psc_settings', [] );
    ?>
    <textarea name="asr_psc_settings[extra_emails]" rows="3" cols="50"><?php echo esc_textarea( $options['extra_emails'] ?? '' ); ?></textarea>
    <p class="description"><?php _e( 'Enter comma-separated email addresses to receive submissions.', 'asr-psc' ); ?></p>
    <?php
}

function asr_psc_options_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'ASR Price Sheet Settings', 'asr-psc' ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'asr_psc_settings' );
            do_settings_sections( 'asr_psc' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 2. Data Array
function asr_psc_get_data() {
    return [
        "18x14 Screen" => [
            "Custom Panels" => 65,
            "Lower Side 24' One Story" => 85,
            "Upper Side 32' Two Story" => 127,
            "24' One Story Top Panel" => 109,
            "32' Two Story Top Panel" => 171,
            "24' One Story Riser" => 109,
            "32' Two Story Riser" => 171,
            "Doors" => 65,
        ],
        "20x20 Screen" => [
            "Bottom Panels" => 90,
            "Lower Side 24' One Story" => 110,
            "Upper Side 32' Two Story" => 129,
            "24' One Story Top Panel" => 144,
            "32' Two Story Top Panel" => 174,
            "24' One Story Riser" => 144,
            "32' Two Story Riser" => 174,
        ],
        "Pet Screen" => [
            "Bottom Panels" => 111,
            "Lower Side 24' One Story" => 151,
            "Upper Side 32' Two Story" => 159,
            "24' One Story Top Panel" => 167,
            "32' Two Story Top Panel" => 179,
            "24' One Story Riser" => 167,
            "32' Two Story Riser" => 179,
            "Doors" => 108,
        ],
        "FL Glass" => [
            "Bottom Panels" => 109,
            "Lower Side 24' One Story" => 150,
            "Upper Side 32' Two Story" => 159,
            "24' One Story Top Panel" => 164,
            "32' Two Story Top Panel" => 180,
            "24' One Story Riser" => 164,
            "32' Two Story Riser" => 180,
            "Doors" => 108,
        ],
        "Doors & Cables" => [
            "Door Kits" => 54,
            "Hinges" => 20,
            "Door (1814)" => 65,
            "Door (2020)" => 90,
            "Door w/ Pet Door (all sizes)" => 425,
        ],
        "Pet Doors" => [
            "Small" => 200,
            "Medium" => 210,
            "Large" => 230,
            "X-Large" => 240,
        ],
    ];
}

// 3. Shortcode to Display Form
add_shortcode( 'asr_psc_form', 'asr_psc_display_form' );
function asr_psc_display_form() {
    $data = asr_psc_get_data();
    ob_start();

    if ( isset($_GET['asr_psc_submitted']) && $_GET['asr_psc_submitted'] === '1' ) {
        echo '<div class="notice notice-success" style="padding:10px;background:#d4edda;border:1px solid #c3e6cb;color:#155724;margin-bottom:20px;">';
        echo esc_html__( 'Thank You Installer! Make sure you write this pricing down on your spec sheet and get a signature from the customer. Miranda will be calling you soon.', 'asr-psc' );
        echo '</div>';
    }

    ?>
    <style>
    body .asr-psc-calculator { font-family: Arial, sans-serif; margin: 20px 0; }
    .asr-psc-calculator table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .asr-psc-calculator th, .asr-psc-calculator td { border: 1px solid #ddd; padding: 8px; }
    .asr-psc-calculator th { background-color: #f4f4f4; text-align: left; }
    .asr-psc-calculator input[type='number'] { width: 80px; }
    .asr-psc-calculator .section-total, .asr-psc-calculator .total { font-size: 1.1em; font-weight: bold; margin: 10px 0; }
    .ascr-forms-section{ display:grid; grid-template-columns: 1fr 1fr; gap:20px; }
    @media (max-width: 768px) { .ascr-forms-section { grid-template-columns: 1fr; } }
    .ascr-form-top {display:flex; flex-direction:row; justify-content:space-between; align-items:center; padding:0px 0px;}
    .ascr-form-total{display:flex; flex-direction:row; justify-content:flex-end;}
    .ascr-username{min-width:300px;}
    .ascr-radius{ border-radius:32px; }
    </style>

    <div class="asr-psc-calculator">
      <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
        
        <div class="ascr-form-top">
            <p><label><?php esc_html_e('Name', 'asr-psc'); ?>: <input class="ascr-username" type="text" name="user_name"></label></p>
            <button class="ascr-radius fusion-button button-flat button-large button-default fusion-button-default button-9 fusion-button-default-span fusion-button-default-type btn btn-learn" type="submit"><?php esc_html_e('Submit', 'asr-psc'); ?></button>
        </div>
        
        <div class="ascr-form-total">
            <div class="total"><?php esc_html_e('Grand Total', 'asr-psc'); ?>: $<span class="asr-psc-grand-total">0.00</span></div>
        </div>
        
        <input type="hidden" name="action" value="asr_psc_submit">
        <?php wp_nonce_field( 'asr_psc_submit', 'asr_psc_nonce' ); ?>

        <div class='ascr-forms-section'>
            <?php foreach ( $data as $section => $items ) : ?>
              <fieldset>
                <legend><?php echo esc_html( $section ); ?></legend>
                <table>
                  <tr><th><?php esc_html_e('Item', 'asr-psc'); ?></th><th><?php esc_html_e('Price', 'asr-psc'); ?></th><th><?php esc_html_e('Quantity', 'asr-psc'); ?></th></tr>
                  <?php foreach ( $items as $item => $price ) : ?>
                    <tr>
                      <td><?php echo esc_html( $item ); ?></td>
                      <td>$<?php echo number_format( $price, 2 ); ?></td>
                      <td><input type="number" name="qty[<?php echo esc_attr( $section ); ?>][<?php echo esc_attr( $item ); ?>]" min="0" value="0" data-price="<?php echo esc_attr( $price ); ?>" data-section="<?php echo esc_attr( $section ); ?>" onchange="asrPSC_updateTotals()"></td>
                    </tr>
                  <?php endforeach; ?>
                </table>
                <div class="section-total"><?php echo esc_html__('Subtotal for', 'asr-psc'); ?> <?php echo esc_html( $section ); ?>: $<span id="subtotal-<?php echo sanitize_title( $section ); ?>">0.00</span></div>
              </fieldset>
            <?php endforeach; ?>
        </div>

         <div class="ascr-form-top">
            <p><label><?php esc_html_e('Name', 'asr-psc'); ?>: <input class="ascr-username" type="text" name="user_name"></label></p>
            <button class="ascr-radius fusion-button button-flat button-large button-default fusion-button-default button-9 fusion-button-default-span fusion-button-default-type btn btn-learn" type="submit"><?php esc_html_e('Submit', 'asr-psc'); ?></button>
        </div>
        
        <div style="margin-bottom:80px;" class="ascr-form-total">
            <div class="total"><?php esc_html_e('Grand Total', 'asr-psc'); ?>: $<span class="asr-psc-grand-total">0.00</span></div>
        </div>
        
        <?php
        
            if ( isset($_GET['asr_psc_submitted']) && $_GET['asr_psc_submitted'] === '1' ) {
                echo '<div class="notice notice-success" style="padding:10px;background:#d4edda;border:1px solid #c3e6cb;color:#155724;margin-bottom:20px;">';
                echo esc_html__( 'Thank You Installer! Make sure you write this pricing down on your spec sheet and get a signature from the customer. Miranda will be calling you soon.', 'asr-psc' );
                echo '</div>';
            }
            
        
        ?>
            
        </form>
    </div>

    <script>
    function asrPSC_updateTotals() {
      var grand = 0;
      var sections = {};
      document.querySelectorAll('.asr-psc-calculator input[type=number]').forEach(function(input) {
        var qty = parseInt(input.value) || 0;
        var price = parseFloat(input.dataset.price) || 0;
        var section = input.dataset.section;
        var sub = qty * price;
        sections[section] = (sections[section] || 0) + sub;
        grand += sub;
      });
      for (var sec in sections) {
        var sanitized = sec.toLowerCase().replace(/&/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '');
        var el = document.getElementById('subtotal-' + sanitized);
        if (el) el.textContent = sections[sec].toFixed(2);
      }

     document.querySelectorAll('.asr-psc-grand-total').forEach(function(el) {
       el.textContent = grand.toFixed(2);
     });

    }
    document.addEventListener('DOMContentLoaded', asrPSC_updateTotals);
    </script>
    <?php
    return ob_get_clean();
}

// 4. Handle Form Submission
add_action( 'admin_post_nopriv_asr_psc_submit', 'asr_psc_handle_submission' );
add_action( 'admin_post_asr_psc_submit', 'asr_psc_handle_submission' );
function asr_psc_handle_submission() {
    if ( ! isset($_POST['asr_psc_nonce']) || ! wp_verify_nonce( $_POST['asr_psc_nonce'], 'asr_psc_submit' ) ) {
        wp_die( __( 'Security check failed.', 'asr-psc' ) );
    }

    // Sanitize user input
    $name  = sanitize_text_field( $_POST['user_name'] );
    $qty   = $_POST['qty'] ?? [];
    $data  = asr_psc_get_data();

    $grand = 0;

    // Start email HTML
    $body = '<html><body style="font-family: Arial, sans-serif; color: #333;">';

    // Header branding
    $body .= "<div style='background-color:#004466; color:#fff; padding:20px; text-align:center;'>
                <h2 style='margin:0;'>ASR Estimate Submission</h2>
              </div>";

    $body .= "<div style='padding:20px;'>";

    // Intro
    $body .= "<p>A new estimate form has been submitted using the ASR Pricing Calculator.</p>";

    // Customer Info
    $body .= "<h3 style='margin-bottom:5px;'>Customer Information</h3>";
    $body .= "<p><strong>Name:</strong> " . esc_html($name) . "</p>";

    // Estimate Table
    $body .= "<h3 style='margin-top:30px;'>Estimate Details</h3>";
    $body .= "<table style='border-collapse: collapse; width: 100%; font-size: 14px;' border='1' cellpadding='8' cellspacing='0'>";
    $body .= "<thead style='background-color:#f4f4f4;'>
                <tr>
                    <th align='left'>Section</th>
                    <th align='left'>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
              </thead>";
    $body .= "<tbody>";

    foreach ( $qty as $section => $items ) {
        foreach ( $items as $item => $q ) {
            $q = intval( $q );
            if ( $q > 0 && isset( $data[$section][$item] ) ) {
                $price = $data[$section][$item];
                $sub = $q * $price;
                $grand += $sub;

                $body .= "<tr>
                            <td>" . esc_html($section) . "</td>
                            <td>" . esc_html($item) . "</td>
                            <td align='center'>$q</td>
                            <td align='center'>$" . number_format($price, 2) . "</td>
                            <td align='center'><strong>$" . number_format($sub, 2) . "</strong></td>
                          </tr>";
            }
        }
    }

    if ( $grand === 0 ) {
        return; // don't send empty estimate
    }

    $body .= "</tbody>";
    $body .= "<tfoot>
                <tr style='background-color:#eafaf1;'>
                    <td colspan='4' align='right'><strong>Grand Total:</strong></td>
                    <td align='center'><strong>$" . number_format($grand, 2) . "</strong></td>
                </tr>
              </tfoot>";
    $body .= "</table>";

    // Footer note
    $body .= "<p style='margin-top:30px; font-size:13px; color:#666;'>
                Thank you for using the ASR Price Sheet Calculator. Please follow up with the customer as needed.
              </p>";

    $body .= "</div></body></html>";

    // Email recipients
    $opts = get_option('asr_psc_settings', []);
    $tos  = [];

    if ( ! empty($opts['extra_emails']) ) {
        $list = array_map('trim', explode(',', $opts['extra_emails']));
        $tos = array_merge( $tos, $list );
    }

    if ( empty($tos) ) {
        $tos[] = get_option('admin_email');
    }

    $subject = __( 'New ASR Price Sheet Submission', 'asr-psc' );
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    foreach ( $tos as $to ) {
        wp_mail( $to, $subject, $body, $headers );
    }

    // Redirect back
    $ref = wp_get_referer() ?: home_url();
    wp_redirect( add_query_arg('asr_psc_submitted', '1', $ref) );
    exit;
}


add_action('wp_head', 'asr_psc_block_bots_if_shortcode');

function asr_psc_block_bots_if_shortcode() {
    global $post;
    if ( isset($post) && has_shortcode($post->post_content, 'asr_psc_form') ) {
        echo '<meta name="robots" content="noindex, nofollow">';
    }
}
