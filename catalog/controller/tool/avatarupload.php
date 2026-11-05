<?php
class ControllerToolAvatarupload extends Controller {
	public function index() {
		$this->load->language('tool/upload');
    
		$json = array();

		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Allowed file extension types
			$allowed = array();

			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

			$filetypes = explode("\n", $extension_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Allowed file mime types
			$allowed = array();

			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

			$filetypes = explode("\n", $mime_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

					if ($this->request->files['file']['size'] > $this->config->get('config_file_max_size')) {
						$json['error'] = $this->language->get('error_filesize');
					}

			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($this->request->files['file']['tmp_name']);

			if (preg_match('/\<\?php/i', $content)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
      
$path_directory = 'image/avatars/';


	
if(preg_match('/[.](JPG)|(jpg)|(jpeg)|(JPEG)|(gif)|(GIF)|(png)|(PNG)$/',$this->request->files['file']['name']))

	 {	
	 	 	
		$filename = $this->request->files['file']['name'];
		$source = $this->request->files['file']['tmp_name'];	
		$target = $path_directory . $filename;
		move_uploaded_file($source, $target);


	if(preg_match('/[.](GIF)|(gif)$/', $filename)) {
	$im = imagecreatefromgif($path_directory.$filename) ;

	}
	if(preg_match('/[.](PNG)|(png)$/', $filename)) {
	$im = imagecreatefrompng($path_directory.$filename) ;

	}	
	if(preg_match('/[.](JPG)|(jpg)|(jpeg)|(JPEG)$/', $filename)) {
    $im = imagecreatefromjpeg($path_directory.$filename); 
	}

$w = 240; 

$w_src = imagesx($im); 
$h_src = imagesy($im); 

         $dest = imagecreatetruecolor($w,$w); 

         if ($w_src>$h_src) 
         imagecopyresampled($dest, $im, 0, 0,
         round((max($w_src,$h_src)-min($w_src,$h_src))/2),
         0, $w, $w, min($w_src,$h_src), min($w_src,$h_src)); 
 
         if ($w_src < $h_src) 
         imagecopyresampled($dest, $im, 0, 0, 0, 0, $w, $w,
         min($w_src,$h_src), min($w_src,$h_src)); 

         if ($w_src==$h_src) 
         imagecopyresampled($dest, $im, 0, 0, 0, 0, $w, $w, $w_src, $w_src); 
 
// удаляем старые
$dir = opendir($path_directory);
$string_to_search=str_replace(['(', ')', '-', ' ', '+'], '', $this->customer->getTelephone());
$mass_sas[] = "";
while(($file = readdir($dir)) !== false) {
    $mass_sa = strstr($file,$string_to_search);
    if($mass_sa != "") {
        $mass_sas[] = $mass_sa;
		$delfull1 = $path_directory.$mass_sa; 
		unlink ($delfull1);
    }
}
closedir($dir);
//

$date=time(); 

if ($this->customer->isLogged()) {
	$date=str_replace(['(', ')', '-', ' ', '+'], '', $this->customer->getTelephone()).'-'.rand(1111, 9999); 
}

imagejpeg($dest, $path_directory.$date.".jpg");

$avatar = $path_directory.$date.".jpg";

$delfull = $path_directory.$filename; 
unlink ($delfull);



}

		} else {
			$json['error'] = $this->language->get('error_upload');
		}
    
		if (!$json) { 
			$file = $filename ;
      
      
      /////

       

      /////
			move_uploaded_file($this->request->files['file']['tmp_name'], $path_directory.$file);

			$json['code'] = $avatar;

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}