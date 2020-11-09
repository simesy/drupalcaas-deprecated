# Plugins and the Plugin Manager

**Plugins** do the actual work in Backup and Migrate. **The Plugin Manager** manages the configuration of all installed plugins as well as the calling of plugins during an operation.

## Plugins ##

Plugins may be one of the following type:

* **Sources** - Items which can be backed up and restored. (e.g: A MySQL database)
* **Destinations** - Places where backup files can be stored. (e.g: A directory on your server)
* **Filters** - Actions that can be performed on backup files after backup or before restore. (e.g: Gzip compression)

While these three types of plugin are conceptually separate they are technically identical.

##### Sources #####
Each backup and restore operation works on a single source. For simplicity more than one source may be added to the BackupMigrate object. The source to be backed up is identified by id when `backup()` or `restore()` is called.

See: [Sources](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Source)

##### Destinations #####
Destinations are the places where the backup files are sent (during `backup()`) or from which they are loaded (uring `restore()`). Restore operations loads a file from a single destination. Backup operations save the backup file to 1 or more specified destinations.

See: [Destinations](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Destination)

##### Filters #####
Filters can alter backup files before `restore()` or after `backup()`. Unlike sources and destinations there can be many filters run per operation. During an operation all installed filters will run unless they are configured not to (e.g: if compression type is set to 'none' for a compression filter).

See: [Filters](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Filter)

## The Plugin Manager ##

The plugin manager is a registry that stores each of the installed plugins and configures the plugin as needed. Plugins are added to the manager with an id which is used for 2 things:

* Specifying the configuration of the plugin
* Specifying the source and destination for a backup or restore

The consuming application accesses the plugin manager only to add plugins. It may do so by adding the plugins before passing the plugin manager to the `BackupMigrate` object or by callign `plugins()` on the BackupMigrate object:

	$backup_migrate->plugins()->add('demoplugin', new MyPlugin());
	
To configure this plugin the consuming application would have a section called 'demoplugin' in the plugin manager configuration object:

	$conf = new Config([
		'demoplugin' => ['foo => 'bar']
	]);
	
	$plugins = new PluginManager(NULL, $conf);
	$backup_migrate = new BackupMigrate($plugins);
	
### Calling Plugins ###
Internally the plugin manager is used to run all plugins for a given operation. This is done using the `call()` method:

	$file = $this->plugins()->call('afterBackup', $file);
	
The call method takes 3 parameters:
	
* **Operation**: the name of the operation to call
* **Operand**: The object being operated on (optional)
* **Params**: An associative array of additional parameters

Each plugin that implements the **operation** will be called in order. The  **operand** will be passed to the plugin and will be overwritten by the return value from the plugin. In this way plugin operations are chained. A plugin is responsible for returning the operand that was passed in if it does not wish to overwrite it. The **params** array can contain additional information needed to run the operation but it cannot be modified by plugins.

### Implementing Operations ###
If a plugin wishes to be called for a given operation it simply needs to define a method with the same name as the operation. For example, to compress a backup file after it has been created, the plugin must have a method called `afterBackup()` which takes a file as the operand and returns the a new, compressed file.

#### Operation Weights ####
The order in which plugins are called cannot be guaranteed. However, if a plugin needs to run in a specific order it may specify a weight for each operation it implements. To specify a weight it must implement a `opWeight()` method which takes an operation name and returns a numerical weight. Plugins are called from lowest to highest and plugins which do not specify a weight are considered to have a weight of `0`.

To specify the weight of may operations it may be easier to extend the `\Drupal\backup_migrate\Core\Plugin\PluginBase` class and override the `supportedOps()` method which returns an array of supported operations and their weight:

	public function supportedOps() {
	    return [
	      'afterBackup'     => ['weight' => 100],
	      'beforeRestore'   => ['weight' => -100],
	    ];
	  }
	  
### Calling Other Plugins ###
Plugins can call other plugins using the Plugin Manager. For example, a source plugin might want to expose a line-item filter operation to allow other plugins to alter single values before they are added to the backup file. An encryption plugin may want to delegate the actual work of encrypting to other sub-plugins for better code organization and extendability.

By default plugins are not given access to the plugin manager. However, if a plugin implements the `\Drupal\backup_migrate\Core\Plugin\PluginCallerInterface` then the plugin manager will inject itself into the plugin for use when the plugin is prepared for use. The `\Drupal\backup_migrate\Core\Plugin\PluginCallerTrait` can be used to implement the actual requirements of the interface. Plugins with this interface and trait will be able to use `$this->plugins()` to access the plugin manager:

	class MyPlugin implements PluginCallerInterface {
		use PluginCallerTrait;
		
		function someOperation() {
			$this->plugins()->call(...);	
		}
	}

### Accessing Services ###
If a plugin requires the use of a cache, logger, state storage, mailer or any other backing service it must have the service injected into it by the plugin manger. To make a service avaible to the plugin manager it may be added to an object which implenents `ServiceManagerInterface`. That service locater may be passed to the plugin manager though the constructor or it can be passed in later using `setServiceManager()`.

Any service provided by the service locator will be injected into a plugin when it is added to the plugin manager if the name of the service matches a setter present in the plugin. For example: if a plugin has a method called `setLogger` and the service locator has a service called 'Logger' then the logger service will be injected via the `setLogger` method:

	$services = new ServiceManager();
	$services->add('Logger', new FileLogger('/path/to/log.txt'));
	
	$plugins = new PluginManager($services);
	
	// If this plugin has a `setLogger` the logger will be injected.
	$plugins->add('test', new TestPlugin());

See: [Services](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Service)
	
### Creating New Temporary Files ###
If a plugin needs to create a new temporary file (for example to decompress a backup file). It may request that the TempFileManager be injected by implementing `\Drupal\backup_migrate\Core\Plugin\FileProcessorInterface` and using the `\Drupal\backup_migrate\Core\Plugin\FileProcessorTrait`. This will allow the following:

	class MyFilePlugin implements FileProcessorInterface {
		use FileProcessorTrait;

		function someOperation($file_in) {
			$file_out = $this->getTempFileManager()->popExt($file_in);
			// ...
			
			// Return the new file and so it overwrites the old file 
			// during plugin chaining.
			return $file_out;
		}
	}
		

See: [Backup Files](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/File)
 
## Sources and Destinations ##
  
Sources and destinations are special case plugins. While they technically identical to filter plugins they are not called using the plugin manager's `call()` method. Only one source and one destination can be use for each backup or restore operation so they are called individually rather than being chained like most plugin operations. These plugin types are different by convention only and are injected and configured in the same way as filters.

See: [Sources](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Source), [Destinations](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Destination)
