<?php

// Text
$_['text_title'] = 'Кларна Invoice - 15 дней без регистрации';
$_['text_terms_fee'] = '<span id="klarna_invoice_toc"></span> (+%s)<script type="text/javascript">var terms = new Klarna.Terms.Invoice({el: \'klarna_invoice_toc\', eid: \'%s\', country: \'%s>, ,';
$_['text_terms_no_fee'] = '<span id="klarna_invoice_toc"></span><script type="text/javascript">var terms = new Klarna.Terms.Invoice({el: \'klarna_invoice_toc\', eid: \'%s\', country: \'%s\'});</script>';
$_['text_additional'] = 'Klarna Invoice требует некоторые информационные данные после того, как может работать.';
$_['text_male'] = 'Male';
$_['text_female'] = 'Female';
$_['text_year'] = 'Год';
$_['text_month'] = 'Луна';
$_['text_day'] = 'День';
$_['text_comment']				= 'Klarna’s Invoice ID: %s. ' . "\n" . '%s/%s: %.4f';

// Entry
$_['entry_gender'] = 'Гендер';
$_['entry_pno'] = 'Personal Number';
$_['entry_dob'] = 'Date of Birth';
$_['entry_phone_no'] = 'Phone number';
$_['entry_street'] = 'Street';
$_['entry_house_no'] = 'House No.';
$_['entry_house_ext'] = 'House Ext.';
$_['entry_company'] = 'Company Registration Number';

// Help
$_['help_pno'] = 'Please enter your Social Security номер здесь.';
$_['help_phone_no'] = 'Please enter your phone number.';
$_['help_street'] = 'Следует заметить, что уведомление может только получить место в зарегистрированных адресах при получении с Klarna.';
$_['help_house_no'] = 'Please enter your house number.';
$_['help_house_ext'] = 'Please submit your house extension here. E.g. A, B, C, Red, Blue ect.';
$_['help_company'] = 'Please enter your Company registration number';

// Error
$_['error_deu_terms'] = 'Вы должны достичь Klarna\'s privacy policy (Datenschutz)';
$_['error_address_match'] = 'Billing и Shipping addressses должны бороться, если вы хотите использовать Klarna Invoice';
$_['error_network'] = 'Проблема была при участии Клярна. Please try again later.';