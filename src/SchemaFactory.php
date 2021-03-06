<?php

namespace Drupal\graphql;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\graphql\GraphQL\Relay\Schema;
use Drupal\graphql\GraphQL\Validator\ConfigValidator\Rules\TypeValidationRule;
use Youshido\GraphQL\Validator\ConfigValidator\ConfigValidator;

/**
 * Loads and caches a generated GraphQL schema.
 */
class SchemaFactory {
  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The schema provider service.
   *
   * @var \Drupal\graphql\SchemaProviderInterface
   */
  protected $schemaProvider;

  /**
   * The schema cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $schemaCache;

  /**
   * The service configuration.
   *
   * @var array
   */
  protected $config;

  /**
   * Constructs a SchemaFactory object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   * @param \Drupal\graphql\SchemaProviderInterface $schemaProvider
   *   The schema provider service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $schemaCache
   *   The schema cache backend.
   * @param array $config
   *   The configuration provided through the services.yml.
   */
  public function __construct(LanguageManagerInterface $languageManager, SchemaProviderInterface $schemaProvider, CacheBackendInterface $schemaCache, array $config) {
    $this->config = $config;

    // Override the default type validator to enable services as field resolver
    // callbacks.
    $validator = ConfigValidator::getInstance();
    $validator->addRule('type', new TypeValidationRule($validator));

    $this->schemaProvider = $schemaProvider;
    $this->languageManager = $languageManager;
    $this->schemaCache = $schemaCache;
  }

  /**
   * Loads and caches the generated schema.
   *
   * @return \Drupal\graphql\GraphQL\Relay\Schema
   *   The generated GraphQL schema.
   */
  public function getSchema() {
    $useCache = $this->config['cache'];
    $language = $this->languageManager->getCurrentLanguage();
    if ($useCache && $schema = $this->schemaCache->get($language->getId())) {
      return $schema->data;
    }

    $schemaClass = $this->config['schema_class'];
    $query = $this->schemaProvider->getQuerySchema();
    $mutation = $this->schemaProvider->getMutationSchema();
    $schema = new $schemaClass($query, $mutation);

    if ($useCache) {
      // Cache the generated schema in the configured cache backend.
      $tags = array_unique($this->schemaProvider->getCacheTags() ?: []);
      $this->schemaCache->set($language->getId(), $schema, Cache::PERMANENT, $tags);
    }

    return $schema;
  }
}
