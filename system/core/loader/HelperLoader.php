<?php

class HelperLoader
{
    public function __construct()
    {
        
    }

    /**
	 * @param string $loadAll
	 * @param string $helperName
	 */
    public function load($loadAll, $helperName = null)
    {
    	if ( $loadAll ) {
    		$files = glob(PATH_SYSTEM . '/helper/*.php');

    		foreach ($files as $fileName) {
    			include_once $fileName;
    		}
    		return true;
    	} else {
            $helperFullPath = PATH_SYSTEM . '/helper/' . $helperName . '.php';
            if ( !file_exists($helperFullPath) ){
                throw new Exception('File does not exist: ' . $helperFullPath);
            }
            
			include_once $helperFullPath;
			return true;
    	}
        return false;
    }
}