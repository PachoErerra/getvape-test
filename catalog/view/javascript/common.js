function getURLVar(key) {
    var value = [];

    var query = String(document.location).split('?');

    if (query[1]) {
        var part = query[1].split('&');

        for (i = 0; i < part.length; i++) {
            var data = part[i].split('=');

            if (data[0] && data[1]) {
                value[data[0]] = data[1];
            }
        }

        if (value[key]) {
            return value[key];
        } else {
            return '';
        }
    }
}

$(document).ready(function () {

    $(function () {

        var autoscroller = [];
        //setting
        autoscroller.hidePagination = 0;
        autoscroller.autoScroll = 0;
        autoscroller.catcher = '#endless';
        autoscroller.delay = 200;
        //setting

        autoscroller.loading = 0;

        loginControl.init()

        $(window).scroll(function () {


            if (inWindow(autoscroller.catcher) && !autoscroller.loading && autoscroller.autoScroll) {

                autoscroller.loading = true;
                $('#endless .fa-refresh').addClass('fa-spin');

                setTimeout(function () {

                    $('#endless').trigger('click');

                }, autoscroller.delay);

                $('.product__inner-item').hover(function () {
                    var newimg = $(this).find('a').attr('data-img-lg');
                    $(this).parent().parent().parent().find('.product__image img').attr("src", newimg);
                }, function () {
                    var oldimg = $(this).parent().parent().parent().find('.product__image img').attr("data-old-src");
                    $(this).parent().parent().parent().find('.product__image img').attr("src", oldimg);
                });

            }

        });

        if (autoscroller.hidePagination) {
            $('.pagination').hide();
        }


        if ($('.products.loadmore').length) {

            if ($('.pagination').length && !$('.pagination > li:last-child').hasClass('active')) {

                $('#content div.col-sm-6.text-right').hide();

                $('.pagination').parent().before('<button type="button" id="endless" class="button-light-blue button-light-blue--with-arrow" style="width: 100%;max-width: 100%;" >Показати ще</button>');

                $('#endless').on('click', function (e) {

                    var lastProduct = $('.products.loadmore').find('.product:last-child');

                    var nextPage = $('ul.pagination li.active').next().find('a:first-child');
                    dataUrl = $(nextPage).attr('href');

                    $.ajax({
                        url: $(nextPage).attr('href'), beforeSend: function () {
                            $('#endless').text('Зачекайте..');
                        }, success: function (data) {

                            var products = $(data).find('.products>div');

                            lastProduct.after(products);


                            $('.pagination').html($(data).find('.pagination > *'));

                            nextPage = $('ul.pagination li.active').next().find('a:first-child');

                            if (nextPage.length == 0) {
                                $('#morereviews').remove();
                                $('#endless').remove();
                            } else {
                                $('#endless').text('Показати ще');
                            }

                            autoscroller.loading = 0;

                            history.pushState(null, null, dataUrl);

                            h1 = $(data).find('h1').text();
                            $('h1').text(h1);
                        }
                    });

                    return false;
                });

            }
        }


        function inWindow(el) {
            if ($(el).length) {
                var scrollTop = $(window).scrollTop();
                var windowHeight = $(window).height();
                var offset = $(el).offset();

                if (scrollTop <= offset.top && ($(el).height() + offset.top) < (scrollTop + windowHeight + 400)) return true;
            }
            ;

            return false;
        }

    });


    // $(document).ajaxSuccess(function (event, xhr, settings) {
    //
    //     if (settings.url.indexOf('checkout/simplecheckout&group=0') != -1) {
    //
    //         if ($('#checkout-order-additional').length && $('#simplecheckout_summary').length) {
    //
    //             $('#checkout-order-additional').addClass('is-open');
    //             $('#simplecheckout_summary').removeClass('is-open');
    //
    //             $('[data-open="checkout-order-additional"]').addClass('is-open');
    //             $('[data-open="simplecheckout_summary"]').removeClass('is-open');
    //
    //             if (typeof window.initCheckoutAdditionalSlider === 'function') {
    //                 window.initCheckoutAdditionalSlider();
    //             }
    //         }
    //     }
    // });

    $('.product__inner-item').hover(function () {
        var newimg = $(this).find('a').attr('data-img-lg');
        $(this).parent().parent().parent().find('.product__image img').attr("src", newimg);
    }, function () {
        var oldimg = $(this).parent().parent().parent().find('.product__image img').attr("data-old-src");
        $(this).parent().parent().parent().find('.product__image img').attr("src", oldimg);
    });


    if ($(window).width() <= 1023) {
        $('.home__categories--mob').append($('.movecategories>*'));
    }

    $('.product__favorites, .card__rside-box-favorites').on('click', function () {
        if ($(this).hasClass('is-active')) {
            $(this).attr('onclick', $(this).attr('onclick').replace('wishlist.remove', 'wishlist.add'))
        } else {
            $(this).attr('onclick', $(this).attr('onclick').replace('wishlist.add', 'wishlist.remove'))
        }
        $(this).toggleClass('is-active');
    });

    $('.product__comparison, .card__rside-box-comparison').on('click', function () {
        if ($(this).hasClass('is-active')) {
            $(this).attr('onclick', $(this).attr('onclick').replace('compare.remove', 'compare.add'))
        } else {
            $(this).attr('onclick', $(this).attr('onclick').replace('compare.add', 'compare.remove'))
        }
        $(this).toggleClass('is-active');
    });
    // Highlight any found errors
    $('.text-danger').each(function () {
        var element = $(this).parent().parent();

        if (element.hasClass('form-group')) {
            element.addClass('has-error');
        }
    });

    // Currency
    $('#form-currency .currency-select').on('click', function (e) {
        e.preventDefault();

        $('#form-currency input[name=\'code\']').val($(this).attr('name'));

        $('#form-currency').submit();
    });

    // Language
    $('#form-language .language-select').on('click', function (e) {
        e.preventDefault();

        $('#form-language input[name=\'code\']').val($(this).attr('name'));

        $('#form-language').submit();
    });

    // Search
    $('.header__search input[name=\'search\']').parent().find('button').on('click', function (e) {
        e.preventDefault();
        var url = $('base').attr('href') + 'index.php?route=product/search';

        var value = $('.header__search input[name=\'search\']').val();

        if (value) {
            url += '&search=' + encodeURIComponent(value);
        }

        location = url;
    });

    $('.header__search input[name=\'search\']').on('keydown', function (e) {
        if (e.keyCode == 13) {
            $('.header__search input[name=\'search\']').parent().find('button').trigger('click');
        }
    });

    // Menu
    $('#menu .dropdown-menu').each(function () {
        var menu = $('#menu').offset();
        var dropdown = $(this).parent().offset();

        var i = (dropdown.left + $(this).outerWidth()) - (menu.left + $('#menu').outerWidth());

        if (i > 0) {
            $(this).css('margin-left', '-' + (i + 10) + 'px');
        }
    });

    // Product List
    $('#list-view').click(function () {
        $('#content .product-grid > .clearfix').remove();

        $('#content .row > .product-grid').attr('class', 'product-layout product-list col-xs-12');
        $('#grid-view').removeClass('active');
        $('#list-view').addClass('active');

        localStorage.setItem('display', 'list');
    });

    // Product Grid
    $('#grid-view').click(function () {
        // What a shame bootstrap does not take into account dynamically loaded columns
        var cols = $('#column-right, #column-left').length;

        if (cols == 2) {
            $('#content .product-list').attr('class', 'product-layout product-grid col-lg-6 col-md-6 col-sm-12 col-xs-12');
        } else if (cols == 1) {
            $('#content .product-list').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12');
        } else {
            $('#content .product-list').attr('class', 'product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12');
        }

        $('#list-view').removeClass('active');
        $('#grid-view').addClass('active');

        localStorage.setItem('display', 'grid');
    });

    if (localStorage.getItem('display') == 'list') {
        $('#list-view').trigger('click');
        $('#list-view').addClass('active');
    } else {
        $('#grid-view').trigger('click');
        $('#grid-view').addClass('active');
    }

    // Checkout
    $(document).on('keydown', '#collapse-checkout-option input[name=\'email\'], #collapse-checkout-option input[name=\'password\']', function (e) {
        if (e.keyCode == 13) {
            $('#collapse-checkout-option #button-login').trigger('click');
        }
    });

    // tooltips on hover
    $('[data-toggle=\'tooltip\']').tooltip({container: 'body'});

    // Makes tooltips work on ajax generated content
    $(document).ajaxStop(function () {
        $('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
    });

});

// Cart add remove functions
var cart = {
    'add': function (product_id, quantity, show_modal_basket = true) {
        $.ajax({
            url: 'index.php?route=checkout/cart/add',
            type: 'post',
            data: 'product_id=' + product_id + '&quantity=' + (typeof (quantity) != 'undefined' ? quantity : 1),
            dataType: 'json',
            success: function (json) {
                $('.alert-dismissible, .text-danger').remove();

                if (json['redirect']) {
                    location = json['redirect'];
                }

                if (json['success']) {
                    // Need to set timeout otherwise it wont update the total
                    setTimeout(function () {
                        $('#cart .modal__container').load('index.php?route=common/cart/info .modal__container >*', function () {
                            $('.icon-cart span').html(json['total']);

                            if (show_modal_basket) {
                                $('.modal--basket').fadeIn(400);
                                $('body').addClass('is-open-modal');
                            }

                            var $toast = $('.toast');
                            $toast.find('.p').html(json['success'])
                            $toast.removeClass('toast-danger')
                            $toast.addClass('is-show');

                            setTimeout(function () {
                                $toast.removeClass('is-show');
                            }, 4000);

                            basketTopSaleProducts();
                        });

                        //	$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');

                    }, 100);


                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }, 'showQtyWarning': function (message) {
        if (!message) {
            return;
        }

        if (typeof regsmsShowToastError === 'function') {
            regsmsShowToastError(message);
            return;
        }

        var $toast = $('.toast');
        $toast.addClass('toast-danger is-show');
        $toast.find('.p').text(message);
        setTimeout(function () {
            $toast.removeClass('is-show');
        }, 4000);
    }, 'normalizeQty': function ($input, quantity, showWarning) {
        var maxQty = parseInt($input.attr('data-max-qty'), 10);
        var warning = '';

        if (isNaN(maxQty) || maxQty < 1) {
            maxQty = 99;
        }

        if (isNaN(quantity) || quantity < 1) {
            quantity = 1;
        }

        if (quantity > maxQty) {
            quantity = maxQty;
            warning = ($input.attr('data-error-max') || 'Максимальна кількість товару - %s шт.').replace('%s', maxQty);
            if (showWarning) {
                this.showQtyWarning(warning);
            }
        }

        return quantity;
    }, 'stepQty': function (button, delta) {
        var $input = $(button).parent().find('input');
        var key = $input.attr('data-cart-id');
        var quantity = parseInt(String($input.val()).replace(/[^\d]/g, ''), 10);

        if (!key) {
            return;
        }

        if (isNaN(quantity)) {
            quantity = 1;
        }

        quantity = this.normalizeQty($input, quantity + delta, delta > 0);
        $input.val(quantity);
        $input.attr('data-prev-qty', quantity);
        this.update(key, quantity);
    }, 'applyQty': function (input) {
        var $input = $(input);
        var key = $input.attr('data-cart-id');
        var raw = parseInt(String($input.val()).replace(/[^\d]/g, ''), 10);
        var quantity = this.normalizeQty($input, raw, true);

        if (!key) {
            return;
        }

        $input.val(quantity);

        if (parseInt($input.attr('data-prev-qty'), 10) === quantity) {
            return;
        }

        $input.attr('data-prev-qty', quantity);
        this.update(key, quantity);
    }, 'update': function (key, quantity) {
        $.ajax({
            url: 'index.php?route=checkout/cart/edit',
            type: 'post',
            data: 'quantity[' + key + ']=' + (typeof (quantity) != 'undefined' ? quantity : 1),
            dataType: 'json',
            success: function (json) {
                if (json['warning']) {
                    cart.showQtyWarning(json['warning']);
                }

                // Need to set timeout otherwise it wont update the total
                setTimeout(function () {
                    $('#cart .modal__container').load('index.php?route=common/cart/info .modal__container >*', function () {
                        $('.icon-cart span').html(json['total']);
                        basketTopSaleProducts();
                    });
                    //	$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');

                }, 100);

                if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
                    location = 'index.php?route=checkout/cart';
                } else {
                    $('#cart .modal__container').load('index.php?route=common/cart/info .modal__container >*', function () {
                        basketTopSaleProducts();
                    });
                }

                if (getURLVar('route') == 'checkout/simplecheckout') {
                    reloadAll();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }, 'remove': function (key) {
        $.ajax({
            url: 'index.php?route=checkout/cart/remove',
            type: 'post',
            data: 'key=' + key,
            dataType: 'json',
            success: function (json) {
                // Need to set timeout otherwise it wont update the total
                setTimeout(function () {
                    $('#cart .modal__container').load('index.php?route=common/cart/info .modal__container >*', function () {
                        $('.icon-cart span').html(json['total']);
                        basketTopSaleProducts();
                    });
                }, 100);
                if (json['remove']) {
                    $('input[name="coupon"]').val('');
                }
                if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
                    location = 'index.php?route=checkout/cart';
                } else {
                    $('#cart .modal__container').load('index.php?route=common/cart/info .modal__container >*', function () {
                        basketTopSaleProducts();
                    });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
}

var vaporsPunchGift = {
    reloadCart: function (callback) {
        $('#cart .modal__container').load('index.php?route=common/cart/info .modal__container >*', function () {
            if (typeof basketTopSaleProducts === 'function') {
                basketTopSaleProducts();
            }
            if (typeof callback === 'function') {
                callback();
            }
        });
    },
    markInvalid: function () {
        $('.modal--basket .modal__item--gift .custom-dropdown-img--gift').each(function () {
            var $dropdown = $(this);
            var selected = $dropdown.find('.dropdown-list li.selected').length > 0;
            var hasChoice = $.trim($dropdown.find('.dropdown-header span').first().text()) !== '' &&
                $.trim($dropdown.find('.dropdown-header span').first().text()) !== 'Оберіть смак';

            if (!selected && !hasChoice) {
                $dropdown.addClass('custom-dropdown-img--invalid');
            }
        });
    },
    openCartInvalid: function () {
        vaporsPunchGift.reloadCart(function () {
            $('.modal--basket').fadeIn(400);
            $('body').addClass('is-open-modal');
            vaporsPunchGift.markInvalid();
        });
    },
    validate: function (onValid, onInvalid) {
        $.ajax({
            url: 'index.php?route=extension/module/vapors_punch_gift/validate',
            type: 'get',
            dataType: 'json',
            success: function (json) {
                if (json && json.active && !json.valid) {
                    if (typeof onInvalid === 'function') {
                        onInvalid(json);
                    } else {
                        vaporsPunchGift.openCartInvalid();
                    }
                } else if (typeof onValid === 'function') {
                    onValid(json);
                }
            },
            error: function () {
                if (typeof onValid === 'function') {
                    onValid({active: false, valid: true});
                }
            }
        });
    }
};

$(document).on('click', '.js-vapors-punch-option', function (e) {
    e.preventDefault();
    e.stopPropagation();

    var $li = $(this);
    var productId = $li.data('value');
    var slotIndex = $li.data('slot');
    var $dropdown = $li.closest('.custom-dropdown-img');

    $dropdown.removeClass('open custom-dropdown-img--invalid');

    $.ajax({
        url: 'index.php?route=extension/module/vapors_punch_gift/select',
        type: 'post',
        data: {
            product_id: productId,
            slot_index: slotIndex
        },
        dataType: 'json',
        success: function (json) {
            if (json && json.success) {
                if (json.total !== undefined) {
                    $('.icon-cart span').html(json.total);
                }
                vaporsPunchGift.reloadCart(function () {
                    if (getURLVar('route') == 'checkout/simplecheckout' && typeof reloadAll === 'function') {
                        reloadAll();
                    }
                });
            }
        }
    });
});

$(document).on('click', '.js-vapors-punch-clear', function (e) {
    e.preventDefault();
    e.stopPropagation();

    var slotIndex = $(this).data('slot');

    $.ajax({
        url: 'index.php?route=extension/module/vapors_punch_gift/clear',
        type: 'post',
        data: {
            slot_index: slotIndex
        },
        dataType: 'json',
        success: function (json) {
            if (json && json.success) {
                if (json.total !== undefined) {
                    $('.icon-cart span').html(json.total);
                }
                vaporsPunchGift.reloadCart(function () {
                    if (getURLVar('route') == 'checkout/simplecheckout' && typeof reloadAll === 'function') {
                        reloadAll();
                    }
                });
            }
        }
    });
});

$(document).on('click', '.js-vapors-punch-checkout', function (e) {
    var $link = $(this);
    var href = $link.attr('href');

    e.preventDefault();

    vaporsPunchGift.validate(function () {
        location = href;
    }, function () {
        vaporsPunchGift.openCartInvalid();
    });
});

var voucher = {
    'add': function () {

    }, 'remove': function (key) {
        $.ajax({
            url: 'index.php?route=checkout/cart/remove',
            type: 'post',
            data: 'key=' + key,
            dataType: 'json',
            beforeSend: function () {
                $('#cart > button').button('loading');
            },
            complete: function () {
                $('#cart > button').button('reset');
            },
            success: function (json) {
                // Need to set timeout otherwise it wont update the total
                setTimeout(function () {
                    $('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
                }, 100);

                if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
                    location = 'index.php?route=checkout/cart';
                } else {
                    $('#cart > ul').load('index.php?route=common/cart/info ul li');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
}

var wishlist = {
    'add': function (product_id) {
        $.ajax({
            url: 'index.php?route=account/wishlist/add',
            type: 'post',
            data: 'product_id=' + product_id,
            dataType: 'json',
            success: function (json) {
                $('.alert-dismissible').remove();

                if (json['redirect']) {
                    location = json['redirect'];
                }

                if (json['success']) {
                    //	$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                    $('.toast .p').html(json['success']);
                    $('.toast').addClass('is-show');
                    $('#wishlist-total').append('<span>' + json['total'] + '</span>');

                    // TODO: автоскрытие 4 sek
                    setTimeout(function () {
                        $('.toast').removeClass('is-show');
                    }, 4000);
                    return false

                }

                //$('#wishlist-total span').html(json['total']);
                //$('#wishlist-total').attr('title', json['total']);

                //$('html, body').animate({ scrollTop: 0 }, 'slow');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }, 'remove': function (product_id) {
        $.ajax({
            url: 'index.php?route=account/wishlist/remove',
            type: 'post',
            data: 'product_id=' + product_id,
            dataType: 'json',
            success: function (json) {

                if (json['success']) {
                    $('#wishlist-total').append('<span>' + json['total'] + '</span>');
                    $('.toast .p').html(json['success']);
                    $('.toast').addClass('is-show');

                    // TODO: автоскрытие 4 sek
                    setTimeout(function () {
                        $('.toast').removeClass('is-show');
                    }, 4000);
                    return false
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
}

var compare = {
    'add': function (product_id) {
        $.ajax({
            url: 'index.php?route=product/compare/add',
            type: 'post',
            data: 'product_id=' + product_id,
            dataType: 'json',
            success: function (json) {
                $('.alert-dismissible').remove();

                if (json['success']) {
                    // $('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

                    $('#compare-total').append('<span>' + json['total'] + '</span>');

                    // $('html, body').animate({ scrollTop: 0 }, 'slow');

                    $('.toast .p').html(json['success']);
                    $('.toast').addClass('is-show');

                    // TODO: автоскрытие 4 sek
                    setTimeout(function () {
                        $('.toast').removeClass('is-show');
                    }, 4000);
                    return false

                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }, 'remove': function (product_id) {
        $.ajax({
            url: 'index.php?route=product/compare/remove',
            type: 'post',
            data: 'product_id=' + product_id,
            dataType: 'json',
            success: function (json) {

                if (json['success']) {
                    $('#compare-total').append('<span>' + json['total'] + '</span>');
                    $('.toast .p').html(json['success']);
                    $('.toast').addClass('is-show');

                    // TODO: автоскрытие 4 sek
                    setTimeout(function () {
                        $('.toast').removeClass('is-show');
                    }, 4000);
                    return false
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
}

/* Agree to Terms */
$(document).delegate('.agree', 'click', function (e) {
    e.preventDefault();

    $('#modal-agree').remove();

    var element = this;

    $.ajax({
        url: $(element).attr('href'), type: 'get', dataType: 'html', success: function (data) {
            html = '<div id="modal-agree" class="modal">';
            html += '  <div class="modal-dialog">';
            html += '    <div class="modal-content">';
            html += '      <div class="modal-header">';
            html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
            html += '        <h4 class="modal-title">' + $(element).text() + '</h4>';
            html += '      </div>';
            html += '      <div class="modal-body">' + data + '</div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';

            $('body').append(html);

            $('#modal-agree').modal('show');
        }
    });
});


function scrollToBlock(el) {
    $('html, body').animate({
        scrollTop: parseInt($(el).offset().top - $('.header').outerHeight() - 30)
    }, 500);
}


// Autocomplete */
(function ($) {
    $.fn.autocomplete = function (option) {
        return this.each(function () {
            this.timer = null;
            this.items = new Array();

            $.extend(this, option);

            $(this).attr('autocomplete', 'off');

            // Focus
            $(this).on('focus', function () {
                this.request();
            });

            // Blur
            $(this).on('blur', function () {
                setTimeout(function (object) {
                    object.hide();
                }, 200, this);
            });

            // Keydown
            $(this).on('keydown', function (event) {
                switch (event.keyCode) {
                    case 27: // escape
                        this.hide();
                        break;
                    default:
                        this.request();
                        break;
                }
            });

            // Click
            this.click = function (event) {
                event.preventDefault();

                value = $(event.target).parent().attr('data-value');

                if (value && this.items[value]) {
                    this.select(this.items[value]);
                }
            }

            // Show
            this.show = function () {
                var pos = $(this).position();

                $(this).siblings('ul.dropdown-menu').css({
                    top: pos.top + $(this).outerHeight(), left: pos.left
                });

                $(this).siblings('ul.dropdown-menu').show();
            }

            // Hide
            this.hide = function () {
                $(this).siblings('ul.dropdown-menu').hide();
            }

            // Request
            this.request = function () {
                clearTimeout(this.timer);

                this.timer = setTimeout(function (object) {
                    object.source($(object).val(), $.proxy(object.response, object));
                }, 200, this);
            }

            // Response
            this.response = function (json) {
                html = '';

                if (json.length) {
                    for (i = 0; i < json.length; i++) {
                        this.items[json[i]['value']] = json[i];
                    }

                    for (i = 0; i < json.length; i++) {
                        if (!json[i]['category']) {
                            html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
                        }
                    }

                    // Get all the ones with a categories
                    var category = new Array();

                    for (i = 0; i < json.length; i++) {
                        if (json[i]['category']) {
                            if (!category[json[i]['category']]) {
                                category[json[i]['category']] = new Array();
                                category[json[i]['category']]['name'] = json[i]['category'];
                                category[json[i]['category']]['item'] = new Array();
                            }

                            category[json[i]['category']]['item'].push(json[i]);
                        }
                    }

                    for (i in category) {
                        html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

                        for (j = 0; j < category[i]['item'].length; j++) {
                            html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
                        }
                    }
                }

                if (html) {
                    this.show();
                } else {
                    this.hide();
                }

                $(this).siblings('ul.dropdown-menu').html(html);
            }

            $(this).after('<ul class="dropdown-menu"></ul>');
            $(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

        });
    }
})(window.jQuery);

/**
 * Модал авторизації (#regsmsModal), шаблон regsms_telegram.twig.
 *
 * Кроки (.modal-step + .is-active):
 *   modal-step-1            — телефон, SMS / Telegram
 *   modal-step-tg-connect   — підключення бота (need_bot)
 *   modal-step-4            — введення SMS-коду
 *   modal-tg-success-code   — введення коду з Telegram
 *
 * init() — один раз (initialized). Обробники через $(document).on.
 */

/** Назад на крок 1 (SMS step-4), без очищення Telegram-сесії. */
$(document).on('click', '#regsmsModal .js-regsms-step-back', function (e) {
    e.preventDefault();
    regsmsGoToStep1();
});

/** «Відправити код повторно» — повернення на step-1 (нова спроба відправки SMS). */
$(document).on('click', '#regsmsModal .no_code', function (e) {
    e.preventDefault();
    regsmsGoToStep1();

    if ($('#regsms-input-telephone').length) {
        new MaskInput('#regsms-input-telephone', {
            mask: "+38(0A#) ###-##-##", tokens: {
                A: {pattern: /[1-9]/,}
            }
        });
    }
});


/** Підтвердження коду з Telegram (кнопка на modal-tg-success-code). */
$(document).on('click', '#regsmsModal .js_telegram__auth', function (e) {
    e.preventDefault();
    let $code = $('#regsmsModal .telgram-code'), btn = $(this), chat_id = $('.telephone_code-chat_id').val(),
        message_id = $('.telephone_code-message_id').val(), code = encodeURIComponent($code.val());


    if (!code) {
        $code.addClass('error');
        return;
    }
    $code.removeClass('error');

    $.ajax({
        url: 'index.php?route=extension/module/telegram/telegram_login_code_auth',
        type: 'post',
        data: 'chat_id=' + chat_id + '&code=' + code + '&message_id=' + message_id,
        dataType: 'json',
        crossDomain: true,
        beforeSend: function () {
            btn.button('loading');
        },
        complete: function () {
            btn.button('reset');
        },
        success: function (json) {

            if (json['error']) {
                if (json['type'] === 'rate_limit') {
                    regsmsShowToastError(json['text']);
                } else {
                    $('#regsmsModal .modal-tg-success-code .info').html(json['text']);
                }
            }

            if (json['success']) {

                if (json['redirect']) {
                    location.href = json['redirect'];
                } else {
                    location.reload();
                }
            }

        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });


});

/** Підстановка %s у рядок з data-атрибутів кроку (переклади з twig). */
function regsmsFormatLang(template, value) {
    if (!template) {
        return '';
    }
    return String(template).replace('%s', value);
}

/** Довжина SMS/Telegram коду на бекенді (sendcode2: rand 1111–9999). */
var REGSMS_CODE_LENGTH = 4;

/** Вмикає .regsms_auth / .js_telegram__auth лише при 4 цифрах у полі. */
function regsmsUpdateCodeConfirmButtons() {
    var smsLen = ($('#regsmsModal #regsms-input-smscode').val() || '').replace(/\D/g, '').length;
    var tgLen = ($('#regsmsModal #telegram-input-code').val() || '').replace(/\D/g, '').length;

    $('#regsmsModal .regsms_auth').prop('disabled', smsLen !== REGSMS_CODE_LENGTH);
    $('#regsmsModal .js_telegram__auth').prop('disabled', tgLen !== REGSMS_CODE_LENGTH);
}

/**
 * Скидання до кроку введення телефону.
 * Очищає SMS-поле, таймер, вимикає кнопки підтвердження коду.
 */
function regsmsGoToStep1() {
    clearInterval(regsms_timer);
    $('#regsmsModal .modal-step').removeClass('is-active');
    $('#regsmsModal .modal-step-1').addClass('is-active');
    $('#regsmsModal .modal-step-4 .smscode-code').val('');
    $('#regsmsModal .modal-step-4 .info').html('');
    $('#regsmsModal .modal-step-4 .codetimer').hide();
    $('#regsmsModal .modal-step-4 .no_code').hide();
    $('#regsmsModal .regsms_auth, #regsmsModal .js_telegram__auth').prop('disabled', true);
}

/**
 * Тексти під заголовком на step-4 після sendcode2.
 * json.break — код уже був за 24 год (нова SMS не відправлялась).
 */
function updateSmsStep4Texts(json) {
    var $step = $('#regsmsModal .modal-step-4');
    var phone = $('#regsmsModal #regsms-input-telephone').val() || json['telephone'] || '';
    var line1;
    var line2;

    if (json['break']) {
        line1 = json['break'];
        line2 = json['break2'] || '';
    } else {
        line1 = regsmsFormatLang($step.data('text-sent'), phone);
        line2 = $step.data('text-back') || '';
    }

    $step.find('.js-sms-step-line1').text(line1);
    $step.find('.js-sms-step-line2').text(line2);
}

/** Показати один крок модала (решта .modal-step ховаються). */
function regsmsShowStep(stepSelector) {
    $('#regsmsModal .modal-step').removeClass('is-active');
    $(stepSelector).addClass('is-active');
}

/** Помилка через toast (червоний). Toast у body — поверх модалок. */
function regsmsShowToastError(text) {
    var $toast = $('.toast');

    if ($toast.length && !$toast.parent().is('body')) {
        $toast.appendTo('body');
    }

    $toast.addClass('toast-danger');
    $toast.find('.p').text(text || 'Помилка, спробуйте ще раз');
    $('.js-open-toast').trigger('click');
}

/** Рядок таймера повторної SMS; шаблон у data-resend-timer на .modal-step-4. */
function regsmsCodetimerText(seconds) {
    var template = $('#regsmsModal .modal-step-4').data('resend-timer');

    if (template) {
        return String(template).replace('%s', seconds);
    }

    return 'Відправити код повторно можно через ' + seconds + ' секунд';
}

/**
 * Таймер після нової відправки SMS (60 с), потім показує посилання .no_code.
 * Не запускати при json.break — повторна SMS не надсилалась.
 */
function regsmsStartSmsResendTimer(seconds) {
    clearInterval(regsms_timer);
    $('#regsmsModal .modal__body .no_code').hide();
    $('#regsmsModal .modal__body .codetimer').show();

    var time = seconds;

    if (time <= 0) {
        return;
    }

    $('#regsmsModal .codetimer').html(regsmsCodetimerText(time));

    regsms_timer = setInterval(function () {
        time = time - 1;
        $('#regsmsModal .codetimer').html(regsmsCodetimerText(time));

        if (time <= 0) {
            clearInterval(regsms_timer);
            $('#regsmsModal .no_code').show();
            $('#regsmsModal .codetimer').hide();
        }
    }, 1000);
}

/** Таймер на step-1 при json.error.session (занадто часті запити). */
function regsmsStartSessionRateTimer($btn) {
    $('#regsmsModal .regsms_send_code').hide();
    $('#regsmsModal .modal__body .no_code').hide();
    $('#regsmsModal .modal__body .codetimer').show();
    clearInterval(regsms_timer);

    var time = 60;

    regsms_timer = setInterval(function () {
        time = time - 1;
        $('#regsmsModal .info .time').html(time);

        if (time <= 0) {
            clearInterval(regsms_timer);
            $('#regsmsModal .info').html('');
            $('#regsmsModal .regsms_send_code').show();
            $btn.prop('disabled', false);
        }
    }, 1000);
}

/** Перехід на step-4: ввід SMS-коду. */
function regsmsShowSmsCodeStep(json) {
    regsmsShowStep('#regsmsModal .modal-step-4');
    $('#regsmsModal .modal__body .no_code').hide();
    $('#regsmsModal .modal-step-4 .smscode-telephone').val(json['telephone']);
    updateSmsStep4Texts(json);
    clearInterval(regsms_timer);

    if (json['break']) {
        $('#regsmsModal .modal__body .codetimer').hide();
    } else {
        regsmsStartSmsResendTimer(60);
    }

    $('#regsmsModal #regsms-input-smscode').val('');
    regsmsUpdateCodeConfirmButtons();
    $('#regsmsModal #regsms-input-smscode').focus();
}

/** Обробка відповіді extension/module/regsms/sendcode2. */
function regsmsHandleSendcode2(json, $btn) {
    $('#regsmsModal .error').removeClass('error');
    $('#regsmsModal .info').html('');
    $('#regsmsModal .regsms_send_code').prop('disabled', false);

    if (json['error'] && !json['break']) {
        if (json['error']['session']) {
            regsmsStartSessionRateTimer($btn);
        }

        if (json['error']['telephone']) {
            $('#regsmsModal #regsms-input-telephone').addClass('error');
            $('#regsmsModal .info').html(json['error']['telephone']);
        }

        return;
    }

    regsmsShowSmsCodeStep(json);
}

/** Оновити data-message_id / data-chat_id на «Назад» і «Змінити телефон». */
function regsmsUpdateTelegramEditIds(result) {
    $('.js_edit_number').attr('data-message_id', result['messageId']).attr('data-chat_id', result['chatId']);
}

/** Крок введення коду з Telegram після telegram_login_code. */
function regsmsShowTelegramCodeStep(result) {
    $('.telephone_code-chat_id').val(result['telegram_user_id']);
    $('.telephone_code-message_id').val(result['messageId']);
    regsmsUpdateTelegramEditIds(result);
    regsmsShowStep('#regsmsModal .modal-tg-success-code');
    $('#regsmsModal #telegram-input-code').val('');
    $('#regsmsModal .modal-tg-success-code .info').html('');
    regsmsUpdateCodeConfirmButtons();
    $('#regsmsModal #telegram-input-code').focus();
}

/** type need_bot (або застарілий sms) — бот не привʼязаний до номера. */
function regsmsIsTelegramNeedBot(result) {
    return result['type'] === 'need_bot' || result['type'] === 'sms';
}

/** Крок підключення Telegram: інструкція, відкрити бота, посилання «Надіслати код». */
function regsmsShowTelegramConnectStep(result) {
    var $step = $('#regsmsModal .modal-step-tg-connect');
    var phone = $('#regsmsModal #regsms-input-telephone').val() || '';
    var introTemplate = $step.data('text-intro') || '';
    var link = result['link'] || $step.data('bot-link') || '';

    $step.find('.js-tg-connect-intro').text(regsmsFormatLang(introTemplate, phone));

    if (link) {
        $step.data('bot-link', link);
        $step.find('.js-tg-open-bot').attr('href', link);
    }

    regsmsShowStep('#regsmsModal .modal-step-tg-connect');
}

/**
 * Відповідь telegram_login_code: код у боті або крок підключення (need_bot).
 * @param {object} [options] notifyNotSubscribed — toast «не підписалися» при повторній спробі
 */
function regsmsHandleTelegramLoginCodeResult(result, $btn, options) {
    options = options || {};

    if (result['type'] === 'rate_limit') {
        regsmsShowToastError(result['text']);
        return;
    }

    if (result['error'] && !regsmsIsTelegramNeedBot(result)) {
        regsmsShowToastError(result['text']);
        return;
    }

    if (regsmsIsTelegramNeedBot(result)) {
        var wasOnConnectStep = $('#regsmsModal .modal-step-tg-connect').hasClass('is-active');

        regsmsShowTelegramConnectStep(result);

        if (options.notifyNotSubscribed || wasOnConnectStep) {
            var $step = $('#regsmsModal .modal-step-tg-connect');
            var toastText = $step.data('not-subscribed');

            if (result['error'] && result['text']) {
                toastText = result['text'];
            }

            regsmsShowToastError(toastText);
        }

        return;
    }

    regsmsShowTelegramCodeStep(result);
}

/** POST telegram_login_code (з step-1 або посилання «Надіслати код» на connect). */
function regsmsRequestTelegramLoginCode($triggerBtn) {
    var $formBtn = $('#regsmsModal .js-nts-btn-login[data-source="telegram_code"]');

    if ($triggerBtn && $triggerBtn.length) {
        if ($triggerBtn.is('button')) {
            $triggerBtn.prop('disabled', true);
        } else {
            $triggerBtn.addClass('is-disabled');
        }
    } else if ($formBtn.length) {
        $formBtn.prop('disabled', true);
    }

    return $.ajax({
        url: 'index.php?route=extension/module/telegram/telegram_login_code',
        method: 'POST',
        data: $('#regsmsModal .telegram_modal_form').serialize(),
        dataType: 'json'
    }).always(function () {
        if ($triggerBtn && $triggerBtn.length) {
            if ($triggerBtn.is('button')) {
                $triggerBtn.prop('disabled', false);
            } else {
                $triggerBtn.removeClass('is-disabled');
            }
        }
        if ($formBtn.length) {
            $formBtn.prop('disabled', false);
        }
    });
}

/**
 * Ініціалізація модала входу. Викликати один раз з document.ready.
 * Кнопки step-1: data-source = sms | telegram_code.
 */
let loginControl = {
    /** Захист від повторного навішування обробників. */
    initialized: false,

    /** Підписка на кліки та поля; безпечно викликати лише один раз. */
    init: function () {
        if (loginControl.initialized) {
            return;
        }

        loginControl.initialized = true;
        loginControl.bindLoginButtons();
        loginControl.bindPinCode();
        loginControl.bindCodeInputs();
        loginControl.bindEditNumber();
        loginControl.bindTelegramConnectStep();
        regsmsUpdateCodeConfirmButtons();
    },

    /** Клік «Увійти за SMS» / «Підтвердити в Telegram» на step-1. */
    bindLoginButtons: function () {
        $(document).on('click', '#regsmsModal .js-nts-btn-login', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var source = $btn.data('source');

            if (source === 'sms') {
                loginControl.sendSmsCode($btn);
            } else if (source === 'telegram_code') {
                loginControl.sendTelegramCode($btn);
            }
        });
    },

    /** POST sendcode2 → step-4 або помилка на step-1. */
    sendSmsCode: function ($btn) {
        var telephone = encodeURIComponent($('#regsmsModal #regsms-input-telephone').val());

        $.ajax({
            url: 'index.php?route=extension/module/regsms/sendcode2',
            type: 'post',
            data: 'telephone=' + telephone,
            dataType: 'json',
            crossDomain: true,
            beforeSend: function () {
                $('#regsmsModal .regsms_send_code').button('loading');
            },
            complete: function () {
                $('#regsmsModal .regsms_send_code').button('reset');
            },
            success: function (json) {
                regsmsHandleSendcode2(json, $btn);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    },

    /** POST telegram_login_code → код або modal-step-tg-connect. */
    sendTelegramCode: function ($btn) {
        regsmsRequestTelegramLoginCode($btn)
            .done(function (result) {
                regsmsHandleTelegramLoginCodeResult(result, $btn);
            })
            .fail(function (xhr) {
                if (xhr.status === 429) {
                    regsmsShowToastError();
                } else {
                    loginControl.showFormErrors(xhr);
                }
            });
    },

    /** Назад, відкрити бота, повторне надсилання коду на кроці підключення TG. */
    bindTelegramConnectStep: function () {
        $(document).on('click', '#regsmsModal .js-tg-connect-back', function (e) {
            e.preventDefault();
            regsmsGoToStep1();
        });

        $(document).on('click', '#regsmsModal .js-tg-open-bot', function (e) {
            var link = $('#regsmsModal .modal-step-tg-connect').data('bot-link') || $(this).attr('href');

            if (!link) {
                e.preventDefault();
            }
        });

        $(document).on('click', '#regsmsModal .js-tg-connect-resend', function (e) {
            e.preventDefault();
            var $btn = $(this);

            regsmsRequestTelegramLoginCode($btn)
                .done(function (result) {
                    regsmsHandleTelegramLoginCodeResult(result, $btn, {notifyNotSubscribed: true});
                })
                .fail(function (xhr) {
                    if (xhr.status === 429) {
                        regsmsShowToastError();
                    } else {
                        loginControl.showFormErrors(xhr);
                    }
                });
        });
    },

    /** Помилки валідації з responseJSON.errors під полями форми. */
    showFormErrors: function (result) {
        var errors = result.responseJSON && result.responseJSON.errors;

        if (!errors) {
            return;
        }

        $.each(errors, function (index, value) {
            $('input[name="' + index + '"]').addClass('has-error').after('<div class="error_register_enter_modals">' + value + '</div>');
        });
    },

    /** Окремі pin-code-input (якщо є поза основним модалом). */
    bindPinCode: function () {
        if (!$('.pin-code-input').length) {
            return;
        }

        var fields = document.querySelectorAll('.pin-code-input');

        function handleInputField(event) {
            var target = event.target;
            var value = target.value.slice(0, 1);
            target.value = value;

            var step = value ? 1 : -1;
            var fieldIndex = Array.prototype.indexOf.call(fields, target);
            var focusToIndex = fieldIndex + step;

            if (focusToIndex < 0 || focusToIndex >= fields.length) {
                return;
            }

            fields[focusToIndex].focus();
        }

        fields.forEach(function (field) {
            field.addEventListener('input', handleInputField);
        });
    },

    /** Тільки цифри, max 4 символи, Enter → підтвердження коду. */
    bindCodeInputs: function () {
        $(document).on('input', '#regsmsModal #regsms-input-smscode, #regsmsModal #telegram-input-code', function () {
            var $input = $(this);
            var digits = ($input.val() || '').replace(/\D/g, '').slice(0, REGSMS_CODE_LENGTH);

            if ($input.val() !== digits) {
                $input.val(digits);
            }

            regsmsUpdateCodeConfirmButtons();
        });

        $(document).on('keydown', '#regsmsModal #regsms-input-smscode, #regsmsModal #telegram-input-code', function (e) {
            if (e.keyCode !== 13) {
                return;
            }

            if (this.id === 'regsms-input-smscode' && !$('#regsmsModal .regsms_auth').prop('disabled')) {
                $('#regsmsModal .regsms_auth').trigger('click');
            }

            if (this.id === 'telegram-input-code' && !$('#regsmsModal .js_telegram__auth').prop('disabled')) {
                $('#regsmsModal .js_telegram__auth').trigger('click');
            }
        });
    },

    /**
     * «Назад» / «Змінити телефон» (js_edit_number): step-1 + telegram_clear_cache
     * (видаляє повідомлення бота, скидає кеш сесії).
     */
    bindEditNumber: function () {
        $(document).on('click', '#regsmsModal .js_edit_number', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var messageId = $btn.attr('data-message_id') || $('#regsmsModal .telephone_code-message_id').val();
            var chatId = $btn.attr('data-chat_id') || $('#regsmsModal .telephone_code-chat_id').val();

            regsmsGoToStep1();
            $('#regsmsModal #telegram-input-code').val('').removeClass('error');
            $('#regsmsModal .modal-tg-success-code .info').html('');

            $.ajax({
                url: 'index.php?route=extension/module/telegram/telegram_clear_cache', method: 'POST', data: {
                    messageId: messageId, chatId: chatId
                }
            });
        });
    }
};
