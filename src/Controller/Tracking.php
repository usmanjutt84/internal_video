<?php

namespace Drupal\internal_video\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A controller to track video
 */
class Tracking extends ControllerBase {

  CONST TABLE_NAME = 'internal_video_tracking';

  /**
   * Current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;


  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected  $database;

  /**
   * Constructor for Tracking.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *  The current user.
   * @param \Drupal\Core\Database\Connection $database
   *  The database connection service
   */
  public function __construct(AccountInterface $current_user, Connection $database) {
    $this->currentUser = $current_user;
    $this->database = $database;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
    // Load the service required to construct this class.
      $container->get('current_user'),
      $container->get('database')
    );
  }

  /**
   * Callback for custom_function route.
   * @param Request $request
   */
  public function tracking(Request $request) {
    $respose = 'not_authorized';

    if($this->currentUser->isAuthenticated()) {
      // Get the JSON data from the request and decode it into an array.
      $tarcking_data = json_decode($request->get('tracking'), true);
      $respose = $this->insertTracking($tarcking_data);
    }

    return new JsonResponse($respose);
  }

  /**
   * Find of the tracking is already in the database and return boolean value
   *
   * @param array $tracking
   * @return boolean
   */
  public function findTracking($tracking) {
    if(isset($tracking['tid'])) unset($tracking['tid']);
    if(isset($tracking['timestamp'])) unset($tracking['timestamp']);

    $result = $this->database
      ->select(self::TABLE_NAME, 'track')
      ->condition('track.uid', $tracking['uid'], '=')
      ->condition('track.entity_type', $tracking['entity_type'], '=')
      ->condition('track.entity_bundle', $tracking['entity_bundle'], '=')
      ->condition('track.entity_id', $tracking['entity_id'], '=')
      ->condition('track.field_name', $tracking['field_name'], '=')
      ->condition('track.video_uri', $tracking['video_uri'], '=')
      ->fields('track', ['tid'])
      ->countQuery()
      ->execute()
      ->fetchField();

    return $result ? true : false;
  }

  /**
   * Insert tracking data into the database and return string response
   *
   * @param array $tracking
   * @return string
   */
  protected function insertTracking($tracking) {
    if($this->findTracking($tracking)) {
      return 'already_tracked';
    }

    $tracking = [
      'timestamp' => \Drupal::time()->getRequestTime(),
    ] + $tracking;

    try {
      $this->database->insert(self::TABLE_NAME)->fields($tracking)->execute();
      return 'video_is_tracked';
    } catch (\Exception $e) {
      \Drupal::logger('internal_video')->error($e->getMessage());
      return 'video_could_not_be_tracked';
    }
  }
}
