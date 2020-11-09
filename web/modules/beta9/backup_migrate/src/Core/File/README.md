# Backup Files

Backup files are objects that represent a single backup. They are what is created during a backup operation and restored from during a restore. They do not need to necessarily represent an actual file on disk on the local system as long as they implement one or more of the following interfaces:

	\Drupal\backup_migrate\Core\File\BackupFileInterface
	\Drupal\backup_migrate\Core\File\BackupFileReadableInterface
	\Drupal\backup_migrate\Core\File\BackupFileWritableInterface
	
The latter 2 interfaces extend the `BackupFileInterface`.

A single backup file may be represented by more than one object during the lifecycle of an operation and may transition from one to another depending on the needs of the plugins operating on the file.

Files should be considered essentially immutable once written. A plugin that wishes to alter a file should create a new file object and copy the data from the old file to the new one. This helps maintain the 'chain' nature of plugin calling (see [Plugins](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Plugin)).

### BackupFileInterface
This is the most basic representation of a backup file. An object extending just this interface contains only metadata about the backup. The data contained in the file cannot be read from this object nor can it be written to. This type is lightweight and may be returned by a destination in response to a `listFiles` or `getFile` call. This allows remote destinations (such as FTP, or Amazon S3) to return file metadata without having to load the file contents until it is requested. It is also the they type returned by `getFile()` on a destination. 

### BackupFileReadableInterface
This subclass of the `BackupFileInterface` can also be read from. This allows the file contents to be used to restore a source. To turn a `BackupFileInterface` object into a readable file you must call `loadFileForReading()` on the destination that was used get the original file object:

	$destination = new DirectoryDestination(['directory' => '~/mybackups']);
	$file = $destination->getFile('databse.mysql');

	// This object has metadata but the contents cannot neccessarily be read.
	if ($file && $file->getMeta('filesize') > 1000) {

		// To read the file we must allow the destination to load it for us if needed.
		$file = $destination->loadFileForReading($file);

		// The file contents should now be available to us.
		if ($file) {
			echo $file->readAll();		
		}
	}
	
### BackupFileWriteableInterface
This subclass can be read from AND written to. Writable files in Backup and Migrate are always temporary files and must be created by the TempFileManager. Source plugins will create an empty temporary file to write the backup to while file filter plugins (like compression or encryption filters) will create a new temporary file and copy the contents from the input file to the new output file. The file that results at the end of the plugin chain will either be used to restore to the source (restore operation) or sent to a destination to be persisted (backup operation). Because plugins are responsible for creating new temporary writable files as needed, they should never require a writable file as input or promise one as a return value.

## The Temporary File Manager
All writable files must be created by the Temporary File Manager. This class can create a new blank file with a given file extension. The standard flow of file filters is a chain where one filter hands a file to the next which copies the data to a new file and hands that on. For example, the MySQL source generates a new database dump file which gets handed to an encryption filter which copies the metadata to a new file containing the encrypted data. That file is then passed to a compression filter which creates a new compressed version of the file which is finally handed off to a destination for saving. At each step along the way a new file is created with an a new extension appended to the end:

	file.mysql -> file.mysql.aes -> file.mysql.aes.gz
	
To facilitate this the Temporary File Manager takes care of the details of copying file metadata and provisioning a new temporary file with the new file extension to write the modified data to. A compressor plugin might do something like this:

	function afterBackup($file_in) {
		// Get a new file with '.gz' added to the end of the filename.
		$file_out = $this->getTempFileManager()->pushExt($file_in, 'gz');
		if ($this->doCompress($file_in, $file_out)) {
			return $file_out;		
		}
		// Compression failed, return the original
		return $file_in;
	}	

Similarly `$this->getTempFileManager()->popExt()` will pull the last item from the file extension and return a blank file for decompression prior to import.

See [Plugins](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Plugin) for details on how to make the Temporary File Manager accessible within a plugin.

### The Temporary File Adapter ###
While the file manager takes care of the metadata of temporary files, it cannot provision actual on-disk files to write to. That is because that operation will be different depending on where the code is run and is therefore the responsibility of the [Environment](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Environment) object. The environment provides a service called called the Temporary File Adaptor (an object whose class which implements `\Drupal\backup_migrate\Core\Services\TempFileAdapterInterface`). The job of this class is to provision actual temporary files in the host operating system that can be written to and read from. That service is also responsible for tracking all of the files that have been created during the running of an operation and deleting those files when the operation completes. Backup and Migrate core comes with a basic adapter which accepts any writable directory as an argument and creates new temporary files within that directory. This implementation should suffice for most consuming software but can be replaced with another adapter if needed.

See: [Environment](https://github.com/backupmigrate/backup_migrate_core/tree/master/src/Environment)
