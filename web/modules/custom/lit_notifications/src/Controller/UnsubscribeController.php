<?php

namespace Drupal\lit_notifications\Controller;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use Drupal\flag\Entity\Flag;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for unsubscribing users from notifications.
 */
class UnsubscribeController extends ControllerBase {

  /**
   * Generates has for unsubscribing user.
   *
   * @param UserInterface $user
   *   User who should be unsubscribed.
   * @param NodeInterface $node
   *   Content from which user should be unsubscribed.
   *
   * @return string
   *   Generated hash.
   */
  public static function getHash(UserInterface $user, NodeInterface $node) {
    $data = $user->get('uuid')->value . ':' . $node->get('uuid')->value;
    $private_key = \Drupal::service('private_key')->get();
    $salt = Settings::getHashSalt();

    return Crypt::hmacBase64($data, $private_key . $salt);
  }

  /**
   * Checks whether user has access for unsubscribing from notifications.
   *
   * @param int $user
   *   ID of user who should be unsubscribed.
   * @param int $node
   *   ID Node from which user should be unsubscribed.
   * @param string $hash
   *   Validating hash.
   *
   * @return AccessResult
   *   Whether user has access.
   */
  public function unsubscribeAccess($user, $node, $hash) {
    $user = User::load($user);
    $node = Node::load($node);

    if (!$user || !$node) {
      return AccessResult::forbidden();
    }

    $expected_hash = static::getHash($user, $node);
    return AccessResult::allowedIf(Crypt::hashEquals($expected_hash, $hash));
  }

  /**
   * Notifications unsubscribe callback.
   *
   * @param UserInterface $user
   *   User who should be unsubscribed.
   * @param NodeInterface $node
   *   Content from which user should be unsubscribed.
   *
   * @return RedirectResponse
   *   Redirect response to the node page.
   */
  public function unsubscribe(UserInterface $user, NodeInterface $node) {
    $flag = Flag::load('notifications');

    if ($flag->isFlagged($node, $user)) {
      /** @var \Drupal\flag\FlagService $flag_service */
      $flag_service = \Drupal::service('flag');

      try {
        $flag_service->unflag($flag, $node, $user);
        drupal_set_message(t("You've successfully unscubscribed from %node_title.", ['%node_title' => $node->getTitle()]));
      }
      catch (\LogicException $exception) {
        drupal_set_message(t('Something sent wrong during the unsubscribing. Please contact the administrator.'), 'error');
      }
    }
    else {
      drupal_set_message(t("You already unscubscribed from %node_title.", ['%node_title' => $node->getTitle()]));
    }

    return new RedirectResponse($node->toUrl()->toString());
  }

}
