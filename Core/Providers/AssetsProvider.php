<?php
namespace PressForward\Core\Providers;

use Intraxia\Jaxion\Contract\Core\Container as Container;
use Intraxia\Jaxion\Assets\Register as Assets;
use Intraxia\Jaxion\Assets\ServiceProvider as ServiceProvider;

class AssetsProvider extends ServiceProvider {

	/**
	 * {@inheritDoc}
	 *
	 * @param Container $container
	 */
	public function register( Container $container ) {
		$this->container = $container;
        //var_dump($this->container); die();
		$register = $this->container->fetch(
			'assets'
		);

		$this->add_assets( $register );
	}

    protected function add_assets( Assets $assets ){
        //$this->container =
        $slug = $this->container->fetch( 'slug' );
		$url  = $this->container->fetch( 'url' );
		$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		if ( $debug ) {
			$assets->set_debug( true );
		}

        $provider = $this;

		$assets->register_style(array(
			'type'	=>	'admin',
			'condition'	=> (function($hook) use ($provider){
								$exclusions = array('pf-options');
								//return true;
								return $provider->check_hook_for_pressforward_string($hook, $exclusions);
							}),
			'handle'	=> $slug.'-reset-style',
			'src'		=> 'assets/css/reset'
		));

		$assets->register_style(array(
			'type'	=>	'admin',
			'condition'	=> function($hook) use ($provider){
								$exclusions = array('pf-options');
								return $provider->check_hook_for_pressforward_string($hook, $exclusions);
							},
			'handle'	=> $slug.'-bootstrap-style',
			'src'		=> 'lib/twitter-bootstrap/css/bootstrap'
		));

		$assets->register_style(array(
			'type'	=>	'admin',
			'condition'	=> function($hook) use ($provider){
								$exclusions = array('pf-options');
								return $provider->check_hook_for_pressforward_string($hook, $exclusions);
							},
			'handle'	=> $slug.'-bootstrap-responsive-style',
			'src'		=> 'lib/twitter-bootstrap/css/bootstrap-responsive'
		));

		$assets->register_style( array(
			'type'	=>	'admin',
			'condition'	=> function($hook) use ($provider){
								$exclusions = array('pf-options');
								return $provider->check_hook_for_pressforward_string($hook, $exclusions);
							},
			'handle'	=>	$slug.'-style',
			'src'		=>	'assets/css/pressforward',
			'deps'		=>	array( $slug . '-bootstrap-style', $slug . '-bootstrap-responsive-style' )
		) );

		$assets->register_style(array(
			'type'	=>	'admin',
			'condition'	=> function($hook) use ($provider){
								$exclusions = array();
								return $provider->check_hook_for_pressforward_string($hook, $exclusions);
							},
			'handle'	=> $slug.'-settings-style',
			'src'		=> 'assets/css/pf-settings'
		));



		$assets->register_script(
			array(
				'type'	=>	'admin',
				'condition'	=> function(){ return true; },
				'handle'	=>	$slug.'-jq-fullscreen',
				'src'		=>	'lib/jquery-fullscreen/jquery.fullscreen',
				'deps'		=>	array( 'jquery' )
			)
		);

		$assets->register_script(
			array(
				'type'	=>	'admin',
				'condition'	=> function(){ return true; },
				'handle'	=>	$slug.'-twitter-bootstrap',
				'src'		=>	'lib/twitter-bootstrap/js/bootstrap',
				'deps'		=>	array( 'jquery' )
			)
		);

		$assets->register_script(
			array(
				'type'	=>	'admin',
				'condition'	=> function(){ return true; },
				'handle'	=>	$slug.'-tools',
				'src'		=>	'assets/js/tools-imp',
				'deps'		=>	array( 'jquery' )
			)
		);

		$assets->register_script( array(
			'type'	=>	'admin',
			'condition'	=> function(){ return true; },
			'handle'	=>	$slug.'-settings-tools',
			'src'		=>	'assets/js/settings-tools',
			'deps'		=>	array( 'jquery' )
		) );


		//var_dump($assets); die();
	}

	public function check_hook_for_pressforward_string($hook, $exclusions = array()){

         $position_test_one = strpos($hook, 'pressforward');
         $position_test_two = strpos($hook, 'pf');
		 if ( ( false === $position_test_one ) && ( false === $position_test_two ) ){ return false; }

		 if (!empty($exclusions)){
		 		 foreach ($exclusions as $exclusion){
		 		 	if (false !== strpos($hook, $exclusion)){
		 		 		return false;
		 		 	}
		 		 }
		 }

		 return true;
	}

}
