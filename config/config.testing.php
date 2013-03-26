<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo;

use Imbo\Image\Transformation,
    PHPUnit_Framework_MockObject_Generator,
    PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount,
    PHPUnit_Framework_MockObject_Stub_Return;

// Require composer autoloader
require __DIR__ . '/../vendor/autoload.php';

return array(
    'auth' => array('publickey' => 'privatekey'),

    'database' => function() {
        $adapter = PHPUnit_Framework_MockObject_Generator::getMock(
            'Imbo\Database\MongoDB',
            array('getStatus'),
            array(array('databaseName' => 'imbo_testing'))
        );

        $adapter->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount())
                ->method('getStatus')
                ->will(new PHPUnit_Framework_MockObject_Stub_Return(isset($_GET['databaseDown']) ? false : true));

        return $adapter;
    },

    'storage' => function() {
        $adapter = PHPUnit_Framework_MockObject_Generator::getMock(
            'Imbo\Storage\GridFS',
            array('getStatus'),
            array(array('databaseName' => 'imbo_testing'))
        );

        $adapter->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount())
                ->method('getStatus')
                ->will(new PHPUnit_Framework_MockObject_Stub_Return(isset($_GET['storageDown']) ? false : true));

        return $adapter;
    },

    'eventListeners' => array(
        'auth' => function() {
            return new EventListener\Authenticate();
        },
        'accessToken' => function() {
            return new EventListener\AccessToken();
        },
        'imageTransformationCache' => function() {
            return new EventListener\ImageTransformationCache('/tmp/imbo-behat-image-transformation-cache');
        },
    ),

    'imageTransformations' => array(
        'border' => function (array $params) {
            return new Transformation\Border($params);
        },
        'canvas' => function (array $params) {
            return new Transformation\Canvas($params);
        },
        'compress' => function (array $params) {
            return new Transformation\Compress($params);
        },
        'convert' => function (array $params) {
            return new Transformation\Convert($params);
        },
        'crop' => function (array $params) {
            return new Transformation\Crop($params);
        },
        'desaturate' => function (array $params) {
            return new Transformation\Desaturate();
        },
        'flipHorizontally' => function (array $params) {
            return new Transformation\FlipHorizontally();
        },
        'flipVertically' => function (array $params) {
            return new Transformation\FlipVertically();
        },
        'maxSize' => function (array $params) {
            return new Transformation\MaxSize($params);
        },
        'resize' => function (array $params) {
            return new Transformation\Resize($params);
        },
        'rotate' => function (array $params) {
            return new Transformation\Rotate($params);
        },
        'sepia' => function (array $params) {
            return new Transformation\Sepia($params);
        },
        'thumbnail' => function (array $params) {
            return new Transformation\Thumbnail($params);
        },
        'transpose' => function (array $params) {
            return new Transformation\Transpose();
        },
        'transverse' => function (array $params) {
            return new Transformation\Transverse();
        },

        // collection
        'graythumb' => function (array $params) {
            return new Transformation\Collection(array(
                new Transformation\Thumbnail($params),
                new Transformation\Desaturate(),
            ));
        }
    ),
);
