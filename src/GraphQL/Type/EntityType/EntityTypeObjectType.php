<?php

namespace Drupal\graphql\GraphQL\Type\EntityType;

use Drupal\Core\Entity\EntityTypeInterface;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\StringType;

class EntityTypeObjectType extends AbstractObjectType {
  /**
   * EntityTypeObjectType constructor.
   */
  public function __construct() {
    $config = [
      'name' => 'EntityType',
      'fields' => [
        'label' => [
          'type' => new NonNullType(new StringType()),
          'resolve' => [__CLASS__, 'resolveLabelFieldValue'],
        ],
        'pluralLabel' => [
          'type' => new NonNullType(new StringType()),
          'resolve' => [__CLASS__, 'resolvePluralLabelFieldValue'],
        ],
      ],
    ];

    parent::__construct($config);
  }

  /**
   * Field value resolver function.
   *
   * Implemented on behalf of the "label" field definition on this type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $value
   *   The entity type for which to resolve the label.
   *
   * @return string
   *   The label of the entity type.
   */
  public static function resolveLabelFieldValue(EntityTypeInterface $value) {
    return $value->getLabel();
  }

  /**
   * Field value resolver function for.
   *
   * Implemented on behalf of the "pluralLabel" field definition on this type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $value
   *   The entity type for which to resolve the label.
   *
   * @return string
   *   The label of the entity type.
   */
  public static function resolvePluralLabelFieldValue(EntityTypeInterface $value) {
    return $value->getPluralLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // @todo This method should not be required.
  }
}