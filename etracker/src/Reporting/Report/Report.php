<?php
/**
 * Report.
 *
 * @link       https://etracker.com
 * @since      2.0.0
 *
 * @package    Etracker
 */

namespace Etracker\Reporting\Report;

use Etracker\Reporting\Exceptions\ReportQueryDataException;
use Etracker\Reporting\Exceptions\ReportQueryMetaException;
use Etracker\Reporting\Client;

/**
 * Abstract class for Reports.
 *
 * @package    Etracker
 *
 * @author     etracker GmbH <support@etracker.com>
 */
abstract class Report implements ReportInterface {
	/**
	 * Report Cache.
	 *
	 * @var array
	 */
	private $report_cache = array();

	/**
	 * Client object to talk to etrackers reporting API.
	 *
	 * @var Client
	 */
	protected $api = null;

	/**
	 * Report name to query.
	 *
	 * @var string
	 */
	protected $report_name = null;

	/**
	 * Reporting API response with reports meta_data.
	 *
	 * @var array
	 */
	protected $meta_data = null;

	/**
	 * Reporting API response with reporting data.
	 *
	 * @var array
	 */
	protected $report_data = null;

	/**
	 * ReportConfig used to query report.
	 *
	 * @var ReportConfig
	 */
	protected $report_config = null;

	/**
	 * Construct a Report.
	 *
	 * @param Client $api Connected client api object.
	 */
	public function __construct( Client $api ) {
		$this->api           = $api;
		$this->report_config = new ReportConfig();
		$this->report_cache  = array();
	}

	/**
	 * Returns report name.
	 *
	 * @return string
	 */
	public function get_report_name(): string {
		return $this->report_name;
	}

	/**
	 * Sets report name.
	 *
	 * @param string $name Report name.
	 *
	 * @return void
	 */
	protected function set_report_name( string $name ) {
		$this->report_name = $name;
	}

	/**
	 * Fetch report with report_config.
	 *
	 * @param array $report_config Report configuration.
	 *
	 * @throws ReportQueryMetaException Thrown on error while fetching report meta data.
	 * @throws ReportQueryDataException Thrown on error while fetching report data.
	 *
	 * @return Report
	 */
	public function fetch_report_with_config( array $report_config ): Report {
		$report_name      = $this->get_report_name();
		$result_meta_data = $this->api->get( "report/$report_name/metaData", $report_config );

		if ( 200 == $result_meta_data->info->http_code ) {
			$this->meta_data = $result_meta_data->decode_response();
		} else {
			throw new ReportQueryMetaException( 'Error during query report metaData: ' . json_encode( $result_meta_data->info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . ' url: ' . $result_meta_data->url );
		}

		unset( $result_meta_data );

		$result_data = $this->api->get( "report/$report_name/data", $report_config, array( 'Accept' => 'application/json' ) );

		if ( 200 == $result_data->info->http_code ) {
			$this->report_data = json_decode( $result_data->response, true );
		} else {
			throw new ReportQueryDataException( 'Error during query report data: ' . json_encode( $result_data->info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		}

		unset( $result_data );

		return $this;
	}

	/**
	 * Fetch report with default ReportConfig for this object.
	 *
	 * @return Report
	 */
	public function fetch_report(): Report {
		$report_config_checksum = $this->report_config->checksum();

		// Check if report has already been queried.
		if ( array_key_exists( $report_config_checksum, $this->report_cache ) && ! empty( $this->report_cache[ $report_config_checksum ] ) ) {
			// Return cached report result if it is in cache.
			$this->report_data = $this->report_cache[ $report_config_checksum ];
			return $this;
		}

		// Fetch fresh report.
		$this->fetch_report_with_config( $this->report_config->build() );

		// Store fresh report in cache.
		$this->report_cache[ $report_config_checksum ] = $this->report_data;

		// Return report.
		return $this;
	}

	/**
	 * Filter Sum row from report_data.
	 *
	 * @return array report_data
	 */
	public function get_report_data_without_sum(): array {
		return array_values(
			array_filter(
				$this->report_data,
				function ( $data ) {
					if ( ! array_key_exists( 'tree_status', $data ) ) {
						return false;
					}
					if ( '=S' === $data['tree_status'] ) {
						return false;
					}
					return true;
				}
			)
		);
	}

	/**
	 * Get first value from column $column.
	 *
	 * Function returns NULL if no reporting available.
	 *
	 * @param string $column Report column to return.
	 *
	 * @return string|int|null Reporting column value.
	 */
	public function get_first_value( string $column ) {
		$data = $this->get_report_data_without_sum();
		if ( empty( $data ) ) {
			return null;
		}
		return $data[0][ $column ];
	}

	/**
	 * Returns start date of report.
	 *
	 * @return string Start date.
	 */
	public function get_start_date(): string {
		$report_config = $this->report_config->build();
		return $report_config['startDate'];
	}

	/**
	 * Returns end date of report.
	 *
	 * @return string End date.
	 */
	public function get_end_date(): string {
		$report_config = $this->report_config->build();
		return $report_config['endDate'];
	}
}
