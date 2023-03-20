<?php
/**
 * 404Controller
 * @filesource apps/site/controllers/404Controller.php
 */
class ErrorController extends BaseController
{
	public function index()
    {
        $this->view->renderAny(page404);
    }
}