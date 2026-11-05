var regsms_timer;


jQuery(document).ready(function ($) {


    $(document).on('click', '.regsms_open', function (e) {

            $('.modal--authorization').fadeIn(400);
            $('body').addClass('is-open-modal');


            $('#regsms-input-telephone').on('keyup', function (e) {
                detectedMobileErrors($(this))
            });

            if ($('#regsms-input-telephone').length) {
                new MaskInput('#regsms-input-telephone', {
                    mask: "+38(0A#) ###-##-##", tokens: {
                        A: {pattern: /[1-9]/,}
                    }
                });
            }

            $('#regsmsModal').modal();
        }); 




});

$(document).on('click', '#regsmsModal .offer a', function (e) {
    $('#regsmsOfferModal').load('index.php?route=extension/module/regsms/offer', function (e) {

        $('#regsmsOfferModal').modal();
    });

});

$(document).on('click', '#regsmsModal .regsms_auth', function (e) {
    var code = encodeURIComponent($('#regsmsModal .smscode-code').val());
    var telephone = encodeURIComponent($('#regsmsModal .smscode-telephone').val());
    var firstname = encodeURIComponent($('#customer_firstname').val());
    var lastname = encodeURIComponent($('#customer_lastname').val());

    $.ajax({
        url: 'index.php?route=extension/module/regsms/auth',
        type: 'post',
        data: 'telephone=' + telephone + '&code=' + code + '&firstname=' + firstname + '&lastname=' + lastname,
        dataType: 'json',
        crossDomain: true,
        beforeSend: function () {
            $('#regsmsModal .regsms_auth').button('loading');
        },
        complete: function () {
            $('#regsmsModal .regsms_auth').button('reset');
        },
        success: function (json) {

            $('#regsmsModal .smscode-code').removeClass('error');

            if (json['error']) {
                $('#regsmsModal .smscode-code').addClass('error');
                $('#regsmsModal .info').html('Невірний код');
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

function getMask(mask = '', placeholder = '') {
    $('#regsms-input-telephone').mask(mask).attr('placeholder', 'Введіть номер телефону');
}


$(document).on('click', '#button-customer_telephone', function (e) {
    // $('#regsmsModal .telegram_login').attr("disabled", true);

    var telephone = encodeURIComponent($('#customer_telephone').val());

    $.ajax({
        url: 'index.php?route=extension/module/telegram/check',
        type: 'post',
        data: 'telephone=' + telephone,
        dataType: 'json',
        crossDomain: true,
        beforeSend: function () {
            // $('#regsmsModal .telegram_login').button('loading');
        },
        complete: function () {
            // $('#regsmsModal .telegram_login').button('reset');
        },
        success: function (json) {
            $('#regsmsModal .error').removeClass('error');
            $('#regsmsModal .info').html('');
            // $('#regsmsModal .telegram_login').prop('disabled', false);

            console.log(json);
            if (!json['error']) {
                $('#regsmsModal .modal__body').load('index.php?route=extension/module/regsms', function (e) {
                    $('.modal--authorization').fadeIn(400);
                    $('body').addClass('is-open-modal');

                    if ($('#regsms-input-telephone').length) {

                        new MaskInput('#regsms-input-telephone', {
                            mask: "+38(0A#) ###-##-##", tokens: {
                                A: {pattern: /[1-9]/,}
                            }
                        });

                        $('#regsms-input-telephone').val(($('#customer_telephone').val()));
                    }

                    $('#regsmsModal').modal();

                    $('.modal-step').removeClass('is-active');
                    $('.modal-step-1').addClass('is-active');

                    if (json['link']) {
                        window.open(json['link'], '_blank');
                    }
                });

            } else {
                if (json['error']['telephone']) {
                    $('#button-customer_telephone').next().children().html(json['error']['telephone']);
                    $('#button-customer_telephone').next().children().css('display', 'block');
                    $('#button-customer_telephone').attr("disabled", false);
                    $('#button-customer_telephone').show();

                } else {
                    $('.toast').addClass('toast-danger');
                    $('.toast .p').text('Помилка, спробуйте ще раз');
                    $(".js-open-toast").trigger('click');
                }
            }


            //console.log(json);

        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

$(document).on('click', '#button-customer_sms', function (e) {
    var code = encodeURIComponent($('#customer_sms').val());
    var telephone = encodeURIComponent($('#smstelephone').val());
    var firstname = encodeURIComponent($('#customer_firstname').val());
    var lastname = encodeURIComponent($('#customer_lastname').val());

    $.ajax({
        url: 'index.php?route=extension/module/regsms/auth',
        type: 'post',
        data: 'telephone=' + telephone + '&code=' + code + '&firstname=' + firstname + '&lastname=' + lastname,
        dataType: 'json',
        crossDomain: true,
        beforeSend: function () {
            $('#button-customer_sms').button('loading');
        },
        complete: function () {
            $('#button-customer_sms').button('reset');
        },
        success: function (json) {

            $('#regsmsModal .smscode-code').removeClass('error');

            if (json['error']) {
                $('#button-customer_sms').next().children().css('display', 'block');
                $('#button-customer_sms').next().children().html('Введить код отриманого з SMS');
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