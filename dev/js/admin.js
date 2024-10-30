(function($){
  $(document).ready(function(){
    var fields = [
      'c4d-woo-cart-icon-popup-quantity-update',
      'c4d-woo-cart-icon-call-for-price-text',
      'c4d-woo-cart-icon-slide-position',
    ];
    $.each(fields, function(index, el){
      var element = $('fieldset[id*="' + el + '"]');
      element.append('<div class="c4d-label-pro-version"><a target="blank" href="http://coffee4dev.com/woocommerce-ajax-cart-popup-side">Pro Version</a></div>');
    });
  });
})(jQuery);