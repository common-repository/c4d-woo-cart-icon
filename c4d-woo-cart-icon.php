<?php
/*
Plugin Name: C4D Woocommerce Cart
Plugin URI: http://coffee4dev.com/
Description: Popup Cart, Side Cart, Sticky Add To Cart Button, Call For Price
Author: Coffee4dev.com
Author URI: http://coffee4dev.com/
Text Domain: c4d-woo-cart-icon
Version: 3.0.9
*/
$c4d_woo_cart_params = array(
  "c4d-woo-cart-icon-type" => "slide",
  "c4d-woo-cart-icon-title" => "Your Cart",
  "c4d-woo-cart-icon-icon" => "",
  "c4d-woo-cart-icon-svg" => "<svg version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" enable-background=\"new 0 0 512 512\">\r\n<g>\r\n  <path d=\"m417.9,104.4h-65.5c-2.2-51-44.8-92.4-96.4-92.4s-94.2,41.3-96.5,92.4h-66.5l-30.1,395.6h386.2l-31.2-395.6zm-161.9-71.6c40.1,0 73.5,32 75.7,71.6h-151.4c2.2-39.6 35.6-71.6 75.7-71.6zm-143.3,92.4h46.7v68.5h20.8v-68.5h151.6v68.5h20.8v-68.5h47.8l27,354h-341.7l27-354z\"/>\r\n</g>\r\n</svg>",
  "c4d-woo-cart-icon-button-icon" => "fa fa-shopping-bag",
  "c4d-woo-cart-icon-button-svg" => "<svg version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" enable-background=\"new 0 0 512 512\">\r\n<g>\r\n  <path d=\"m417.9,104.4h-65.5c-2.2-51-44.8-92.4-96.4-92.4s-94.2,41.3-96.5,92.4h-66.5l-30.1,395.6h386.2l-31.2-395.6zm-161.9-71.6c40.1,0 73.5,32 75.7,71.6h-151.4c2.2-39.6 35.6-71.6 75.7-71.6zm-143.3,92.4h46.7v68.5h20.8v-68.5h151.6v68.5h20.8v-68.5h47.8l27,354h-341.7l27-354z\"/>\r\n</g>\r\n</svg>",
  "c4d-woo-cart-icon-fly-cart-button" => "1",
  "c4d-woo-cart-icon-call-for-price" => "1",
  "c4d-woo-cart-icon-call-for-price-text" => "",
  "c4d-woo-cart-icon-side-position" => "right",
  "c4d-woo-cart-icon-side-buttons" => array(),
  "c4d-woo-cart-icon-popup-quantity-update" => "0",
  "c4d-woo-cart-icon-popup-buttons" => array()
);

if (!isset($c4d_plugin_manager)) {
  $c4d_plugin_manager = $c4d_woo_cart_params;
} else {
  $c4d_plugin_manager = array_merge($c4d_woo_cart_params, $c4d_plugin_manager);
}

define('C4DWCARTICON_PLUGIN_URI', plugins_url('', __FILE__));

///// ACTION
add_action( 'wp_enqueue_scripts', 'c4d_woo_cart_icon_safely_add_stylesheet_to_frontsite');
add_action( 'admin_enqueue_scripts', 'c4d_woo_cart_icon_load_scripts_admin');
add_action( 'c4d-plugin-manager-section', 'c4d_woo_cart_icon_section_options');
add_action( 'wp_ajax_c4d_woo_cart_popup_template', 'c4d_woo_cart_popup_template' );
add_action( 'wp_ajax_nopriv_c4d_woo_cart_popup_template', 'c4d_woo_cart_popup_template' );
add_action( 'wp_ajax_c4d_woo_cart_icon_popup_qty_update', 'c4d_woo_cart_icon_popup_qty_update' );
add_action( 'wp_ajax_nopriv_c4d_woo_cart_icon_popup_qty_update', 'c4d_woo_cart_icon_popup_qty_update' );
add_action( 'plugins_loaded', 'c4d_woo_cart_icon_load_textdomain' );

///// FILTER
add_filter( 'plugin_row_meta', 'c4d_woo_cart_icon_plugin_row_meta', 10, 2 );
add_filter( 'add_to_cart_fragments', 'c4d_woo_cart_icon_fragments');

//// SHORTCODE
add_shortcode( 'c4d-woo-cart', 'c4d_woo_cart_icon_shortcode');

