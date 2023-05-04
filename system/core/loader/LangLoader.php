<?php

class LangLoader
{
    public function __construct()
    {
        
    }

    /**
	 * @param string $loadAll
	 * @param string $helperName
	 */
    public function load()
    {
    	$lang = $_SESSION['lang'];
		$langSystem = glob(PATH_SYSTEM . "/lang/$lang/*.json");
		$langApp = glob(PATH_APP . "/vendor/lang/$lang/*.json");
		$files = array_merge($langApp, $langSystem);
		$langData = [];
		foreach ($files as $fileName) {
			$kfile = basename($fileName, '.json');
			$json = file_get_contents($fileName);
			if ( array_key_exists($kfile, $langData) ) {
				$langData[$kfile] = array_merge($langData[$kfile], json_decode($json, true));
			} else {
				$langData[$kfile] = json_decode($json, true);
			}
			
		}
		$GLOBALS['lang'] = $langData;
    }
}