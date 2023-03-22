<?php

class TemplateLoader
{
    private $tagNotMatch = ['media', 'keyframes', 'include'];

    public function __construct($request)
    {
        $this->pathView = ($request == REQUEST_ADMIN) ? PATH_ADMIN : PATH_SITE;
    }

    /**
     * @param string $viewName
     */
    public function render($viewName, $data = array(), $returnView = false)
    {
        $shareData = SessionApp::getShareData();
        if ( !empty($shareData) ) {
            extract($shareData);
        }
        
        if ( !empty($data) ) {
            extract($data);
        }

        $childPath = str_replace('.', '/', $viewName);
        $fullChildPath = $this->pathView . "/views/$childPath.php";
        if (!file_exists($fullChildPath)) {
            throw new Exception("File template does not exist: $fullChildPath");
        }

        ob_start();
        require_once $fullChildPath;
        $childPage = ob_get_contents();
        ob_end_clean();

        $layoutPath = $this->filterLayoutExtend($childPage, $fullChildPath);
        $layoutPath = $this->pathView . "/views/$layoutPath.php";
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

        if ( !is_error_app() ) {
            if ($returnView) {
                return $pageHtml;
            }
            echo $pageHtml;
        }
        SessionApp::removeMSG();
    }

    private function filterLayoutExtend(&$childPage, $fullChildPath) {
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
        
        $matchExistTag = array_unique(array_filter($matchExistTag[0], function($v) {
            return !empty($v) && $v != '@';
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
                $pathTemplate = $this->pathView . "/views/$pathTemplate.php";
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

    /**
     * @param string $pathView
     * @param array $data
     */
    public function renderAny($pathFullView, $data = array())
    {
        $shareData = SessionApp::getShareData();
        if ( !empty($shareData) ) {
            extract($shareData);
        }

        if ( !empty($data) ) {
            extract($data);
        }

        $pathFullView = "$pathFullView.php";

        ob_start();
        if ( !file_exists($pathFullView) ) {
            throw new Exception("File does not exist: $pathFullView");
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