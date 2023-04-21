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
		$files = glob(PATH_SYSTEM . "/lang/$lang/*.json");
		$langData = [];
		foreach ($files as $fileName) {
			$kfile = basename($fileName, '.json');
			$json = file_get_contents($fileName);
			$langData[$kfile] = json_decode($json, true);
		}
		$GLOBALS['lang'] = $langData;
    }
}