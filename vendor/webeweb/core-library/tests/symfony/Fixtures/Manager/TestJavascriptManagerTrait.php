<?php

/*
 * This file is part of the core-library package.
 *
 * (c) 2022 WEBEWEB
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WBW\Library\Symfony\Tests\Fixtures\Manager;

use WBW\Library\Symfony\Manager\JavascriptManagerTrait;

/**
 * Test javascript manager trait.
 *
 * @author webeweb <https://github.com/webeweb>
 * @package WBW\Library\Symfony\Tests\Fixtures\Manager
 */
class TestJavascriptManagerTrait {

    use JavascriptManagerTrait {
        setJavascriptManager as public;
    }
}
