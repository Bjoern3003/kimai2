<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Export\Renderer;

use App\Export\Renderer\PDFRenderer;
use App\Tests\Mocks\Security\UserDateTimeFactoryFactory;
use App\Utils\HtmlToPdfConverter;
use App\Utils\MPdfConverter;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

/**
 * @covers \App\Export\Base\PDFRenderer
 * @covers \App\Export\Base\RendererTrait
 * @covers \App\Export\Renderer\PDFRenderer
 * @covers \App\Export\Renderer\RendererTrait
 * @group integration
 */
class PdfRendererTest extends AbstractRendererTest
{
    protected function getDateTimeFactory()
    {
        return (new UserDateTimeFactoryFactory($this))->create();
    }

    public function testConfiguration()
    {
        $sut = new PDFRenderer(
            $this->createMock(Environment::class),
            $this->getDateTimeFactory(),
            $this->createMock(HtmlToPdfConverter::class)
        );

        $this->assertEquals('pdf', $sut->getId());
        $this->assertEquals('pdf', $sut->getTitle());
        $this->assertEquals('pdf', $sut->getIcon());
    }

    public function testRender()
    {
        $kernel = self::bootKernel();
        /** @var Environment $twig */
        $twig = $kernel->getContainer()->get('twig');
        $stack = $kernel->getContainer()->get('request_stack');
        $cacheDir = $kernel->getContainer()->getParameter('kernel.cache_dir');
        $converter = new MPdfConverter($cacheDir);
        $request = new Request();
        $request->setLocale('en');
        $stack->push($request);

        $sut = new PDFRenderer($twig, $this->getDateTimeFactory(), $converter);

        $response = $this->render($sut);

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename=kimai-export.pdf', $response->headers->get('Content-Disposition'));

        $this->assertNotEmpty($response->getContent());
    }
}
