<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\EventListener;

use Imbo\EventManager\EventInterface;

/**
 * Image transformer listener
 *
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @package Event\Listeners
 */
class ImageTransformer implements ListenerInterface {
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return array(
            'image.transform' => 'transform',
        );
    }

    /**
     * Transform images
     *
     * @param EventInterface $event The current event
     */
    public function transform(EventInterface $event) {
        $request = $event->getRequest();
        $image = $event->getResponse()->getModel();
        $eventManager = $event->getManager();
        $presets = $event->getConfig()['transformationPresets'];

        // Fetch transformations specifed in the query and transform the image
        foreach ($request->getTransformations() as $transformation) {
            if (isset($presets[$transformation['name']])) {
                // Preset
                foreach ($presets[$transformation['name']] as $name => $params) {
                    if (is_int($name)) {
                        $name = $params;
                        $params = $transformation['params'];
                    }

                    $eventManager->trigger(
                        'image.transformation.' . strtolower($name),
                        array(
                            'image' => $image,
                            'params' => $params,
                        )
                    );
                }
            } else {
                // Regular transformation
                $eventManager->trigger(
                    'image.transformation.' . strtolower($transformation['name']),
                    array(
                        'image' => $image,
                        'params' => $transformation['params'],
                    )
                );
            }
        }
    }
}
