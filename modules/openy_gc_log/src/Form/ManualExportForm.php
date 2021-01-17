<?php

namespace Drupal\openy_gc_log\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\openy_gc_log\LogArchiver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ManualExportForm provides an export form for the VY log entities.
 *
 * @ingroup openy_gc_log
 */
class ManualExportForm extends FormBase {

  /**
   * Log Archiver service.
   *
   * @var \Drupal\openy_gc_log\LogArchiver
   */
  private $logArchiver;


  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $config;

  /**
   * Form constructor.
   *
   * @param \Drupal\openy_gc_log\LogArchiver $logArchiver
   *   Log Archiver service.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory.
   */
  public function __construct(
    LogArchiver $logArchiver,
    ConfigFactory $configFactory
  ) {
    $this->logArchiver = $logArchiver;
    $this->config = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('openy_gc_log.log_archiver'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_log_manual_export';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $default_max = new \DateTime(
      'now',
      $this->getDateTimeZone()
    );

    $default_min = clone $default_max;
    $default_min->modify('first day of this month 00:00');

    $form['created_min'] = [
      '#title' => $this->t('Date Start'),
      '#type' => 'textfield',
      '#default_value' => $default_min->format('Y-m-d'),
    ];

    $form['created_max'] = [
      '#title' => $this->t('Date To'),
      '#type' => 'textfield',
      '#default_value' => $default_max->format('Y-m-d'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Create an export file'),
        '#button_type' => 'primary',
      ],
    ];

    $form['#attached']['library'][] = 'openy_gc_log/datepicker';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $keys = [
      'created_min',
      'created_max',
    ];
    $dates = [];
    foreach ($keys as $key) {
      $value = $form_state->getValue($key, NULL);
      if (empty($value)) {
        $form_state->setErrorByName($key, $this->t('Please, enter the date.'));
        return;
      }
      try {
        $dates[$key] = DrupalDateTime::createFromFormat('Y-m-d', $value, $this->getDateTimeZone());
      }
      catch (\InvalidArgumentException $e) {
        $form_state->setErrorByName($key, $this->t('The date cannot be created from the given format.'));
      }
      catch (\UnexpectedValueException $e) {
        $form_state->setErrorByName($key, $this->t('The created date does not match the input value.'));
      }
    }
    if (
      isset($dates['created_min']) &&
      isset($dates['created_max']) &&
      $dates['created_min'] > $dates['created_max']
    ) {
      $form_state->setErrorByName('created_max', $this->t('"Date To" value should be greater than a "Date Start".'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $start = new \DateTime(
      $form_state->getValue('created_min', NULL),
      $this->getDateTimeZone()
    );
    $end = (new \DateTime(
      $form_state->getValue('created_max', NULL),
      $this->getDateTimeZone())
    );
    $now = new \DateTime('now', $this->getDateTimeZone());
    if ($end->diff($now)->days < 1) {
      $end = $now;
    }
    else {
      $end->setTime(23, 59, 59);
    }

    $this->logArchiver->archive($start, $end, FALSE);

    $form_state->setRedirectUrl(Url::fromRoute('view.files.page_1', [], [
      'query' => [
        'filename' => 'virtual-y',
        'filemime' => 'application/x-gzip',
      ],
    ]));
  }

  /**
   * Takes the timezone from a site config.
   *
   * @return \DateTimeZone
   *   Timezone object.
   */
  public function getDateTimeZone() {
    return new \DateTimeZone(
      $this->config->get('system.date')->get('timezone')['default']
    );
  }

}
