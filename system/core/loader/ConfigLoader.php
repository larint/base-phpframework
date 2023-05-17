<?php

class ConfigLoader
{
    public function __construct($request)
    {
        $this->request = $request;
        $this->pathConfig = ($request == REQUEST_ADMIN) ? PATH_ADMIN : PATH_WEB;
    }

    /**
     * @param bool $loadAll
     * @param string $configName
     */
    public function load($loadAll, $configName = null)
    {
        // if controler in  system then noneed load config
        if ($this->request == REQUEST_SYSTEM) {
            return true;
        }
        if ($loadAll) {
            $files = glob($this->pathConfig . "/config/{,*/,*/*/,*/*/*/}*.php", GLOB_BRACE);
            foreach ($files as $fileName) {
                $config = include $fileName;
                if (!is_array($config)) {
                    throw new Exception("Config file format must return as array.");
                }
                if (!empty($config)) {
                    $pref = basename($fileName, '.php');
                    $configArr = array();
                    $this->buildNonNestedRecursive($configArr, "$pref.", $config);
                    foreach ($configArr as $key => $item) {
                        $this->{$key} = $item;
                    }
                }
            }
            return true;
        } else {
            $configFullPath = $this->pathConfig . "/config/$configName.php";
            if (!file_exists($configFullPath)) {
                throw new Exception("File does not exist: $configFullPath");
            }

            $configName = strtolower($configName);
            $config = include $configFullPath;
            if (!is_array($config)) {
                throw new Exception("Config file format must return as array.");
            }
            if (!empty($config)) {
                $configArr = array();
                $this->buildNonNestedRecursive($configArr, "$configName.", $config);
                foreach ($configArr as $key => $item) {
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
        return isset($this->{$key}) ? $this->{$key} : $defaulVal;
    }

    private function buildNonNestedRecursive(array &$out, $key, array $in, $split = '.')
    {
        foreach($in as $k => $v) {
            if(is_array($v)) {
                $this->buildNonNestedRecursive($out, "$key$k$split", $v, $split);
            } else {
                $out[$key . $k] = $v;
            }
        }
    }

}