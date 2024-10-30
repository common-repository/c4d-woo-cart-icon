(function($){
	"use strict";
	var c4dWooCartIcon = {};

	c4dWooCartIcon.c4d_woo_cart_icon_number = function(count) {
		var number = $('.c4d-woo-cart-icon__icon .number');
		number.html(parseFloat(number.html()) + (count));
	}

	c4dWooCartIcon.hideList = function(self, add){
		if (add) {
			$('.c4d-woo-cart-icon').removeClass('empty');
		} else {
			if ($(self).parents('.c4d-woo-cart-icon').find('.product_list_widget li').length <= 1){
					$(self).parents('.c4d-woo-cart-icon').addClass('empty');
			} else {
				$(self).parents('.c4d-woo-cart-icon').removeClass('empty');
			}
		}
	};

	c4dWooCartIcon.miniTotalButtons = function() {
		var findButtons = setInterval(function(){
			$('.c4d-woo-cart-icon-wrap').each(function(){
				if ($(this).hasClass('type-slide')) {
					var cart = $(this).find('.c4d-woo-cart-icon');
					cart.find('.c4d-woo-cart-icon__footer').remove();
					cart.append('<div class="c4d-woo-cart-icon__footer"></div>');
					if (cart.find('.woocommerce-mini-cart__buttons').length > 0) {
						clearTimeout(findButtons);
					}
					cart.find('.c4d-woo-cart-icon__footer').append(cart.find('.woocommerce-mini-cart__total').detach()).append(cart.find('.woocommerce-mini-cart__buttons').detach());
				} else {
					clearTimeout(findButtons);
				}
			});
		}, 500);

	}
	c4dWooCartIcon.buildCart = function() {
		var cart = $('.c4d-woo-cart-icon-wrap');
		var openState = 'c4d-woo-cart-open';

		$('body').on('click', '.c4d-woo-cart-icon-wrap', function(event){
			if (event.target == event.currentTarget) {
				$('body').removeClass(openState);
			}
		});

		$('body').on('click', '.js_c4d_wci_close_cart', function(event){
			if (event.target == event.currentTarget) {
				$('body').removeClass(openState);
			}
		});

		// shortcut open cart
		$('body').on('click', '.js_c4d_wci_cart_click', function(event){
			event.preventDefault();
			$('body').toggleClass(openState);
			return false;
		});

		$( document.body ).on( 'added_to_cart removed_from_cart', function(event, fragments, cart_hash) {
			if (cart.hasClass('type-slide')) {
				c4dWooCartIcon.miniTotalButtons();
			}
			$('body').addClass(openState);
		});

		if (cart.hasClass('type-slide')) {
			// slide cart use mini cart template, need move total, button.
			// consider to create new template!!!
			c4dWooCartIcon.miniTotalButtons();
		}
	};

	c4dWooCartIcon.c4dcount = 0;

	c4dWooCartIcon.updateCountNumber = function() {
		$(document).ajaxComplete(function(event, request, settings) {
			clearTimeout(c4dWooCartIcon.c4dcount);
			if (typeof (settings.data) != 'undefined' || typeof (settings.url) != 'undefined'){
				var cart = -1;
				if (typeof (settings.data) != 'undefined'){
					cart = (settings.url).search('add_to_cart');
				}
				if (cart != -1) {
					c4dWooCartIcon.c4dcount = setTimeout(function(){
						var count_number = request.responseJSON.fragments.cart_count;
						if (count_number < 1) {
							$('.c4d-woo-cart-icon__icon').addClass('empty');
						} else {
							$('.c4d-woo-cart-icon__icon').removeClass('empty');
						}
						$('.c4d-woo-cart-icon__icon .number').html(count_number);
					}, 1000);
				}
			}
		});
	}

	c4dWooCartIcon.removeItem = function() {
		$('body').on('click', '.remove_from_cart_button, .c4d-woo-cart-icon__remove-link', function(event){
			$('body').addClass('c4d_woo_cart_popup_remove_item');
		});
		$( document.body ).on( 'removed_from_cart', function(){
			$('body').removeClass('c4d_woo_cart_popup_remove_item');
		});
		// remove product from mini cart by ajax
		// $('body').on('click', '.remove_from_cart_button, .c4d-woo-cart-icon__remove-link', function(event){
		// 	event.preventDefault();
		// 	c4dWooCartIcon.c4d_woo_cart_icon_number(-1);
		// 	var url = $(this).attr('href'),
		// 	self = this,
		// 	productId = url.match("remove_item?=(.*)&"),
		// 	isCartPage = $('body').hasClass('woocommerce-cart') ? true : false,
		// 	data = {
		// 		'action' : 'c4d_woo_cart_icon_remove_cart_item',
		// 		'product_id' : productId[1],
		// 		'is_cart_page': isCartPage
		// 	};

		// 	$.ajax({
		// 		type: "POST",
		// 		data: data,
		// 		url: woocommerce_params.ajax_url,
		// 		dataType: 'json'
		// 	}).done(function(){

		// 	});

		// 	setTimeout(function(){
		// 		c4dWooCartIcon.hideList(self);
		// 		$(self).parents('.mini_cart_item').remove();
		// 	}, 500);
		// });
	}

	c4dWooCartIcon.popupUpdateQty = function() {
		var updateCart;
		$( 'body' ).on('change', '.widget_shopping_cart_content_popup .woocommerce-cart-form input.qty', function(){
			var input = $(this);
			clearTimeout(updateCart);
			updateCart = setTimeout(function(){
				var data = {
					'action' : 'c4d_woo_cart_icon_popup_qty_update',
					'cart_item_key': input.attr('name').replace('cart[', '').replace('][qty]', ''),
					'qty': input.val()
				};
				$('body').addClass('c4d_woo_cart_popup_update_qty');
				$.ajax({
					type: "POST",
					data: data,
					url: woocommerce_params.ajax_url,
					dataType: 'json'
				}).done(function(){
					$( document.body ).trigger( 'wc_fragment_refresh' );
				});
				$( document.body ).on( 'wc_fragments_refreshed wc_fragments_ajax_error', function(){
					$('body').removeClass('c4d_woo_cart_popup_update_qty');
				});
			}, 200);

		});
	}

	c4dWooCartIcon.callForPriceButton = function() {
		// variable product
		$( 'form.variations_form' ).on('show_variation', function(event, variation){
			if (!variation.is_in_stock) {
				$('body').addClass('c4d-woo-cart-is-call-for-price');
			} else {
				$('body').removeClass('c4d-woo-cart-is-call-for-price');
			}
		});

		// simple product
		if ($('body.single-product button[name="add-to-cart"]').length < 1 ) {
			$('body').addClass('c4d-woo-cart-is-call-for-price');
		} else {
			$('body').removeClass('c4d-woo-cart-is-call-for-price');
		}
	}

	c4dWooCartIcon.flyAddToCartButton = function() {
		var trigger = $('.entry-summary');

		if ( trigger.length > 0 ) {
			var flyAddToCartToggle = function() {
				var top_of_element = trigger.offset().top;
		    var bottom_of_element = trigger.offset().top + trigger.outerHeight();
		    var bottom_of_screen = $(window).scrollTop() + $(window).innerHeight();
		    var top_of_screen = $(window).scrollTop();

		    if (top_of_screen + 150 > bottom_of_element) {
		    	$('body').addClass( 'c4d-woo-cart-fly-cart-active' );
		    } else {
		    	$('body').removeClass( 'c4d-woo-cart-fly-cart-active' );
		    }
			};

			flyAddToCartToggle();

			window.addEventListener( 'scroll', function() {
				flyAddToCartToggle();
			});

			$('.c4d-woo-cart-fly-add-to-cart').each(function(){
				var parent = $(this);
				var pid = parent.data('product_id');
				if ( pid ) {
					var product = $('#product-' + pid);
					if ( product.length > 0 ) {
						if ( ! product.hasClass( 'product-type-simple' ) && ! product.hasClass( 'product-type-external' ) ) {
							$('body').on('click', '.c4d-woo-cart-fly-add-to-cart .js-add-to-cart', function(event){
								event.preventDefault();
								$('html, body').animate({
								    scrollTop: trigger.offset().top
								}, 400);
							});
						}
					}
				}
			});
		}
	}

	$(document).ready(function(){

		c4dWooCartIcon.buildCart();

		c4dWooCartIcon.updateCountNumber();

		c4dWooCartIcon.removeItem();

		c4dWooCartIcon.popupUpdateQty();

		c4dWooCartIcon.callForPriceButton();

		c4dWooCartIcon.flyAddToCartButton();

		$('body').on('click', '.add_to_cart_button', function(){
			c4dWooCartIcon.hideList(this, true);
		});

		$('.c4d-woo-cart-icon').hover(function(){
			$('body').addClass('c4d-woo-cart-icon-body-not-scroll');
		}, function(){
			$('body').removeClass('c4d-woo-cart-icon-body-not-scroll');
		});
	});
})(jQuery)