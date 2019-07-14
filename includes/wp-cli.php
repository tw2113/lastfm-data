<?php

namespace tw2113\lastfm;

class ProcessLastFMData extends \WP_CLI_Command {

	public $args;

	public $assoc_args;

	public $type;

	public $data = array();

	private $path;

	private $wpdb;

	public function __construct() {
		$this->path = WP_CONTENT_DIR . '/plugins/lastfm-data/';

		global $wpdb;
		$this->wpdb = $wpdb;
	}


	public function run( $args, $assoc_args ) {
		$this->args       = $args;
		$this->assoc_args = $assoc_args;

		$files = $this->get_files_by_data_type();

		$progress_bar = \WP_CLI\Utils\make_progress_bar(
			'Processing ' . count( $files ) . ' files',
			count( $files )
		);

		foreach ( $this->file_generator( $files ) as $f ) {
			$file = $this->parse_data_file( $f );

			foreach ( $this->json_data_generator( $file ) as $item ) {
				$this->insert( $item );
			}
			$progress_bar->tick();
		}
		$progress_bar->finish();
	}

	private function get_files_by_data_type(): array {
		$paths = glob( $this->path . "data/page*.json" );

		return $paths;
	}

	private function file_generator( $files ) {
		foreach ( $files as $file ) {
			yield $file;
		}
	}

	private function parse_data_file( $file ) {
		$data = json_decode( file_get_contents( $file ) );

		return $data;
	}

	private function json_data_generator( $data ) {
		foreach ( $data->recenttracks->track as $datum ) {
			yield $datum;
		}
	}

	private function insert( $item ) {
		$formatted = date( 'Y-m-d H:i:s', $item->date->uts );
		$this->wpdb->insert(
			$this->wpdb->prefix . 'lastfm_tracks',
			[
				'track_name'    => $item->name,
				'track_album'   => $item->album->{'#text'},
				'track_artist'  => $item->artist->name,
				'date_listened' => $formatted,
			],
			[
				'%s',
				'%s',
				'%s',
				'%s',
			]
		);
	}
}

\WP_CLI::add_command( 'lastfm', __NAMESPACE__ . '\ProcessLastFMData' );
