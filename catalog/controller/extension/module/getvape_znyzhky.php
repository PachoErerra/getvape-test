<?php
/**
 * GetVape Знижки сайту - cron expire
 * Author: Not The Same (https://ntsame.agency/)
 * Contact: hoffmanxyz@gmail.com
 *
 * URL: index.php?route=extension/module/getvape_znyzhky/cron&token=GvZ9kT7mQ2wX4nR8pL5sH1cY6bF0uA3d
 */
class ControllerExtensionModuleGetvapeZnyzhky extends Controller {
	private $cron_token = 'GvZ9kT7mQ2wX4nR8pL5sH1cY6bF0uA3d';

	public function cron() {
		if (!isset($this->request->get['token']) || !hash_equals($this->cron_token, (string)$this->request->get['token'])) {
			$this->response->addHeader('HTTP/1.1 403 Forbidden');
			$this->response->setOutput('Forbidden');
			return;
		}

		$this->load->model('extension/module/getvape_znyzhky');

		$cleared = $this->model_extension_module_getvape_znyzhky->cronExpire();

		$this->response->addHeader('Content-Type: text/plain; charset=utf-8');
		$this->response->setOutput('getvape_znyzhky cron ok, cleared=' . (int)$cleared);
	}
}
