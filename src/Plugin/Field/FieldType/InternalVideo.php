<?php

namespace Drupal\internal_video\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Plugin implementation of the 'internal_video' field type.
 *
 * @FieldType(
 *   id = "internal_video",
 *   label = @Translation("Internal video"),
 *   description = @Translation("This field stores a Internal video in the database."),
 *   default_widget = "internal_video",
 *   default_formatter = "internal_video"
 * )
 */
class InternalVideo extends FieldItemBase {
  /**
   * Definitions of the contained properties.
   *
   * @var array
   */
  public static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'description' => 'The video URL.',
          'type' => 'varchar',
          'length' => 1024,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Video URL'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'value';
  }

}
