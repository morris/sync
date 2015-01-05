# Synchronization for PHP

```php
<?php

// Call a function in a mutually exclusive way using a lockfile.
// A process will only block other processes and never block itself,
// so you can safely nest synchronized operations.

Sync::call( function() {

	// do critical stuff like IO here

} );

// Set default lockfile

Sync::$lock = 'my/default/.lock';

// Use different lockfile by passing it as the second argument

Sync::call( $func, 'my/other/.lock' );
```

## Requirements

- PHP 5.3+


## Installation

The composer package name is `morris/sync`. You can also download or
fork the repository.


## License

Sync is licensed under the MIT License. See `LICENSE.md` for details.
