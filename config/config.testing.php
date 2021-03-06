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
    Imbo\Cache,
    Imbo\Resource\ResourceInterface,
    Imbo\EventManager\EventInterface,
    Imbo\EventListener\ListenerInterface,
    Imbo\Model\ArrayModel,
    Memcached as PeclMemcached,
    PHPUnit_Framework_MockObject_Generator,
    PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount,
    PHPUnit_Framework_MockObject_Stub_Return;

// Require composer autoloader
require __DIR__ . '/../vendor/autoload.php';

class CustomResource implements ResourceInterface {
    public function getAllowedMethods() {
        return array('GET');
    }

    public static function getSubscribedEvents() {
        return array(
            'custom1.get' => 'get',
        );
    }

    public function get(EventInterface $event) {
        $model = new ArrayModel();
        $model->setData(array(
            'event' => $event->getName(),
            'id' => $event->getRequest()->getRoute()->get('id'),
        ));
        $event->getResponse()->setModel($model);
    }
}

class CustomResource2 implements ResourceInterface {
    public function getAllowedMethods() {
        return array('GET', 'PUT');
    }

    public static function getSubscribedEvents() {
        return array(
            'custom2.get' => 'get',
            'custom2.put' => 'put',
        );
    }

    public function get(EventInterface $event) {
        $model = new ArrayModel();
        $model->setData(array(
            'event' => $event->getName(),
        ));
        $event->getResponse()->setModel($model);
    }

    public function put(EventInterface $event) {
        $model = new ArrayModel();
        $model->setData(array('event' => $event->getName()));
        $event->getResponse()->setModel($model);
    }
}

class CustomEventListener implements ListenerInterface {
    private $value1;
    private $value2;

    public function __construct($value1, $value2) {
        $this->value1 = $value1;
        $this->value2 = $value2;
    }

    public static function getSubscribedEvents() {
        return array(
            'index.get' => 'getIndex',
            'user.get' => 'getUser',
        );
    }

    public function getIndex(EventManager\EventInterface $event) {
        $event->getResponse()->headers->add(array(
            'X-Imbo-Value1' => $this->value1,
            'X-Imbo-Value2' => $this->value2,
        ));
    }

    public function getUser(EventManager\EventInterface $event) {
        $event->getResponse()->headers->set('X-Imbo-CurrentUser', $event->getRequest()->getPublicKey());
    }
}

$statsWhitelist = array('127.0.0.1', '::1');
$statsBlacklist = array();

if (!empty($_GET['statsWhitelist'])) {
    // The scenario has specified a whitelist to use
    $statsWhitelist = explode(',', $_GET['statsWhitelist']);
}

if (!empty($_GET['statsBlacklist'])) {
    // The scenario has specified a blacklist to use
    $statsBlacklist = explode(',', $_GET['statsBlacklist']);

    if (empty($_GET['statsWhitelist'])) {
        $statsWhitelist = array();
    }
}

if (isset($_SERVER['HTTP_X_CLIENT_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_CLIENT_IP'];
}

return array(
    'auth' => array(
        'publickey' => 'privatekey',
        'user' => 'key',
    ),

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
        'auth' => 'Imbo\EventListener\Authenticate',
        'accessToken' => 'Imbo\EventListener\AccessToken',
        'statsAccess' => array(
            'listener' => 'Imbo\EventListener\StatsAccess',
            'params' => array(
                array(
                    'whitelist' => $statsWhitelist,
                    'blacklist' => $statsBlacklist,
                )
            ),
        ),
        'imageTransformationCache' => array(
            'listener' => 'Imbo\EventListener\ImageTransformationCache',
            'params' => array('/tmp/imbo-behat-image-transformation-cache'),
        ),
        'metadataCache' => function() {
            $memcached = new PeclMemcached();
            $memcached->addServer('localhost', 11211);

            $namespace = $_SERVER['HTTP_X_TEST_SESSION_ID'];

            $adapter = new Cache\Memcached($memcached, $namespace);

            return new EventListener\MetadataCache($adapter);
        },
        'someHandler' => array(
            'events' => array(
                'index.get' => 1000,
            ),
            'callback' => function(EventManager\EventInterface $event) {
                $event->getResponse()->headers->set('X-Imbo-SomeHandler', microtime(true));
            }
        ),
        'someOtherHandler' => array(
            'events' => array(
                'index.get',
                'index.head',
            ),
            'callback' => function(EventManager\EventInterface $event) {
                $event->getResponse()->headers->set('X-Imbo-SomeOtherHandler', microtime(true));
            },
            'priority' => 10,
        ),
        'someEventListener' => array(
            'listener' => __NAMESPACE__ . '\CustomEventListener',
            'params' => array(
                'value1', 'value2'
            ),
            'publicKeys' => array(
                'whitelist' => array('publickey'),
            ),
        ),
        'cors' => array(
            'listener' => 'Imbo\EventListener\Cors',
            'params' => array(
                array(
                    'allowedOrigins' => array('http://allowedhost'),
                    'maxAge' => 1349,
                ),
            ),
        ),
        'maxImageSize' => array(
            'listener' => 'Imbo\EventListener\MaxImageSize',
            'params' => array(1000, 1000),
        ),
        'varnishHashTwo' => 'Imbo\EventListener\VarnishHashTwo',
        'customVarnishHashTwo' => array(
            'listener' => 'Imbo\EventListener\VarnishHashTwo',
            'params' => array('X-Imbo-HashTwo'),
        ),
        'exifMetadataListener' => 'Imbo\EventListener\ExifMetadata',
        'autoRotateListener' => 'Imbo\EventListener\AutoRotateImage',

        // Image transformations
        'autoRotate' => 'Imbo\Image\Transformation\AutoRotate',
        'border' => 'Imbo\Image\Transformation\Border',
        'canvas' => 'Imbo\Image\Transformation\Canvas',
        'compress' => 'Imbo\Image\Transformation\Compress',
        'convert' => 'Imbo\Image\Transformation\Convert',
        'crop' => 'Imbo\Image\Transformation\Crop',
        'desaturate' => 'Imbo\Image\Transformation\Desaturate',
        'flipHorizontally' => 'Imbo\Image\Transformation\FlipHorizontally',
        'flipVertically' => 'Imbo\Image\Transformation\FlipVertically',
        'maxSize' => 'Imbo\Image\Transformation\MaxSize',
        'resize' => 'Imbo\Image\Transformation\Resize',
        'rotate' => 'Imbo\Image\Transformation\Rotate',
        'sepia' => 'Imbo\Image\Transformation\Sepia',
        'thumbnail' => 'Imbo\Image\Transformation\Thumbnail',
        'transpose' => 'Imbo\Image\Transformation\Transpose',
        'transverse' => 'Imbo\Image\Transformation\Transverse',
        'watermark' => 'Imbo\Image\Transformation\Watermark',

        // Imagick-specific event listener for the built in image transformations
        'imagick' => 'Imbo\EventListener\Imagick',
    ),

    'eventListenerInitializers' => array(
        'imagick' => 'Imbo\EventListener\Initializer\Imagick',
    ),

    'transformationPresets' => array(
        'graythumb' => array(
            'thumbnail',
            'desaturate',
        ),
    ),

    'routes' => array(
        'custom1' => '#^/custom/(?<id>[a-zA-Z0-9]{7})$#',
        'custom2' => '#^/custom(?:\.(?<extension>json|xml))?$#',
    ),

    'resources' => array(
        'custom1' => __NAMESPACE__ . '\CustomResource',
        'custom2' => function() {
            return new CustomResource2();
        }
    ),
);
