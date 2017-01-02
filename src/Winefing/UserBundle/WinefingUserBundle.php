<?php

namespace Winefing\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WinefingUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
