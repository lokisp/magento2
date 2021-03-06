<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CategoryUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $canonicalUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $currentUrlRewritesRegenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $childrenUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeViewService;

    /** @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject */
    protected $category;

    /**
     * Test method
     */
    protected function setUp()
    {
        $this->currentUrlRewritesRegenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator'
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->childrenUrlRewriteGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->storeViewService = $this->getMockBuilder('Magento\CatalogUrlRewrite\Service\V1\StoreViewService')
            ->disableOriginalConstructor()->getMock();
        $this->category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);

        $this->categoryUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator',
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'childrenUrlRewriteGenerator' => $this->childrenUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'storeViewService' => $this->storeViewService,
            ]
        );
    }

    /**
     * Test method
     */
    public function testGenerationForGlobalScope()
    {
        $this->category->expects($this->any())->method('getStoreId')->will($this->returnValue(null));
        $this->category->expects($this->any())->method('getStoreIds')->will($this->returnValue([1]));
        $this->storeViewService->expects($this->once())->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(false));
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $children = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $children->setTargetPath('category-2')
            ->setStoreId(2);
        $this->childrenUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$children]));
        $current = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $current->setTargetPath('category-3')
            ->setStoreId(3);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$current]));

        $this->assertEquals(
            [$canonical, $children, $current],
            $this->categoryUrlRewriteGenerator->generate($this->category)
        );
    }

    /**
     * Test method
     */
    public function testGenerationForSpecificStore()
    {
        $this->category->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $this->category->expects($this->never())->method('getStoreIds');
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
        $canonical->setTargetPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $this->childrenUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals([$canonical], $this->categoryUrlRewriteGenerator->generate($this->category));
    }

    /**
     * Test method
     */
    public function testSkipGenerationForGlobalScope()
    {
        $this->category->expects($this->any())->method('getStoreIds')->will($this->returnValue([1, 2]));
        $this->storeViewService->expects($this->exactly(2))->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(true));

        $this->assertEquals([], $this->categoryUrlRewriteGenerator->generate($this->category));
    }
}
