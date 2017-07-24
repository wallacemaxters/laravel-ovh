<?php

namespace Sausin\LaravelOvh;

use GuzzleHttp\Psr7\Stream;
use League\Flysystem\Config;
use OpenStack\Common\Error\BadResponseError;
use OpenStack\ObjectStore\v1\Models\Container;
use Nimbusoft\Flysystem\OpenStack\SwiftAdapter;

class OVHSwiftAdapter extends SwiftAdapter
{
    /**
     * URL base path variables for OVH service
     * the HTTPS url is typically of the format
     * https://storage.[REGION].cloud.ovh.net/v1/AUTH_[PROJECT_ID]/[CONTAINER_NAME].
     * @var array
     */
    protected $urlBasePathVars;

    /**
     * Constructor.
     *
     * @param Container $container
     * @param string    $prefix
     */
    public function __construct(Container $container, $urlBasePathVars = [], $prefix = null)
    {
        $this->setPathPrefix($prefix);
        $this->container = $container;

        $this->urlBasePathVars = $urlBasePathVars;
    }

    /**
     * Custom function to comply with the Storage::url() function in laravel
     * without checking the existence of a file (faster).
     *
     * @param  string $path
     * @return string
     */
    public function getUrl($path)
    {
        if (! $this->urlBasePathVars) {
            throw new \Exception('Empty array', 1);
        }

        $urlBasePath = sprintf(
            'https://storage.%s.cloud.ovh.net/v1/AUTH_%s/%s/',
            $this->urlBasePathVars[0],
            $this->urlBasePathVars[1],
            $this->urlBasePathVars[2]
        );

        return $urlBasePath.$path;
    }

    /**
     * Custom function to get a url with confirmed file existence.
     *
     * @param  string $path
     * @return string
     */
    public function getUrlConfirm($path)
    {
        // check if object exists
        try {
            $this->getTimestamp($path);
        } catch (BadResponseError $e) {
            throw $e;
        }

        if (! $this->urlBasePathVars) {
            throw new \Exception('Empty array', 1);
        }

        $urlBasePath = sprintf(
            'https://storage.%s.cloud.ovh.net/v1/AUTH_%s/%s/',
            $this->urlBasePathVars[0],
            $this->urlBasePathVars[1],
            $this->urlBasePathVars[2]
        );

        return $urlBasePath.$path;
    }
}
