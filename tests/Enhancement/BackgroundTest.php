<?php

require_once 'Zend/Pdf/Page.php';

use PHPPdf\Enhancement\Background;
use PHPPdf\Glyph\Page;
use PHPPdf\Util\Point;

class BackgroundTest extends PHPUnit_Framework_TestCase
{
    private $imagePath;

    public function setUp()
    {
        $this->imagePath = dirname(__FILE__).'/../resources/domek-min.jpg';
    }

    /**
     * @test
     */
    public function backgroundWithoutRepeat()
    {
        $background = new Background(null, $this->imagePath);

        $x = 0;
        $y = 200;
        $width = $height = 100;
        
        $glyphMock = $this->getGlyphMock($x, $y, $width, $height);

        $gcMock = $this->getMock('PHPPdf\Glyph\GraphicsContext', array('drawImage', 'clipRectangle', 'saveGS', 'restoreGS'), array(), '', false);

        $pageMock = $this->getMock('PHPPdf\Glyph\Page', array('getGraphicsContext'));

        $pageMock->expects($this->atLeastOnce())
                 ->method('getGraphicsContext')
                 ->will($this->returnValue($gcMock));

        $gcMock->expects($this->at(0))
               ->method('saveGS');

        $gcMock->expects($this->at(1))
               ->method('clipRectangle')
               ->with($x, $y, $x+$width, $y-$height);

        $gcMock->expects($this->at(2))
               ->method('drawImage')
               ->with($background->getImage(), $x, $y-$background->getImage()->getPixelHeight(), $x+$background->getImage()->getPixelWidth(), $y);

        $gcMock->expects($this->at(3))
               ->method('restoreGS');

        $background->enhance($pageMock, $glyphMock);
    }

    /**
     * @test
     * @dataProvider kindOfBackgroundsProvider
     */
    public function backgroundWithRepeat($repeat)
    {
        $x = 0;
        $y = 200;
        $width = $height = 100;

        $image = \Zend_Pdf_Image::imageWithPath($this->imagePath);
        $background = new Background(null, $image, $repeat);

        $x = 1;
        if($repeat & Background::REPEAT_X)
        {
            $x = ceil($width / $image->getPixelWidth());
        }

        $y = 1;
        if($repeat & Background::REPEAT_Y)
        {
            $y = ceil($height / $image->getPixelHeight());
        }

        $count = (int) ($x*$y);

        $gcMock = $this->getMock('PHPPdf\Glyph\GraphicsContext', array('drawImage', 'clipRectangle', 'saveGS', 'restoreGS'), array(), '', false);

        $pageMock = $this->getMock('PHPPdf\Glyph\Page', array('getGraphicsContext'));

        $pageMock->expects($this->atLeastOnce())
                 ->method('getGraphicsContext')
                 ->will($this->returnValue($gcMock));

        $gcMock->expects($this->once())
               ->method('saveGS');

        $gcMock->expects($this->once())
               ->method('clipRectangle')
               ->with($x, $y, $x+$width, $y-$height);

        $gcMock->expects($this->exactly($count))
               ->method('drawImage');

        $gcMock->expects($this->once())
               ->method('restoreGS');

        $glyphMock = $this->getGlyphMock($x, $y, $width, $height);

        $background->enhance($pageMock, $glyphMock);
    }

    public function kindOfBackgroundsProvider()
    {
        return array(
            array(Background::REPEAT_X),
            array(Background::REPEAT_Y),
            array(Background::REPEAT_ALL),
        );
    }

    private function getGlyphMock($x, $y, $width, $height)
    {
        $boundaryMock = $this->getBoundaryStub($x, $y, $width, $height);

        $glyphMock = $this->getMock('PHPPdf\Glyph\AbstractGlyph', array('getBoundary', 'getWidth', 'getHeight'));
        $glyphMock->expects($this->atLeastOnce())
                  ->method('getBoundary')
                  ->will($this->returnValue($boundaryMock));
        $glyphMock->expects($this->any())
                  ->method('getWidth')
                  ->will($this->returnValue($width));

        $glyphMock->expects($this->any())
                  ->method('getHeight')
                  ->will($this->returnValue($height));

        return $glyphMock;
    }

    private function getBoundaryStub($x, $y, $width, $height)
    {
        $boundaryMock = new \PHPPdf\Util\Boundary();

        $points = array(
            Point::getInstance($x, $y),
            Point::getInstance($x+$width, $y),
            Point::getInstance($x+$width, $y - $height),
            Point::getInstance($x, $y - $height),
        );

        foreach($points as $point)
        {
            $boundaryMock->setNext($point);
        }
        $boundaryMock->close();

        return $boundaryMock;
    }
}