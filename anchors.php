<?php
namespace Grav\Plugin;

use \Grav\Common\Plugin;
use \Grav\Common\Grav;
use \Grav\Common\Page\Page;

class AnchorsPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            'onPageInitialized' => ['onPageInitialized', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ];
    }

    /**
     * Initialize configuration
     */
    public function onPageInitialized()
    {
        $defaults = (array) $this->config->get('plugins.anchors');

        /** @var Page $page */
        $page = $this->grav['page'];
        if (isset($page->header()->anchors)) {
            $this->config->set('plugins.anchors', array_merge($defaults, $page->header()->anchors));
        }
    }

    /**
     * if enabled on this page, load the JS + CSS theme.
     */
    public function onTwigSiteVariables()
    {
        if ($this->config->get('plugins.anchors.enabled')) {
            $selectors = $this->config->get('plugins.anchors.selectors') ?: 'h1,h2,h3';
            $this->grav['assets']->addCss('plugin://anchors/css/anchor.css');
            $this->grav['assets']->addJs('plugin://anchors/js/anchor.min.js');
            $this->grav['assets']->addInlineJs('$(document).ready(function() { addAnchors(\''.$selectors.'\'); });');
        }
    }
}
