<?php

namespace Drupal\internal_video\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Form\FormStateInterface;
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
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'tracking' => false,
    ] + parent::defaultFieldSettings();
  }

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
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['tracking'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable tracking'),
      '#default_value' => $this->getSetting('tracking'),
      '#description' => $this->t('Enable tracking of internal videos.'),
    ];

    return $element;
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
