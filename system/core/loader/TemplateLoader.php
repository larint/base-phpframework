<?php

class TemplateLoader
{
    private $tagNotMatch = ['media', 'keyframes', 'include'];

    public function __construct($request)
    {
        $this->request = $request;
        if ($request == REQUEST_ADMIN) {
            $this->pathView = PATH_VIEW_ADMIN;
        } else if ($request == REQUEST_SITE) {
            $this->pathView = PATH_VIEW_SITE;
        } else if ($request == REQUEST_SYSTEM) {
            $this->pathView = PATH_VIEW_SYSTEM;
        }
    }

    /**
     * @param string $viewName
     */
    public function render($viewName, $data = array(), $return = false)
    {
        $childPath = str_replace('.', '/', $viewName);
        $pathFullView = $this->pathView . "/$childPath.php";
        $pageHtml = $this->buildHtmlContent($pathFullView, $data);
        if ( !is_error_app() ) {
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
        if ( !is_error_app() ) {
            if ($return) {
                return $pageHtml;
            }
            echo $pageHtml;
        }
    }

    private function buildHtmlContent($pathFullView, $data = array()) {
        if (!file_exists($pathFullView)) {
            throw new Exception("File template does not exist: $pathFullView");
        }

        $shareData = SessionApp::getShareData();
        if ( !empty($shareData) ) {
            extract($shareData);
        }
        
        if ( !empty($data) ) {
            extract($data);
        }
        
        ob_start();
        require_once $pathFullView;
        $childPage = ob_get_contents();
        ob_end_clean();

        $layoutPath = $this->filterLayoutExtend($childPage);
        $layoutPath = $this->pathView . "/$layoutPath.php";
        $layoutPage = '';
        if (file_exists($layoutPath) ) {
            ob_start();
            require_once $layoutPath;
            $layoutPage = ob_get_contents();
            ob_end_clean();
        }

        // filter tag
        $pageHtml = $this->renderIncludeTag($layoutPage);
        $pageHtml = $this->renderSectionTag($pageHtml, $childPage);
        // rerender include if subpgae has @include tag
        $pageHtml = $this->renderIncludeTag($pageHtml);

        SessionApp::removeMSG();
        return $pageHtml;
    }

    private function filterLayoutExtend(&$childPage) {
        preg_match("/@extend (.*)/i", $childPage, $matchTag);
        if (isset($matchTag[1])) {
            $layoutPath = str_replace('.', '/', $matchTag[1]);
            $tag = $matchTag[0];
            $childPage = str_replace($tag, '', $childPage);
            return $layoutPath;
        }
        return '';
    }

    private function renderSectionTag($layoutPage, $childPage)
    {
        $childPage = str_replace('@csrf_field', csrf_field(), $childPage);
        preg_match_all("/@end_(.*)/i", $childPage, $matchTagName);
        if (isset($matchTagName[1])) {
            $matchTagNameUniq = array_unique(array_map(function($v) {
                return trim($v);
            }, $matchTagName[1]));

            foreach ($matchTagNameUniq as $tag) {
                preg_match_all("/@$tag(((?!@$tag).|\n)*)@end_$tag/i", $childPage, $matchBlockContent);

                $blockContent = '';
                foreach ($matchBlockContent[1] as $v) {
                    $blockContent .= $v;
                }
                $layoutPage = str_replace("@$tag", $blockContent, $layoutPage);
            }
        }

        // check for undeclared tags in subpages
        $tagNotMatchPattern = implode('|', $this->tagNotMatch);
        preg_match_all("/@(((?!($tagNotMatchPattern)).)*)/i", $layoutPage, $matchExistTag);
        preg_match_all("/value\=\"(.*)@(.*)/i", $layoutPage, $matchInputValue);

        // check input has @ email
        $matchExistTag = array_unique(array_filter($matchExistTag[0], function($v) use ($matchInputValue) {
            return !empty($v) && $v != '@' && !in_array(str_replace('@', '', $v), $matchInputValue[2]);
        }));

        if (count($matchExistTag) > 0) {
            $layoutPage = str_replace($matchExistTag, array_fill(0, count($matchExistTag), ''), $layoutPage);
        }
        
        return !empty($layoutPage) ? $layoutPage : $childPage;
    }

    private function renderIncludeTag($layoutPage)
    {
        preg_match_all("/@include (.*)/i", $layoutPage, $match);
        if (isset($match[1][0])) {
            for ($i = 0; $i < count($match[1]); $i++) {  
                $tag = $match[0][$i];
                $path = $match[1][$i];
                $pathTemplate = str_replace('.', '/', $path);
                $pathTemplate = $this->pathView . "/$pathTemplate.php";
                if (!file_exists($pathTemplate)) {
                    throw new Exception("File template does not exist: $pathTemplate");
                }
                ob_start();
                require $pathTemplate;
                $page = ob_get_contents();
                ob_end_clean();
                $layoutPage = str_replace($tag, $page, $layoutPage);
            }
        }
        return $layoutPage;
    }
}