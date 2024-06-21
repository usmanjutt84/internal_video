<?php

namespace Drupal\internal_video\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Internal video settings for this site.
 */
class InternalVideoSettingsForm extends ConfigFormBase {

  /**
   * Config settings
   */
  const SETTINGS = 'internal_video.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'internal_video_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get(static::SETTINGS);
    $form['text'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('The following settings will be used as default values on all internal video fields.') . '</p>',
    ];

    $form['source_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source domain'),
      '#default_value' => $config->get('source_domain'),
      '#placeholder' => 'http://www.example.com',
      '#required' => true,
      '#description' => $this->t('Enter the domain including the https scheme and without trailing slash.'),
    ];

    $form['allowed_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed extensions'),
      '#default_value' => $config->get('allowed_extensions'),
      '#placeholder' => 'mp4, webm',
      '#required' => true,
      '#description' => $this->t('Comma separated list of allowed extensions without dots e.g "mp4, webm"'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('source_domain', $values['source_domain'])
      ->set('allowed_extensions', $values['allowed_extensions'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
