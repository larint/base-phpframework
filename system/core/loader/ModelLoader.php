<?php

class ModelLoader
{

    public function __construct($request)
    {
        $this->request = $request;
        $this->pathModel = ($request == REQUEST_ADMIN) ? PATH_ADMIN : PATH_SITE;
    }

    public function setShareData() {
        include_once PATH_SYSTEM . '/share/ShareData.php';
        $this->shareData = new ShareData();
    }

    public function instanceShareData() {
        return $this->shareData;
    }

    public function load($loadAll, $modelName = null) {
        // if controler in system then noneed load model
		if ($this->request == REQUEST_SYSTEM) return true;

        if (DB_CONNECTION == 'mysql') {
            include_once PATH_SYSTEM . '/core/crud/CRUDMysql.php';
        } else if (DB_CONNECTION == 'sqlserver') {
            include_once PATH_SYSTEM . '/core/crud/CRUDSqlServer.php';
        } else {
            throw new Exception("DB connection does not exist: " . DB_CONNECTION);
        }
        include_once PATH_SYSTEM . '/core/crud/Model.php';
    	if ( $loadAll ) {
    		$files = glob($this->pathModel . '/models/*.php');

    		foreach ($files as $fileName) {
    			$objName = basename($fileName, '.php');
    			include_once $fileName;
    		}

            $this->setShareData();
    		return true;
    	} else {
    		$modelFullPath = $this->pathModel . "/models/$modelName.php";
            if ( !file_exists($modelFullPath) ) {
                throw new Exception("File does not exist: $modelFullPath");
            }
			include_once $modelFullPath;

            $this->setShareData();
			return true;
    	}
        return false;
    }
}