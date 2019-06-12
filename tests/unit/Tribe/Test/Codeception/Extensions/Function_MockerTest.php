<?php namespace Tribe\Test\Codeception\Extensions;

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Suite;

class Function_MockerTest extends \Codeception\Test\Unit {
	/**
	 * It should throw if init file is not specified
	 *
	 * @test
	 */
	public function should_throw_if_init_file_is_not_specified() {
		$config  = [];
		$options = [];
		$event   = $this->prophesize( SuiteEvent::class );

		$extension = new Function_Mocker( $config, $options );

		$this->expectException( ExtensionException::class );

		$extension->init( $event->reveal() );
	}

	/**
	 * It should throw if init file does not exist
	 *
	 * @test
	 */
	public function should_throw_if_init_file_does_not_exist() {
		$config  = [ 'initFile' => __DIR__ . '/nope.php' ];
		$options = [];
		$event   = $this->prophesize( SuiteEvent::class );

		$extension = new Function_Mocker( $config, $options );

		$this->expectException( ExtensionException::class );

		$extension->init( $event->reveal() );
	}

	/**
	 * It should run for specified suites only
	 *
	 * @test
	 */
	public function should_run_for_specified_suites_only() {
		global $fm_fake_inits;
		$starting_value = (int) $fm_fake_inits;
		$config         = [
			'initFile' => codecept_data_dir( 'fm-fake-init.php' ),
			'suites'   => [ 'suite_two' ],
		];
		$options        = [];
		$event_one      = $this->prophesize( SuiteEvent::class );
		$suite_one      = $this->prophesize( Suite::class );
		$suite_one->getName()->willReturn( 'suite_one' );
		$event_one->getSuite()->willReturn( $suite_one->reveal() );

		$extension = new Function_Mocker( $config, $options );

		$extension->init( $event_one->reveal() );

		$this->assertEquals( $starting_value, $fm_fake_inits );

		$event_two = $this->prophesize( SuiteEvent::class );
		$suite_two = $this->prophesize( Suite::class );
		$suite_two->getName()->willReturn( 'suite_two' );
		$event_two->getSuite()->willReturn( $suite_two->reveal() );

		$extension->init( $event_two->reveal() );

		$this->assertEquals( $starting_value + 1, $fm_fake_inits );
	}

	/**
	 * It should  run for all suites if none specified
	 *
	 * @test
	 */
	public function should_run_for_all_suites_if_none_specified() {
		global $fm_fake_inits;
		$starting_value = (int) $fm_fake_inits;
		$config         = [ 'initFile' => codecept_data_dir( 'fm-fake-init.php' ) ];
		$options        = [];
		$event_one      = $this->prophesize( SuiteEvent::class );
		$suite_one      = $this->prophesize( Suite::class );
		$suite_one->getName()->willReturn( 'suite_one' );
		$event_one->getSuite()->willReturn( $suite_one->reveal() );

		$extension = new Function_Mocker( $config, $options );

		$extension->init( $event_one->reveal() );

		$this->assertEquals( $starting_value + 1, $fm_fake_inits );

		$event_two = $this->prophesize( SuiteEvent::class );
		$suite_two = $this->prophesize( Suite::class );
		$suite_two->getName()->willReturn( 'suite_two' );
		$event_two->getSuite()->willReturn( $suite_two->reveal() );

		$extension->init( $event_two->reveal() );

		$this->assertEquals( $starting_value + 2, $fm_fake_inits );
	}
}
