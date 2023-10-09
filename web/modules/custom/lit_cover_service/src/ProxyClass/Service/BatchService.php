<?php
// @codingStandardsIgnoreFile

/**
 * This file was generated via php core/scripts/generate-proxy-class.php 'Drupal\lit_cover_service\Service\BatchService' "modules/custom/lit_cover_service/src".
 */

namespace Drupal\lit_cover_service\ProxyClass\Service {

    /**
     * Provides a proxy class for \Drupal\lit_cover_service\Service\BatchService.
     *
     * @see \Drupal\Component\ProxyBuilder
     */
    class BatchService implements \Drupal\lit_cover_service\Service\BatchServiceInterface
    {

        use \Drupal\Core\DependencyInjection\DependencySerializationTrait;

        /**
         * The id of the original proxied service.
         *
         * @var string
         */
        protected $drupalProxyOriginalServiceId;

        /**
         * The real proxied service, after it was lazy loaded.
         *
         * @var \Drupal\lit_cover_service\Service\BatchService
         */
        protected $service;

        /**
         * The service container.
         *
         * @var \Symfony\Component\DependencyInjection\ContainerInterface
         */
        protected $container;

        /**
         * Constructs a ProxyClass Drupal proxy object.
         *
         * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
         *   The container.
         * @param string $drupal_proxy_original_service_id
         *   The service ID of the original service.
         */
        public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container, $drupal_proxy_original_service_id)
        {
            $this->container = $container;
            $this->drupalProxyOriginalServiceId = $drupal_proxy_original_service_id;
        }

        /**
         * Lazy loads the real service from the container.
         *
         * @return object
         *   Returns the constructed real service.
         */
        protected function lazyLoadItself()
        {
            if (!isset($this->service)) {
                $this->service = $this->container->get($this->drupalProxyOriginalServiceId);
            }

            return $this->service;
        }

        /**
         * {@inheritdoc}
         */
        public function deleteBookCovers(int $batchId, $batchTotal, array $nids, object &$context)
        {
            return $this->lazyLoadItself()->deleteBookCovers($batchId, $batchTotal, $nids, $context);
        }

        /**
         * {@inheritdoc}
         */
        public function fetchBookCovers(int $batchId, $batchTotal, array $nids, object &$context)
        {
            return $this->lazyLoadItself()->fetchBookCovers($batchId, $batchTotal, $nids, $context);
        }

        /**
         * {@inheritdoc}
         */
        public function replaceBookCovers(int $batchId, int $batchTotal, array $nids, object &$context)
        {
            return $this->lazyLoadItself()->replaceBookCovers($batchId, $batchTotal, $nids, $context);
        }

        /**
         * {@inheritdoc}
         */
        public function batchFinished(bool $success, array $results, array $operations)
        {
            return $this->lazyLoadItself()->batchFinished($success, $results, $operations);
        }

    }

}
