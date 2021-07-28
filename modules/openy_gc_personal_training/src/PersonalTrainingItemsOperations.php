<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class for reacting to Personal Trainings items events.
 */
class PersonalTrainingItemsOperations implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The Identity Provider plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
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
   * {@inheritdoc}
   */
  public function __construct(
    PluginManagerInterface $auth_manager,
    MailManagerInterface $mail_manager,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager
  ) {
    $this->authManager = $auth_manager;
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.gc_identity_provider'),
      $container->get('plugin.manager.mail'),
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * Implements logic for the ENTITY_TYPE_delete hook.
   *
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $personal_training
   *   Entity that was just deleted.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *
   * @see hook_ENTITY_TYPE_delete()
   */
  public function entityDelete(PersonalTrainingInterface $personal_training) {
    $timezone = date_default_timezone_get();
    $startDt = DrupalDateTime::createFromFormat(
      DateTimeItemInterface::DATETIME_STORAGE_FORMAT,
      $personal_training->get('date')->value,
      DateTimeItemInterface::STORAGE_TIMEZONE
    );
    $startDt->setTimezone(timezone_open($timezone));
    $personal_training_settings = $this->configFactory->get('openy_gc_personal_training.settings');
    $params = [
      'subject' => $personal_training_settings->get('meeting_delete_subject'),
      'message' => $personal_training_settings->get('meeting_delete_message'),
      'meeting_title' => $personal_training->get('title')->value,
      'meeting_start_date' => $startDt->format('r'),
    ];

    $this->sendNotification($this->getUserEmail($personal_training->getCustomerId()), $params);
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

}
