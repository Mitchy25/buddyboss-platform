<?php

namespace Buddyboss\LearndashIntegration\Buddypress;

use Buddyboss\LearndashIntegration\Buddypress\ReportsGenerator;

class Ajax
{
	protected $bpGroup  = null;
	protected $ldGroup  = null;

	public function __construct()
	{
		add_action('bp_ld_sync/init', [$this, 'init']);
	}

	public function init()
	{
		add_action('wp_ajax_bp_ld_group_get_reports', [$this, 'ajaxGetReports']);
		add_action('wp_ajax_download_bp_ld_reports', [$this, 'ajaxDownloadReport']);
		add_action('bp_ld_sync/ajax/post_fetch_reports', [$this, 'ajaxGetExports']);
		add_action('bp_ld_sync/report_columns', [$this, 'removeIdsOnNonExport'], 10, 2);
		add_action('bp_ld_sync/reports_generator_args', [$this, 'unsetCompletionOnExport']);
	}

	public function ajaxGetReports()
	{
		$this->enableDebugOnDev();
		$this->validateRequest();

		$generator = $this->getGenerator();

		do_action('bp_ld_sync/ajax/pre_fetch_reports', $generator);
		$generator->fetch();
		do_action('bp_ld_sync/ajax/post_fetch_reports', $generator);

		echo json_encode([
			'draw'            => (int) bp_ld_sync()->getRequest('draw'),
			'recordsTotal'    => $generator->getPager()['total_items'],
			'recordsFiltered' => $generator->getPager()['total_items'],
			'data'            => $generator->getData(),
		]);

		header('Content-Type: application/json; charset=' . get_option('blog_charset'));
		wp_die();
		// wp_send_json_success([
		// 	'draw' => (int) bp_ld_sync()->getRequest('draw'),
		// 'results' => $generator->getData(),
		// 'pager'   => $generator->getPager(),
		// ]);
	}

	public function unsetCompletionOnExport($args)
	{
		if (bp_ld_sync()->getRequest('export')) {
			$args['completed'] = null;
		}

		return $args;
	}

	public function removeIdsOnNonExport($column, $args)
	{
		if (! isset($args['report'])) {
			unset($column['user_id']);
			unset($column['course_id']);
		}

		return $column;
	}

	public function ajaxGetExports($generator)
	{
		if (! bp_ld_sync()->getRequest('export')) {
			return;
		}

		return $generator->export();
	}

	public function ajaxDownloadReport()
	{
		$hash    = bp_ld_sync()->getRequest('hash');
		$exports = get_transient($hash);
		$info    = get_transient("{$hash}_info");

        if (! $hash || ! $exports) {
            wp_die(__('Session has expired, please refresh and try again.', 'buddyboss'));
        }

		$file = fopen('php://output', 'w');
    	fputcsv($file, wp_list_pluck($info['columns'], 'label'));

    	foreach ($exports as $export) {
    		fputcsv($file, $export);
    	}

		header('Content-Encoding: '. DB_CHARSET);
		header('Content-type: text/csv; charset='.DB_CHARSET);
		header('Content-Disposition: attachment; filename='. $info['filename']);
		header('Pragma: no-cache');
		header('Expires: 0');
    	fclose($df);
	    die();
	}

	protected function enableDebugOnDev()
	{
		if (strpos(get_bloginfo('url'), '.test') === false) {
			return;
		}

		error_reporting(E_ALL);
		ini_set("display_errors", 1);
	}

	protected function validateRequest()
	{
        if (! wp_verify_nonce(bp_ld_sync()->getRequest('nonce'), 'bp_ld_report')) {
            wp_send_json_error([
                'message' => __('Session has expired, please refresh and try again.', 'buddyboss')
            ]);
        }

        if ( $this->setRequestGroups() && ( ! $this->bp_group || ! $this->ld_group ) ) {
            wp_send_json_error([
                'message' => __('Unable to find selected group.', 'buddyboss')
            ]);
        }
	}

	protected function setRequestGroups()
	{
		if (! $groupId = bp_ld_sync()->getRequest('group')) {
			return;
		}

		$bpGroup = groups_get_group($groupId);

		if (! $bpGroup->id) {
			return;
		}

		$this->bpGroup = $bpGroup;
		$this->ldGroup = get_post(bp_ld_sync('buddypress')->helpers->getLearndashGroupId($groupId));
	}

	protected function getGenerator()
	{
		$generators = bp_ld_sync('buddypress')->reports->getGenerators();
		$type = bp_ld_sync()->getRequest('step');

		return (new $generators[$type]['class']);
	}
}