<?php

class TemplateLoader
{
    private $tagNotMatch = ['media', 'keyframes', 'include'];
    public function __construct($request)
    {
        $this->request = $request;
        if ($request == REQUEST_ADMIN) {
            $this->pathView = PATH_VIEW_ADMIN;
        } elseif ($request == REQUEST_SITE) {
            $this->pathView = PATH_VIEW_SITE;
        } elseif ($request == REQUEST_SYSTEM) {
            $this->pathView = PATH_VIEW_SYSTEM;
        }

        if ($request != REQUEST_SYSTEM) {
            $this->viewData = new ViewData($request);
        }
    }

    /**
     * @param string $viewName
     */
    public function render($viewName, $data = array(), $return = false)
    {
        $childPath = str_replace('.', '/', $viewName);
        $pathFullView = $this->pathView . "/$childPath.php";
        $pageHtml = $this->buildHtmlContent($pathFullView, $data, $viewName);
        if (!is_error_app()) {
            if ($return) {
                return $pageHtml;
            }
            echo $pageHtml;
        }
    }

    /**
     * @param string $pathView
     * @param array $data
     */
    public function renderAny($pathFullView, $data = array(), $return = false)
    {
        $pageHtml = $this->buildHtmlContent($pathFullView, $data);
        if (!is_error_app()) {
            if ($return) {
                return $pageHtml;
            }
            echo $pageHtml;
        }
    }

    private function buildHtmlContent($pathFullView, $data = array(), $viewName = '')
    {
        if (!file_exists($pathFullView)) {
            throw new Exception("File template does not exist: $pathFullView");
        }

        if (!empty($data)) {
            extract($data);
        }

        // extract data to views
        $shareData = isset($this->viewData) ? $this->viewData->getData($viewName) : null;
        if (!empty($shareData)) {
            extract($shareData);
        }

        ob_start();
        require_once $pathFullView;
        $childPage = ob_get_contents();
        ob_end_clean();

        $layoutPath = $this->filterLayoutExtend($childPage);
        $layoutPath = $this->pathView . "/$layoutPath.php";
        $layoutPage = '';
        if (file_exists($layoutPath)) {
            ob_start();
            require_once $layoutPath;
            $layoutPage = ob_get_contents();
            ob_end_clean();
        }

        // filter include tag
        $layoutPage = $this->renderIncludeTag($layoutPage, $shareData);
        // rerender include if subpgae has @include tag
        $childPage = $this->renderIncludeTag($childPage, $shareData);
        $pageHtml = $this->renderSectionTag($layoutPage, $childPage);
        $pageHtml = $this->renderAssetTag($pageHtml);

        // remove data in views
        if (!empty($shareData)) {
            $this->viewData->removeData($viewName);
        }

        SessionApp::removeMSG();
        return $pageHtml;
    }

    private function filterLayoutExtend(&$childPage)
    {
        preg_match("/@extend (.*)\r/i", $childPage, $matchTag);
        preg_match("/@extend (.*)/i", $childPage, $matchTag);
        if (isset($matchTag[1])) {
            $layoutPath = str_replace('.', '/', $matchTag[1]);
            $tag = $matchTag[0];
            $childPage = str_replace($tag, '', $childPage);
            return $layoutPath;
        }
        return '';
    }


    private function renderAssetTag($pageHtml)
    {
        preg_match_all("/@asset (.*)\"/i", $pageHtml, $matchs);
        if (isset($matchs[1])) {
            $links = array_map(function ($item) {
                return asset(trim($item), false, true). '"';
            }, $matchs[1]);
            $pageHtml = str_replace($matchs[0], $links, $pageHtml);
        }
        return $pageHtml;
    }

    private function renderSectionTag($layoutPage, $childPage)
    {
        $childPage = str_replace('@csrf_field', csrf_field(), $childPage);

        ob_start();
        require_once PATH_VIEW_SYSTEM . "/error/message.php";
        $errorTag = ob_get_contents();
        ob_end_clean();
        $childPage = str_replace('@error', $errorTag, $childPage);

        preg_match_all("/@end_(.*)/i", $childPage, $matchTagName);
        if (isset($matchTagName[1])) {
            $matchTagNameUniq = array_unique(array_map(function ($v) {
                return trim($v);
            }, $matchTagName[1]));

            foreach ($matchTagNameUniq as $tag) {
                preg_match_all("/@$tag(.*)@end_$tag/is", $childPage, $matchBlockContent);
                $blockContent = '';
                foreach ($matchBlockContent[1] as $v) {
                    $blockContent .= $v;
                }
                $layoutPage = str_replace("@$tag", $blockContent, $layoutPage);
            }
        }

        // check for undeclared tags in subpages
        $tagNotMatchPattern = implode('|', $this->tagNotMatch);
        preg_match_all("/[ *]@(((?!($tagNotMatchPattern)).)*)/i", $layoutPage, $matchExistTag);
        $matchExistTag = array_unique(array_filter($matchExistTag[0], function ($v) {
            return !empty($v) && trim($v)!= '@';
        }));


        if (count($matchExistTag) > 0) {
            $layoutPage = str_replace($matchExistTag, array_fill(0, count($matchExistTag), ''), $layoutPage);
        }

        return !empty($layoutPage) ? $layoutPage : $childPage;
    }

    private function renderIncludeTag($layoutPage, $shareData = array())
    {
        preg_match_all("/@include (.*)\r/i", $layoutPage, $matchTag);
        if (!isset($matchTag[1][0])) {
            preg_match_all("/@include (.*)/i", $layoutPage, $matchTag);
        }
        if (isset($matchTag[1][0])) {
            if (!empty($shareData)) {
                extract($shareData);
            }
            for ($i = 0; $i < count($matchTag[1]); $i++) {
                $tag = $matchTag[0][$i];
                $path = $matchTag[1][$i];
                $pathTemplate = trim(str_replace('.', '/', $path));
                $pathTemplate = $this->pathView . "/$pathTemplate.php";
                if (!file_exists($pathTemplate)) {
                    throw new Exception("File template does not exist: $pathTemplate");
                }
                ob_start();
                require_once $pathTemplate;
                $page = ob_get_contents();
                ob_end_clean();
                $layoutPage = str_replace($tag, $page, $layoutPage);
            }
        }
        return $layoutPage;
    }
}