//// INIT
add_action( 'woocommerce_init', 'c4d_woo_cart_icon_init' );
function c4d_woo_cart_icon_init() {
  c4d_woo_cart_icon_add_buttons();
  add_action( 'wp_footer', 'c4d_woo_cart_icon_footer' );
  add_action( 'wp_ajax_c4d_woo_cart_icon_remove_cart_item', 'c4d_woo_cart_icon_remove_cart_item');
  add_action( 'wp_ajax_nopriv_c4d_woo_cart_icon_remove_cart_item', 'c4d_woo_cart_icon_remove_cart_item');
  add_action( 'woocommerce_single_product_summary',  'c4d_woo_cart_call_for_price', 30 );
  add_action( 'woocommerce_after_main_content', 'c4d_woo_cart_fly_add_to_cart_button' );
  add_filter( 'c4d_woo_cart_woocommerce_cart_item_quantity', 'c4d_woo_cart_woocommerce_cart_item_quantity', 10, 3 );
}

///// FUNCTIONS
function c4d_woo_cart_icon_load_textdomain() {
  load_plugin_textdomain( 'c4d-woo-cart-icon', false, dirname(plugin_basename( __FILE__ )) . '/languages' );
}

function c4d_woo_cart_fly_add_to_cart_button () {
  global $product, $c4d_plugin_manager;

  if (isset($c4d_plugin_manager['c4d-woo-cart-icon-fly-cart-button']) && $c4d_plugin_manager['c4d-woo-cart-icon-fly-cart-button'] == 0) {
    return;
  }

  if ( ! is_product() ) {
    return;
  }

  $show = false;

  if ( $product->is_purchasable() && $product->is_in_stock() ) {
    $show = true;
  } else if ( $product->is_type( 'external' ) ) {
    $show = true;
  }

  if ( ! $show ) {
    return;
  }


  ?>
    <section class="c4d-woo-cart-fly-add-to-cart" data-product_id="<?php esc_attr(the_id()); ?>">
        <div class="block-content">
          <div class="block-image">
            <?php echo wp_kses_post( woocommerce_get_product_thumbnail() ); ?>
          </div>
          <div class="block-product-info">
            <span class="block-title"><strong><?php the_title(); ?></strong></span>
            <span class="block-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
            <span class="block-rate"><?php echo wp_kses_post( wc_get_rating_html( $product->get_average_rating() ) ); ?></span>
          </div>
          <div class="block-button">
            <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="button js-add-to-cart">
              <?php echo esc_attr( $product->add_to_cart_text() ); ?>
            </a>
          </div>
        </div>
    </section>
  <?php
}

function c4d_woo_cart_call_for_price () {
  global $product, $c4d_plugin_manager;
  if ( ! is_product() ) {
    return;
  }
  if (isset($c4d_plugin_manager['c4d-woo-cart-icon-call-for-price']) && $c4d_plugin_manager['c4d-woo-cart-icon-call-for-price'] == 1) {
    $text = $c4d_plugin_manager['c4d-woo-cart-icon-call-for-price-text'] != '' ? $c4d_plugin_manager['c4d-woo-cart-icon-call-for-price-text'] : esc_html__('Call For Price', 'c4d-woo-cart-icon');
    echo '<a class="button c4d-woo-cart-icon-call-for-price" href="tel:'.esc_attr($c4d_plugin_manager['c4d-woo-cart-icon-call-for-price-number']).'">'. $text .'</a>';
  }
}

function c4d_woo_cart_icon_popup_qty_update() {
  $cart_item_key = $_POST['cart_item_key'];
  $qty = $_POST['qty'];

  if ($cart_item_key && $qty) {
    $cart = WC()->cart;
    $cart->set_quantity($cart_item_key, $qty);
  }
}

