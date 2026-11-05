<?php //microdatapro 10.1 ?>
<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="content">
	<script>
		$(document).ready(function(){
			$('#form-microdatapro input[type="checkbox"]').wrap('<label class="swl"></label>').after('<span></span>');
		});
	</script>
	<style>
		.swl input[type="checkbox"] {position: absolute;z-index: -1;opacity: 0;margin: 10px 0 0 20px;}
		.swl input[type="checkbox"] + span {position: relative;padding: 0 0 0 60px;cursor: pointer;}
		.swl input[type="checkbox"] + span:before {content: '';position: absolute;top: 1px;left: 0;width: 50px;height: 26px;border-radius: 13px;background: #CDD1DA;box-shadow: inset 0 2px 3px rgba(0,0,0,.2);transition: .2s;}
		.swl input[type="checkbox"] + span:after {content: '';position: absolute;top: 3px;left: 2px;width: 22px;height: 22px;border-radius: 10px;background: #FFF;box-shadow: 0 2px 5px rgba(0,0,0,.3);transition: .2s;}
		.swl input[type="checkbox"]:checked + span:before {background:#9FD468;}
		.swl input[type="checkbox"]:checked + span:after {left: 26px;}
		.swl input[type="checkbox"]:focus + span:before {box-shadow: inset 0 2px 3px rgba(0,0,0,.2), 0 0 0 3px rgba(255,255,0,.7);}
		#success_up{float:right;}
		#success_up i{font-size:34px;color:#00b32d;margin-top:-8px;animation:great 0.5s infinite;}
		.or:last-child{display:none;}
		#dia h3{margin-top:20px;font-size:19px;}
		#dia h3:hover{cursor:pointer;color:#14628c;}
		.mod_detail{margin-left: 11px;padding-left: 8px;border-left: 2px solid #777;}
		.file_item{margin-bottom:5px;margin-left:5px;}
		.file_item:hover{cursor:pointer;color:#14628c;}
		.active_item{color:#14628c;}
		.last_in_block{padding-bottom:13px;margin-bottom:14px;border-bottom:1px solid #eee;}
		.counter_files{position: absolute;margin: 9px 0 0 -19px;background: #fff;border-radius: 50%;display: inline-block;width: 20px;height: 20px;text-align: center;border: 1px solid #777;line-height: 18px;}
		.service_info{border:1px dashed #14628c;padding:10px;font-size:13px;line-height:20px;margin-top:25px;}
		.service_info h4{font-weight:600;}
		.mbn{margin-bottom:0;}
		#microdatapro_category_block .form-group{margin-left:0;margin-right:0;}
		.view_theme{cursor:pointer;}
		#view_file_load b{color:red;}
		.form-group{position:relative;}
		.field_counter {position: absolute;left: 0px;top: -1px;background: #ededed;padding: 2px 4px;display: block;color: #555;font-size: 12px;}
		.form-group:hover .field_counter {background: #5bc0de;color: #fff;}
		@keyframes great{0%{font-size:34px;margin-top: -8px;}50%{font-size:26px;margin-top: -4px;}100%{font-size:34px;margin-top: -8px;}}
	</style>
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-microdatapro" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <div class="container-fluid">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
				<?php if($success){ ?>
					<span id="success_up"><i class="fa fa-thumbs-up" aria-hidden="true"></i></span>
					<script>
						setTimeout(function(){
							$('#success_up').fadeOut('300');
						}, 2000);
					</script>
				<?php } ?>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-microdatapro" class="form-horizontal">
				<?php if(!$a){ ?>
					<div style="font-size:22px;text-align:center;"><?php echo $message; ?></div>
          <script>
					  $('#key_manual').on('keyup', function() {
					    $.ajax({
								url: 'index.php?route=<?php echo $mdp_path; ?>/addKey&<?php echo $token_name; ?>=<?php echo $token; ?>',
					      dataType: 'json',
					      data: $('#key_manual').serialize(),
					      method: 'POST',
					      success: function(json) {
					        if(json){
					          location.reload();
					        }
					      }
					    });
					  });
					</script>
				<?php }else{ ?>
					<div class="form-group">
						<div class="col-sm-4">
							<h3 style="display:inline-block;line-height:30px;vertical-align:top;margin-right:15px;"><?php echo $text_microdata_status; ?> MicrodataPro:</h3>
							<input onchange="$('#microdatapro_all').toggle();" type="checkbox" name="microdatapro_status" <?php if($microdatapro_status) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
						</div>
						<div class="col-sm-8">
							<div class="alert alert-warning" role="alert" style="margin-bottom:0px;margin-top:-15px;">
								<?php echo $text_support_info; ?>
							</div>
						</div>
          </div>
					<div id="microdatapro_all" <?php if(!$microdatapro_status) { ?>style="display:none;"<?php } ?>>

					<ul class="nav nav-tabs" role="tablist">
						<?php if(!$count_errors){ ?>
				    	<li><a href="#dia" aria-controls="home" role="tab" data-toggle="tab"><?php echo $text_diagnostic; ?></a></li>
						<?php }else{ ?>
							<li class="active"><a href="#dia" aria-controls="home" role="tab" data-toggle="tab"><?php echo $text_diagnostic_e; ?> (<span style="color:#ff0000;"><?php echo $count_errors; ?></span>)</a></li>
						<?php } ?>
				    <li <?php if(!$count_errors){ ?>class="active"<?php } ?>><a href="#company" aria-controls="home" role="tab" data-toggle="tab"><?php echo $text_company; ?></a></li>
				    <li><a href="#product" aria-controls="profile" role="tab" data-toggle="tab"><?php echo $text_product_page; ?></a></li>
				    <li><a href="#social" aria-controls="messages" role="tab" data-toggle="tab"><?php echo $text_social; ?></a></li>
				    <li><a href="#other" aria-controls="settings" role="tab" data-toggle="tab"><?php echo $text_other; ?></a></li>
				    <li><a href="#info" aria-controls="settings" role="tab" data-toggle="tab"><?php echo $text_information; ?></a></li>
				  </ul>
				  <div class="tab-content">
						<div role="tabpanel" class="tab-pane fade <?php if($count_errors){ ?>in active<?php } ?>" id="dia" style="padding-left:15px;">
							<p><h4 style="font-weight:700;"><?php echo $text_for_work; ?></h4></p>
							<div class="mod_status">
								<?php if(!$mod_errors){ ?>
									<h3 class="h3_more_info">1) <i style="color:#00b32d;" class="fa fa-thumbs-o-up" aria-hidden="true"></i> <?php echo $text_succ_mod; ?> <a href="#" class="mod_more_info" title="<?php echo $text_click_view; ?>"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h3>
								<?php }else{ ?>
									<h3 class="h3_more_info">1) <i style="color:#ff0000;" class="fa fa-thumbs-o-down" aria-hidden="true"></i> <?php echo $text_err_mod; ?> <span style="color:#ff0000;"><?php echo $mod_errors; ?></span>)</h3>
								<?php } ?>
								<div class="mod_detail" style="display:none;">
									<?php $counter_files = 1; foreach($mod_files as $file => $file_data){ ?>
										<div class="counter_files"><?php echo $counter_files; ?></div>
										<?php if($file_data['status'] == 1){ ?>
											<div class="file_item"><?php echo $text_file_original; ?> <span style="font-weight:600;"><?php echo $file; ?></span> <span style="color:#00b32d;"><?php echo $text_modok; ?></span> <a href="#" class="why_mod"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
												<div style="display:none; padding:10px;border:1px dashed #777;color:#111;">
													 <?php echo $text_in_file; ?> <b><?php echo $file; ?></b><br>
													 <?php echo $text_find_string; ?> <b>$data['header']</b><br>
													 <?php echo $text_code_in_file; ?> <b>system/storage/modification/<?php echo $file; ?></b>
												</div>
											</div>
										<?php }else{ ?>
											<div class="file_item" style="color:#ff0000;">
												<span><?php echo $text_opencart_file; ?> <?php echo $file; ?> <?php echo $text_none_mode; ?></span> <a href="#" class="why_mod"><?php echo $text_why_mod; ?></a>
												<div style="display:none; padding:10px;border:1px dashed #ff0000;color:#111;">
													 <?php echo $text_in_file_strong; ?> <b><?php echo $file; ?></b><br>
													 <p class="mbn"><?php echo $text_start_string; ?> <b>$data['header']</b></p>
													 <?php $text_support_text; ?>
												</div>
											</div>
										<?php } ?>

										<?php if($file_data['ocmod'] == 1){ ?>
											<div class="file_item last_in_block"><?php echo $text_module_in_file; ?> <span style="font-weight:600;"><?php echo "system/storage/modification/" . $file; ?></span> <span style="color:#00b32d;"><?php echo $text_installed; ?></span> <a href="#" class="why_mod"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
												<div style="display:none; padding:10px;border:1px dashed #777;color:#111;">
													 <?php echo $text_in_file; ?> <b>system/storage/modification/<?php echo $file; ?></b><br>
													 <?php echo $text_added_code_before; ?> <b>$data['header']</b><br>
														<blockquote>
														 <small>//microdatapro <?php echo $version; ?> start</small>
														 <?php echo $text_module_code; ?><br>
														 <small>//microdatapro <?php echo $version; ?> end</small>
														</blockquote>
												</div>
											</div>
										<?php }else{ ?>
											<div class="file_item last_in_block" style="color:#ff0000;">
												<span><?php echo $text_module_in_file; ?> <?php echo "system/storage/modification/" . $file; ?> <?php echo $text_not_installed; ?></span> <a href="#" class="why_mod"><?php echo $text_what_to_do; ?></a>
												<div style="display:none; padding:10px;border:1px dashed #ff0000;color:#111;">
													 <?php echo $text_if_in_orig; ?> <b><?php echo $file; ?></b> <?php echo $text_all_good; ?>
												</div>
											</div>
										<?php } ?>

									<?php $counter_files++; } ?>
									<?php echo $text_for_good_work; ?>
								</div>
							</div>

							<?php if(!$found_old){ ?>
								<h3 class="h3_more_info" id="old_h3_title">2) <i style="color:#00b32d;" class="fa fa-thumbs-o-up" aria-hidden="true"></i> <?php echo $text_old_microdata_not_find; ?> <a href="#" class="mod_more_info" title="<?php echo $text_click_view; ?>"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h3>
								<div class="mod_detail" style="display:none;">
									<?php echo $text_old_info; ?>
								</div>
							<?php }else{ ?>
								<h3 class="h3_more_info" id="old_h3_title">2) <i style="color:#ff0000;" class="fa fa-thumbs-o-down" aria-hidden="true"></i> <?php echo $text_old_microdata_find; ?> (<span style="color:#ff0000;"><?php echo $found_old_count; ?></span>) <?php echo $text_recoment_clear; ?> <a href="#" class="mod_more_info" title="<?php echo $text_click_view; ?>"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h3>
								<div class="mod_detail" style="display:none;">
									<div class="alert alert-danger" id="old_microdata_block">
									  <strong><?php echo $text_old_microdata; ?></strong>
										<ul style="margin:10px 0;">
											<?php foreach($found_old as $file_full => $file){ ?>
												<li><span class="view_theme" data-toggle="modal" data-target="#view_file" data-file="<?php echo $file; ?>" data-file_full="<?php echo $file_full; ?>"><?php echo $file; ?> <i class="fa fa-eye" aria-hidden="true"></i></span></li>
											<?php } ?>
										</ul>
										<button id="clear_old" data-loading-text="<?php echo $text_clear; ?> <i class='fa fa-spinner fa-spin'></i>" type="button" class="btn btn-primary btn-sm"><?php echo $text_clear; ?></button>
									</div>
									<hr>
									<?php echo $text_cleared_info; ?>
								</div>
							<?php } ?>

							<?php if($found_mod){ ?>
							<h3 class="h3_more_info">3) <i style="color:#ff0000;" class="fa fa-thumbs-o-down" aria-hidden="true"></i> <?php echo $text_other_modules_find; ?> <a href="#" class="mod_more_info" title="<?php echo $text_click_view; ?>"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h3>
							<div class="mod_detail" style="display:none;">

								<div class="alert alert-danger">
									<strong><?php echo $text_old_microdata; ?> <?php echo $text_mod_cache_mod; ?></strong>
									<ul style="margin:10px 0;">
										<?php foreach($found_mod as $file_full => $file){ ?>
											<li><span class="view_theme" data-toggle="modal" data-target="#view_file" data-file="<?php echo $file; ?>" data-file_full="<?php echo $file_full; ?>"><?php echo $file; ?> <i class="fa fa-eye" aria-hidden="true"></i></span></li>
										<?php } ?>
									</ul>
								</div>

								<?php if($found_mod_admin){ ?>
									<div class="alert alert-danger">
										<strong><?php echo $text_mod_admin; ?></strong>
										<ul style="margin:10px 0;">
											<?php foreach($found_mod_admin as $mod_id => $file_name){ ?>
												<li><span class="view_theme" data-toggle="modal" data-target="#view_file" data-file="<?php echo $file_name; ?>" data-file_full="DB:<?php echo $mod_id; ?>"><?php echo $file_name; ?> <i class="fa fa-eye" aria-hidden="true"></i></span></li>
											<?php } ?>
										</ul>
									</div>
								<?php } ?>

								<?php if($found_mod_system){ ?>
									<div class="alert alert-danger">
										<strong><?php echo $text_mod_system; ?></strong>
										<ul style="margin:10px 0;">
											<?php foreach($found_mod_system as $file_full => $file_name){ ?>
												<li><span class="view_theme" data-toggle="modal" data-target="#view_file" data-file="<?php echo $file_name; ?>" data-file_full="<?php echo $file_full; ?>"><?php echo $file_name; ?> <i class="fa fa-eye" aria-hidden="true"></i></span></li>
											<?php } ?>
										</ul>
									</div>
								<?php } ?>
								<hr>
								<?php echo $text_other_modules_info; ?>
							</div>
							<?php }else{ ?>
								<h3 class="h3_more_info">3) <i style="color:#00b32d;" class="fa fa-thumbs-o-up" aria-hidden="true"></i> <?php echo $text_other_modules_not_find; ?> <a href="#" class="mod_more_info" title="<?php echo $text_click_view; ?>"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h3>
								<div class="mod_detail" style="display:none;">
									<?php echo $text_not_other_modules_info; ?>
								</div>
							<?php } ?>

							<h3 class="h3_more_info">4) <i style="color:#14628c;" class="fa fa-check-square-o" aria-hidden="true"></i> <?php echo $text_google_check; ?></h3>
							<div class="mod_detail" style="display:none;">
								<?php echo $text_link_check; ?>
								<ul>
									<li><a href="https://search.google.com/test/rich-results?url=<?php echo $link_main; ?>" target="_blank" title="<?php echo $text_in_new_tab; ?>"><?php echo $text_main_page; ?> <i class="fa fa-external-link" aria-hidden="true"></i></a></li>
									<?php if($link_category){ ?>
										<li><a href="https://search.google.com/test/rich-results?url=<?php echo $link_category; ?>" target="_blank" title="<?php echo $text_in_new_tab; ?>"><?php echo $text_category_page; ?> <i class="fa fa-external-link" aria-hidden="true"></i></a></li>
									<?php }else{ ?>
										<?php echo $text_empty_category; ?>
									<?php } ?>
									<?php if($link_product){ ?>
										<li><a href="https://search.google.com/test/rich-results?url=<?php echo $link_product; ?>" target="_blank" title="<?php echo $text_in_new_tab; ?>"><?php echo $text_product_page; ?> <i class="fa fa-external-link" aria-hidden="true"></i></a></li>
									<?php }else{ ?>
										<?php echo $text_empty_product; ?>
									<?php } ?>
									<?php if($link_manufacturer){ ?>
										<li><a href="https://search.google.com/test/rich-results?url=<?php echo $link_manufacturer; ?>" target="_blank" title="<?php echo $text_in_new_tab; ?>"><?php echo $text_manufacturer_page; ?> <i class="fa fa-external-link" aria-hidden="true"></i></a></li>
									<?php }else{ ?>
										<?php echo $text_empty_brand; ?>
									<?php } ?>
									<?php if($link_information){ ?>
										<li><a href="https://search.google.com/test/rich-results?url=<?php echo $link_information; ?>" target="_blank" title="<?php echo $text_in_new_tab; ?>"><?php echo $text_info_page; ?> <i class="fa fa-external-link" aria-hidden="true"></i></a></li>
									<?php }else{ ?>
										<?php echo $text_empty_info; ?>
									<?php } ?>
								</ul>
								<?php echo $text_google_validator; ?>
							</div>

							<div class="service_info-off">
								<div class="alert alert-warning" role="alert">
									<?php echo $text_warning_info; ?>
								</div>
							</div>
						</div>

						<!--modal export system-->
							<div class="modal fade" style="z-index:1052;" id="view_file" tabindex="-1" role="dialog" aria-labelledby="view_fileTitle" aria-hidden="true">
								<div class="modal-dialog modal-lg" role="document" style="width:90%;">
									<div class="modal-content">
										<div class="modal-body" style="padding-top:20px;">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close" id="view_file_close" onclick="location.reload();">
												<span aria-hidden="true">&times;</span>
											</button>
											<h3 id="title_view_file"></h3>
											<pre style="font-size:12px;line-height:14px;margin-top:24px;max-height:70%;overflow:scroll;" id="view_file_load">
												<i style="margin-left:5px;color:red;" class="fa fa-spinner fa-spin tmp_spin" aria-hidden="true"></i>
											</pre>
											<input type="hidden" class="form-control replace_in_file_file" name="replace_in_file_file">
											<div class="row">
												<div class="col-sm-5">
													<input type="text" class="form-control replace_in_file_from" name="replace_in_file_from" placeholder="<?php echo $text_mod_from; ?>">
												</div>
												<div class="col-sm-5">
													<input type="text" class="form-control replace_in_file_to" name="replace_in_file_to" placeholder="<?php echo $text_mod_to; ?>">
												</div>
												<div class="col-sm-2">
													<button style="width:100%;" type="button" class="replace_in_file btn btn-success"><?php echo $text_mod_go; ?></button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<!--/modal export system-->

						<script>
						$(document).on('click', '.view_theme', function(){
									file = $(this).data('file');
									file_full = $(this).data('file_full');
									$('.replace_in_file_file').val(file_full);
									$('#title_view_file').html(file);
									$('#view_file_load').load('index.php?route=<?php echo $mdp_path; ?>/getViewFile&<?php echo $token_name; ?>=<?php echo $token; ?>&file=' + file_full);
								});

								$(document).on('click', '.replace_in_file', function(){
									from = $('.replace_in_file_from').val();
									to = $('.replace_in_file_to').val();
									file_full = $('.replace_in_file_file').val();

									$.ajax({
										url: 'index.php?route=<?php echo $mdp_path; ?>/replaceInFile&<?php echo $token_name; ?>=<?php echo $token; ?>',
										type: 'post',
										data: {'from':from,'to':to,'file_full':file_full},
										dataType: 'json',
										success: function(succ){
											$('#view_file_load').load('index.php?route=<?php echo $mdp_path; ?>/getViewFile&<?php echo $token_name; ?>=<?php echo $token; ?>&file=' + file_full);
										}
									});
								});
								$('#clear_old').click(function(){
									var $this = $(this);
									$this.button('loading');
									$.ajax({
										url: 'index.php?route=<?php echo $mdp_path; ?>/clearOldMicrodata&<?php echo $token_name; ?>=<?php echo $token; ?>',
										type: 'post',
										dataType: 'json',
										success: function(succ){
											$.post('<?php echo $refresh; ?>').done(function(data){
												location.reload();
											});
										}
									});
								});
								$('.refresh').on('click', function(e){
									e.preventDefault();
									$(this).after('<i style="margin-left:5px;color:red;" class="fa fa-spinner fa-spin tmp_spin" aria-hidden="true"></i>');
									$.post('<?php echo $refresh; ?>').done(function(data){
										location.reload();
									});
								});
								$('.h3_more_info').on('click', function(e){
									e.preventDefault();
									$(this).toggleClass('active_item');
									$(this).next('.mod_detail').slideToggle('200');
								});
								$('.file_item').on('click', function(e){
									e.preventDefault();
									$(this).toggleClass('active_item');
									$(this).find('div').slideToggle('200');
								});
						</script>


				    <div role="tabpanel" class="tab-pane fade <?php if(!$count_errors){ ?>in active<?php } ?>" id="company">
							<div class="form-group" style="border-top:1px solid #ededed;">
								<span class="field_counter">1.1</span>
		            <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/1-1" target="_blank" ><?php echo $text_microdata_status; ?></a></label>
		            <div class="col-sm-10">
									<input onchange="$('#microdatapro_store').toggle();" type="checkbox" name="microdatapro_company" <?php if($microdatapro_company) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
		            </div>
		          </div>
							<div id="microdatapro_store" <?php if(!$microdatapro_company) { ?>style="display:none;"<?php } ?>>
							<div class="alert alert-warning" role="alert">
							  <?php echo $text_page_info; ?>
							</div>
							<div class="form-group" style="border-top:1px solid #ededed;">
								<span class="field_counter">1.2</span>
		            <label class="col-sm-2 control-label"><a data-toggle="tooltip" title="<?php echo $text_store_type_h; ?>" target="_blank" href="https://microdata.pro/set/1-2"><?php echo $text_store_type; ?></a></label>
		            <div class="col-sm-10">
									<select name="microdatapro_store_type" class="form-control">
										<option value="" <?php if($microdatapro_store_type === ''){ ?>selected="selected"<?php } ?>><?php echo $text_select; ?></option>
										<?php for($st = 0; $st < 30; $st++){ ?>
											<option value="<?php echo $st; ?>" <?php if($microdatapro_store_type == $st){ ?>selected="selected"<?php } ?>><?php echo ${'text_storetype_' . $st}; ?></option>
										<?php } ?>
									</select>
		            </div>
		          </div>
							<div class="form-group" style="padding-bottom:0px;">
								<span class="field_counter">1.3</span>
								<label class="col-sm-2 control-label"><a target="_blank" href="https://microdata.pro/set/1-3"><?php echo $text_name; ?></a></label>
								<div class="col-sm-10">
								 <div class="row">
									<?php foreach($languages as $language){ ?>
										<div class="col-sm-12">
											<div class="input-group">
												<span class="input-group-addon"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /></span>
												<input type="text" name="microdatapro_name[<?php echo $language['language_id']; ?>]" value="<?php echo isset($microdatapro_name[$language['language_id']])?$microdatapro_name[$language['language_id']]:''; ?>" placeholder="<?php echo $language['name']; ?>" class="form-control" />
											</div>
										</div>
									<?php } ?>
								 </div>
								</div>
							</div>

							<div class="form-group">
								<span class="field_counter">1.4</span>
		            <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/1-4" target="_blank"><?php echo $text_email; ?></a></label>
		            <div class="col-sm-10">
									<input name="microdatapro_email" placeholder="<?php echo $email; ?>" value="<?php echo $microdatapro_email; ?>" class="form-control">
		            </div>
		          </div>
							<div class="form-group">
								<span class="field_counter">1.5</span>
		            <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/1-5" target="_blank"><?php echo $text_oh; ?></a></label>
		            <div class="col-sm-10" style="padding-left:0;padding-right:0;">
									<div class="col-sm-1" style="width:14.28%;"><div><?php echo $text_monday; ?></div><div><input class="form-control" type="text" name="microdatapro_oh_1" value="<?php echo $microdatapro_oh_1; ?>" placeholder="10:00-20:00"></div></div>
									<div class="col-sm-1" style="width:14.28%;"><div><?php echo $text_tuesday; ?></div><div><input class="form-control" type="text" name="microdatapro_oh_2" value="<?php echo $microdatapro_oh_2; ?>" placeholder="10:00-20:00"></div></div>
									<div class="col-sm-1" style="width:14.28%;"><div><?php echo $text_wednesday; ?></div><div><input class="form-control" type="text" name="microdatapro_oh_3" value="<?php echo $microdatapro_oh_3; ?>" placeholder="10:00-20:00"></div></div>
									<div class="col-sm-1" style="width:14.28%;"><div><?php echo $text_thursday; ?></div><div><input class="form-control" type="text" name="microdatapro_oh_4" value="<?php echo $microdatapro_oh_4; ?>" placeholder="10:00-20:00"></div></div>
									<div class="col-sm-1" style="width:14.28%;"><div><?php echo $text_friday; ?></div><div><input class="form-control" type="text" name="microdatapro_oh_5" value="<?php echo $microdatapro_oh_5; ?>" placeholder="10:00-20:00"></div></div>
									<div class="col-sm-1" style="width:14.28%;"><div><?php echo $text_saturday; ?></div><div><input class="form-control" type="text" name="microdatapro_oh_6" value="<?php echo $microdatapro_oh_6; ?>" placeholder="10:00-18:00"></div></div>
									<div class="col-sm-1" style="width:14.28%;"><div><?php echo $text_sunday; ?></div><div><input class="form-control" type="text" name="microdatapro_oh_7" value="<?php echo $microdatapro_oh_7; ?>" placeholder="10:00-18:00"></div></div>
								</div>
		          </div>
							<div class="form-group">
								<span class="field_counter">1.6</span>
		            <label class="col-sm-2 control-label"><a data-toggle="tooltip" title="<?php echo $text_entry_telephone_h; ?>" href="https://microdata.pro/set/1-6" target="_blank"><?php echo $text_entry_telephone; ?></a></label>
		            <div class="col-sm-10">
									<textarea name="microdatapro_phones" placeholder="+12-345-678-90-00,+34-555-678-11-11" class="form-control"><?php echo $microdatapro_phones; ?></textarea>
									<?php if($stores){ ?>
										<?php foreach($stores as $store){ ?>
											<span class="placeholder_name"><?php echo $store['name']; ?></span>
											<textarea name="microdatapro_phones<?php echo $store['store_id']; ?>" placeholder="+12-345-678-90-00,+34-555-678-11-11" class="form-control"><?php echo ${'microdatapro_phones' . $store['store_id']}; ?></textarea>
										<?php } ?>
									<?php } ?>
		            </div>
		          </div>
							<div class="form-group">
								<span class="field_counter">1.7</span>
		            <label class="col-sm-2 control-label"><a data-toggle="tooltip" title="<?php echo $text_entry_group_h; ?>" href="https://microdata.pro/set/1-7" target="_blank"><?php echo $text_entry_group; ?></a></label>
		            <div class="col-sm-10">
									<textarea name="microdatapro_groups" placeholder="https://facebook.com/group, https://twitter.com/group" class="form-control"><?php echo $microdatapro_groups; ?></textarea>
									<?php if($stores){ ?>
										<?php foreach($stores as $store){ ?>
											<span class="placeholder_name"><?php echo $store['name']; ?></span>
											<textarea name="microdatapro_groups<?php echo $store['store_id']; ?>" placeholder="https://facebook.com/group, https://twitter.com/group" class="form-control"><?php echo ${'microdatapro_groups' . $store['store_id']}; ?></textarea>
										<?php } ?>
									<?php } ?>
		            </div>
		          </div>
							<div class="form-group">
								<span class="field_counter">1.8</span>
						    <label class="col-sm-2 control-label"><a data-toggle="tooltip" title="<?php echo $text_entry_address_h; ?>" href="https://microdata.pro/set/1-8" target="_blank"><?php echo $text_entry_address; ?></a></label>
						    <div class="col-sm-10">
									<div class="row">
										<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_coordinates; ?>:<br><input placeholder="<?php echo $text_locations_coordinates; ?>" value="<?php echo $microdatapro_locations_coordinates; ?>" name="microdatapro_locations_coordinates" type="text" class="form-control"></div>
										<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_country; ?>:<br><input placeholder="<?php echo $text_locations_country; ?>" value="<?php echo $microdatapro_locations_country; ?>" name="microdatapro_locations_country" type="text" class="form-control"></div>
										<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_city; ?>:<br><input placeholder="<?php echo $text_locations_city; ?>" value="<?php echo $microdatapro_locations_city; ?>" name="microdatapro_locations_city" type="text" class="form-control"></div>
										<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_address; ?>:<br><input placeholder="<?php echo $text_locations_address; ?>" value="<?php echo $microdatapro_locations_address; ?>" name="microdatapro_locations_address" type="text" class="form-control"></div>
										<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_code; ?>:<br><input placeholder="<?php echo $text_locations_code; ?>" value="<?php echo $microdatapro_locations_code; ?>" name="microdatapro_locations_code" type="text" class="form-control"></div>
									</div>
									<?php if($stores){ ?>
										<?php foreach($stores as $store){ ?>
											<div>
												<br><br>
												<hr>
												<span style="background:#ededed;padding:4px 10px;margin-top:-44px;position:absolute;border:1px solid #ededed;" class="placeholder_name"><?php echo $store['name']; ?></span>
												<div class="row">
													<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_coordinates; ?>:<br><input placeholder="<?php echo $text_locations_coordinates; ?>" value="<?php echo ${'microdatapro_locations_coordinates' . $store['store_id']}; ?>" name="microdatapro_locations_coordinates<?php echo $store['store_id']; ?>" type="text" class="form-control"></div>
													<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_country; ?>:<br><input placeholder="<?php echo $text_locations_country; ?>" value="<?php echo ${'microdatapro_locations_country' . $store['store_id']}; ?>" name="microdatapro_locations_country<?php echo $store['store_id']; ?>" type="text" class="form-control"></div>
													<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_city; ?>:<br><input placeholder="<?php echo $text_locations_city; ?>" value="<?php echo ${'microdatapro_locations_city' . $store['store_id']}; ?>" name="microdatapro_locations_city<?php echo $store['store_id']; ?>" type="text" class="form-control"></div>
													<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_address; ?>:<br><input placeholder="<?php echo $text_locations_address; ?>" value="<?php echo ${'microdatapro_locations_address' . $store['store_id']}; ?>" name="microdatapro_locations_address<?php echo $store['store_id']; ?>" type="text" class="form-control"></div>
													<div class="col-sm-2" style="width:20%;"><?php echo $text_locations_code; ?>:<br><input placeholder="<?php echo $text_locations_code; ?>" value="<?php echo ${'microdatapro_locations_code' . $store['store_id']}; ?>" name="microdatapro_locations_code<?php echo $store['store_id']; ?>" type="text" class="form-control"></div>
												</div>
											</div>
										<?php } ?>
									<?php } ?>
						    </div>
						  </div>
							<div class="form-group">
								<span class="field_counter">1.9</span>
						    <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/1-9" target="_blank" data-toggle="tooltip" title="<?php echo $text_entry_map_h; ?>"><?php echo $text_entry_map; ?></a></label>
						    <div class="col-sm-10">
									<textarea name="microdatapro_map" placeholder="https://www.google.com/maps/@48.605665,11.6267341,5z?hl=ru" class="form-control"><?php echo $microdatapro_map; ?></textarea>
									<?php if($stores){ ?>
										<?php foreach($stores as $store){ ?>
											<span class="placeholder_name"><?php echo $store['name']; ?></span>
											<textarea name="microdatapro_map<?php echo $store['store_id']; ?>" placeholder="https://www.google.com/maps/@48.605665,11.6267341,5z?hl=ru" class="form-control"><?php echo ${'microdatapro_map' . $store['store_id']}; ?></textarea>
										<?php } ?>
									<?php } ?>
						    </div>
						  </div>

							<div class="form-group">
								<span class="field_counter">1.10</span>
						    <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/1-10" target="_blank"><?php echo $text_logo_size; ?></a></label>
						    <div class="col-sm-1">
									<input type="text" name="microdatapro_logosize_1" value="<?php echo $microdatapro_logosize_1; ?>" placeholder="144" class="form-control text-center">
						    </div>
								<div class="col-sm-1">
									<span style="font-size:20px;position:absolute;margin-left:-20px;margin-top:4px;">×</span>
									<input type="text" name="microdatapro_logosize_2" value="<?php echo $microdatapro_logosize_2; ?>" placeholder="144" class="form-control text-center">
								</div>
						  </div>
							<div class="form-group">
								<span class="field_counter">1.11</span>
                <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/1-11" target="_blank"><?php echo $text_logo_alt; ?></a></label>
                <div class="col-sm-10"><a href="" id="thumb-image" data-toggle="image" class="img-thumbnail"><img src="<?php echo $logoalt_thumb; ?>" alt="" title="" data-placeholder="<?php echo $placeholder; ?>" /></a>
                  <input type="hidden" name="microdatapro_logoalt" value="<?php echo $microdatapro_logoalt; ?>" id="input-image" />
                </div>
              </div>
						 </div>
						</div>

				    <div role="tabpanel" class="tab-pane fade" id="product">
							<div class="form-group" style="border-top:1px solid #ededed;">
								<span class="field_counter">2.1</span>
		            <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-1" target="_blank"><?php echo $text_microdata_status; ?></a></label>
		            <div class="col-sm-10">
									<input onchange="$('.microdatapro_product_block').toggle();" type="checkbox" name="microdatapro_product" <?php if($microdatapro_product) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
		            </div>
		          </div>

							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.2</span>
							  <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-2" target="_blank" ><?php echo $text_breadcrumb; ?></a></label>
							  <div class="col-sm-10">
							    <input type="checkbox" name="microdatapro_product_breadcrumb" <?php if($microdatapro_product_breadcrumb) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
							  </div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.3</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-3" target="_blank" ><?php echo $text_hide_price; ?></a></label>
							  <div class="col-sm-10">
							    <input type="checkbox" name="microdatapro_hide_price" <?php if($microdatapro_hide_price) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
							  </div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.4</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-4" target="_blank" data-toggle="tooltip" title="<?php echo $text_other_data_h; ?>">sku</a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_sku" <?php if($microdatapro_sku) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.5</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-5" target="_blank" data-toggle="tooltip" title="<?php echo $text_other_data_h; ?>">upc</a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_upc" <?php if($microdatapro_upc) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.6</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-6" target="_blank" data-toggle="tooltip" title="<?php echo $text_other_data_h; ?>">ean</a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_ean" <?php if($microdatapro_ean) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.7</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-7" target="_blank" data-toggle="tooltip" title="<?php echo $text_other_data_h; ?>">mpn</a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_mpn" <?php if($microdatapro_mpn) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.8</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-8" target="_blank" data-toggle="tooltip" title="<?php echo $text_other_data_h; ?>">isbn</a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_isbn" <?php if($microdatapro_isbn) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.9</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-9" target="_blank"><?php echo $text_reviews; ?></a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_product_reviews" <?php if($microdatapro_product_reviews) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.10</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-10" target="_blank"><?php echo $text_related; ?></a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_product_related" <?php if($microdatapro_product_related) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.11</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-11" target="_blank"><?php echo $text_attribute; ?></a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_product_attribute" <?php if($microdatapro_product_attribute) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.12</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-12" target="_blank"><?php echo $text_gallery; ?></a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_product_gallery" <?php if($microdatapro_product_gallery) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.13</span>
								<label class="col-sm-2 control-label" style="padding-top:0px;"><a href="https://microdata.pro/set/2-13" target="_blank"><?php echo $text_url_contact; ?></a></label>
								<div class="col-sm-10">
									<select name="microdatapro_url_contact" class="form-control">
										<?php foreach($url_informations as $information_id => $information_name){ ?>
												<option value="<?php echo $information_id; ?>"<?php if ($information_id == $microdatapro_url_contact){ ?> selected="selected"<?php } ?>><?php echo $information_name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.14</span>
								<label class="col-sm-2 control-label" style="padding-top:0px;"><a href="https://microdata.pro/set/2-14" target="_blank"><?php echo $text_url_privacy; ?></a></label>
								<div class="col-sm-10">
									<select name="microdatapro_url_privacy" class="form-control">
										<?php foreach($url_informations as $information_id => $information_name){ ?>
												<option value="<?php echo $information_id; ?>"<?php if ($information_id == $microdatapro_url_privacy){ ?> selected="selected"<?php } ?>><?php echo $information_name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.15</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-15" target="_blank" data-toggle="tooltip" title="<?php echo $text_in_stock_h; ?>"><?php echo $text_in_stock; ?></a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_product_in_stock" <?php if($microdatapro_product_in_stock) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<?php foreach($stock_statuses as $stock_status){ ?>
								<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
									<span class="field_counter">2.16</span>
									<label class="col-sm-2 control-label" style="padding-top:0px;"><div style="font-weight:400;"><a href="https://microdata.pro/set/2-16" target="_blank"><?php echo $text_status_from_status; ?></a></div><?php echo $stock_status['name']; ?></label>
									<div class="col-sm-10">
										<select name="microdatapro_stock_statuses[<?php echo $stock_status['stock_status_id']; ?>]" class="form-control">
											<option value="0">---</option>
											<?php foreach($schema_stock_statuses as $schema_key => $schema_item){ ?>
													<option value="<?php echo $schema_key; ?>"<?php if ($stock_status['active'] == $schema_key){ ?> selected="selected"<?php } ?>><?php echo $schema_item; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							<?php } ?>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.17</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-17" target="_blank"><?php echo $text_shipping_cost; ?></a></label>
								<div class="col-sm-10">
									<div class="input-group">
										<input name="microdatapro_shipping_cost" placeholder="50" value="<?php echo $microdatapro_shipping_cost; ?>" class="form-control">
										<span class="input-group-addon"><?php echo $text_shipping_cost_default; ?></span>
									</div>
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.18</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-18" target="_blank"><?php echo $text_shipping_country; ?></a></label>
								<div class="col-sm-10">
									<div class="input-group">
										<input name="microdatapro_shipping_country" placeholder="US" value="<?php echo $microdatapro_shipping_country; ?>" class="form-control">
										<span class="input-group-addon"><?php echo $text_shipping_country_default; ?></span>
									</div>
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.19</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-19" target="_blank"><?php echo $text_shipping_time1_min; ?></a></label>
								<div class="col-sm-10">
									<div class="input-group">
										<input name="microdatapro_shipping_time1_min" placeholder="0" value="<?php echo $microdatapro_shipping_time1_min; ?>" class="form-control">
										<span class="input-group-addon"><?php echo $text_shipping_time1_min_default; ?></span>
									</div>
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.20</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-20" target="_blank"><?php echo $text_shipping_time1_max; ?></a></label>
								<div class="col-sm-10">
									<div class="input-group">
										<input name="microdatapro_shipping_time1_max" placeholder="1" value="<?php echo $microdatapro_shipping_time1_max; ?>" class="form-control">
										<span class="input-group-addon"><?php echo $text_shipping_time1_max_default; ?></span>
									</div>
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.21</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-21" target="_blank"><?php echo $text_shipping_time2_min; ?></a></label>
							  <div class="col-sm-10">
							    <div class="input-group">
							      <input name="microdatapro_shipping_time2_min" placeholder="1" value="<?php echo $microdatapro_shipping_time2_min; ?>" class="form-control">
							      <span class="input-group-addon"><?php echo $text_shipping_time2_min_default; ?></span>
							    </div>
							  </div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.22</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-22" target="_blank"><?php echo $text_shipping_time2_max; ?></a></label>
							  <div class="col-sm-10">
							    <div class="input-group">
							      <input name="microdatapro_shipping_time2_max" placeholder="3" value="<?php echo $microdatapro_shipping_time2_max; ?>" class="form-control">
							      <span class="input-group-addon"><?php echo $text_shipping_time2_max_default; ?></span>
							    </div>
							  </div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.23</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-23" target="_blank"><?php echo $text_return_days; ?></a></label>
							  <div class="col-sm-10">
							    <div class="input-group">
							      <input name="microdatapro_return_days" placeholder="14" value="<?php echo $microdatapro_return_days; ?>" class="form-control">
							      <span class="input-group-addon"><?php echo $text_return_days_default; ?></span>
							    </div>
							  </div>
							</div>

							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.24</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-24" target="_blank"><?php echo $text_attr_color; ?></a></label>
								<div class="col-sm-10">
									<select name="microdatapro_attr_color" class="form-control">
										<option <?php if($microdatapro_attr_color == "") { ?>selected="selected"<?php } ?> value=""><?php echo $text_select; ?></option>
										<?php foreach($attributes as $attribute){ ?>
											<option <?php if($microdatapro_attr_color == $attribute['attribute_id']) { ?>selected="selected"<?php } ?> value="<?php echo $attribute['attribute_id']; ?>"><?php echo $attribute['attribute_group'] . '=>' . $attribute['name']; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.25</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-25" target="_blank"><?php echo $text_attr_material; ?></a></label>
								<div class="col-sm-10">
									<select name="microdatapro_attr_material" class="form-control">
										<option <?php if($microdatapro_attr_material == "") { ?>selected="selected"<?php } ?> value=""><?php echo $text_select; ?></option>
										<?php foreach($attributes as $attribute){ ?>
											<option <?php if($microdatapro_attr_material == $attribute['attribute_id']) { ?>selected="selected"<?php } ?> value="<?php echo $attribute['attribute_id']; ?>"><?php echo $attribute['attribute_group'] . '=>' . $attribute['name']; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.26</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-26" target="_blank"><?php echo $text_attr_size; ?></a></label>
								<div class="col-sm-10">
									<select name="microdatapro_attr_size" class="form-control">
										<option <?php if($microdatapro_attr_size == "") { ?>selected="selected"<?php } ?> value=""><?php echo $text_select; ?></option>
										<?php foreach($attributes as $attribute){ ?>
											<option <?php if($microdatapro_attr_size == $attribute['attribute_id']) { ?>selected="selected"<?php } ?> value="<?php echo $attribute['attribute_id']; ?>"><?php echo $attribute['attribute_group'] . '=>' . $attribute['name']; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group microdatapro_product_block" <?php if(!$microdatapro_product) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">2.27</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/2-27" data-toggle="tooltip" title="<?php echo $text_target_gender_desc; ?>" target="_blank"><?php echo $text_target_gender; ?></a></label>
								<div class="col-sm-10">
									<select name="microdatapro_target_gender" class="form-control">
										<option <?php if($microdatapro_target_gender == "") { ?>selected="selected"<?php } ?> value=""><?php echo $text_select; ?></option>
										<option <?php if($microdatapro_target_gender == "female") { ?>selected="selected"<?php } ?> value="female"><?php echo $text_target_gender_female; ?></option>
										<option <?php if($microdatapro_target_gender == "male") { ?>selected="selected"<?php } ?> value="male"><?php echo $text_target_gender_male; ?></option>
										<option <?php if($microdatapro_target_gender == "unisex") { ?>selected="selected"<?php } ?> value="unisex"><?php echo $text_target_gender_unisex; ?></option>
									</select>
								</div>
							</div>
						</div>

				    <div role="tabpanel" class="tab-pane fade" id="social">
							<div class="form-group" style="border-top:1px solid #ededed;">
								<span class="field_counter">3.1</span>
								<label class="col-sm-2 control-label"><a data-toggle="tooltip" title="<?php echo $text_opengraph_h; ?>" href="https://microdata.pro/set/3-1" target="_blank"><?php echo $text_opengraph; ?></a></label>
								<div class="col-sm-10">
									<input onchange="$('.microdatapro_opengraph').toggle();" type="checkbox" name="microdatapro_opengraph" <?php if($microdatapro_opengraph) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_opengraph" <?php if(!$microdatapro_opengraph) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">3.2</span>
								<label class="col-sm-2 control-label"><a data-toggle="tooltip" title="<?php echo $text_meta_desc_h; ?>" href="https://microdata.pro/set/3-2" target="_blank"><?php echo $text_meta_desc; ?></a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_opengraph_meta" <?php if($microdatapro_opengraph_meta) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_opengraph" <?php if(!$microdatapro_opengraph) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">3.3</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/3-3" data-toggle="tooltip" title="<?php echo $text_profile_id_desc; ?>" target="_blank"><?php echo $text_profile_id; ?></a></label>
								<div class="col-sm-10">
									<input type="text" name="microdatapro_profile_id" value="<?php echo $microdatapro_profile_id; ?>" class="form-control" />
								</div>
							</div>
							<div class="form-group microdatapro_opengraph" <?php if(!$microdatapro_opengraph) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">3.4</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/3-4" data-toggle="tooltip" title="<?php echo $text_age_group_desc; ?>" target="_blank"><?php echo $text_age_group; ?></a></label>
								<div class="col-sm-10">
									<select name="microdatapro_age_group" class="form-control">
										<option <?php if($microdatapro_age_group == "") { ?>selected="selected"<?php } ?> value=""><?php echo $text_age_default; ?></option>
										<option <?php if($microdatapro_age_group == "kids") { ?>selected="selected"<?php } ?> value="kids"><?php echo $text_age_children; ?></option>
										<option <?php if($microdatapro_age_group == "adult") { ?>selected="selected"<?php } ?> value="adult"><?php echo $text_age_adult; ?></option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<span class="field_counter">3.5</span>
								<label class="col-sm-2 control-label"><a data-toggle="tooltip" title="<?php echo $text_twitter_account_h; ?>" href="https://microdata.pro/set/3-5" target="_blank"><?php echo $text_twitter_account; ?></a></label>
								<div class="col-sm-10">
									<input type="text" name="microdatapro_twitter_account" value="<?php echo $microdatapro_twitter_account; ?>" class="form-control" />
								</div>
							</div>
							<div class="form-group">
								<span class="field_counter">3.6</span>
								<label class="col-sm-2 control-label"><a data-toggle="tooltip" title="<?php echo $text_config_hcard_h; ?>" href="https://microdata.pro/set/3-6" target="_blank"><?php echo $text_config_hcard; ?></a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_hcard" <?php if($microdatapro_hcard) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
							</div>
						</div>

				    <div role="tabpanel" class="tab-pane fade" id="other">
							<div class="form-group">
								<span class="field_counter">4.1</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/4-1" target="_blank"><?php echo $text_category_page; ?></a></label>
		            <div class="col-sm-10">
									<input onchange="$('.microdatapro_category_block').toggle();" type="checkbox" name="microdatapro_category" <?php if($microdatapro_category) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
		            </div>
	            </div>
							<div class="form-group microdatapro_category_block" <?php if(!$microdatapro_category) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">4.2</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/4-2" target="_blank"><?php echo $text_price_from_to; ?></a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_category_range" <?php if($microdatapro_category_range) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
		            </div>
							</div>
							<?php if($cron_view){ ?>
								<div class="form-group microdatapro_category_block" <?php if(!$microdatapro_category) { ?>style="display:none;"<?php } ?>>
									<span class="field_counter">4.3</span>
									<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/4-3" target="_blank"><?php echo $text_cron_key; ?></a></label>
									<div class="col-sm-10">
										<div class="input-group">
                      <span class="input-group-addon"><?php echo $cron_link; ?></span>
                      <input type="text" name="microdatapro_cron_key" value="<?php echo $microdatapro_cron_key; ?>" class="form-control" />
											<span class="input-group-addon"><?php echo $text_recommended_to_fill; ?></span>
										</div>
			            </div>
								</div>
							<?php } ?>
							<div class="form-group microdatapro_category_block" <?php if(!$microdatapro_category) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">4.4</span>
								<label class="col-sm-2 control-label"><a data-toggle="tooltip" title="<?php echo $text_category_rating; ?>" href="https://microdata.pro/set/4-4" target="_blank"><?php echo $text_all_rating; ?></a></label>
								<div class="col-sm-10">
									<input type="checkbox" name="microdatapro_category_review" <?php if($microdatapro_category_review) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
		            </div>
							</div>
							<div class="form-group microdatapro_category_block" <?php if(!$microdatapro_category) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">4.5</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/4-5" target="_blank"><?php echo $text_products_carousel; ?></a></label>
								<div class="col-sm-1">
									<input onchange="$('.category_list_extended').toggle();" type="checkbox" name="microdatapro_category_list" <?php if($microdatapro_category_list) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>

								<label <?php if(!$microdatapro_category_list) { ?>style="display:none;"<?php } ?> class="col-sm-3 control-label category_list_extended"><a href="https://microdata.pro/set/4-5" target="_blank"><?php echo $text_products_carousel_extended; ?></a></label>
								<div <?php if(!$microdatapro_category_list) { ?>style="display:none;"<?php } ?> class="col-sm-6 category_list_extended">
									<input type="checkbox" name="microdatapro_category_list_extended" <?php if($microdatapro_category_list_extended) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>

							</div>
							<div class="form-group microdatapro_category_block" <?php if(!$microdatapro_category) { ?>style="display:none;"<?php } ?>>
								<span class="field_counter">4.6</span>
								<label class="col-sm-2 control-label"><a data-toggle="tooltip" title="" href="https://microdata.pro/set/4-6" target="_blank"><?php echo $text_photo_in_list; ?></a></label>
								<div class="col-sm-1">
									<input onchange="$('.category_gallery_block').toggle();" type="checkbox" name="microdatapro_category_gallery" <?php if($microdatapro_category_gallery) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
		            </div>
								<label class="col-sm-3 control-label category_gallery_block" <?php if(!$microdatapro_category_gallery) { ?>style="display:none;"<?php } ?>><a href="https://microdata.pro/set/4-6" target="_blank"><?php echo $text_preview_size; ?></a></label>
								<div class="col-sm-1 category_gallery_block" <?php if(!$microdatapro_category_gallery) { ?>style="display:none;"<?php } ?>>
									<input type="text" name="microdatapro_category_thumb_1" value="<?php echo $microdatapro_category_thumb_1; ?>" class="form-control text-center">
						    </div>
								<div class="col-sm-1 category_gallery_block" <?php if(!$microdatapro_category_gallery) { ?>style="display:none;"<?php } ?>>
									<span style="font-size:20px;position:absolute;margin-left:-20px;margin-top:4px;">×</span>
									<input type="text" name="microdatapro_category_thumb_2" value="<?php echo $microdatapro_category_thumb_2; ?>" class="form-control text-center">
								</div>
								<label class="col-sm-2 control-label category_gallery_block" <?php if(!$microdatapro_category_gallery) { ?>style="display:none;"<?php } ?>><a href="https://microdata.pro/set/4-6" target="_blank"><?php echo $text_thumb_size; ?></a></label>
								<div class="col-sm-1 category_gallery_block" <?php if(!$microdatapro_category_gallery) { ?>style="display:none;"<?php } ?>>
									<input type="text" name="microdatapro_category_popup_1" value="<?php echo $microdatapro_category_popup_1; ?>" class="form-control text-center">
						    </div>
								<div class="col-sm-1 category_gallery_block" <?php if(!$microdatapro_category_gallery) { ?>style="display:none;"<?php } ?>>
									<span style="font-size:20px;position:absolute;margin-left:-20px;margin-top:4px;">×</span>
									<input type="text" name="microdatapro_category_popup_2" value="<?php echo $microdatapro_category_popup_2; ?>" class="form-control text-center">
								</div>
							</div>
							<div class="form-group">
								<span class="field_counter">4.7</span>
		            <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/4-7" target="_blank"><?php echo $text_manufacturer_page; ?></a></label>
		            <div class="col-sm-2">
									<input type="checkbox" name="microdatapro_manufacturer" <?php if($microdatapro_manufacturer) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
		            </div>
		          </div>
							<div class="form-group">
								<span class="field_counter">4.8</span>
		            <label class="col-sm-2 control-label"><a href="https://microdata.pro/set/4-8" target="_blank"><?php echo $text_information_page; ?></a></label>
		            <div class="col-sm-2">
									<input type="checkbox" name="microdatapro_information" <?php if($microdatapro_information) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
		            </div>
		          </div>
							<div class="form-group">
								<span class="field_counter">4.9</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/4-9" target="_blank"><?php echo $text_blog; ?></a></label>
								<div class="col-sm-1">
									<input onchange="$('.blog_block').toggle();" type="checkbox" name="microdatapro_blog" <?php if($microdatapro_blog) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
								<div style="padding:0;<?php if(!$microdatapro_blog) { ?> display:none;<?php } ?>" class="col-sm-1 blog_block">
									<select name="microdatapro_blog_type" class="form-control">
										<option value="Article"><?php echo $text_blog_type; ?></option>
										<?php foreach($blog_types as $type){ ?>
											<option value="<?php echo $type; ?>" <?php if($type == $microdatapro_blog_type){ ?>selected="selected"<?php } ?>><?php echo $type; ?></option>
										<?php } ?>
									</select>
		            </div>
								<label class="col-sm-2 control-label blog_block" <?php if(!$microdatapro_blog) { ?>style="display:none;"<?php } ?>><a href="https://microdata.pro/set/4-9" target="_blank"><?php echo $text_blog_author; ?></a></label>
								<div class="col-sm-2 blog_block" <?php if(!$microdatapro_blog) { ?>style="display:none;"<?php } ?>>
									<input type="text" name="microdatapro_blog_author" value="<?php echo $microdatapro_blog_author; ?>" class="form-control">
								</div>
								<label class="col-sm-2 control-label blog_block" <?php if(!$microdatapro_blog) { ?>style="display:none;"<?php } ?>><a href="https://microdata.pro/set/4-9" target="_blank"><?php echo $text_blog_author_url; ?></a></label>
								<div class="col-sm-2 blog_block" <?php if(!$microdatapro_blog) { ?>style="display:none;"<?php } ?>>
									<input type="text" name="microdatapro_blog_author_url" value="<?php echo $microdatapro_blog_author_url; ?>" class="form-control">
								</div>
							</div>
							<div class="form-group">
								<span class="field_counter">4.10</span>
								<label class="col-sm-2 control-label"><a href="https://microdata.pro/set/4-10" target="_blank"><?php echo $text_video; ?></a></label>
								<div class="col-sm-1">
										<input onchange="$('.video_block').toggle();" type="checkbox" name="microdatapro_video" <?php if($microdatapro_video) { ?>checked="checked"<?php } ?> value="1" class="form-control" />
								</div>
								<label class="col-sm-3 control-label video_block" <?php if(!$microdatapro_video) { ?>style="display:none;"<?php } ?>><a href="https://microdata.pro/set/4-10" target="_blank"><?php echo $text_video_key; ?></a></label>
								<div class="col-sm-6 video_block" <?php if(!$microdatapro_video) { ?>style="display:none;"<?php } ?>>
									<input type="text" name="microdatapro_video_key" value="<?php echo $microdatapro_video_key; ?>" class="form-control">
								</div>
							</div>
						</div>

				    <div role="tabpanel" class="tab-pane fade" id="info">
							<div class="form-group">
								<div class="col-sm-12">
									<?php echo $text_thanks_new; ?>
								</div>
							</div>
							<div class="form-group">
							  <div class="col-sm-3"><span data-toggle="tooltip" title="<?php echo $text_faq_h; ?>"><?php echo $text_faq; ?></span></div>
							  <div class="col-sm-9"><a href="https://microdata.pro/faq/" target="_blank">https://microdata.pro/faq/</a></div>
							</div>
							<div class="form-group">
							  <div class="col-sm-3"><span data-toggle="tooltip" title="<?php echo $text_check_h; ?>"><b><?php echo $text_check; ?></b></span></div>
							  <div class="col-sm-9">
									<a target="_blank" href="https://search.google.com/test/rich-results?url=<?php echo $site_url; ?>"><i class="fa fa-google" aria-hidden="true"></i> Google structured data testing tool</a><br>
									<a target="_blank" href="https://cards-dev.x.com/validator"><i class="fa fa-twitter" aria-hidden="true"></i> X card validator</a><br>
									<a target="_blank" href="https://developers.facebook.com/tools/debug/sharing"><i class="fa fa-facebook" aria-hidden="true"></i> Facebook sharing debug</a>
							  </div>
							</div>
							<div class="form-group">
								<div class="col-sm-3"><span data-toggle="tooltip" title="<?php echo $text_contacts_h; ?>"><?php echo $text_contacts; ?></span></div>
							  <div class="col-sm-9">
									Telegram <a href="https://t.me/PrutNikolay" target="_blank">https://t.me/PrutNikolay</a><br>
									<?php echo $text_email; ?> <a href="mailto:info@microdata.pro">info@microdata.pro</a><br>
									<?php echo $text_site; ?> <a href="https://microdata.pro" target="_blank">https://microdata.pro</a><br>
									Opencartforum <a href="https://opencartforum.com/profile/18336-exploits/" target="_blank">https://opencartforum.com/profile/18336-exploits/</a><br>
							  </div>
							</div>
							<div class="form-group">
								<div class="col-sm-3"><?php echo $text_other_modules; ?></div>
							  <div class="col-sm-9">
									<a href="https://opencartforum.com/files/developer/18336-exploits/" target="_blank">https://opencartforum.com/files/developer/18336-exploits/</a>
							  </div>
							</div>

							<div class="form-group">
								<div class="col-sm-3"><?php echo $text_reviews_mod; ?></div>
							  <div class="col-sm-9">
									<?php echo $text_on_site; ?> <a href="https://opencartforum.com/files/file/2859-microdatapro-mikrorazmetka-json-ldmicrodata/?tab=reviews" target="_blank">opencartforum.com</a>
							  </div>
							</div>

						</div>
				  </div>
				<?php } ?>
				<input type="hidden" name="microdatapro_key" value="<?php echo $microdatapro_key; ?>" >
			  </div>
				</form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
