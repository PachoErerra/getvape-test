<?php
/**
 * Серверная фиксация менеджера ТТН.
 * JS-хук после saveCN срабатывает через раз (редирект/гонка/кэш).
 * Event в том же PHP-запросе, что и ionCube saveCN - надежнее.
 */
class ControllerEventCnManagerTrack extends Controller {
	public function before(&$route, &$data = null) {
		$this->markFromRequest($route, false);
	}

	public function after(&$route, &$output = null) {
		// router: (&$route, &$output) | loader: (&$route, &$data, &$output)
		$args = func_get_args();
		$route_name = isset($args[0]) ? $args[0] : $route;

		$this->markFromRequest($route_name, true);
	}

	public function modelAfter(&$route, &$args, &$output = null) {
		$order_id = 0;
		$number = '';

		if (is_array($args)) {
			if (isset($args[0])) {
				$order_id = (int)$args[0];
			}

			if (isset($args[1])) {
				$number = trim((string)$args[1]);
			}
		}

		if ($order_id < 1) {
			return;
		}

		$carrier = (strpos((string)$route, 'ukrposhta') !== false) ? 'ukrposhta' : 'novaposhta';

		$this->load->model('extension/module/cn_manager_track');
		$this->model_extension_module_cn_manager_track->mark($order_id, $carrier, $number, true);
	}

	private function markFromRequest($route, $write_history) {
		if (!$this->user || !$this->user->isLogged()) {
			return;
		}

		$route = (string)$route;
		$carrier = (strpos($route, 'ukrposhta') !== false) ? 'ukrposhta' : 'novaposhta';

		$order_id = 0;

		if (!empty($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} elseif (!empty($this->request->post['order_id'])) {
			$order_id = (int)$this->request->post['order_id'];
		}

		if ($order_id < 1) {
			return;
		}

		$number = '';

		if (!empty($this->request->post['cn_number'])) {
			$number = trim((string)$this->request->post['cn_number']);
		} elseif (!empty($this->request->get['cn_number'])) {
			$number = trim((string)$this->request->get['cn_number']);
		}

		$this->load->model('extension/module/cn_manager_track');
		$this->model_extension_module_cn_manager_track->mark($order_id, $carrier, $number, $write_history);
	}
}
