<?php
use SessionApp as Data;

class TemplateLoader
{
    public function __construct($request)
    {
        $this->pathTemplate = ($request == REQUEST_ADMIN) ? PATH_ADMIN : PATH_SITE;
    }

    /**
     * @param string $viewName
     */
    public function render($viewName, $data = array(), $returnView = false)
    {
        $shareData = Data::getShareData();
        if ( !empty($shareData) ) {
            extract($shareData);
        }
        
        if ( !empty($data) ) {
            extract($data);
        }

        if ( strpos($viewName, ':') !== false) {
            $viewName = explode(':', $viewName);
            $layout = $viewName[0]; 
            $pathChildPage = str_replace('.', '/', $viewName[1]) . '.php';
        } else {
            $layout = str_replace('.', '/', $viewName);
        }
        
        $viewFullPath = $this->pathTemplate . '/views/' . $layout . '.php';
        if ( !file_exists($viewFullPath) ) {
            throw new Exception('File does not exist: ' . $viewFullPath);
        }

        if (isset($pathChildPage)) {
            ob_start();
            require_once $this->pathTemplate . '/views/' . $pathChildPage;
            $html_child_page = ob_get_contents();
            ob_end_clean();
        }
        
        ob_start();
        require_once $viewFullPath;
        $content = ob_get_contents();
        ob_end_clean();

        if ( !is_error_app() ) {
            if ($returnView) {
                return $content;
            }
            echo $content;
        }
        SessionApp::removeMSG();
    }

    /**
     * @param string $pathView
     * @param array $data
     */
    public function renderAny($pathFullView, $data = array())
    {
        $shareData = Data::getShareData();
        if ( !empty($shareData) ) {
            extract($shareData);
        }

        if ( !empty($data) ) {
            extract($data);
        }

        $pathFullView = $pathFullView . '.php';

        ob_start();
        if ( !file_exists($pathFullView) ) {
            throw new Exception('File does not exist: ' . $pathFullView);
        }
        require_once $pathFullView;
        $content = ob_get_contents();
        ob_end_clean();

        if ( !is_error_app() ) {
            echo $content;
        }
        SessionApp::removeMSG();
    }
}