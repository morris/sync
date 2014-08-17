<?php

require_once 'vendor/autoload.php';

/**
 * @runInSeparateProcess
 */
class SyncTest extends PHPUnit_Framework_TestCase {

	static function setupBeforeClass() {

		Sync::$lock = 'tests/.lock';

	}

	function testBasics() {

		$a = array();

		Sync::call( function() use ( &$a ) {

			$a[] = 'called';

			$handle = fopen( Sync::$lock, 'w+' );

			if ( $handle && flock( $handle, LOCK_EX|LOCK_NB, $wouldblock ) ) {

				flock( $handle, LOCK_UN );
				fclose( $handle );

			} else {

				$a[] = 'locked';

			}

			if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {

				// this fails on windows
				// whereas LOCK_NB appears to be supported, at least by windows 8
				// $this->assertEquals(1, $wouldblock);

			} else {

				$this->assertEquals( 1, $wouldblock );

			}

			Sync::call( function() use (&$a) {

				$a[] = 'nested';

			} );

		} );

		$ex = array( 'called', 'locked', 'nested' );

		$this->assertEquals( $ex, $a );

	}

	function testIncrement() {

		$self = $this;

		Sync::call( function() use ( $self ) {

			$file = 'tests/increment';

			file_put_contents( $file, 0 );

			for ( $i = 0; $i < 100; ++$i ) {

				$j = intval( file_get_contents( $file ) );

				$self->assertEquals( $i, $j );

				file_put_contents( $file, $j + 1 );

				usleep( rand( 100, 300) );

			}

		} );

	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	function testException() {

		Sync::call( function() {

			throw new \InvalidArgumentException( 'Oops' );

		} );

	}

}
