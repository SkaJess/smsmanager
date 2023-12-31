<?php

/*
 * This file is part of the core-library package.
 *
 * (c) 2021 WEBEWEB
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WBW\Library\Symfony\Tests\Renderer\Floats;

use WBW\Library\Symfony\Tests\AbstractTestCase;
use WBW\Library\Symfony\Tests\Fixtures\Renderer\Floats\TestFloatRendererTrait;

/**
 * Float renderer trait test.
 *
 * @author webeweb <https://github.com/webeweb>
 * @package WBW\Library\Symfony\Tests\Renderer\Floats
 */
class FloatRendererTraitTest extends AbstractTestCase {

    /**
     * Tests the renderFloat() methods.
     *
     * @return void
     */
    public function testRenderFloat(): void {

        $obj = new TestFloatRendererTrait();

        $this->assertEquals("", $obj->renderFloat(null));
        $this->assertEquals("1,000.00", $obj->renderFloat(1000));
        $this->assertEquals("1,000.000", $obj->renderFloat(1000, 3));
        $this->assertEquals("1 000,000", $obj->renderFloat(1000, 3, ",", " "));
    }

    /**
     * Tests the renderPercent() methods.
     *
     * @return void
     */
    public function testRenderPercent(): void {

        $obj = new TestFloatRendererTrait();

        $this->assertEquals(null, $obj->renderPercent(null));
        $this->assertEquals("100.00 %", $obj->renderPercent(100));
    }

    /**
     * Tests the renderPrice() methods.
     *
     * @return void
     */
    public function testRenderPrice(): void {

        $obj = new TestFloatRendererTrait();

        $this->assertEquals(null, $obj->renderPrice(null));
        $this->assertEquals("1,000.00 €", $obj->renderPrice(1000));
        $this->assertEquals("1,000.00 $", $obj->renderPrice(1000, "$"));
    }
}
