<?php

namespace SilverStripe\Subsites\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\View\SSViewer;
use SilverStripe\Subsites\Model\Subsite;

/**
 * @package subsites
 */
class ControllerSubsites extends Extension
{

    public function controllerAugmentInit()
    {
        if ($subsite = Subsite::currentSubsite()) {
            if ($theme = $subsite->Theme) {
                SSViewer::set_theme($theme);
            }
        }
    }

    public function CurrentSubsite()
    {
        if ($subsite = Subsite::currentSubsite()) {
            return $subsite;
        }
    }

}
