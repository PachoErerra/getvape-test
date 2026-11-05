<?php

// Heading
$_['heading_title'] = '<strong style="color:#41637d">DEV-OPENCART.COM —</strong> <b>Seo Tags generator</b> <a href="https://dev-opencart.com" target="_blank" title="Dev-opencart.com - Модули и шаблоны для Opencart"><img style="margin-left:15px;height:35px;margin-top:10px;margin-bottom:10px;" src="https://dev-opencart.com/logob.svg" alt="Dev-opencart.com - Модули и шаблоны для Opencart"/></a>';


// Text
$_['text_extension']			 = 'Modules';
$_['text_success']				 = 'SEO Tags Generator settings modified';
$_['text_edit']						 = 'Edit SEO Tags Generator';
$_['text_available_vars']	 = 'Available Variables';

$_['text_author']					 = 'Author';
$_['text_author_support']	 = 'Support';


// Fieldsets
$_['fieldset_setting']					 = 'Setting';
$_['fieldset_formula_common']		 = 'The general formula by default';
$_['fieldset_attributes_common'] = 'Attribute setting';


// Tab
$_['tab_category']		 = 'Categories';
$_['tab_product']			 = 'Products';
$_['tab_manufacturer'] = 'Manufacturers';


// Entry
$_['entry_licence'] = 'Licence Code';

$_['entry_status'] = 'Module Status';

$_['entry_generate_mode_category']					 = 'Generate meta tags for categories';
$_['entry_generate_mode_category_h1']				 = 'Generate Tag H1 for categories';
$_['entry_generate_mode_category_text']			 = 'Generate text for categories';
$_['entry_generate_mode_product']						 = 'Generate meta tags for products';
$_['entry_generate_mode_product_h1']				 = 'Generate Tag H1 for products';
$_['entry_generate_mode_product_text']			 = 'Generate text for products';
$_['entry_generate_mode_manufacturer']			 = 'Generate meta tags for manufacturers';
$_['entry_generate_mode_manufacturer_h1']		 = 'Generate Tag H1 for manufacturers';
$_['entry_generate_mode_manufacturer_text']	 = 'Generate text for manufacturers';

$_['text_generate_mode_nofollow']	 = 'Don\'t generate';
$_['text_generate_mode_empty']		 = 'Only if are empty';
$_['text_generate_mode_forced']		 = 'Even if already filled in the admin panel';

$_['entry_inheritance']					 = 'Inheriting formulas from parent to child category';
$_['entry_inheritance_tooltip']	 = 'If a specific (non-default) formula is designated for the parent category (ex: MP3 players), then what to do if the formula is not designated for its child category (ex: MP3 players Transcend)?<br><br>Does the child category use a formula from the parent category?';

$_['entry_declension']				 = 'Use cases declension for category names';
$_['entry_declension_tooltip'] = 'If this option is enabled, then it is necessary to fill the "denoting word" for all categories required + other cases as desired. If you do not want to edit each category, then it is better not to enable this option.';

$_['entry_category_title']			 = 'HTML Tag Title';
$_['entry_category_description'] = 'Meta Tag Description';
$_['entry_category_keyword']		 = 'Meta Tag Keywords';
$_['entry_category_h1']					 = 'HTML Tag H1';
$_['entry_category_text']				 = 'Category Text';

$_['entry_product_title']				 = 'HTML Tag Title';
$_['entry_product_description']	 = 'Meta Tag Description';
$_['entry_product_keyword']			 = 'Meta Tag Keywords';
$_['entry_product_h1']					 = 'HTML Tag H1';
$_['entry_product_text']				 = 'Product Text';

$_['entry_manufacturer_title']			 = 'HTML Tag Title';
$_['entry_manufacturer_description'] = 'Meta Tag Description';
$_['entry_manufacturer_keyword']		 = 'Meta Tag Keywords';
$_['entry_manufacturer_h1']					 = 'HTML Tag H1';
$_['entry_manufacturer_text']				 = 'Manufacturer Text';


// Attributes
$_['attributes_title']						 = 'Add the attributes that will be used when generating meta tags for all products by default.';
$_['attributes_title_specific']		 = 'Attribute setting';
$_['attributes_subtitle_specific'] = 'Add the attributes that will be used when generating meta tags for products of THIS category.';
$_['add_attribute']								 = 'Add attribute';
$_['delete_attribute']						 = 'Remove attribute';
$_['text_attribute_select']				 = 'Select attribute';
$_['error_attributes_empty']			 = 'Attributes are defined NOT FOR ALL variables!';


