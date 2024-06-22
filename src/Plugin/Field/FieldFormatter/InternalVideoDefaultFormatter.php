<?php

namespace Drupal\internal_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'internal_video' formatter.
 *
 * @FieldFormatter(
 *   id = "internal_video",
 *   label = @Translation("Internal video"),
 *   field_types = {
 *     "internal_video"
 *   }
 * )
 */
class InternalVideoDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'video_theme' => 'city',
      'video_size' => '450x315',
      'video_width' => '',
      'video_height' => '',
      'video_autoplay' => '',
      'video_mute' => '',
      'video_loop' => '',
      'video_controls' => '',
      'video_autohide' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    foreach ($this->getFields() as $key => $field) {
      $elements[$key] = $field;
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();
    $fields = $this->getFields();

    foreach ($fields as $key => $field) {
      $summary[] = $field['#title'].': '.$settings[$key];
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareView(array $entities_items) {}

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $settings = $this->getSettings();

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'internal_video',
        '#value' => $item->value,
        '#entity_title' => $items->getEntity()->label(),
        '#settings' => $settings,
      ];

      // Attach videoJS to the element
      $element[$delta]['#attached']['library'][] = 'internal_video/internal_video.videojs';

      // Attach videoJS theme to the element
      if ($theme = $settings['video_theme']) {
        $element[$delta]['#attached']['library'][] = 'internal_video/internal_video.theme.'.$theme;
      }


      if ($settings['video_size'] == 'responsive') { // TODO: remove?
        // $element[$delta]['#attached']['library'][] = 'youtube/drupal.youtube.responsive';
      }
    }
    return $element;
  }

  /**
   * Get all custom fields for the specified format
   */
  protected function getFields() {
    $elements = [];

    $elements['video_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => [
        'city' => $this->t('City'),
        'fantasy' => $this->t('Fantasy'),
        'forest' => $this->t('Forest'),
        'sea' => $this->t('Sea'),
      ],
      '#default_value' => $this->getSetting('video_theme'),
    ];
    $elements['video_size'] = [
      '#type' => 'select',
      '#title' => $this->t('Size'),
      '#options' => _internal_video_size_options(),
      '#default_value' => $this->getSetting('video_size'),
    ];
    $elements['video_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#size' => 10,
      '#default_value' => $this->getSetting('video_width'),
      '#states' => [
        'visible' => [
          ':input[name*="video_size"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $elements['video_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#size' => 10,
      '#default_value' => $this->getSetting('video_height'),
      '#states' => [
        'visible' => [
          ':input[name*="video_size"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $elements['video_autoplay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autoplay'),
      '#description' => $this->t('Play video automatically when loaded (autoplay).'),
      '#default_value' => $this->getSetting('video_autoplay'),
    ];
    $elements['video_mute'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mute'),
      '#description' => $this->t('Mute video by default when loaded (mute).'),
      '#default_value' => $this->getSetting('video_mute'),
    ];
    $elements['video_loop'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Loop'),
      '#description' => $this->t('Loop the playback of the video (loop).'),
      '#default_value' => $this->getSetting('video_loop'),
    ];
    $elements['video_controls'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Controls'),
      '#description' => $this->t('Always hide video controls (controls).'),
      '#default_value' => $this->getSetting('video_controls'),
    ];
    $elements['video_autohide'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autohide'),
      '#description' => $this->t('Hide video controls after play begins (autohide).'),
      '#default_value' => $this->getSetting('video_autohide'),
    ];

    return $elements;
  }
}
