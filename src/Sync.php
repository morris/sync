<?php

/**
 * Provides a static method to call functions synchronously using
 * lockfiles.
 */
class Sync {

	/**
	 * Default lock file
	 */
	public static $lock = '.lock';

	/**
	 * Call a function in a mutually exclusive way using a lockfile.
	 * A process will only block other processes and never block itself,
	 * so you can safely nest synchronized operations.
	 */
	public static function call( $func, $lock = null ) {

		// fall back to default lock

		if ( !isset( $lock ) ) {

			$lock = self::$lock;

		}

		// get absolute path

		touch( $lock );
		$lock = realpath( $lock );

		// just call function if lock already acquired

		if ( in_array( $lock, self::$locks ) ) {

			return $func();

		}

		try {

			// acquire lock

			$handle = fopen( $lock, 'w' );

			if ( !$handle || !flock( $handle, LOCK_EX ) ) {

				throw new \Exception(

					'Unable to synchronize over "'. $lock . '"'

				);

			}

			self::$locks[ $lock ] = true;

			// call function and release lock

			$return = $func();

			self::release( $lock, $handle );

			return $return;

		} catch ( \Exception $e ) {

			// release lock and rethrow any exception

			if ( isset( $handle ) ) {

				self::release( $lock, $handle );

			}

			throw $e;

		}

	}

	private static function release( $lock, $handle ) {

		self::$locks[ $lock ] = false;

		if ( $handle ) {

			flock( $handle, LOCK_UN );
			fclose( $handle );

		}

	}

	// set of already acquired locks
	private static $locks = array();

}
