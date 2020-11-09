# Configuration

Backup and Migrate core is configured by the consuming software when the library is instantiated using a `\Drupal\backup_migrate\Core\Config\ConfigInterface` object. This object is a simple key-value store which should contain the configuration for each of the available plugins (sources, destinations and filters). Each plugin should have it's own entry in the config object which contains an array of all of the configuration for that item. The key for this entry must be the same as the key assigned to the plugin when it is added to the `BackupMigrate` object using `->plugins()->add()`.

Any object that implements the `\Drupal\backup_migrate\Core\Config\ConfigInterface` may be used to configure Backup and Migrate. For example, a consuming application may want to implement a class that directly accesses the application's persistence layer to retrieve configuration values. In many cases, however the simple default `\Drupal\backup_migrate\Core\Config\Config` will suffice.

## The Config Class
The built in `\Drupal\backup_migrate\Core\Config\Config` is a simple implementation of the configuration interface which can be instantiated using a PHP associative array:

    <?php

    use Drupal\backup_migrate\Core\Filter\CompressionFilter;
    use Drupal\backup_migrate\Core\Source\MySQLiSource;
    use Drupal\backup_migrate\Core\Config\Config;

    $config = new Config(
        [
            // Add configuration for the 'db' source.
          'database1' => [
            'host' => '127.0.0.1',
            'database' => 'mydb',
            'user' => 'myuser',
            'password' => 'mypass',
            'port' => '8889',
          ],
              // Configure the compression filter.
          'compressor' => [
            'compression' => 'gzip',
          ],
              // Add more filter, source and destination configuration.
        ]
    );

    $plugins = new PluginManager();

    // Add the database source. This will read the configuration with the same key ('database1')
    plugins->add(
        'database1',
        new MySQLiSource()
    );
    // Add the compression plugin.
    plugins->add(
        'compressor',
        new CompressionFilter()
    );
    // Add more filters and a destination.
    ...


    // Create a new Backup and Migrate object with this configuration.
    $bam = new BackupMigrate($plugins);

    $bam->backup('database1', 'somedestination');

    // Initial Config vs. Run-time Config ##.
    A plugin may have two types of configuration: initial configuration, added when the plugin is created, and run - time configuration, added later by the plugin manager . Initial configuration can be overriden by run - time configuration but it cannot be overwritten by run - time config . That means that you can reconfigure plugins after the plugin manager has been created but the initial configuration will not be permanently overwriten .

    An example that illustrates the difference is a database source plugin . The database connection information should not change per operation and should be considered initial configuration . The list of tables to exclude during a backup, or whether the tables should be locked during a restore may change from run to run and should be run - time configuration .

    To specify initial configuration pass it to the plugin's constructor:

	// The db credentials are passed in to the constructor and are permanent.
	$plugins->add(
		'main_database',
		new MySQLiSource(new Config([
			'database' => '...',
			'username' => '...',
			...
		])
	);
	
	// Setting this configuration will not overwrite the db credentials.
	$plugins->setConfig(new Config([
		'main_database' => [
			'exclude_tables' => [...],
    ]);

