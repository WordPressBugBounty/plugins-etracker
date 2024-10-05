<?php
/**
 * EAPageReport.
 *
 * @link       https://etracker.com
 * @since      2.0.0
 *
 * @package    Etracker
 */

namespace Etracker\Reporting\Report;

use Etracker\Reporting\Client;
use Etracker\Reporting\ReportingUtil;

/**
 * EAPageReport implements a subset of etrackers EAPage report.
 *
 * @package    Etracker
 *
 * @author     etracker GmbH <support@etracker.com>
 */
class EAPageReport extends Report implements EAPageReportInterface {
	/**
	 * Query EAPage report via Client $api.
	 *
	 * @param Client       $api           already connected Client object.
	 * @param ReportConfig $report_config ReportConfig to override embedded one.
	 */
	public function __construct( Client $api, $report_config = null ) {
		parent::__construct( $api );
		$this->set_report_name( 'EAPage' );
		$this->report_config['limit']   = 100000;
		$this->report_config['figures'] = array( 'unique_visits' );
		if ( is_a( $report_config, ReportConfig::class ) ) {
			// Allow to override embedded ReportConfig.
			foreach ( array( 'startDate', 'endDate' ) as $key ) {
				$this->report_config[ $key ] = $report_config[ $key ];
			}
		}
	}

	/**
	 * Query EAPage-Report for page $page_name.
	 *
	 * @param string $page_name Page Name to query report for.
	 *
	 * @return EAPageReportInterface
	 */
	public function get_report_by_page_name( string $page_name ): EAPageReportInterface {
		// Update report_config with requested attributes.
		$this->report_config['attributes'] = array( 'page_name' );

		// Query, filter and return report.
		$this->fetch_report();
		$this->report_data = array_values(
			array_filter(
				$this->report_data,
				function ( $data ) use ( $page_name ) {
					if ( ! array_key_exists( 'page_name', $data ) ) {
						return false;
					}
					// Filter by page_name.
					if ( $page_name === $data['page_name'] ) {
						return true;
					}
					return false;
				}
			)
		);
		return $this;
	}

	/**
	 * Query EAPage-Report for URL $url.
	 *
	 * @param string $url       URL of Page.
	 * @param string $page_name Page Name.
	 *
	 * @return EAPageReportInterface
	 */
	public function get_report_by_url_and_page_name( string $url, string $page_name ): EAPageReportInterface {
		$etracker_url = ReportingUtil::url2etracker_url( $url );

		// Update report_config with requested attributes.
		$this->report_config['attributes'] = array( 'page_name', 'url' );

		// Query, filter and return report.
		$this->fetch_report();
		$this->report_data = array_values(
			array_filter(
				$this->report_data,
				function ( $data ) use ( $etracker_url, $page_name ) {
					if ( ! array_key_exists( 'page_name', $data ) ) {
						return false;
					}

					if ( ! array_key_exists( 'url', $data ) ) {
						return false;
					}

					// Filter by url and page_name.
					if ( $etracker_url === $data['url'] && $page_name === $data['page_name'] ) {
						return true;
					}
					return false;
				}
			)
		);
		return $this;
	}

	/**
	 * Query EAPage-Report for URL $url.
	 *
	 * @param string $url URL of Page.
	 *
	 * @return EAPageReportInterface
	 */
	public function get_report_by_url( string $url ): EAPageReportInterface {
		$etracker_url = ReportingUtil::url2etracker_url( $url );

		// Update report_config with requested attributes.
		$this->report_config['attributes'] = array( 'url' );

		// Query, filter and return report.
		$this->fetch_report();
		$this->report_data = array_values(
			array_filter(
				$this->report_data,
				function ( $data ) use ( $etracker_url ) {
					if ( ! array_key_exists( 'url', $data ) ) {
						return false;
					}
					// Filter by url.
					if ( $etracker_url === $data['url'] ) {
						return true;
					}
					return false;
				}
			)
		);
		return $this;
	}

	/**
	 * Return UniqueVisits.
	 *
	 * Function returns NULL if no reporting available.
	 *
	 * @return int|null UniqueVisits.
	 */
	public function get_unique_visits() {
		return $this->get_first_value( 'unique_visits' );
	}
}
