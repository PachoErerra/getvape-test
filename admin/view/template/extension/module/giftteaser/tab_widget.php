<div class="tab-pane" id="notification_message" >
     <table class="table gt-widget-design">

	   	<tr>
            <td class="col-xs-3">
                <h5><strong><?php echo $wrap_in_widget; ?></strong></h5>
            </td>
      		<td class="col-xs-9">
            	<div class="col-xs-4">
					 <select name="giftteaser[widget]" class="form-control">
						<option value="yes" <?php echo (!empty($data['giftteaser']['widget']) && $data['giftteaser']['widget'] == 'yes') ? 'selected=selected' : '' ?>><?php echo $text_yes;?></option>
						<option value="no"  <?php echo (empty($data['giftteaser']['widget']) || $data['giftteaser']['widget']== 'no') ? 'selected=selected' : '' ?>><?php echo $text_no;?></option>
					 </select>
				</div>
			</td>
	    </tr>
		<tr>
      		<td class="col-xs-3">
                <h5><strong><span class="required">*</span><?php echo $gift_image_size; ?></strong></h5>
                <span class="help"><i class="fa fa-info-circle"></i>&nbsp;<?php echo $gift_image_size_help; ?></span>
            </td>
      		<td class="col-xs-9">
            	<div class="col-xs-4">
                    <div class="input-group">
                        <input type="text" value="<?php if(!empty($data['giftteaser']['giftImageWidth'])) echo (int)$data['giftteaser']['giftImageWidth']; else echo 100; ?>"  name="giftteaser[giftImageWidth]" class="form-control brSmallField" style="text-align:center;">
                        <span class="input-group-addon">X</span>
                        <input type="text" value="<?php if(!empty($data['giftteaser']['giftImageHeight'])) echo (int)$data['giftteaser']['giftImageHeight']; else echo 100; ?>"  name="giftteaser[giftImageHeight]" class="form-control brSmallField" style="text-align:center;">
                    </div>
                 </div>
            </td>
    </tr>
	    <tr class="customCssForm">
        	<td class="col-xs-3">
                <h5><strong><?php echo $custom_css; ?></strong></h5>
            </td>
      		<td class="col-xs-9">
            	<div class="col-xs-4"> 
		        	<textarea rows="10" class="form-control customCss" name="giftteaser[customCss]" placeholder="Place your custom CSS here..."><?php if(!empty($data['giftteaser']['customCss'])){ echo $data['giftteaser']['customCss']; } else { echo ".giftTeaserWidget {  }";} ?></textarea>
            	</div>
		    </td>
	    </tr>
	   	<tr>
        	<td class="col-xs-3">
                <h5><strong><?php echo $custom_design; ?></strong></h5>
            </td>
      		<td class="col-xs-9">
			    <div class="form-group col-xs-4">		
					 <select name="giftteaser[customDesign]" class="form-control">
						<option value="custom" <?php echo (!empty($data['giftteaser']['customDesign']) && $data['giftteaser']['customDesign'] == 'custom') ? 'selected=selected' : '' ?>><?php echo $text_custom;?></option>
						<option value="default"  <?php echo (empty($data['giftteaser']['customDesign']) || $data['giftteaser']['customDesign']== 'default') ? 'selected=selected' : '' ?>><?php echo $entry_default;?></option>
					 </select>
				</div>
			</td>
	    </tr>
       
	    <tr id="custom_settings" class="customCssForm hidden hiddeable">
		    <td class="col-xs-3">
               <h5><strong><?php echo $custom_colors; ?></strong></h5>
            </td>
      		<td class="col-xs-9">
            	<div class=" col-xs-5">
                    <div class="customDesignColors form-group">
                    
                        <div class="input-group" id="widget_font_color">     
                        	<span class="input-group-addon"><?php echo $text_text;?></span>
                        	<input name="giftteaser[FontColor]" class="form-control colorp" type="text" value="<?php echo (isset($data['giftteaser']['FontColor']) ? $data['giftteaser']['FontColor'] : '#FFF') ?> ">
                			<span class="input-group-addon"><i class="colorpicker_box_color"></i></span>
                        </div>
                    </div>
                    <div class="customDesignColors form-group">
                        <div class="input-group" id="widget_bg_color">
                            <span class="input-group-addon"><?php echo $text_background;?></span>
                            <input name="giftteaser[BackgroundColor]" class="form-control colorp" type="text" value="<?php  if(!empty($data['giftteaser']['BackgroundColor'])) { echo $data['giftteaser']['BackgroundColor']; } else { echo  '#A1E3F0'; }  ?>">
                        	<span class="input-group-addon"><i class="colorpicker_box_color"></i></span>
                        </div>
                    </div>
                    <div class="customDesignColors form-group">          
                        <div class="input-group" id="widget_border_color">
                            <span class="input-group-addon"><?php echo $text_border;?></span>
                            <input name="giftteaser[BorderColor]" class="form-control colorp" type="text" value="<?php  if(!empty($data['giftteaser']['BorderColor'])){ echo $data['giftteaser']['BorderColor']; } else { echo '#86CBD9';} ?>">
                        	<span class="input-group-addon"><i class="colorpicker_box_color"></i></span>
                        </div>
                    </div>
                    <div class="customDesignColors form-group">
                        <div class="input-group" id="widget_head_bg_color">            	
                            <span class="input-group-addon"><?php echo $heading_background;?></span>
                            <input name="giftteaser[headingBackground]" class="form-control colorp" type="text" value="<?php if(!empty($data['giftteaser']['headingBackground'])){ echo $data['giftteaser']['headingBackground']; } else { echo  '#FFFFFF'; } ?>">
                        	<span class="input-group-addon"><i class="colorpicker_box_color"></i></span>
                        </div>
                    </div>
		       	</div>
		    </td>
	    </tr>
          <tr>
            <td class="col-xs-3">
                <h5><strong><?php echo $show_free_gifts; ?></strong></h5>
                <span class="help"><i class="fa fa-info-circle"></i>&nbsp;<?php echo $show_free_gifts_help; ?></span>
            </td>
      		<td class="col-xs-9">
            	<div class="col-xs-4">
                     <select name="giftteaser[showFreeGift]" class="form-control">
                        <option value="yes" <?php echo (!empty($data['giftteaser']['showFreeGift']) && $data['giftteaser']['showFreeGift'] == 'yes') ? 'selected=selected' : '' ?>><?php echo $text_yes;?></option>
                        <option value="no"  <?php echo (empty($data['giftteaser']['showFreeGift']) || $data['giftteaser']['showFreeGift']== 'no') ? 'selected=selected' : '' ?>><?php echo $text_no;?></option>
                     </select>
                 </div>
			</td>
	    </tr>
        <tr class="freeGiftMessage hidden"> 
         <td class="col-xs-3">
                <h5><strong><?php echo $free_gifts_message_title; ?></strong></h5>
                <span class="help"><i class="fa fa-info-circle"></i>&nbsp;<?php echo $free_gifts_message_title_help; ?></span>
            </td>
      		<td class="col-xs-9">
            	<div class="col-xs-12"> 
					<?php foreach ($languages as $language) { ?>
                        <div class="input-group col-xs-6" style="padding-left:0px !important; margin:10px 0" >
                            <span class="input-group-addon"><a href="#language_<?php echo $language["code"]; ?>" ><img src="<?php echo $language['flag_url']; ?>" title="<?php echo $language['name']; ?>" /></a></span>
                            <input class="form-control" name="giftteaser[notification_freeGift_<?php echo $language["code"]; ?>]" value="<?php if(isset($data['giftteaser']['notification_freeGift_'.$language["code"]])) { echo $data['giftteaser']['notification_freeGift_'.$language["code"]]; } else { echo "Get this product for free!"; } ?>" >
                        </div>
                    <?php } ?>
               </div>
               
               <div class=" col-xs-5">
                    <div class="customDesignColors form-group">
                        <div class="input-group" id="note_font_color">     
                        	<span class="input-group-addon"><?php echo $text_text;?></span>
                        	<input name="giftteaser[NoteFontColor]" class="form-control colorp" type="text" value="<?php if(!empty($data['giftteaser']['NoteFontColor'])){ echo $data['giftteaser']['NoteFontColor']; } else { echo  '#FFFFFF'; } ?>">
                			<span class="input-group-addon"><i class="colorpicker_box_color"></i></span>
                        </div>
                    </div>
                    <div class="customDesignColors form-group">
                        <div class="input-group" id="note_bg_color">
                            <span class="input-group-addon"><?php echo $text_background;?></span>
                            <input name="giftteaser[NoteBackgroundColor]" class="form-control colorp" type="text" value="<?php  if(!empty($data['giftteaser']['NoteBackgroundColor'])) { echo $data['giftteaser']['NoteBackgroundColor']; } else { echo  '#A1E3F0'; }  ?>">
                        	<span class="input-group-addon"><i class="colorpicker_box_color"></i></span>
                        </div>
                    </div>
                    <div class="customDesignColors form-group">          
                        <div class="input-group" id="note_border_color">
                            <span class="input-group-addon"><?php echo $text_border;?></span>
                            <input name="giftteaser[NoteBorderColor]" class="form-control colorp" type="text" value="<?php  if(!empty($data['giftteaser']['NoteBorderColor'])){ echo $data['giftteaser']['NoteBorderColor']; } else { echo '#86CBD9';} ?>">
                        	<span class="input-group-addon"><i class="colorpicker_box_color"></i></span>
                        </div>
                    </div>
		       	</div>
                
            </td>
           
        </tr>
        
        
	</table>
    <div class="tabbable">
      <div class="tab-navigation">
        <ul class="nav nav-tabs notificationMessageTabs" style="margin-top:10px;">
          <?php $class="active"; foreach ($languages as $lang) :  ?>
               <li class="<?php echo $class; $class='';?>"><a href="#lang_<?php echo $lang["code"]; ?>" data-toggle="tab"><img src="<?php echo $lang['flag_url']; ?>" title="<?php echo $lang['name']; ?>" /></a></li>
          <?php endforeach; ?> 
        </ul>
      </div><!-- /.tab-navigation --> 
      <div class="tab-content">
        <?php $class="active"; foreach ($languages as $lang) : ?>
               <div class="tab-pane <?php echo $class; $class='';?> " id="lang_<?php echo $lang["code"]; ?>">
                  <div class="col-md-2">
                    <div class="panel panel-info"><div class="panel-heading"><i class="fa fa-info"></i>&nbsp;<?php echo $store_front_widget; ?></div>
                    	<div class="panel-body"><?php echo $widget_help; ?></div></div>
                  </div>
                  <div class="col-md-10">
                    <input type="text" placeholder="Head title" class="form-control messageSubject"  name="giftteaser[headtitle_<?php echo $lang["code"];?>]" value="<?php if(!empty($data['giftteaser']['headtitle_'.$lang["code"]])) { echo $data['giftteaser']['headtitle_'.$lang["code"]]; } else { echo $default_heading_title; }?>"/>
                    <textarea class="summernote" id="message_<?php echo $lang["code"]; ?>" name="giftteaser[notification_<?php echo $lang["code"]; ?>]"><?php if(isset($data['giftteaser']['notification_'.$lang["code"]])) { echo $data['giftteaser']['notification_'.$lang["code"]]; }?></textarea>
                  </div>
               </div>
          <?php endforeach; ?> 
      </div>
    </div>
