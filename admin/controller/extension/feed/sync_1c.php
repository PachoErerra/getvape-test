<?php
class ControllerExtensionFeedSync1c extends Controller {
	public function index() {
		$this->load->language('extension/feed/sync_1c');

		if (!$this->user->hasPermission('access', 'extension/feed/sync_1c')) {
			$this->session->data['error'] = $this->language->get('text_error_permission');
			$this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}

		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/feed/sync_1c');

		$data['user_token'] = $this->session->data['user_token'];
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_panel'] = $this->language->get('text_panel');
		$data['text_history'] = $this->language->get('text_history');
		$data['text_idle'] = $this->language->get('text_idle');
		$data['text_wait'] = $this->language->get('text_wait');
		$data['text_no_jobs'] = $this->language->get('text_no_jobs');
		$data['text_confirm_reset'] = $this->language->get('text_confirm_reset');
		$data['button_run'] = $this->language->get('button_run');
		$data['button_reset'] = $this->language->get('button_reset');
		$data['button_reset_stuck'] = $this->language->get('button_reset_stuck');
		$data['button_refresh'] = $this->language->get('button_refresh');
		$data['column_id'] = $this->language->get('column_id');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_source'] = $this->language->get('column_source');
		$data['column_created'] = $this->language->get('column_created');
		$data['column_sec'] = $this->language->get('column_sec');
		$data['column_upd'] = $this->language->get('column_upd');
		$data['column_skip'] = $this->language->get('column_skip');
		$data['column_new'] = $this->language->get('column_new');
		$data['column_off'] = $this->language->get('column_off');
		$data['column_stages'] = $this->language->get('column_stages');
		$data['column_error'] = $this->language->get('column_error');
		$data['can_modify'] = $this->user->hasPermission('modify', 'extension/feed/sync_1c');

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_products'),
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/feed/sync_1c', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['run'] = $this->url->link('extension/feed/sync_1c/run', 'user_token=' . $this->session->data['user_token'], true);
		$data['reset'] = $this->url->link('extension/feed/sync_1c/reset', 'user_token=' . $this->session->data['user_token'], true);
		$data['refresh'] = $this->url->link('extension/feed/sync_1c', 'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$data['error_warning'] = '';
		}

		$active = $this->model_extension_feed_sync_1c->getActiveJob();
		$data['active'] = $active ? $this->formatJob($active) : null;

		if ($data['active']) {
			$data['text_running_formatted'] = sprintf(
				$this->language->get('text_running'),
				$data['active']['job_id'],
				$data['active']['status_label'],
				$data['active']['source_label']
			);
		} else {
			$data['text_running_formatted'] = '';
		}

		$jobs = $this->model_extension_feed_sync_1c->getJobs(40);
		$data['jobs'] = array();

		foreach ($jobs as $job) {
			$data['jobs'][] = $this->formatJob($job);
		}

		$chart = $this->buildChartData($data['jobs']);
		$data['chart_json'] = $chart['json'];
		$data['chart_summary'] = $chart['summary'];
		$data['text_chart'] = $this->language->get('text_chart');
		$data['has_chart'] = $chart['json'] !== '';

		$data['auto_refresh'] = $active ? true : false;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/feed/sync_1c', $data));
	}

