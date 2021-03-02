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
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize configuration
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
        } else {
            $this->enable([
                'onPageInitialized' => ['onPageInitialized', 0],
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
                'onTwigExtensions' => ['onTwigExtensions', 0]
            ]);
        }
    }

    public function onTwigExtensions()
    {
        $config = (array) $this->config->get('plugins.anchors');
        require_once(__DIR__ . '/twig/AnchorsTwigExtension.php');
        $this->grav['twig']->twig->addExtension(new AnchorsTwigExtension($config));
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
     * if enabled on this page, load the JS + CSS and set the selectors.
     */
    public function onTwigSiteVariables()
    {
        $stickyValue = $this->config->get('plugins.anchors.sticky');
        $smoothScrollingValue = $this->config->get('plugins.anchors.smooth_scrolling');
        $offsetTop = $this->config->get('plugins.anchors.offset_top') ?: 0;

        if ($this->config->get('plugins.anchors.active')) {
            $selectors = $this->config->get('plugins.anchors.selectors', 'h1,h2,h3,h4');

            $visible = "visible: '{$this->config->get('plugins.anchors.visible', 'hover')}',";
            $placement = "placement: '{$this->config->get('plugins.anchors.placement', 'right')}',";
            $icon = $this->config->get('plugins.anchors.icon') ? "icon: '{$this->config->get('plugins.anchors.icon')}'," : '';
            $class = $this->config->get('plugins.anchors.class') ? "class: '{$this->config->get('plugins.anchors.class')}'," : '';
            $truncate = "truncate: {$this->config->get('plugins.anchors.truncate', 64)}";

            $this->grav['assets']->addJs('plugin://anchors/js/anchor.min.js');
            if ($stickyValue)
                $this->grav['assets']->addJs('plugin://anchors/js/jquery.sticky.js');

            $anchors_init = "$(document).ready(function() {
                                $('#sticker').on('sticky-start', function() { 
                                    $(this).addClass('custom-sticker-content');
                                });
                                $('#sticker').on('sticky-end', function() { 
                                    $(this).removeClass('custom-sticker-content');
                                });
                                anchors.options = {
                                    $visible
                                    $placement
                                    $icon
                                    $class
                                    $truncate
                                };
                                anchors.add('$selectors');
                                if ('$smoothScrollingValue')
                                 $(document).on('click', 'a[href^=\"#\"]', function (e) {
                                    var id = $(this).attr('href');
                                    var target = $(id);
                                    if (target.length === 0) {
                                      return;
                                    }
                                    e.preventDefault();
                                    $('body, html').animate({ scrollTop: target.offset().top - $offsetTop });
                                });
                                if ('$stickyValue')
                                    $('#sticker').sticky({topSpacing:0});
                             });";


            $this->grav['assets']->addInlineJs($anchors_init);
        }
    }
}
