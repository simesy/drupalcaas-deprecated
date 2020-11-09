# Sources

A source in Backup and Migrate is a thing that can be backed up. This could be a database or a file directory. An object that implements the `\Drupal\backup_migrate\Core\Source\SourceInterface` is responsible for creating a single backup file that represents the specified source. It is also responsible for restoring the to that source from a backup file.

Sources in Backup and Migrate are implemented as plugins and will have dependencies and configuration injected into them by the Plugin Manager.

A single Backup and Migrate instance can have more than one source of a given type. Each source will have a unique key that will be used to pass the configuration to the source object and to specify the source when running a `backup()` or `restore()` operation.

Like other plugins, sources are passed to the Backup and Migrate object by the consuming application by calling the `add()` method on the sources plugin manager.

	$backup_migrate->sources()->add('source1', new MySourcePlugin());
