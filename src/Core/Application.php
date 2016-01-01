<?php namespace Intraxia\Jaxion\Core;

use Intraxia\Jaxion\Contract\Core\Application as ApplicationContract;
use Intraxia\Jaxion\Contract\Core\HasActions;
use Intraxia\Jaxion\Contract\Core\HasFilters;
use Intraxia\Jaxion\Contract\Core\Loader as LoaderContract;
use WP_CLI;

/**
 * Class Application
 * @package Intraxia\Jaxion
 */
class Application extends Container implements ApplicationContract {
	/**
	 * Singleton instance of the Application object
	 *
	 * @var Application
	 */
	protected static $instance = null;

	/**
	 * {@inheritdoc}
	 *
	 * @param string $file
	 * @param array  $providers
	 *
	 * @throws ApplicationAlreadyBootedException
	 */
	public function __construct( $file, array $providers = array() ) {
		if ( static::$instance !== null ) {
			throw new ApplicationAlreadyBootedException;
		}

		static::$instance = $this;

		parent::__construct( $providers );
		$this->register_constants( $file );
		$this->register_core_services();
		$this->load_i18n();

		register_activation_hook( $file, array( $this, 'activate' ) );
		register_deactivation_hook( $file, array( $this, 'deactivate' ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot() {
		$loader = $this->fetch( 'loader' );

		if ( ! $loader instanceof LoaderContract ) {
			throw new \UnexpectedValueException;
		}

		foreach ( $this as $alias => $value ) {
			if ( $value instanceof HasActions ) {
				$loader->register_actions( $value );
			}

			if ( $value instanceof HasFilters ) {
				$loader->register_filters( $value );
			}
		}

		add_action( 'plugins_loaded', array( $loader, 'run' ) );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @codeCoverageIgnore
	 */
	public function activate() {
		// no-op
	}

	/**
	 * {@inheritdoc}
	 *
	 * @codeCoverageIgnore
	 */
	public function deactivate() {
		// no-op
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return Application
	 * @throws ApplicationNotBootedException
	 */
	public static function instance() {
		if ( static::$instance === null ) {
			throw new ApplicationNotBootedException;
		}

		return static::$instance;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function shutdown() {
		if ( static::$instance !== null ) {
			static::$instance = null;
		}
	}

	/**
	 * Sets the plugin's url, path, and basename.
	 *
	 * @param string $file
	 */
	private function register_constants( $file ) {
		$this->share( 'url', plugin_dir_url( $file ) );
		$this->share( 'path', plugin_dir_path( $file ) );
		$this->share( 'basename', plugin_basename( $file ) );

		$plugin_data = get_plugin_data( $file, false, false );
		$this->share( 'version', isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : null );
	}

	/**
	 * Registers the built-in services with the Application container.
	 */
	private function register_core_services() {
		$this->share( array( 'loader' => 'Intraxia\Jaxion\Contract\Core\Loader' ), function ( $app ) {
			return new Loader( $app );
		} );
	}

	/**
	 * Load's the plugin's translation files.
	 */
	private function load_i18n() {
		load_plugin_textdomain(
			$this->fetch( 'basename' ),
			false,
			basename( $this->fetch( 'path' ) ) . '/languages/'
		);
	}
}
