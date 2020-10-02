<?php

namespace Drupal\openy_gc_shared_content\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form handler for the SharedContentSource add and edit forms.
 */
class SharedContentSourceServerForm extends EntityForm {

  /**
   * A guzzle http client instance.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The configuration factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs an SharedContentSource object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   * @param \GuzzleHttp\Client $client
   *   A guzzle http client instance.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, Client $client, RequestStack $request_stack, ConfigFactoryInterface $config, LoggerInterface $logger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->client = $client;
    $this->config = $config;
    $this->logger = $logger;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('http_client'),
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('logger.factory')->get('openy_gc_shared_content')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source URL'),
      '#maxlength' => 255,
      '#default_value' => $entity->getUrl(),
      '#required' => TRUE,
    ];

    $form['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source Token'),
      '#description' => $this->t('Token will be generated on form submit.'),
      '#maxlength' => 255,
      '#attributes' => [
        'disabled' => TRUE,
      ],
      '#default_value' => $entity->getToken(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    if (!$form_state->getValue('token')) {
      $token = $this->requestToken($form_state->getValue('url'));
      if (!$token) {
        $this->messenger()->addError($this->t('Something went wrong during token generating.'));
      }
      else {
        $entity->set('token', $token);
      }
    }
    $status = $entity->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('The %label shared source created.', [
        '%label' => $entity->label(),
      ]));
      $this->messenger()->addMessage($this->t("Your request to use Virtual Y Shared Content network was sent. You can use it after administrator's approval.", [
        '%label' => $entity->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label shared source updated.', [
        '%label' => $entity->label(),
      ]));
    }

    $form_state->setRedirect('entity.shared_content_source_server.collection');
  }

  /**
   * Helper function to check whether an shared source configuration exists.
   */
  protected function requestToken($url) {
    $options = [
      'headers' => [
        'Content-type' => 'application/json',
      ],
      'body' => json_encode([
        'name' => $this->config->get('system.site')->get('name'),
        'host' => $this->request->getSchemeAndHttpHost(),
      ]),
    ];
    try {
      $response = $this->client->request('POST', $url . '/virtual-y/shared-source/generate-token', $options);
      $code = $response->getStatusCode();
      if ($code == 200) {
        $body = json_decode($response->getBody()->getContents(), TRUE);
        return isset($body['token']) ? $body['token'] : NULL;
      }
    }
    catch (RequestException $e) {
      $this->logger->notice($e->getMessage());
      return NULL;
    }
  }

  /**
   * Helper function to check whether an shared source configuration exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager
      ->getStorage('shared_content_source_server')
      ->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
