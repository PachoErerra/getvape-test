<?php
// Auto-generated
$_['text_lviv'] = 'Львів';
$_['text_ivano_frankivsk'] = 'Івано-Франківськ';
$_['text_yasynya'] = 'Ясиня';
$_['text_select_insertbefore_row_shipping'] = '\';	
	$(select).insertBefore($(\'.row-shipping_address_city input\'));

	$(\'.row-shipping_address_city input\').hide();
	
	$(\'#shipping_address_city\').val($(\'#shipping_address_city_select\').val()).trigger(\'change\');
	
	$(\'body\').on(\'change\', \'#shipping_address_city_select\', function(){
		$(\'#shipping_address_city\').val($(\'#shipping_address_city_select\').val()).trigger(\'change\');
	});
	
	if ($(\'#shipping_address_city\').val()){
		var val = text = $(\'#shipping_address_city\').val();
		$("#shipping_address_city_select option[value=" + val + "]").attr(\'selected\', \'true\').text(text);
		
		if ($(\'#shipping_address_city\').val() != $(\'#shipping_address_city_select\').val()) {
			$(\'#shipping_address_city\').val(\'Львів\');
		}
	}
	
	
/*	
	$(\'.row-shipping_address_ya>div>label\').hide();
	
	if ($("#shipping_address_ya").prop(\'checked\')) {
		// console.log(1);
		$(\'.row-shipping_address_receiver_phone\').hide();
		$(\'.row-shipping_address_pib\').hide();
	} else {
	// console.log(0);
	}
*/';
