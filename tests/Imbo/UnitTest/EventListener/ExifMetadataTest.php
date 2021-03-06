<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\UnitTest\EventListener;

use Imbo\EventListener\ExifMetadata;

/**
 * @covers Imbo\EventListener\ExifMetadata
 * @group unit
 * @group listeners
 */
class ExifMetadataTest extends ListenerTests {
    /**
     * @var ExifMetadata
     */
    private $listener;

    private $imagick;

    /**
     * Set up the listener
     */
    public function setUp() {
        $this->listener = new ExifMetadata();
    }

    /**
     * Tear down the listener
     */
    public function tearDown() {
        $this->listener = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListener() {
        return $this->listener;
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getFilterData() {
        $data = array(
            'date:create' => '2013-11-26T19:42:48+01:00',
            'date:modify' => '2013-11-26T19:42:48+01:00',
            'exif:Flash' => '16',
            'exif:GPSAltitude' => '254/5',
            'exif:GPSAltitudeRef' => '0',
            'exif:GPSDateStamp' => '2012:06:09',
            'exif:GPSInfo' => '730',
            'exif:GPSLatitude' => '63/1, 40/1, 173857/3507',
            'exif:GPSLatitudeRef' => 'N',
            'exif:GPSLongitude' => '9/1, 5/1, 38109/12500',
            'exif:GPSLongitudeRef' => 'E',
            'exif:GPSProcessingMethod' => '65, 83, 67, 73, 73, 0, 0, 0',
            'exif:GPSTimeStamp' => '17/1, 17/1, 51/1',
            'exif:GPSVersionID' => '2, 2, 0, 0',
            'exif:Make' => 'SAMSUNG',
            'exif:Model' => 'GT-I9100',
            'jpeg:colorspace' => '2',
            'jpeg:sampling-factor' => '2x2,1x1,1x1',
        );
        return array(
            'all values' => array(
                'data' => $data,
                'tags' => array('*'),
                'expectedData' => array_merge($data, array(
                    'gps:location' => array(9.0841802, 63.680437300003),
                    'gps:altitude' => 50.8,
                )),

            ),
            'specific value' => array(
                'data' => $data,
                'tags' => array('exif:Make'),
                'expectedData' => array(
                    'exif:Make' => 'SAMSUNG',
                ),
            ),
            'default' => array(
                'data' => $data,
                'tags' => null,
                'expectedData' => array(
                    'exif:Flash' => '16',
                    'exif:GPSAltitude' => '254/5',
                    'exif:GPSAltitudeRef' => '0',
                    'exif:GPSDateStamp' => '2012:06:09',
                    'exif:GPSInfo' => '730',
                    'exif:GPSLatitude' => '63/1, 40/1, 173857/3507',
                    'exif:GPSLatitudeRef' => 'N',
                    'exif:GPSLongitude' => '9/1, 5/1, 38109/12500',
                    'exif:GPSLongitudeRef' => 'E',
                    'exif:GPSProcessingMethod' => '65, 83, 67, 73, 73, 0, 0, 0',
                    'exif:GPSTimeStamp' => '17/1, 17/1, 51/1',
                    'exif:GPSVersionID' => '2, 2, 0, 0',
                    'exif:Make' => 'SAMSUNG',
                    'exif:Model' => 'GT-I9100',
                    'gps:location' => array(9.0841802, 63.680437300003),
                    'gps:altitude' => 50.8,
                ),
            ),
            'mixed' => array(
                'data' => $data,
                'tags' => array('exif:Model', 'jpeg:*', 'date:*'),
                'expectedData' => array(
                    'date:create' => '2013-11-26T19:42:48+01:00',
                    'date:modify' => '2013-11-26T19:42:48+01:00',
                    'exif:Model' => 'GT-I9100',
                    'jpeg:colorspace' => '2',
                    'jpeg:sampling-factor' => '2x2,1x1,1x1',
                ),
            ),
        );
    }

    /**
     * @dataProvider getFilterData
     * @covers Imbo\EventListener\ExifMetadata::setImagick
     * @covers Imbo\EventListener\ExifMetadata::getImagick
     * @covers Imbo\EventListener\ExifMetadata::populate
     * @covers Imbo\EventListener\ExifMetadata::save
     * @covers Imbo\EventListener\ExifMetadata::filterProperties
     * @covers Imbo\EventListener\ExifMetadata::parseProperties
     */
    public function testCanFilterData($data, $tags, $expectedData) {
        $publicKey = 'publickey';
        $checksum = 'checksum';
        $blob = 'blob';

        $image = $this->getMock('Imbo\Model\Image');
        $image->expects($this->once())->method('getChecksum')->will($this->returnValue($checksum));
        $image->expects($this->once())->method('getBlob')->will($this->returnValue($blob));

        $imagick = $this->getMock('Imagick');
        $imagick->expects($this->once())->method('readImageBlob')->will($this->returnValue($blob));
        $imagick->expects($this->once())->method('getImageProperties')->will($this->returnValue($data));

        $request = $this->getMock('Imbo\Http\Request\Request');
        $request->expects($this->once())->method('getPublicKey')->will($this->returnValue($publicKey));
        $request->expects($this->exactly(2))->method('getImage')->will($this->returnValue($image));

        $database = $this->getMock('Imbo\Database\DatabaseInterface');
        $database->expects($this->once())->method('updateMetadata')->with($publicKey, $checksum, $expectedData);

        $event = $this->getMock('Imbo\EventManager\Event');
        $event->expects($this->exactly(2))->method('getRequest')->will($this->returnValue($request));
        $event->expects($this->once())->method('getDatabase')->will($this->returnValue($database));

        $listener = new ExifMetadata($tags);
        $listener->setImagick($imagick);
        $listener->populate($event);
        $listener->save($event);
    }

    /**
     * @covers Imbo\EventListener\ExifMetadata::save
     * @expectedException Imbo\Exception\RuntimeException
     * @expectedExceptionMessage Could not store EXIF-metadata
     * @expectedExceptionCode 500
     */
    public function testWillDeleteImageWhenUpdatingMetadataFails() {
        $databaseException = $this->getMock('Imbo\Exception\DatabaseException');
        $database = $this->getMock('Imbo\Database\DatabaseInterface');
        $database->expects($this->once())->method('updateMetadata')->with('publickey', 'imageidentifier', array())->will($this->throwException($databaseException));
        $database->expects($this->once())->method('deleteImage')->with('publickey', 'imageidentifier');

        $image = $this->getMock('Imbo\Model\Image');
        $image->expects($this->once())->method('getChecksum')->will($this->returnValue('imageidentifier'));

        $request = $this->getMock('Imbo\Http\Request\Request');
        $request->expects($this->once())->method('getPublicKey')->will($this->returnValue('publickey'));
        $request->expects($this->once())->method('getImage')->will($this->returnValue($image));

        $event = $this->getMock('Imbo\EventManager\Event');
        $event->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $event->expects($this->once())->method('getDatabase')->will($this->returnValue($database));

        $this->listener->save($event);
    }

    /**
     * @covers Imbo\EventListener\ExifMetadata::getImagick
     */
    public function testCanInstantiateImagickItself() {
        $this->assertInstanceOf('Imagick', $this->listener->getImagick());
    }
}
