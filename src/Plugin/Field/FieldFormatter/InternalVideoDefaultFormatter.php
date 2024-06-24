<?php

namespace Drupal\internal_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\internal_video\Plugin\Field\FieldType\InternalVideo;

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
   * The configs.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The The tracking service.
   *
   * @var \Drupal\internal_video\Controller\Tracking
   */
  protected $tracking;


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->config = $container->get('config.factory')->get('internal_video.settings');
    $instance->tracking = $container->get('internal_video.tracking');
    return $instance;
  }

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
    $tracking_enabled = $items->getFieldDefinition()->getSettings();
    $settings = $this->getSettings();
    $is_login = \Drupal::currentUser()->isAuthenticated();

    foreach ($items as $delta => $item) {
      // Get tracking info if video tracking is enabled and user is authenticated.
      $tracking_info = $is_login && $tracking_enabled ? $this->getTrackingInfo($item) : [];
      // Find if it's already tracked
      $already_tracked = $tracking_info ? $this->tracking->findTracking($tracking_info) : false;
      /**
       * Generate unique id for tracking only when:
       *  - video tracking is enabled
       *  - user is authenticated
       *  - video is not yet tracked
       */
      $unique_id = $tracking_info && !$already_tracked ? uniqid() : null;

      $src = $item->value;
      $headers = get_headers($src, true);
      $mime_type = $headers['Content-Type'];

      $element[$delta] = [
        '#theme' => 'internal_video',
        '#video' => [
          'src' => $src,
          'mime_type' => $mime_type,
        ],
        '#entity_title' => $items->getEntity()->label(),
        '#settings' => $settings,
        '#tracking_id' => $unique_id,
      ];

      // Attach videoJS to the element
      $element[$delta]['#attached']['library'][] = 'internal_video/internal_video.videojs';

      // Attach videoJS theme to the element
      if ($theme = $settings['video_theme']) {
        $element[$delta]['#attached']['library'][] = 'internal_video/internal_video.theme.'.$theme;
      }
      // if video tracking is enabled unique_id is created
      if($tracking_enabled && $unique_id) {
        $element[$delta]['#attached']['drupalSettings']['internal_video_tracking'][$unique_id] = $tracking_info;
        // Add wait time for video tracking
        $element[$delta]['#attached']['drupalSettings']['internal_video_tracking']['wait_time'] = $this->config->get('wait_time');
        // Attach tracking to the element
        $element[$delta]['#attached']['library'][] = 'internal_video/internal_video.tracking';
      }
    }

    return $element;
  }

  /**
   * Get tracking information to add in the video attribute
   *
   * @param InternalVideo $field
   * @return array
   */
  protected function getTrackingInfo(InternalVideo $field) {
    $curren_user = \Drupal::currentUser();
    if(!$curren_user->isAuthenticated()) return [];

    $field_definition = $field->getFieldDefinition();
    $uid = $curren_user->id();
    $entity_type = $field_definition->getTargetEntityTypeId();
    $entity_bunde = $field_definition->getTargetBundle();
    $entity_id = $field->getEntity()->id();
    $field_name = $field_definition->getName();
    $video_uri = $field->value;

    return [
      'uid' => $uid,
      'entity_type' => $entity_type,
      'entity_bundle' => $entity_bunde,
      'entity_id' => $entity_id,
      'field_name' => $field_name,
      'video_uri' => $video_uri,
    ];
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
