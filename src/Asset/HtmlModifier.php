<?php

namespace Asset;

use Symfony\Component\DomCrawler\Crawler;

class HtmlModifier
{
    protected $path;
    protected $pattern = '{{ asset("%s") }}';

    public function __construct($path = null, $pattern = null)
    {
        if (null !== $path) {
            $this->setPath($path);
        }

        if (null !== $pattern) {
            $this->setPattern($pattern);
        }
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     *
     * @return HtmlModifier
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     *
     * @return HtmlModifier
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function modify($html)
    {
        $crawler = new Crawler($html);

        $cssFiles = $crawler->filter('link[rel="stylesheet"]')->extract(array('href'));
        $jsFiles = array_filter(array_unique($crawler->filter('script')->extract(array('src'))));
        $imgFiles = array_filter(array_unique($crawler->filter('img')->extract(array('src'))));
        $files = array_merge($cssFiles, $jsFiles, $imgFiles);

        $files = array_filter($files, function ($var) {
            return substr($var, 0, 2) !== '{{';
        });

        foreach ($files as $file) {
            $html = str_replace($file, sprintf($this->pattern, $this->getAbsoluteFilename($this->path.$file)), $html);
        }

        return $html;
    }

    protected function getAbsoluteFilename($filename)
    {
        $path = [];
        foreach (explode('/', $filename) as $part) {
            // ignore parts that have no value
            if (empty($part) || $part === '.') {
                continue;
            }

            if ($part !== '..') {
                // cool, we found a new part
                array_push($path, $part);
            } elseif (count($path) > 0) {
                // going back up? sure
                array_pop($path);
            } else {
                // now, here we don't like
                throw new \Exception('Climbing above the root is not permitted.');
            }
        }

        return implode('/', $path);
    }
}