function c4d_woo_cart_woocommerce_cart_item_quantity($product_quantity, $cart_item_key, $cart_item) {
  global $c4d_plugin_manager;
  if (isset($c4d_plugin_manager['c4d-woo-cart-icon-popup-quantity-update']) && $c4d_plugin_manager['c4d-woo-cart-icon-popup-quantity-update'] == 0) {
    return sprintf( '%s <input type="hidden" name="cart[%s][qty]" value="%s" />', $cart_item['quantity'], $cart_item_key,  $cart_item['quantity']);
  }
  return $product_quantity;
}
function c4d_woo_cart_icon_add_buttons() {
  global $c4d_plugin_manager;
  if (isset($c4d_plugin_manager['c4d-woo-cart-icon-side-buttons'])) {
    $sideButtons = $c4d_plugin_manager['c4d-woo-cart-icon-side-buttons'] ? $c4d_plugin_manager['c4d-woo-cart-icon-slide-buttons'] : array();

    if (!in_array('continue', $sideButtons)) {
      add_action( 'woocommerce_widget_shopping_cart_buttons', 'c4d_woo_cart_icon_shopping_cart_buttons', 10000 );
    }
    if (in_array('viewcart', $sideButtons)) {
      remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
    }
    if (in_array('checkout', $sideButtons)) {
      remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );
    }
  }

  if (isset($c4d_plugin_manager['c4d-woo-cart-icon-popup-buttons'])) {
    $popupButtons = $c4d_plugin_manager['c4d-woo-cart-icon-popup-buttons'] ? $c4d_plugin_manager['c4d-woo-cart-icon-popup-buttons'] : array();

    if (!in_array('continue', $popupButtons)) {
      add_action( 'woocommerce_widget_shopping_cart_buttons_popup', 'c4d_woo_cart_icon_shopping_cart_buttons', 10000 );
    }
    if (in_array('viewcart', $popupButtons)) {
      remove_action( 'woocommerce_widget_shopping_cart_buttons_popup', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
    } else {
      add_action( 'woocommerce_widget_shopping_cart_buttons_popup', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
    }
    if (in_array('checkout', $popupButtons)) {
      remove_action( 'woocommerce_widget_shopping_cart_buttons_popup', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );
    } else {
      add_action( 'woocommerce_widget_shopping_cart_buttons_popup', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );
    }
  }


}
function c4d_woo_cart_popup_template() {
  ob_start();

  include dirname(__FILE__) . '/templates/popup-cart.php';

  $cart = ob_get_clean();

  $data = array(
    'div.widget_shopping_cart_content_popup' => '<div class="widget_shopping_cart_content_popup">' . $cart . '</div>'
  );

  return $data;
}
function c4d_woo_cart_icon_plugin_row_meta( $links, $file ) {
  if ('c4d-woo-cart-icon/c4d-woo-cart-icon.php' == $file){
    $new_links = array(
      'options' => '<a target="blank" href="admin.php?page=c4d-plugin-manager">Settings</a>',
      'pro' => '<a target="blank" href="http://coffee4dev.com/woocommerce-cart-popup-side/">Premium Version</a>',
      'demo' => '<a target="blank" href="http://30tet.coffee4dev.com">Demo</a>'
    );
    if (!defined('C4DPMANAGER_PLUGIN_URI')) {
      $new_links['options'] = '<a target="blank" href="https://wordpress.org/plugins/c4d-plugin-manager/">Settings</a>';
    }
    $links = array_merge( $links, $new_links );
  }

  return $links;
}

function c4d_woo_cart_icon_safely_add_stylesheet_to_frontsite( $page ) {
	wp_enqueue_style( 'c4d-woo-cart-icon-frontsite-style', C4DWCARTICON_PLUGIN_URI.'/assets/default.css' );
	wp_enqueue_script( 'c4d-woo-cart-icon-frontsite-plugin-js', C4DWCARTICON_PLUGIN_URI.'/assets/default.js', array( 'jquery' ), false, true );
	wp_localize_script( 'jquery', 'c4d_woo_cart_icon', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

function c4d_woo_cart_icon_load_scripts_admin($hook) {
  if (in_array($hook, array('toplevel_page_c4d-plugin-manager'))) {
    wp_enqueue_script( 'c4d-woo-cart-icon-admin-js', C4DWCARTICON_PLUGIN_URI . '/assets/admin.js' );
    wp_enqueue_style( 'c4d-woo-cart-icon-admin-style', C4DWCARTICON_PLUGIN_URI.'/assets/admin.css' );
  }
}

function c4d_woo_cart_icon_shortcode($params, $content) {
	global $woocommerce, $c4d_plugin_manager;

	if (!$woocommerce) return '';

  $defaultIcon = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 512 512">
                    <g>
                      <path d="m417.9,104.4h-65.5c-2.2-51-44.8-92.4-96.4-92.4s-94.2,41.3-96.5,92.4h-66.5l-30.1,395.6h386.2l-31.2-395.6zm-161.9-71.6c40.1,0 73.5,32 75.7,71.6h-151.4c2.2-39.6 35.6-71.6 75.7-71.6zm-143.3,92.4h46.7v68.5h20.8v-68.5h151.6v68.5h20.8v-68.5h47.8l27,354h-341.7l27-354z"/>
                    </g>
                  </svg>';

  $params = shortcode_atts( array(
    'popup' => 0,
    'slide' => 1,
    'icon' => isset($c4d_plugin_manager['c4d-woo-cart-icon-icon']) ? $c4d_plugin_manager['c4d-woo-cart-icon-icon'] : '',
    'label' => isset($c4d_plugin_manager['c4d-woo-cart-icon-title']) ? $c4d_plugin_manager['c4d-woo-cart-icon-title'] : esc_html__('Your Cart', 'c4d-woo-cart-icon'),
    'svg_icon' => isset($c4d_plugin_manager['c4d-woo-cart-icon-svg']) && $c4d_plugin_manager['c4d-woo-cart-icon-svg'] !== '' ? $c4d_plugin_manager['c4d-woo-cart-icon-svg'] : $defaultIcon,
  ), $params );

  $icon = '';

  if ($params['svg_icon'] !== '') {
   $icon = '<span class="icon svg">'. $params['svg_icon'] .'</span>';
  }

  if ($params['icon'] !== '') {
    $icon = '<span class="icon"><i class="'.esc_attr($params['icon']).'"></i></span>';
  }

  $type = isset($c4d_plugin_manager['c4d-woo-cart-icon-type']) ? $c4d_plugin_manager['c4d-woo-cart-icon-type'] : 'default';

  $html = '<div class="c4d-woo-cart-icon">';
	$html .= '<div class="c4d-woo-cart-icon__icon">';
  $html .= '<a class="js_c4d_wci_cart_click" href="'.esc_attr(wc_get_cart_url ()).'">';
  $html .= $icon . '<span class="text">'.$params['label'].'</span><span class="number">'.(count(WC()->cart->get_cart())).'</span></a></div>';
	$html .= '<div class="c4d-woo-cart-icon__list">';

  if ($params['popup'] == 1) {
    $html .= '<div class="widget_shopping_cart_content_popup"></div>';
  }

  if ($params['slide'] == 1) {
    $html .= '<div class="widget_shopping_cart_content"></div>';
  }

	$html .= '</div>';
	$html .= '</div>';

  return $html;
}

function c4d_woo_cart_icon_fragments( $fragments ) {
  global $c4d_plugin_manager;
  if (isset($c4d_plugin_manager['c4d-woo-cart-icon-type']) && $c4d_plugin_manager['c4d-woo-cart-icon-type'] == 'popup') {
    $popup = c4d_woo_cart_popup_template();

    $fragments = array_merge($fragments, $popup);

  }
	$fragments['cart_count'] = count(WC()->cart->get_cart());
  return $fragments;
}

function c4d_woo_cart_icon_remove_cart_item() {
	$cart = WC()->cart;
  $id   = sanitize_text_field($_POST['product_id']);

  if ($cart->remove_cart_item($id)){
  	ob_start();
    woocommerce_mini_cart();
    $cart_content = ob_get_clean();

    $output = array(
      'cart_content' => $cart_content,
      'cart_count' => count(WC()->cart->get_cart())
    );

    die(json_encode($output));
  }
  die(__('No Product', 'c4d-woo-cart-icon'));
}

function c4d_woo_cart_icon_footer() {
  global $woocommerce, $c4d_plugin_manager;

  if (!$woocommerce) return;

  $type = isset($c4d_plugin_manager['c4d-woo-cart-icon-type']) ? $c4d_plugin_manager['c4d-woo-cart-icon-type'] : 'default';
  $pos = isset($c4d_plugin_manager['c4d-woo-cart-icon-side-position']) ? $c4d_plugin_manager['c4d-woo-cart-icon-side-position'] : 'right';

  $params = array(
    'popup' => $type == 'popup' ? 1 : 0,
    'slide' => $type == 'slide' ? 1 : 0,
  );
  echo '<div class="woocommerce c4d-woo-cart-icon-wrap type-'.$type.' slide-'.$pos.'">';
  echo '<div class="c4d-woo-cart-icon-wrap-inner">';
  do_action( 'c4d_woo_cart_icon_before' );
  echo c4d_woo_cart_icon_shortcode($params, '');
  do_action( 'c4d_woo_cart_icon_after' );
  echo '</div>';
  echo '</div>';

}

function c4d_woo_cart_icon_shopping_cart_buttons() {
    echo '<a href="#" class="button continue-shopping js_c4d_wci_close_cart">'.esc_html__('Continue Shopping', 'c4d-woo-cart-icon').'</a>';
}

function c4d_woo_cart_icon_section_options(){
    $opt_name = 'c4d_plugin_manager';
    Redux::setSection( $opt_name, array(
      'title'            => esc_html__( 'Cart', 'c4d-woo-cart-icon' ),
      'desc'             => '',
      'customizer_width' => '400px',
      'icon'             => 'el el-home',
    ));

    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Global', 'c4d-woo-cart-icon' ),
        'id'               => 'c4d-woo-cart-icon-global',
        'desc'             => '',
        'customizer_width' => '400px',
        'subsection'       => true,
        'fields'           => array(
              array(
                  'id'       => 'c4d-woo-cart-icon-type',
                  'type'     => 'button_set',
                  'title'    => __('Cart Type', 'c4d-woo-cart-icon'),
                  'options' => array(
                      'default' => esc_html__('Default', 'c4d-woo-cart-icon'),
                      'slide' => esc_html__('Slide', 'c4d-woo-cart-icon'),
                      'popup' => esc_html__('Popup', 'c4d-woo-cart-icon'),
                  ),
                  'default' => 'slide'
              ),
              array(
                  'id'       => 'c4d-woo-cart-icon-title',
                  'type'     => 'text',
                  'title'    => esc_html__('Label', 'c4d-woo-cart-icon'),
                  'default'  => 'Your Cart',
              ),
              array(
                  'id'       => 'c4d-woo-cart-icon-icon',
                  'type'     => 'text',
                  'title'    => esc_html__('Default Cart Icon', 'c4d-woo-cart-icon'),
                  'subtitle' => esc_html__('Set default icon. Support icon font only, insert the class of icon', 'c4d-woo-cart-icon'),
                  'default'  => '',
                  'placeholder' => 'fa fa-shopping-bag'
              ),
              array(
                  'id'       => 'c4d-woo-cart-icon-svg',
                  'type'     => 'ace_editor',
                  'mode'     => 'xml',
                  'title'    => esc_html__('Default Cart SVG', 'c4d-woo-cart-icon'),
                  'subtitle' => esc_html__('Set default icon. Support icon font only, insert the class of icon', 'c4d-woo-cart-icon'),
                  'default'  => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 512 512">
  <g>
    <path d="m417.9,104.4h-65.5c-2.2-51-44.8-92.4-96.4-92.4s-94.2,41.3-96.5,92.4h-66.5l-30.1,395.6h386.2l-31.2-395.6zm-161.9-71.6c40.1,0 73.5,32 75.7,71.6h-151.4c2.2-39.6 35.6-71.6 75.7-71.6zm-143.3,92.4h46.7v68.5h20.8v-68.5h151.6v68.5h20.8v-68.5h47.8l27,354h-341.7l27-354z"/>
  </g>
  </svg>',
              ),
              array(
                  'id'       => 'c4d-woo-cart-icon-button-icon',
                  'type'     => 'text',
                  'title'    => esc_html__('Default Button Icon', 'c4d-woo-cart-icon'),
                  'subtitle' => esc_html__('Set default button icon. Support icon font only, insert the class of icon', 'c4d-woo-cart-icon'),
                  'default'  => 'fa fa-shopping-bag'
              ),
              array(
                  'id'       => 'c4d-woo-cart-icon-button-svg',
                  'type'     => 'ace_editor',
                  'mode'     => 'xml',
                  'title'    => esc_html__('Default Button SVG', 'c4d-woo-cart-icon'),
                  'subtitle' => esc_html__('Set default icon. Support icon font only, insert the class of icon', 'c4d-woo-cart-icon'),
                  'default'  => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 512 512">
  <g>
    <path d="m417.9,104.4h-65.5c-2.2-51-44.8-92.4-96.4-92.4s-94.2,41.3-96.5,92.4h-66.5l-30.1,395.6h386.2l-31.2-395.6zm-161.9-71.6c40.1,0 73.5,32 75.7,71.6h-151.4c2.2-39.6 35.6-71.6 75.7-71.6zm-143.3,92.4h46.7v68.5h20.8v-68.5h151.6v68.5h20.8v-68.5h47.8l27,354h-341.7l27-354z"/>
  </g>
  </svg>',
              )
            )

    ));

    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Product Single', 'c4d-woo-cart-icon' ),
        'id'               => 'c4d-woo-cart-icon-single',
        'desc'             => '',
        'customizer_width' => '400px',
        'subsection'       => true,
        'fields'           => array(
            array(
                'id'       => 'c4d-woo-cart-icon-fly-cart-button',
                'type'     => 'button_set',
                'title'    => __('Fly Add To Cart Button', 'c4d-woo-cart-icon'),
                'subtitle'    => __('Show in single product page', 'c4d-woo-cart-icon'),
                'options' => array(
                    0 => esc_html__('No', 'c4d-woo-cart-icon'),
                    1 => esc_html__('Yes', 'c4d-woo-cart-icon'),
                ),
                'default' => 1
            ),
            array(
                'id'       => 'c4d-woo-cart-icon-call-for-price',
                'type'     => 'button_set',
                'title'    => __('Call For Price Button', 'c4d-woo-cart-icon'),
                'subtitle'    => __('Show Call For Price Button for outstock or empty price', 'c4d-woo-cart-icon'),
                'options' => array(
                    0 => esc_html__('No', 'c4d-woo-cart-icon'),
                    1 => esc_html__('Yes', 'c4d-woo-cart-icon'),
                ),
                'default' => 1
            ),
            array(
                'id'       => 'c4d-woo-cart-icon-call-for-price-text',
                'type'     => 'text',
                'title'    => __('Call For Price Text', 'c4d-woo-cart-icon'),
                'default' => ''
            ),
            array(
                'id'       => 'c4d-woo-cart-icon-call-for-price-number',
                'type'     => 'text',
                'title'    => __('Call For Price Number', 'c4d-woo-cart-icon'),
                'default' => '000 111 222'
            ),
          )
    ));

    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Slide Cart', 'c4d-woo-cart-icon' ),
        'id'               => 'c4d-woo-cart-icon-slide-cart',
        'desc'             => '',
        'customizer_width' => '400px',
        'subsection'       => true,
        'fields'           => array(

            array(
              'id'       => 'c4d-woo-cart-icon-slide-position',
              'type'     => 'button_set',
              'title'    => __('Position', 'c4d-woo-cart-icon'),
              'subtitle'    => __('Show Call For Price Button for outstock or empty price', 'c4d-woo-cart-icon'),
              'options' => array(
                  'left' => esc_html__('Left', 'c4d-woo-cart-icon'),
                  'right' => esc_html__('Right', 'c4d-woo-cart-icon'),
              ),
              'default' => 'right'
            ),
            array(
              'id'       => 'c4d-woo-cart-icon-slide-buttons',
              'type'     => 'button_set',
              'title'    => __('Hide Button', 'c4d-woo-cart-icon'),
              'multi'   => true,
              'options' => array(
                  'checkout' => esc_html__('Checkout', 'c4d-woo-cart-icon'),
                  'viewcart' => esc_html__('View Cart', 'c4d-woo-cart-icon'),
                  'continue' => esc_html__('Continue', 'c4d-woo-cart-icon')
              ),
              'default' => 'continue'
            ),
          )
    ));

    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Popup Cart', 'c4d-woo-cart-icon' ),
        'id'               => 'c4d-woo-cart-icon-popup-cart',
        'desc'             => '',
        'customizer_width' => '400px',
        'subsection'       => true,
        'fields'           => array(

            array(
              'id'       => 'c4d-woo-cart-icon-popup-quantity-update',
              'type'     => 'button_set',
              'title'    => __('Qty Update', 'c4d-woo-cart-icon'),
              'options' => array(
                    0 => esc_html__('No', 'c4d-woo-cart-icon'),
                    1 => esc_html__('Yes', 'c4d-woo-cart-icon'),
                ),
                'default' => 0
            ),
            array(
              'id'       => 'c4d-woo-cart-icon-popup-buttons',
              'type'     => 'button_set',
              'title'    => __('Hide Button', 'c4d-woo-cart-icon'),
              'multi'   => true,
              'options' => array(
                  'checkout' => esc_html__('Checkout', 'c4d-woo-cart-icon'),
                  'viewcart' => esc_html__('View Cart', 'c4d-woo-cart-icon'),
                  'continue' => esc_html__('Continue', 'c4d-woo-cart-icon'),
              ),
            )
        )
    ));
}
