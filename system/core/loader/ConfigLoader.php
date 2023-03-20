<?php

class ConfigLoader
{
	public function __construct($request)
	{
        $this->pathConfig = ($request == REQUEST_ADMIN) ? PATH_ADMIN : PATH_SITE;
	}

    /**
     * @param bool $loadAll
     * @param string $configName 
     */
    public function load($loadAll, $configName = null) {

    	if ( $loadAll ) {
    		$files = glob($this->pathConfig . '/config/*.php');

    		foreach ($files as $fileName) {
				$config = include $fileName;
				if ( !empty($config) ){
					foreach ($config as $key => $item){
						$this->{$key} = $item;
					}
				}
    		}
    		return true;
    	} else {
            $configFullPath = $this->pathConfig . '/config/' . $configName . '.php';
            if ( !file_exists($configFullPath) ){
                throw new Exception('File does not exist: ' . $configFullPath);
            }

    		$configName = strtolower($configName);
			$config = include $configFullPath;
			if ( !empty($config) ){
				foreach ($config as $key => $item){
					$this->{$key} = $item;
				}
				return true;
			}
    	}
        return false;
    }

    /**
     * @param string $key
     * @param string $defaulVal
	 */
    public function key($key, $defaulVal = null)
    {
    	return isset($this->config[$key]) ? $this->config[$key] : $defaulVal;
    }
    
}