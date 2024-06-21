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
      'video_size' => '450x315',
      'video_width' => '',
      'video_height' => '',
      'video_autoplay' => '',
      'video_mute' => '',
      'video_loop' => '',
      'video_controls' => '',
      'video_autohide' => '',
      'video_load_policy' => '', // TODO: remove?
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['video_size'] = [
      '#type' => 'select',
      '#title' => $this->t('Internal video size'),
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
      '#title' => $this->t('Play video automatically when loaded (autoplay).'),
      '#default_value' => $this->getSetting('video_autoplay'),
    ];
    $elements['video_mute'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mute video by default when loaded (mute).'),
      '#default_value' => $this->getSetting('video_mute'),
    ];
    $elements['video_loop'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Loop the playback of the video (loop).'),
      '#default_value' => $this->getSetting('video_loop'),
    ];
    $elements['video_controls'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Always hide video controls (controls).'),
      '#default_value' => $this->getSetting('video_controls'),
    ];
    $elements['video_autohide'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide video controls after play begins (autohide).'),
      '#default_value' => $this->getSetting('video_autohide'),
    ];
    $elements['video_load_policy'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide video annotations by default (iv_load_policy).'),
      '#default_value' => $this->getSetting('video_load_policy'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $cp = '';
    $video_size = $this->getSetting('video_size');

    $parameters = [
      $this->getSetting('video_autoplay'),
      $this->getSetting('video_mute'),
      $this->getSetting('video_loop'),
      $this->getSetting('video_controls'),
      $this->getSetting('video_autohide'),
      $this->getSetting('video_load_policy'),
    ];

    foreach ($parameters as $parameter) {
      if ($parameter) {
        $cp = ', custom parameters';
        break;
      }
    }
    $summary[] = $this->t('Internal video: @video_size@cp', [
      '@video_size' => $video_size,
      '@cp' => $cp,
    ]);
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

      $element[$delta]['#attached']['library'][] = 'internal_video/internal_video.videojs';

      if ($settings['video_size'] == 'responsive') { // TODO: remove?
        // $element[$delta]['#attached']['library'][] = 'youtube/drupal.youtube.responsive';
      }
    }
    return $element;
  }

}
