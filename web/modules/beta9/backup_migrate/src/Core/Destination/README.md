# Destinations

A destination in Backup and Migrate is the place where backup files are sent after they are created or where they are read from during a restore. The simplest example of a destination would be a directory on your web server.

An object implementing the `\Drupal\backup_migrate\Core\Destination\DestinationInterface` can be used as a destination and is responsible for persisting a file using the given id (generally the filename). It is also responsible for returning the same file given the same file id.

Destinations in Backup and Migrate are implemented as plugins and will have dependencies and configuration injected into them by the Plugin Manager.

Like other plugins, destinations are passed to the Backup and Migrate object by the consuming application by calling the `add()` method on the plugin manager.

	$backup_migrate->destinations()->add('destination1', new MyDestinationPlugin());

A single Backup and Migrate instance can have more than one destination of a given type. Each destination will have a unique key that will be used to pass the configuration to the destination object as well as to specify the destination(s) when running a `backup()` or `restore()` operation. Only one destination will be used during each backup or restore operation.
