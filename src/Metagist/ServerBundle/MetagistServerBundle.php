<?php

namespace Metagist\ServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MetagistServerBundle extends Bundle
{
    public function boot()
    {
        parent::boot();
        require_once $this->getPath(). '/version.php';
    }
}
