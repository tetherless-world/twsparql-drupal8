<?php

namespace Drupal\twsparql\Form;

// Need this for base class of the form.
use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Form\FormStateInterface;

// Necessary for URL.
use Drupal\Core\Url;

/**
 * Form with the settings for the module.
 */
class TWSparqlSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'twsparql_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'twsparql.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('twsparql.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('TW SPARQL settings'),
      '#collapsible' => FALSE,
    ];

    $form['settings']['endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default SPARQL Endpoint:'),
      '#default_value' => $config->get('endpoint'),
      '#description' => $this->t('If a &lt;sparql&gt; tag does not specify an endpoint, this path will be used instead.'),
    ];
    $form['settings']['default_transform'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default XSL Transform:'),
      '#default_value' => $config->get('default_transform'),
      '#description' => $this->t('If a &lt;sparql&gt; tag does not specify an XSL transformation, this file will be used instead.'),
    ];
    $form['settings']['queries'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Relative Path to Named Queries:'),
      '#default_value' => $config->get('queries'),
      '#description' => $this->t('When a relative path to a named query is given, this path will be used to resolve it'),
    ];
    $form['settings']['xslt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Relative path to XSL Transforms:'),
      '#default_value' => $config->get('xslt'),
      '#description' => $this->t('When a relative path to an XSL transform is specified, this path will be used to resolve it'),
    ];
    $form['settings']['instances_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URI for Instances'),
      '#default_value' => $config->get('instances_uri'),
      '#description' => $this->t('The base path for any items specified using the i= attribute'),
    ];
    $form['settings']['schema_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URI for Schema:'),
      '#default_value' => $config->get('schema_uri'),
      '#description' => $this->t('The base path for any items specified using the s= attribute'),
    ];
    $form['settings']['enable_php_xslt_extensions'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable PHP XSLT Extensions'),
      '#options' => [
        0 => $this->t('Disabled'),
        1 => $this->t('Enabled'),
      ],
      '#default_value' => $config->get('enable_php_xslt_extensions'),
      '#description' => $this->t('Enables PHP extensions in the XSLT processor, allowing stylesheets to make calls to PHP functions. Enable with caution.'),
    ];
    $form['settings']['enable_debug'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable Debug Output?'),
      '#options' => [
        0 => $this->t('Disabled'),
        1 => $this->t('Enabled'),
      ],
      '#default_value' => $config->get('enable_debug'),
      '#description' => $this->t('Outputs debugging information regarding SPARQL queries and XSLT transformations.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check if automatically managed style sheet is posible.
    if ($form_state->getValue('endpoint') == "") {
      $form_state->setErrorByName('endpoint', $this->t('The endpoint specifies what sparql endpoint to hit to run the queries and is required.'));
    }
    if ($form_state->getValue('default_transform') == "") {
      $form_state->setErrorByName('default_transform', $this->t('The default transform url specifies what xsl transformation to use if none is specified and is required'));
    }
    if ($form_state->getValue('queries') == "") {
      $form_state->setErrorByName('queries', $this->t('The queries url specifies the url to find the queries to run and is required.'));
    }
    if ($form_state->getValue('xslt') == "") {
      $form_state->setErrorByName('xslt', $this->t('The xslt url specifies where to find the xslt to run xml translation through and is required.'));
    }
    if ($form_state->getValue('instances_uri') == "") {
      $form_state->setErrorByName('endpoint', $this->t('The base uri for instances is used when constructing the triples of the new instance and is required'));
    }
    if ($form_state->getValue('schema_uri') == "") {
      $form_state->setErrorByName('endpoint', $this->t('The base uri for your schema is used for objects and predicates used in constructing the triples of the new instance and is required'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $errors = $form_state->getErrors();
    if (count($errors) == 0) {
      $config = $this->config('twsparql.settings');
      $config->set('endpoint', $form_state->getValue('endpoint'))
        ->set('default_transform', $form_state->getValue('default_transform'))
        ->set('queries', $form_state->getValue('queries'))
        ->set('xslt', $form_state->getValue('xslt'))
        ->set('instances_uri', $form_state->getValue('instances_uri'))
        ->set('schema_uri', $form_state->getValue('schema_uri'))
        ->set('enable_php_xslt_extensions', $form_state->getValue('enable_php_xslt_extensions'))
        ->set('enable_debug', $form_state->getValue('enable_debug'));
      $config->save();
      parent::submitForm($form, $form_state);
    }
  }
}

