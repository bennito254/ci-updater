# CI Updater Library
This is a very simple PHP script library to check updates and install them for your custom PHP applications.

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
print_r($update_details);
```
The above code will print out an object instance of the update.json details

### Install Updates
To install updates, pass `TRUE` to the `check_system_updates()` method, or use the `install_update()` method.
```
$updater->install_update(); //Installs the available update
// or
// $updater->check_system_updates(TRUE);
```

### Run Custom Code after update
To execute custom code after update to maybe update the database, create a `setup.php` file at the root of your update.zip.
NB: This Updater.php script does not execute any methods/functions in the `setup.php` automatically.
Example setup.php
```
//Save as setup.php in the root directory of your update.zip
function show\_message() {
    echo "This is an example.";
}

show\_message();
```
## Contribution
Contributions are highly welcome.
