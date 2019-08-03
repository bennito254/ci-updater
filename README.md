# CI Updater Library
This is a library to update custom PHP applications.

## Requirements
 - PHP 5.3+
 - ZipArchive
 
## Installation and Usage
In your update server, create a json file with the new version and the URL to the update zip
```
{"version":"0.0.2","description":"Test description","url":"https://domain.tld/update.zip"}
```
PS: The *version* and *url* are required. Any other information added to this file will be available in the stdClass object returned by Updater::check_system_updates()
PS: The file should contain a valid json data.

Edit Updater.php, set up $this->update_url to point to your update.json in your update server.

### Check for updates

```
$updater = new Updater;
$update_details = $updater->check_system_updates();
```
