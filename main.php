<?php

$url = $argv[1];
$level = $argv[2];

class crawler
{
    public $baseUrl = "";
    public $allUrls = array();
    public $level = 0;
    public $nowLevel = 0;

    public function init ($url, $level)
    {
        if (! empty($level))
        {
            $this->level = $level;
        }

        $this->baseUrl = $url;

        $this->block($url);
    }

    public function block ($url)
    {
        $this->nowLevel += 1;

        if ($this->level != 0 and $this->nowLevel > $this->level)
        {
            return true;
        }

        $page = $this->getContent($url);

        $links = $this->getDomLinks($page);

        foreach ($links as $link)
        {
            if (! in_array($link, $this->allUrls))
            {
                $this->allUrls[] = $link;

                $this->block($link);
            }
        }

        return true;
    }

    public function getContent ($url)
    {
        $dom = new DOMDocument();

        $content = file_get_contents($url);

        $content = $this->preLoadHtml($content);

        $dom->loadHTML($content);

        return $dom;
    }

    public function preLoadHtml ($content)
    {
        $this->createTextFile($content);

        return $content;
    }

    public function getDomLinks (DOMDocument $dom)
    {
        $links = $dom->getElementsByTagName("a");

        $linksUrl = array();

        foreach ($links as $link)
        {
            $url = $link->getAttribute("href");

            // 開頭不是 http, https
            if (strpos($url, "http") !== 0)
            {
                $url = $this->baseUrl . $url;
            }
            elseif (strpos($url, $this->baseUrl) !== 0)
            {
                // 跨站網址不收入
                continue;
            }

            $linksUrl[] = $url;
        }

        return $linksUrl;
    }

    public function createTextFile ($content)
    {
        // remove javascript
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content);

        // remove html
        $content = strip_tags($content);

        $fileName = md5($content);

        file_put_contents ("data/{$fileName}.txt", $content);
    }
}

$c = new crawler();

$c->init($url, $level);