</div>
<br />  

<script type="text/javascript"> 

if ($('select[name="giftteaser[customDesign]"]').val() == 'default') {
	$('.hiddeable').addClass('hidden');
} else {
	$('.hiddeable').removeClass('hidden');	
}

$(document).on('change', 'select[name="giftteaser[customDesign]"]', function(){
	if ($('select[name="giftteaser[customDesign]"]').val() == 'default') {
		$('.hiddeable').addClass('hidden');
	} else {
		$('.hiddeable').removeClass('hidden');	;	
	}
});

if ($('select[name="giftteaser[showFreeGift]"]').val() == 'no') {
	$('.freeGiftMessage').addClass('hidden');
} else {
	$('.freeGiftMessage').removeClass('hidden');	
}

$(document).on('change', 'select[name="giftteaser[showFreeGift]"]', function(){
	if ($('select[name="giftteaser[showFreeGift]"]').val() == 'no') {
		$('.freeGiftMessage').addClass('hidden');
	} else {
		$('.freeGiftMessage').removeClass('hidden');	;	
	}
});


var colorpicker_change = function(input, box, color) {
    if (input !== false) {
      $(input).val(color);
    }

    $(box).css('background-color', color);
  }

  $('#widget_font_color').ColorPicker({
    color: $('#widget_font_color').find('input').val(),
    onChange : function(hsb, hex, rgb) {
      colorpicker_change($('#widget_font_color').find('input'), $('#widget_font_color').find('.colorpicker_box_color'), '#' + hex);
    } 
  });

  $('#widget_bg_color').ColorPicker({
    color: $('#widget_bg_color').find('input').val(),
    onChange : function(hsb, hex, rgb) {
      colorpicker_change($('#widget_bg_color').find('input'), $('#widget_bg_color').find('.colorpicker_box_color'), '#' + hex);
    }
  });

  $('#widget_border_color').ColorPicker({
    color: $('#widget_border_color').find('input').val(),
    onChange : function(hsb, hex, rgb) {
      colorpicker_change($('#widget_border_color').find('input'), $('#widget_border_color').find('.colorpicker_box_color'), '#' + hex);
    }
  });
  
   $('#widget_head_bg_color').ColorPicker({
    color: $('#widget_head_bg_color').find('input').val(),
    onChange : function(hsb, hex, rgb) {
      colorpicker_change($('#widget_head_bg_color').find('input'), $('#widget_head_bg_color').find('.colorpicker_box_color'), '#' + hex);
    }
  });
  
 $('#note_font_color').ColorPicker({
    color: $('#note_font_color').find('input').val(),
    onChange : function(hsb, hex, rgb) {
      colorpicker_change($('#note_font_color').find('input'), $('#note_font_color').find('.colorpicker_box_color'), '#' + hex);
    } 
  });

  $('#note_bg_color').ColorPicker({
    color: $('#note_bg_color').find('input').val(),
    onChange : function(hsb, hex, rgb) {
      colorpicker_change($('#note_bg_color').find('input'), $('#note_bg_color').find('.colorpicker_box_color'), '#' + hex);
    }
  });

  $('#note_border_color').ColorPicker({
    color: $('#note_border_color').find('input').val(),
    onChange : function(hsb, hex, rgb) {
      colorpicker_change($('#note_border_color').find('input'), $('#note_border_color').find('.colorpicker_box_color'), '#' + hex);
    }
  });  
  
  
  
  <?php if (!empty($data['giftteaser']['FontColor'])) : ?>
    colorpicker_change($('#widget_font_color').find('input'), $('#widget_font_color').find('.colorpicker_box_color'), '<?php echo $data['giftteaser']['FontColor']; ?>');
  <?php endif; ?>

  <?php if (!empty($data['giftteaser']['BackgroundColor'])) : ?>
    colorpicker_change($('#widget_bg_color').find('input'), $('#widget_bg_color').find('.colorpicker_box_color'), '<?php echo $data['giftteaser']['BackgroundColor']; ?>');
  <?php endif; ?>
  
  <?php if (!empty($data['giftteaser']['BorderColor'])) : ?>
    colorpicker_change($('#widget_border_color').find('input'), $('#widget_border_color').find('.colorpicker_box_color'), '<?php echo $data['giftteaser']['BorderColor']; ?>');
  <?php endif; ?>
  
  <?php if (!empty($data['giftteaser']['headingBackground'])) : ?>
    colorpicker_change($('#widget_head_bg_color').find('input'), $('#widget_head_bg_color').find('.colorpicker_box_color'), '<?php echo $data['giftteaser']['headingBackground']; ?>');
  <?php endif; ?>
  
	<?php if (!empty($data['giftteaser']['NoteFontColor'])) : ?>
    colorpicker_change($('#note_font_color').find('input'), $('#note_font_color').find('.colorpicker_box_color'), '<?php echo $data['giftteaser']['NoteFontColor']; ?>');
  <?php endif; ?>

  <?php if (!empty($data['giftteaser']['NoteBackgroundColor'])) : ?>
    colorpicker_change($('#note_bg_color').find('input'), $('#note_bg_color').find('.colorpicker_box_color'), '<?php echo $data['giftteaser']['NoteBackgroundColor']; ?>');
  <?php endif; ?>
  
  <?php if (!empty($data['giftteaser']['NoteBorderColor'])) : ?>
    colorpicker_change($('#note_border_color').find('input'), $('#note_border_color').find('.colorpicker_box_color'), '<?php echo $data['giftteaser']['NoteBorderColor']; ?>');
  <?php endif; ?>

  $('#cp_color, #cp_bgcolor').find('input').change(function() {
    colorpicker_change(false, $(this).closest('.colorpicker-component').find('.colorpicker_box_color'), $(this).val());
  });

  $('input, textarea').focus(function() {
   	$('#cp_color, #cp_bgcolor').ColorPickerHide();
    $(this).trigger('click'); 
  })
  
</script>