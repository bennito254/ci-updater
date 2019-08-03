<?php
/**
 * Created By: Ben Muriithi <bennito254@gmail.com>
 * Version 0.0.1
 * URL https://bennito254.com
 * 
 */
class Updater {
    
    private $update_url;
    private $update_details;
    public $error;
    
    public function __construct(){
        //URL to check update json from
        $this->update_url = 'https://domain.tld/update.json';
        
        //Base directory to start apllying updates
        $this->FCPATH = dirname(__FILE__);
    }
    
    /**
     * Check for updates
     * 
     * @var $install        TRUE if you want to install update, FALSE for otherwise
     * 
     * @return  mixed       Return installation status if we are installing, return update details if not installing
     */
    public function check_system_updates($install = FALSE) {
        $details = $this->_check_update(VERSION, $this->update_url);
        
        if($install === TRUE){
            $this->update_details = $details;
            
            return $this->_install_system_updates();
            
        }
        
        return $details;
    }
    
    /**
     * Fetch update details
     * 
     * @var $current_version    Current version
     * @var $update_url         URL to check update from
     * 
     * @return mixed            Return update details as a StdClass object or FALSE if update not available
     */
    private function _check_update ($current_version = FALSE, $update_url = FALSE) {
        if ($current_version) {
            $update_url = $update_url ? $update_url : $this->update_url;
            
            if ($update_details = file_get_contents($update_url)) {
                $update_details = json_decode($update_details);
                
                //By default, version_compare() returns -1 if the first version is lower than the second, 0 if they are equal, and 1 if the second is lower. 
                if (version_compare($current_version, $update_details->version, '<')) {
                    return $update_details;
                }
            }
        }
        return FALSE;
    }
    
    private function _install_system_updates() {
        if (is_array($this->update_details)) {
            $details = $this->update_details;
        } else {
            $details = $this->check_system_updates();
        }
        
        $temp_dir = realpath(sys_get_temp_dir());
        $timestamp = time();
        $temp_dir = $temp_dir.'/'.$timestamp;
        
        if (mkdir($temp_dir, 0777)) {
            $update_zip = $temp_dir.'/'.$timestamp.'.zip';
            
            echo $update_zip;
            
            if(file_put_contents($update_zip, file_get_contents($details->url))){
                if (file_exists($update_zip)) {
                    //Unzip to tmp dir
                    $unzipper = new ZipArchive;
                    if ($unzipper->open($update_zip) === TRUE) {
                        
                        $unzipper->extractTo($temp_dir.'/'.$timestamp.'/');
                        $unzipper->close();
                        
                        $files_and_folders = $this->_scan($temp_dir.'/'.$timestamp.'/');
                        
                        //print_r($files_and_folders);
                        $sanity_check_success = TRUE;
                        foreach ($files_and_folders as $file_or_folder) {
                            //strip temp path from the file link
                            $file = explode($temp_dir.'/'.$timestamp.'/', $file_or_folder)[1];
                            
                            //The following check may cause problems because we are only checking if directories are writable,
                            // which at the moment (3/08/2019) I assumed would work
                            if (file_exists($this->FCPATH.$file) || is_writable($this->FCPATH.$file)) {
                                
                            }else{
                                $this->error = "Permissions inconsistency will cause problems: Cannot write to ".$this->FCPATH.$file;
                                
                                $sanity_check_success = FALSE;
                                break;
                            }
                        }
                        
                        if ($sanity_check_success) { //Passed. Update
                            $is_update_ok = TRUE;
                            foreach ($files_and_folders as $file_or_folder) {
                                //strip temp path from the file link
                                $file = explode($temp_dir.'/'.$timestamp.'/', $file_or_folder)[1];
                                
                                if ( file_exists($this->FCPATH.$file) && is_dir($this->FCPATH.$file) ) {
                                    //Skip replacing directories
                                } else {
                                    @chmod($this->FCPATH.$file, 0777);
                                    if(copy($file_or_folder, $this->FCPATH.$file)){
                                        
                                    } else {
                                        $is_update_ok = FALSE;
                                    }
                                    @chmod($this->FCPATH.$file, 0755);
                                }
                            }
                            
                            if($is_update_ok){
                                return TRUE;
                            }
                            $this->error = "I might have fucked up! Some file(s) failed to update.";
                            return FALSE;
                            
                        } else {
                            $this->error = "Sanity checks failed. Check error log";
                        }
                        
                        return TRUE;
                    } else {
                        $this->error = "Failed to open ZIP Archive";
                    }
                } else {
                    $this->error = "Update ZIP file does not exist!";
                }
            } else {
                $this->error = "Failed to save update ZIP file in ".$update_zip;
            }
            
        } else {
            $this->error = "Temporary folder <code>".realpath(sys_get_temp_dir())."</code> is not writable";
        }
    }
    
    /**
     * Fetch files in folders recursively
     * 
     * @var $dir    Base directory to start scanning from
     * 
     * return $array    An array of found files and folder
     */
	private function _scan($dir = FALSE, &$results = array()) {
        
        $files = scandir($dir);

        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                $results[] = $path;
            } else if($value != "." && $value != "..") {
                $this->_scan($path, $results);
                $results[] = $path;
            }
        }

        return array_reverse($results);
	}
}
?>
