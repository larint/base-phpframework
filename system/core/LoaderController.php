<?php

abstract class LoaderController
{
	protected $view     = NULL;
	protected $config   = NULL;

    public function __construct( $request )
    {
    	require_once PATH_SYSTEM . '/core/loader/ConfigLoader.php';
    	$this->config = new ConfigLoader($request);
    	$this->config->load(true);

        require_once PATH_SYSTEM . '/core/loader/HelperLoader.php';
        $helper = new HelperLoader();
        $helper->load(true);

        require_once PATH_SYSTEM . '/core/loader/ModelLoader.php';
        $this->model = new ModelLoader($request);
        $this->model->load(true);

        require_once PATH_SYSTEM . '/core/loader/TemplateLoader.php';
        $this->view = new TemplateLoader($request);
    }
}