// Button
$_['button_save']		 = 'Save';
$_['button_cancel']	 = 'Cancel';


// Warning
$_['warning_licence'] = '';


// Error
$_['error_permission'] = 'Warning: You do not have permission to modify module SEO Tags Generator!';
$_['error_warning']		 = 'Warning: Please check the form carefully for errors!';

$_['error_licence']						 = 'Input licence code!';
$_['error_licence_empty']			 = 'Input licence code!';
$_['error_licence_not_valid']	 = 'Licence code is not valid!';


// For Category
$_['fieldset_seo_tags_generator']							 = 'Declination of category name (for SEO Tags Generator)';
$_['entry_category_name_singular_nominative']	 = '&quot;Denoting word&quot; for products (Category name in singular nominative)';
$_['error_category_name_singular_nominative']	 = '&quot;Denoting word&quot; is required!';
$_['entry_category_name_singular_genitive']	   = 'Category name in singular genitive';
$_['entry_category_name_plural_nominative']		 = 'The full name of the category (in plural nominative)';
$_['entry_category_name_plural_genitive']			 = 'Category name in the plural genitive';

$_['entry_category_meta_stg_no_auto']			 = 'Use manually typed meta tags for this category';
$_['entry_category_meta_stg_no_auto_help'] = 'When SEO Tags Generator is enables, meta tags are generated by the formula on each page opening. A this tick means that the meta tags of this category will be taken from the database and will correspond to what is stored in the admin panel.';
$_['text_category_explain_stg_no_auto']		 = 'Module <b>SEO Tags Generator</b> generate <i>HTML Tag Title</i>, <i>Meta Tag Description</i>, <i>Meta Tag Keywords</i> and <i>HTML Tag H1</i>';

$_['text_stg_preview'] = 'SEO Tags Generator preview';

$_['tab_seo_tags_generator']			 = 'SEO Tags Generator: Category Settings';
$_['tab_seo_tags_generator_info']	 = '<i class="fa fa-info-circle"></i> Warning!<br>The settings in this tab overlap the values that are set in the module settings as defaults';

$_['tab_category_setting']							 = 'Category Settings';
$_['entry_category_setting_inheritance'] = 'Inheriting formulas in child categories if they are empty';
$_['text_inheritance_yes']							 = 'Inherit';
$_['text_inheritance_no']								 = 'Do not inherit';

$_['entry_category_setting_inheritance_copy']	 = 'Copy these formulas into child subcategories';
$_['text_inheritance_copy_yes']								 = 'Yes';
$_['text_inheritance_copy_warning']						 = 'Warning!<br>'
	. 'You have already copied these formulas. And perhaps there have been changes that should not be overwritten.';

$_['entry_category_setting_copy_to_others']	 = 'Copy these formulas to other categories (not children)';
$_['text_copy_to_others_yes']								 = 'Yes';
$_['text_copy_to_others_warning']						 = 'Warning!<br>'
	. 'You have already copied these formulas. And perhaps there have been changes that should not be overwritten.';

$_['entry_categories']	 = 'Select categories';
$_['text_select_all']		 = 'Select all';
$_['text_unselect_all']	 = 'Unselect all';


// For Product
$_['entry_product_meta_stg_no_auto']			 = 'Use manually typed meta tags for this product';
$_['entry_product_meta_stg_no_auto_help']	 = 'When SEO Tags Generator is enables, meta tags are generated by the formula on each page opening. A this tick means that the meta tags of this product will be taken from the database and will correspond to what is stored in the admin panel.';
$_['text_product_explain_stg_no_auto']		 = 'Module <b>SEO Tags Generator</b> generate <i>HTML Tag Title</i>, <i>Meta Tag Description</i>, <i>Meta Tag Keywords</i> and <i>HTML Tag H1</i> for product. <span style="color: red; ">Field &quot;Product Tags&quot; NOT generate</span>.<br><br>The check mark is here because it is here that the separation of language versions ends.';

$_['entry_model_synonym'] = 'Model Synonym';





