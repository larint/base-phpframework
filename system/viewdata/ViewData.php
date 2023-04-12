<?php
use SessionApp as Data;

class ViewData
{

    public function __construct($request) {
        $this->request = $request;
    }

    protected function passData($viewsName = array(), $data = array())
    {
        foreach ($viewsName as $v) {
            $oldDataSet = $this->getData($v);
            $dataSet = $oldDataSet ? array_merge($oldDataSet, $data) : $data;
            Data::set($this->request . ".$v", $dataSet);
        }
    }

    public function getData($viewName)
    {
        return Data::get($this->request .'.'. $viewName);
    }

    public function removeData($viewName)
    {
        return Data::remove($this->request .'.'. $viewName);
    }

}