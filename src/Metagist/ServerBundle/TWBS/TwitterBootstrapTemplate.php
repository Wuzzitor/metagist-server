<?php

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Metagist\ServerBundle\TWBS;

use Pagerfanta\View\Template\TwitterBootstrapTemplate as PagerfantaTwitterBootstrapTemplate;

/**
 * @author Pablo Díez <pablodip@gmail.com>
 */
class TwitterBootstrapTemplate extends PagerfantaTwitterBootstrapTemplate
{
    /**
     * Adapted to TWBS 3.
     * 
     * @return string
     */
    public function container()
    {
        return sprintf('<ul class="%s">%%pages%%</ul>',
            $this->option('css_container_class')
        );
    }
}