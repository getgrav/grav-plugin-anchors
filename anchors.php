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
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
            ]);
        }
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
        if ($this->config->get('plugins.anchors.active')) {
            $selectors = $this->config->get('plugins.anchors.selectors', 'h1,h2,h3,h4');

            $visible = "visible: '{$this->config->get('plugins.anchors.visible', 'hover')}',";
            $placement = "placement: '{$this->config->get('plugins.anchors.placement', 'right')}',";
            $icon = $this->config->get('plugins.anchors.icon') ? "icon: '{$this->config->get('plugins.anchors.icon')}'," : '';
            $class = $this->config->get('plugins.anchors.class') ? "class: '{$this->config->get('plugins.anchors.class')}'," : '';
            $truncate = "truncate: {$this->config->get('plugins.anchors.truncate', 64)}";

            $this->grav['assets']->addJs('plugin://anchors/js/anchor.min.js');

            $anchors_init = "$(document).ready(function() {
                                anchors.options = {
                                    $visible
                                    $placement
                                    $icon
                                    $class
                                    $truncate
                                };
                                // steamlead hack: allow content to be hidden from the anchor 
                                // usefull for instance when anchor is used with the prettyprint plugin activated
                                // juste add the css class 'hideFromAnchorJS' to the elements to hide

                                // store and empty every content that need to be hidden in anchorJS
                                /*
                                var content=[];
                                $('.hideFromAnchorJS').each( function(){
                                    content.unshift($(this).html());
                                    $(this).empty(); 
                                } );
                                */
                                /*
                                anchors.add('.e-content > h1');
                                anchors.add('.e-content > h2');
                                anchors.add('.e-content > h3');
                                anchors.add('.e-content > h4');

                                anchors.add('.list-blog-header > h1');
                                anchors.add('.list-blog-header > h2');
                                anchors.add('.list-blog-header > h3');
                                anchors.add('.list-blog-header > h4');
                                */
                               
                                // we generate the anchor list
                                anchors.add('$selectors');

                                /*
                                // restore the deleted content
                                $('.hideFromAnchorJS').each( function(){
                                    $(this).html(content.shift()); 
                                } );
                                */
                             });";


            $this->grav['assets']->addInlineJs($anchors_init);
        }
    }
}
