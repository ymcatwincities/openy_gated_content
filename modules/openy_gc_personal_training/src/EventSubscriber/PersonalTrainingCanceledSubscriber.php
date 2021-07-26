<?php

namespace Drupal\openy_gc_personal_training\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\openy_gc_auth\GCIdentityProviderManager;
use Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface;
use Drupal\openy_gc_personal_training\PersonalTrainingSeriesManagerInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\Utility\Token;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for Personal Training being cancelled.
 */
class PersonalTrainingCanceledSubscriber implements EventSubscriberInterface {

  /**
   * The gated content authentication manager.
   *
   * @var \Drupal\openy_gc_auth\GCIdentityProviderManager
   */
  protected $authManager;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The personal training series manager.
   *
   * @var \Drupal\openy_gc_personal_training\PersonalTrainingSeriesManagerInterface
   */
  protected $personalTrainingSeriesManager;

  /**
   * PersonalTrainingCanceledSubscriber constructor.
   *
   * @param \Drupal\openy_gc_auth\GCIdentityProviderManager $auth_manager
   *   The gated content authentication manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\openy_gc_personal_training\PersonalTrainingSeriesManagerInterface $personal_training_series_manager
   *   The personal training series manager.
   */
  public function __construct(
    GCIdentityProviderManager $auth_manager,
    MailManagerInterface $mail_manager,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    PersonalTrainingSeriesManagerInterface $personal_training_series_manager
  ) {
    $this->authManager = $auth_manager;
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->personalTrainingSeriesManager = $personal_training_series_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'personal_training.cancel.post_transition' => 'onPersonalTrainingCancelTransition',
    ];
  }

  /**
   * Method called after personal training is canceled.
   *
   * Used to send an email notification to a customer about event being
   * canceled.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   Post transition event.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onPersonalTrainingCancelTransition(WorkflowTransitionEvent $event) {
    /** @var \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $personal_training */
    $personal_training = $event->getEntity();

    // @TODO: Check if personal training has field_parent. Notify customer only
    // when it has no parent. Otherwise multiple notifications are going to be
    // sent.
    $this->notifyCustomer($personal_training);

    if ($personal_training->bundle() === 'training_series') {
      /* @see \Drupal\openy_gc_personal_training\PersonalTrainingSeriesOperations::trainingCancel() */
      $this->personalTrainingSeriesManager->buildBatch(
        $personal_training->id(),
        ['cancelItemsOfSeries']
      );
    }
  }

  /**
   * Notify the personal training customer about session cancel.
   *
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $personal_training
   *   The personal training entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function notifyCustomer(PersonalTrainingInterface $personal_training) {
    $to = $this->getUserEmail($personal_training->getCustomerId());
    $params = $this->prepareMailParams($personal_training);

    $this->sendNotification($to, $params);
  }

  /**
   * Helper function to get user email based on active auth plugin.
   *
   * @param int $uid
   *   Drupal user ID.
   *
   * @return string
   *   User email.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getUserEmail(int $uid) {
    $virtual_y_config = $this->configFactory->get('openy_gc_auth.settings');
    $active_provider = $virtual_y_config->get('active_provider');
    $this->authManager->getDefinition($virtual_y_config->get('active_provider'), TRUE);
    /** @var \Drupal\openy_gc_auth\GCIdentityProviderPluginBase $plugin_instance */
    $plugin_instance = $this->authManager->createInstance($active_provider);
    return $plugin_instance->getMemberNotificationEmail($uid);
  }

  /**
   * Helper function to send email notifications.
   *
   * @param string $to
   *   Destination email.
   * @param array $params
   *   Email params like subject, title, etc.
   *
   * @see openy_gc_personal_training_mail
   */
  public function sendNotification(string $to, array $params) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $this->mailManager->mail('openy_gc_personal_training', 'openy_gc_personal_training_notify', $to, $langcode, $params);
  }

  /**
   * Helper method to prepare parameters to be used for email.
   *
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $personal_training
   *   Personal training entity.
   *
   * @return array
   *   Prepared array of parameters used for email to be sent. The keys are:
   *     - subject: email subject;
   *     - message: the actual mail message;
   *     - personal_training: Personal Training entity;
   */
  protected function prepareMailParams(PersonalTrainingInterface $personal_training) {
    $personal_training_settings = $this->configFactory->get('openy_gc_personal_training.settings');

    return [
      'subject' => $personal_training_settings->get('meeting_cancel_subject'),
      'message' => $personal_training_settings->get('meeting_cancel_message'),
      'personal_training' => $personal_training,
    ];
  }

}
