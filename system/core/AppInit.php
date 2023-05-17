<?php

abstract class AppInit
{
    public function __construct($request)
    {
        require_once PATH_SYSTEM . '/core/loader/LangLoader.php';
        $lang = new LangLoader();
        $lang->load();

        require_once PATH_SYSTEM . '/core/loader/ConfigLoader.php';
        $this->config = new ConfigLoader($request);
        $this->config->load(true);

        require_once PATH_SYSTEM . '/core/loader/HelperLoader.php';
        $helper = new HelperLoader();
        $helper->load(true);

        require_once PATH_SYSTEM . '/core/loader/ModelLoader.php';
        $model = new ModelLoader($request);
        $model->load(true);
        $this->db = new Model();

        require_once PATH_SYSTEM . '/core/loader/MiddlewareLoader.php';
        $middleware = new MiddlewareLoader($request);
        $middleware->load();

        require_once PATH_SYSTEM . '/core/loader/TemplateLoader.php';
        $this->view = new TemplateLoader($request);
    }

}
