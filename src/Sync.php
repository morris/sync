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

		$lock = self::path( $lock );

		// just call function if lock already acquired

		if ( isset( self::$locks[ $lock ] ) ) {

			return $func();

		}

		try {

			// acquire lock

			$handle = fopen( $lock, 'w' );

			if ( !$handle || !flock( $handle, LOCK_EX ) ) {

				throw new \RuntimeException( 'Unable to lock on "' . $lock . '"' );

			}

			self::$locks[ $lock ] = true;

			// call function and release lock

			$return = $func();

			self::release( $lock, $handle );

			return $return;

		} catch ( \Exception $e ) {

			// release lock and rethrow any exception

			self::release( $lock, $handle );

			throw $e;

		}

	}

	/**
	 * Get absolute path to a lock file
	 */
	public static function path( $lock = null ) {

		if ( !isset( $lock ) ) {

			$lock = self::$lock;

		}

		if ( !is_file( $lock ) ) touch( $lock );

		return realpath( $lock );

	}

	private static function release( $lock, $handle ) {

		unset( self::$locks[ $lock ] );

		if ( $handle ) {

			flock( $handle, LOCK_UN );
			fclose( $handle );

		}

	}

	// set of already acquired locks
	private static $locks = array();

}