	public function run() {
		$this->load->language('extension/feed/sync_1c');
		$this->load->model('extension/feed/sync_1c');

		if (!$this->user->hasPermission('modify', 'extension/feed/sync_1c')) {
			$this->session->data['error'] = $this->language->get('text_error_permission');
			$this->response->redirect($this->url->link('extension/feed/sync_1c', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}

		$user_id = (int)$this->user->getId();
		$username = (string)$this->user->getUserName();
		$job_id = $this->model_extension_feed_sync_1c->addJob('admin', $user_id, $username);
		$this->session->data['success'] = sprintf($this->language->get('text_success_queued'), (int)$job_id);

		$this->response->redirect($this->url->link('extension/feed/sync_1c', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function reset() {
		$this->load->language('extension/feed/sync_1c');
		$this->load->model('extension/feed/sync_1c');

		if (!$this->user->hasPermission('modify', 'extension/feed/sync_1c')) {
			$this->session->data['error'] = $this->language->get('text_error_permission');
			$this->response->redirect($this->url->link('extension/feed/sync_1c', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}

		$count = $this->model_extension_feed_sync_1c->forceReset($this->language->get('reset_message'));
		$this->session->data['success'] = $count
			? sprintf($this->language->get('text_success_reset'), (int)$count)
			: $this->language->get('text_success_reset_empty');

		$this->response->redirect($this->url->link('extension/feed/sync_1c', 'user_token=' . $this->session->data['user_token'], true));
	}

	private function buildChartData($jobs) {
		$points = array();
		$sorted = $jobs;

		usort($sorted, function ($a, $b) {
			$ta = !empty($a['created_at']) ? strtotime($a['created_at']) : 0;
			$tb = !empty($b['created_at']) ? strtotime($b['created_at']) : 0;

			if ($ta === $tb) {
				return (int)$a['job_id'] - (int)$b['job_id'];
			}

			return $ta - $tb;
		});

		foreach ($sorted as $job) {
			if ($job['status'] !== 'done' || $job['duration_sec'] === null) {
				continue;
			}

			$date_label = !empty($job['created_at']) ? date('d.m', strtotime($job['created_at'])) : ('#' . (int)$job['job_id']);

			$points[] = array(
				'id' => (int)$job['job_id'],
				'sec' => (int)$job['duration_sec'],
				'skip' => (int)$job['skipped'],
				'upd' => (int)$job['updated'],
				'label' => $date_label,
			);
		}

		if (count($points) < 2) {
			return array('json' => '', 'summary' => '');
		}

		$data = array();
		$xaxis = array();
		$tooltips = array();
		$sum_min = 0;

		foreach ($points as $i => $point) {
			$min = round($point['sec'] / 60, 1);
			$sum_min += $min;
			$data[] = array($i, $min);
			$xaxis[] = array($i, $point['label']);
			$tooltips[$i] = '#' . $point['id'] . ' ' . $point['label'] . ': ' . $min . ' хв, upd ' . $point['upd'] . ', skip ' . $point['skip'];
		}

		$n = count($points);
		$last = $points[$n - 1];
		$last_min = round($last['sec'] / 60, 1);
		$summary = sprintf(
			$this->language->get('text_chart_summary'),
			$last_min,
			round($sum_min / $n, 1),
			(int)$last['upd'],
			(int)$last['skip']
		);

		$payload = array(
			'series' => array(
				'label' => $this->language->get('text_chart'),
				'data' => $data,
				'color' => '#1065D2',
			),
			'xaxis' => $xaxis,
			'tooltips' => $tooltips,
		);

		return array(
			'json' => json_encode($payload),
			'summary' => $summary,
		);
	}

	private function formatJob($job) {
		$stages = array();

		if (!empty($job['stages'])) {
			$decoded = json_decode($job['stages'], true);
			if (is_array($decoded)) {
				foreach ($decoded as $name => $value) {
					if ($name === 'goods_progress') {
						$stages[] = sprintf($this->language->get('text_goods_progress'), (int)$value);
					} elseif (strpos($name, 'diff_') === 0) {
						$stages[] = substr($name, 5) . '=' . (int)$value;
					} else {
						$stages[] = $name . ': ' . $value . 'с';
					}
				}
			}
		}

		$status_labels = array(
			'pending' => $this->language->get('status_pending'),
			'running' => $this->language->get('status_running'),
			'done' => $this->language->get('status_done'),
			'error' => $this->language->get('status_error'),
		);

		$source = isset($job['source']) ? $job['source'] : 'cron';
		$username = isset($job['username']) ? trim((string)$job['username']) : '';

		if ($source === 'legacy' || $source === 'cron' || $username === '') {
			$source_label = 'cron';
		} else {
			$source_label = $username;
		}

		return array(
			'job_id' => (int)$job['job_id'],
			'status' => $job['status'],
			'status_label' => isset($status_labels[$job['status']]) ? $status_labels[$job['status']] : $job['status'],
			'source' => $source,
			'source_label' => $source_label,
			'created_at' => $job['created_at'],
			'started_at' => $job['started_at'],
			'finished_at' => $job['finished_at'],
			'duration_sec' => $job['duration_sec'] !== null ? (int)$job['duration_sec'] : null,
			'duration_min' => $job['duration_sec'] !== null ? round(((int)$job['duration_sec']) / 60, 1) : null,
			'updated' => (int)$job['updated'],
			'created' => (int)$job['created'],
			'disabled' => (int)$job['disabled'],
			'skipped' => isset($job['skipped']) ? (int)$job['skipped'] : 0,
			'stages_text' => $stages ? implode(' | ', $stages) : '-',
			'error_message' => $job['error_message'],
		);
	}
}
