<?php

class LibraryLoader
{
	public function __construct()
	{

	}

    /**
	 * @param string $loadAll
	 * @param string $helperName
	 */
    public function load($loadAll, $libraryName = null)
    {
    	if ( $loadAll ) {
    		$files = glob(PATH_SYSTEM . '/library/*.php');

    		foreach ($files as $fileName) {
    			$objName = basename($fileName, '.php');
    			if ( empty($this->{$objName}) ){
    				include_once $fileName;
    				$this->{$objName} = new $objName;
    			}
    		}
    		return true;
    	} else {
            $libraryFullPath = PATH_SYSTEM . '/library/' . $libraryName . '.php';
            if ( !file_exists($libraryFullPath) ){
                throw new Exception('File does not exist: ' . $libraryFullPath);
            }

    		$objName = $libraryName;
            if ( empty($this->{$objName}) ) {
    			include_once $libraryFullPath;
    			$this->{$objName} = new $objName;
    			return true;
            }
    		
    	}
        return false;
    }
}