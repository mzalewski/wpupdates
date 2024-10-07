<?php

class AntimatterWP {
	private static $antimatter_config = null;
	// Constructor to initialize hooks and filters
	public function __construct() {
		add_filter( 'install_plugins_tabs', array( $this, 'install_plugins_tabs' ) );
		add_filter( 'install_themes_tabs', array( $this, 'install_themes_tabs' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
		add_filter( 'themes_api', array( $this, 'themes_api_filter' ), 10, 3 );
		add_filter( 'plugin_install_action_links', array( $this, 'plugin_install_action_links_filter' ), 10, 2 );
		add_filter( 'antimatter_config', array( $this, 'apply_repo_defaults' ), 10 );
	}
	public function apply_repo_defaults( $config ) {
		foreach ( $config as &$repo ) {

			if ( $repo['slug'] == 'wporg' ) {
				if ( ! isset( $repo['plugins'] ) ) {
					$repo['plugins'] = array(
						'tabs' => array(
							'featured' => 'Featured',
							'popular'  => 'Popular',
						),
					);
				}
				if ( ! isset( $repo['plugins']['url'] ) ) {
					$url                    = apply_filters( 'wporg_plugins_info_url', 'https://api.wordpress.org/plugins/info/1.2/' );
					$repo['plugins']['url'] = $url;
				}

				if ( ! isset( $repo['themes'] ) ) {
					$repo['themes'] = array(
						'tabs' => array(
							'featured' => 'Featured',
							'popular'  => 'Popular',
						),
					);
				}
				if ( ! isset( $repo['themes']['url'] ) ) {
					$url                   = apply_filters( 'wporg_themes_info_url', 'https://api.wordpress.org/themes/info/1.2/' );
					$repo['themes']['url'] = $url;
				}
				if ( ! isset( $repo['name'] ) ) {
					$repo['name'] = 'WP.org';
				}
			}
		}
		return $config;
	}
	public static function get_antimatter_config() {
		if ( self::$antimatter_config === null ) {
			$config                  = defined( 'ANTIMATTER_WP_UPDATE_CONFIG' ) ? ANTIMATTER_WP_UPDATE_CONFIG : false;
			$config                  = apply_filters( 'antimatter_config', $config );
			self::$antimatter_config = $config;
		}
		return self::$antimatter_config;
	}
	public static function get_repositories( $type = 'plugins', $match_slugs = null, $match_tab = null ) {
		$config = self::get_antimatter_config();

		if ( $config ) {
			$repos = array_filter(
				$config,
				function ( $repo ) use ( $type, $match_slugs, $match_tab ) {
					$is_match = isset( $repo[ $type ] ) && is_array( $repo[ $type ] ) && ! empty( $repo[ $type ] );
					if ( $match_slugs != null ) {
						$is_match = $is_match && in_array( $repo['slug'], $match_slugs );
					}
					if ( $match_tab != null ) {
						$is_match = $is_match && isset( $repo[ $type ]['tabs'] ) && isset( $repo[ $type ]['tabs'][ $match_tab ] );
					}
					return $is_match;
				}
			);
			return $repos;
		}
		return null;
	}
	/**
	 * Add custom tabs to the plugin install screen
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public function install_plugins_tabs( $tabs ) {
		$custom_plugin_repo_config = self::get_repositories( 'plugins' );

		if ( $custom_plugin_repo_config != null ) {
			foreach ( $custom_plugin_repo_config as $repo ) {

				if ( isset( $repo['plugins']['tabs'] ) && ! empty( $repo['plugins']['tabs'] ) ) {

					foreach ( $repo['plugins']['tabs'] as $tab_slug => $tab_name ) {
						$tabs[ $tab_slug ] = $tab_name;
						add_action( 'install_plugins_' . $tab_slug, array( $this, 'display_plugins_table' ) );

						add_filter(
							'install_plugins_table_api_args_' . $tab_slug,
							function ( $args ) use ( $repo ) {
								if ( $args == false ) {
									$args = array();
								}
								if ( isset( $args['store'] ) ) {
									$args['store'] = $args['store'] . ',' . $repo['slug'];
									return $args;
								}
								$args['store']    = $repo['slug'];
								$args['per_page'] = 10;
								return $args;
							}
						);
					}
				}
			}
		}
		return $tabs;
	}

	/**
	 * Add custom tabs to the plugin install screen
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	public function install_themes_tabs( $tabs ) {

		/** Does not work - theme screen no longer supports tabs */
		$repos = self::get_repositories( 'themes' );

		if ( $repos != null ) {
			foreach ( $repos as $repo ) {

				if ( isset( $repo['themes'] ) && isset( $repo['themes']['tabs'] ) && ! empty( $repo['themes']['tabs'] ) ) {

					foreach ( $repo['themes']['tabs'] as $tab_slug => $tab_name ) {
						$tabs[ $tab_slug ] = $tab_name;
						add_action( 'install_themes_' . $tab_slug, array( $this, 'display_themes_table' ) );

						add_filter(
							'install_themes_table_api_args_' . $tab_slug,
							function ( $args ) use ( $repo ) {
								if ( $args == false ) {
									$args = array();
								}
								if ( isset( $args['store'] ) ) {
									$args['store'] = $args['store'] . ',' . $repo['slug'];
									return $args;
								}
								$args['store']    = $repo['slug'];
								$args['per_page'] = 10;
								return $args;
							}
						);
					}
				}
			}
		}
		return $tabs;
	}

	/**
	 * Enqueue the custom JS file for the plugin install screen
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			'antimatter-admin-js', // Handle name
			plugin_dir_url( __DIR__ ) . 'assets/antimatter-admin.js', // Path to the JS file
			array( 'jquery' ), // Dependencies
			null, // Version (optional)
			true // Load in the footer
		);
	}

	/**
	 * Filter the plugins API to communicate with custom plugin repositories
	 *
	 * @param object $res
	 * @param string $action
	 * @param object $args
	 *
	 * @return object
	 */
	public function plugins_api_filter( $res, $action, $args ) {

		// Include an unmodified $wp_version.
		require ABSPATH . WPINC . '/version.php';

		if ( is_array( $args ) ) {
			$args = (object) $args;
		}

		if ( 'query_plugins' === $action ) {
			if ( ! isset( $args->per_page ) ) {
				$args->per_page = 24;
			}
		}
		if ( ! isset( $args->locale ) ) {
			$args->locale = get_user_locale();
		}

		if ( ! isset( $args->wp_version ) ) {
			$args->wp_version = substr( $wp_version, 0, 3 ); // x.y
		}

		$store = null;
		if ( isset( $_REQUEST['store'] ) && ! empty( $_REQUEST['store'] ) ) {
			$store = $_REQUEST['store'];
		}
		if ( isset( $args->store ) && ! empty( $args->store ) ) {
			$store = $args->store;
		}
		$stores = null;
		if ( ! empty( $store ) ) {
			$stores = explode( ',', $store );

		}
		$repos        = $this->get_repositories( 'plugins', $stores );
		$existing_res = null;

		if ( $repos ) {
			foreach ( $repos as $repo ) {
				$url         = $this->get_repo_url( $repo, 'plugins' );
				$request_url = add_query_arg(
					array(
						'action'  => $action,
						'request' => $args,
					),
					$url
				);
				$http_url    = $request_url;
				$ssl         = wp_http_supports( array( 'ssl' ) );
				if ( $ssl ) {
					$request_url = set_url_scheme( $request_url, 'https' );
				}
				$http_args = array(
					'headers'    => array( 'Content-Type' => 'application/json' ),
					'timeout'    => 15,
					'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
				);
				$response  = wp_remote_get( $http_url, $http_args );
				if ( ! is_wp_error( $response ) ) {
					$res = json_decode( wp_remote_retrieve_body( $response ), true );
					$res = (object) $res;
					if ( isset( $res->plugins ) ) {
						for ( $i = 0; $i < count( $res->plugins ); $i++ ) {
							$res->plugins[ $i ]['store_id'] = $repo['slug'];
							$res->plugins[ $i ]['store']    = $repo['name'];
						}
					}

					if ( $existing_res !== null && isset( $existing_res->plugins ) ) {

						$res->plugins = array_merge( $existing_res->plugins, $res->plugins );
					}
					$existing_res = $res;
				} else {

				}
			}
		}

		return $existing_res;
	}
	public function get_repo_url( $repo, $type ) {

		$url = '';
		if ( isset( $repo[ $type ] ) && isset( $repo[ $type ]['url'] ) ) {
			return $repo[ $type ]['url'];
		}
		$url = apply_filters( 'wp_update_repo_url', $url, $repo, $type );

		return $url;
	}
	/**
	 * Filter the action links for the plugin install screen
	 *
	 * @param array $action_links
	 * @param array $plugin
	 *
	 * @return array
	 */
	public function plugin_install_action_links_filter( $action_links, $plugin ) {
		// Add parameter to install/update:
		$action_links = array_map(
			function ( $link ) use ( $plugin ) {
				$store_id = esc_attr( $plugin['store_id'] );
				if ( str_contains( $link, 'action=install-plugin' ) ) {
					$link = str_replace( 'data-name=', 'data-store="' . $store_id . '" data-name=', $link );
					return str_replace( 'plugin=', 'store=' . $store_id . '&plugin=', $link );
				}
				if ( str_contains( $link, 'action=update-plugin' ) ) {
					$link = str_replace( 'data-name=', 'data-store="' . $store_id . '" data-name=', $link );
					return str_replace( 'plugin=', 'data-store="' . $store_id . '" store=' . $store_id . '&plugin=', $link );
				}
				return $link;
			},
			$action_links
		);

		// Add a custom action link to the plugin install screen
		if ( isset( $plugin['store'] ) ) {
			array_unshift( $action_links, '<strong style="display:inline-block; background: #f6f7f7; padding:0.25rem 0.5rem; font-size:0.9em; font-weight:600;">' . esc_html( $plugin['store'] ) . '</strong>' );
		}
		return $action_links;
	}

	/**
	 * Filter the themes API to communicate with custom theme repositories
	 *
	 * @param object $res
	 * @param string $action
	 * @param object $args
	 *
	 * @return object
	 */
	public function themes_api_filter( $res, $action, $args ) {

		// Include an unmodified $wp_version.
		require ABSPATH . WPINC . '/version.php';

		if ( is_array( $args ) ) {
			$args = (object) $args;
		}

		if ( 'query_themes' === $action ) {
			if ( ! isset( $args->per_page ) ) {
				$args->per_page = 24;
			}
		}
		if ( ! isset( $args->locale ) ) {
			$args->locale = get_user_locale();
		}

		if ( ! isset( $args->wp_version ) ) {
			$args->wp_version = substr( $wp_version, 0, 3 ); // x.y
		}

		$store = null;
		if ( isset( $_REQUEST['store'] ) && ! empty( $_REQUEST['store'] ) ) {
			$store = $_REQUEST['store'];
		}
		if ( isset( $args->store ) && ! empty( $args->store ) ) {
			$store = $args->store;
		}
		$stores = null;
		if ( ! empty( $store ) ) {
			$stores = explode( ',', $store );

		}

		$repos        = $this->get_repositories( 'themes', $stores, $args->browse );
		$existing_res = null;
		if ( $repos ) {
			foreach ( $repos as $repo ) {
				$url = $this->get_repo_url( $repo, 'themes' );

				$request_url = add_query_arg(
					array(
						'action'  => $action,
						'request' => $args,
					),
					$url
				);
				$http_url    = $request_url;
				$ssl         = wp_http_supports( array( 'ssl' ) );
				if ( $ssl ) {
					$request_url = set_url_scheme( $request_url, 'https' );
				}
				$http_args = array(
					'headers'    => array( 'Content-Type' => 'application/json' ),
					'timeout'    => 15,
					'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
				);

				$response = wp_remote_get( $http_url, $http_args );

				if ( ! is_wp_error( $response ) ) {
					$res = json_decode( wp_remote_retrieve_body( $response ), true );

					$res = (object) $res;
					if ( isset( $res->themes ) ) {
						for ( $i = 0; $i < count( $res->themes ); $i++ ) {
							$res->themes[ $i ]['store_id'] = $repo['slug'];
							$res->themes[ $i ]['store']    = $repo['name'];
						}
					}

					if ( $existing_res !== null && isset( $existing_res->themes ) ) {
						$res->themes = array_merge( $existing_res->themes, $res->themes );
					}

					$existing_res = $res;
				}
			}
		}
		return $existing_res;
	}

	/**
	 * Display the plugins table for the custom plugin tabs
	 */
	public function display_plugins_table() {
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';

		$args = apply_filters( 'install_plugins_table_api_args_' . $current_tab, false );

		$api = plugins_api( 'query_plugins', $args );
		if ( is_wp_error( $api ) ) {
			wp_die( $api );
		}

		$plugin_table        = _get_list_table( 'WP_Plugin_Install_List_Table' );
		$plugin_table->items = $api->plugins;
		$plugin_table->set_pagination_args(
			array(
				'total_items' => $api->info['results'],
				'per_page'    => $api->info['per_page'],
			)
		);
		$plugin_table->display();
	}


	/**
	 * Display the plugins table for the custom plugin tabs
	 */
	public function display_themes_table() {
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';

		$args = apply_filters( 'install_themes_table_api_args_' . $current_tab, false );

		$api = plugins_api( 'query_themes', $args );
		if ( is_wp_error( $api ) ) {
			wp_die( $api );
		}

		$plugin_table        = _get_list_table( 'WP_Theme_Install_List_Table' );
		$plugin_table->items = $api->plugins;
		$plugin_table->set_pagination_args(
			array(
				'total_items' => $api->info['results'],
				'per_page'    => $api->info['per_page'],
			)
		);
		$plugin_table->display();
	}
}

// Initialize the class
new AntimatterWP();
