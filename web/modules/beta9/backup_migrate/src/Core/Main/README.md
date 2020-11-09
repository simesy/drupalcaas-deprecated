# The Backup and Migrate Object

The `\Drupal\backup_migrate\Core\Services\BackupMigrate` service is the object that exposes the operation of Backup and Migrate
to the consuming application. By itself it does nothing, it relies on the consuming application to inject all of the 
necessary plugins, configuration and supporting services to perform it's work.

## Instantiating the Object

Before it can be called, the Backup and Migrate object must be instantiated, configured and all necessary plugins must
be added by the consuming application. This puts the burden of configuring and discovering plugins on the consuming 
application but keeps the library simple, allows greater flexibility and preserves the goal of dependency inversion.

The service is instantiated with by creating a new `BackupMigrate` object:

    use Drupal\backup_migrate\Core\Services\BackupMigrate;
    
    $bam = new BackupMigrate();

### Adding Plugins

Destinations, Sources and Filter plugins are all added are added to the object using a Plugin Manager. Each plugin
that is needed must be added to the plugin manager which can be passed to the `BackupMigrate` object using the constructor or by calling `setPluginManager`. Once the plugin manager has been added to the `BackupMigrate` object it can be accessed externally by calling the `->plugins()` method. The `add()`
method can then be used to add additional plugins. Each added plugin must be given a unique ID when added. This ID will be used
to configure the plugin and to specify which source and destination are used during the operation.

    
    // ...

    // Create a Backup and Migrate Service object
    $bam = new BackupMigrate($);

	// Create a service locator
	$services = new ServiceManager();
	
	// Add necessary services
	$services->add('TempFileManager',
  		new TempFileManager(new TempFileAdapter('/tmp'))
	);
	$services->add('Logger',
		new Logger()
	);

	// Create a plugin manager
	$plugins = new PluginManager($services);

    // Add a source:
    $plugins->add('db1', new MySQLiSource());
    
    // Add some destinations
    $plugins->add('download', new BrowserDownloadDestination());
    $plugins->add('mydirectory', new DirectoryDestination());
    
    // Add some filters
    $plugins->add('compress', new CompressionFilter());
    $plugins->add('namer', new FileNamer());
    
    $bam = new BackupMigrate($plugins);

See: [Plugins](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Plugin)

### Providing Services

If the consuming application needs to use any plugins that must talk to the greater environment (saving state, emailing 
users, creating temporary files) it must provide services to Backup and Migrate that allow it to do so. These services
are contained in an object called the environment. A new environment object should be created and passed to the service
constructor. If you do not pass an environment then a basic one will be created which should work in the simplest 
environments.

Providing an environment.

    use Drupal\backup_migrate\Core\Services\BackupMigrate; 
    use MyAPP\Environment\MyEnvironment;
    
    // Create a custom environment with whatever services or configuration are needed for the application
    $env = new MyEnvironment(...);

    // Pass the environment to the service
    $bam = new BackupMigrate($env);

See: [Environment](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Environment)

### Configuring the Object

The `BackupMigrate` object does not have any configuration but the injected plugins and services may. Services should be configured before they are passed to the `ServiceManager`. Plugins can be configured when they are created and passed to the plugin manager or additional configuration can be passed in by calling `setConfig` on the plugin manager. Often combination of these techniques will be used. Base configuration is passed to the plugin when it is instantiated and run-time configuration is passed in later. 

See: [Configuration](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Config)


## Operations
The Backup and Migrate service provides two main operations:

* `backup($source_id, $destination_id)`
* `restore($source_id, $destination_id, $file_id)`

### The Backup Operation

The `backup()` operation creates a backup file from the specified source, post-processes the file with all installed 
filters and saves the file to the specified destination. The parameters for this operation are:

* **$source_id** ***(string)*** - The id of the source as specified when it is added to the plugin manager.
* **$destination_id** ***(string|array)*** - The id of the destination as specified when it is added to the plugin manager. 
This can also be an array of destination ids to send the backup to multiple destinations.

There is no return value but it may throw an exception if there is an error.

    // ...

    // Create a Backup and Migrate Service object
    $bam = new BackupMigrate($plugins);

    // Run the backup.
    $bam->backup('db1', 'mydirectory');


### The Restore Operation

The `restore()` operation loads the specified file from the specified destination, pre-processes the file with all 
installed filters and restores the data to the specified source. The parameters are:

* **$source_id** ***(string)*** - The id of the source as specified when it is added to the plugin manager.
* **$destination_id** ***(string)*** - The id of the destination as specified when it is added to the plugin manager.
* **$file_id** ***(string)*** - The id of the file within the destination. This is usually the file name but can be any 
unique string specified by the destination.


    // ...
    
    // Create a Backup and Migrate Service object
    $bam = new BackupMigrate($plugins);
        
    // Run the restore.
    $bam->restore('db1', 'mydirectory', 'backup.mysql.gz');
