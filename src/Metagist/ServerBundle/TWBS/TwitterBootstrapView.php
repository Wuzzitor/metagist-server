<?php
/*
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 */

namespace Metagist\ServerBundle\TWBS;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\TwitterBootstrapView as PagerFantaTwitterBootstrapView;

/**
 * TwitterBootstrapView.
 *
 */
class TwitterBootstrapView extends PagerFantaTwitterBootstrapView
{
    protected function createDefaultTemplate()
    {
        return new TwitterBootstrapTemplate();
    }
}
