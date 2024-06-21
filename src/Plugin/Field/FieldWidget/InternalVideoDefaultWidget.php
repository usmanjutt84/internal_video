<?php

namespace Drupal\internal_video\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'internal_video' widget.
 *
 * @FieldWidget(
 *   id = "internal_video",
 *   label = @Translation("Internal video widget"),
 *   field_types = {
 *     "internal_video"
 *   },
 * )
 */
class InternalVideoDefaultWidget extends WidgetBase implements ContainerFactoryPluginInterface {
  const VIDEO_PATH = "/video.mp4";

  /**
   * Drupal configuration service container.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    ConfigFactory $config_factory
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->config = $config_factory->get('internal_video.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('config.factory'),
    );
  }

  protected function sampleUrl() {
    return $this->config->get('source_domain').self::VIDEO_PATH;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'placeholder_url' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['placeholder_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder for URL'),
      '#default_value' => $this->sampleUrl(), //$this->getSetting('placeholder_url'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
      '#maxlength' => 1024,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $placeholder_url = $this->getSetting('placeholder_url');
    if (empty($placeholder_url)) {
      $summary[] = $this->t('No placeholders');
    }
    else {
      if (!empty($placeholder_url)) {
        $summary[] = $this->t('URL placeholder: @placeholder_url', ['@placeholder_url' => $placeholder_url]);
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = $element + [
      '#type' => 'textfield',
      '#placeholder' => $this->sampleUrl(), // $this->getSetting('placeholder_url'),
      '#default_value' => $items[$delta]->value ?? NULL,
      '#maxlength' => 255,
      '#element_validate' => [[$this, 'validateValue']],
    ];

    if ($element['value']['#description'] == '') {
      $element['value']['#description'] = $this->t('Enter the Internal URL. Valid URL format includes: @url', [
        '@url' => $this->sampleUrl(),
      ]);
    }

    return $element;
  }

  /**
   * Validate video URL.
   */
  public function validateValue(&$element, FormStateInterface &$form_state, $form) {
    $value = $element['#value'];
    $message_prefix = 'Please provide a valid Internal video URL';

    // Check if the value is a URL
    if (filter_var($value, FILTER_VALIDATE_URL)) {
      $domain = (string) $this->config->get('source_domain');
      $allowed_extensions = (string) $this->config->get('allowed_extensions');

      // Check if URL doens't start with "source_domain"
      if(!str_starts_with($value, $domain)) {
        $form_state->setError($element, $this->t('@message_prefix that starts with "@url".', [
          '@message_prefix' => $message_prefix,
          '@url' => $domain,
        ]));
      }
      else {
        // Find the extension from the file path
        $video_path = parse_url($value, PHP_URL_PATH);
        $ext = pathinfo($video_path, PATHINFO_EXTENSION);

        // Check if the extension doesn't exist
        if(!$ext) {
          $form_state->setError($element, $this->t('@message_prefix that ends with any of following extensions: "@extensions".', [
            '@message_prefix' => $message_prefix,
            '@extensions' => $allowed_extensions,
          ]));
        }else {
          // Convert comma separated list of extensions into an array
          $allowed_extensions_array = explode(',', preg_replace('/\s+/', '', $allowed_extensions));

          // Check if the extension is in the allowed list
          if(in_array($ext, $allowed_extensions_array)) {
            $form_state->setValueForElement($element, $value);
          }else {
            $form_state->setError($element, $this->t('@message_prefix that ends with any of following extensions: "@extensions".', [
              '@message_prefix' => $message_prefix,
              '@extensions' => $allowed_extensions,
            ]));
          }
        }
      }
    }
    elseif (!empty($value)) {
      $form_state->setError($element, $this->t('@message_prefix e.g "@url".', [
        '@url' => $this->sampleUrl(),
        '@message_prefix' => $message_prefix,
      ]));
    }
  }

}
