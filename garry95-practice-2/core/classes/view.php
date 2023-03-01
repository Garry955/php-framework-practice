<?php
namespace core\classes;

class view
{

    /**
     *
     * @var boolean
     */
    private $disabled = false;

    /**
     *
     * @var \DOMDocument
     */
    private $css = null;

    /**
     *
     * @var \DOMDocument
     */
    private $js = null;

    public function toggleRender($disable = null)
    {
        if ($disable === null) {
            $disabled = ! $this->disabled;
        }
        $this->disabled = (boolean) $disabled;
    }

    public function isDisabled()
    {
        return $this->disabled;
    }

    public function render($viewFile, $viewVars = [])
    {
        if ($this->disabled) {
            return null;
        }
        $viewFileCheck = explode(".", $viewFile);
        if (! isset($viewFileCheck[1])) {
            $viewFile .= ".phtml";
        }
        $viewFile = str_replace("::", "/", $viewFile);
        chdir($_SERVER["DOCUMENT_ROOT"]);
        $path = realpath($GLOBALS["config"]["path"]["app"] . "/views/" . $viewFile);

        if (! $path) {
            return false;
        }
        $self = $this;
        extract($viewVars);
        require $path;
    }

    private function createObject($content, $inline, $type, $attr)
    {
        if ($type == "css") {
            if ($inline) {
                $name = "style";
            } else {
                $name = "link";
                $attr["rel"] = "stylesheet";
                $attr["href"] = $content;
            }
            $attr["type"] = "text/css";
        } else {
            $name = "script";
            $attr["type"] = "text/javascript";
            if (! $inline) {
                $attr["src"] = $content;
            }
        }
        if (! $this->$type) {
            $this->$type = new \DOMDocument();
        }
        $object = $this->$type->createElement($name);
        if ($inline) {
            $object->nodeValue = str_replace("\r\n", "\n", $content);
        }
        foreach ($attr as $key => $value) {
            $object->setAttribute($key, $value);
        }
        $this->$type->appendChild($object);
    }

    public function addCss($path, $attr = [])
    {
        chdir($_SERVER["DOCUMENT_ROOT"]);
        if (file_exists(ltrim($path, "/")) || preg_match("/^(http(s)?:)?\/\//", $path)) {
            $this->createObject($path, false, "css", $attr);
        }
        return $this;
    }

    public function addInlineCss($pathOrContent, $attr = [])
    {
        chdir($_SERVER["DOCUMENT_ROOT"]);
        $content = $pathOrContent;
        if (strpos($pathOrContent, ".css") !== false) {
            if (file_exists($pathOrContent)) {
                $content = file_get_contents($pathOrContent);
            } else {
                $content = "";
            }
        }
        if ($content) {
            $this->createObject($content, true, "css", $attr);
        }
        return $this;
    }

    public function addJs($path, $attr = [])
    {
        chdir($_SERVER["DOCUMENT_ROOT"]);
        if (file_exists($path) || preg_match("/^(http(s)?:)?\/\//", $path)) {
            $this->createObject($path, false, "js", $attr);
        }
        return $this;
    }

    public function addInlineJs($pathOrContent, $attr = [])
    {
        chdir($_SERVER["DOCUMENT_ROOT"]);
        $content = $pathOrContent;
        if (strpos($pathOrContent, ".js") !== false) {
            if (file_exists($pathOrContent)) {
                $content = file_get_contents($pathOrContent);
            } else {
                $content = "";
            }
        }
        if ($content) {
            $this->createObject($content, true, "js", $attr);
        }
        return $this;
    }

    private function output($type)
    {
        if (! $this->$type) {
            return;
        }
        echo $this->$type->C14N() . PHP_EOL;
    }

    public function outputCss()
    {
        $this->output("css");
    }

    public function outputJs()
    {
        $this->output("js");
    }
}
