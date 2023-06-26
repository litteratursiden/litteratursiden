<?php

namespace Drupal\lit_user\Controller;

use Drupal\Core\Url;
use Drupal\user_registrationpassword\Controller\RegistrationController as UserRegistrationPasswordDefault;

class UserRegistrationPassword extends UserRegistrationPasswordDefault {

  /**
   * {@inheritdoc}
   */
  public function confirmAccount($uid, $timestamp, $hash) {
    $route_name = '<front>';
    $route_options = [];
    $url_options = [];
    $current_user = $this->currentUser();

    // When processing the one-time login link, we have to make sure that a user
    // isn't already logged in.
    if ($current_user->isAuthenticated()) {
      // The existing user is already logged in.
      if ($current_user->id() == $uid) {
        \Drupal::messenger()->addMessage(t('You are currently authenticated as user %user.',
          ['%user' => $current_user->getAccountName()]));

        // Redirect to user page.
        $route_name = 'entity.user.canonical';
        $route_options = ['user' => $current_user->id()];
      }
      else {
        // A different user is already logged in on the computer.
        $reset_link_account = $this->userStorage->load($uid);
        if (!empty($reset_link_account)) {
          \Drupal::messenger()->addMessage($this->t('Another user (%other_user) is already logged into the site on this computer, but you tried to use a one-time link for user %resetting_user. Please <a href=":logout">log out</a> and try using the link again.',
            [
              '%other_user' => $current_user->getDisplayName(),
              '%resetting_user' => $reset_link_account->getDisplayName(),
              ':logout' => Url::fromRoute('user.logout'),
            ]), 'warning');
        }
        else {
          // Invalid one-time link specifies an unknown user.
          $route_name = user_registrationpassword_set_message('linkerror', TRUE);
        }
      }
    }
    else {
      // Time out, in seconds, until login URL expires. 24 hours = 86400 seconds.
      $timeout = $this->config('user_registrationpassword.settings')
        ->get('registration_ftll_timeout');
      $current = REQUEST_TIME;
      $timestamp_created = $timestamp - $timeout;

      // Some redundant checks for extra security ?
      $users = $this->entityQuery
        ->condition('uid', $uid)
        ->condition('status', 0)
        ->condition('access', 0)
        ->execute();

      // Timestamp can not be larger then current.
      /** @var \Drupal\user\UserInterface $account */
      if ($timestamp_created <= $current && !empty($users) && $account = $this->userStorage->load(reset($users))) {
        // Check if we have to enforce expiration for activation links.
        if ($this->config('user_registrationpassword.settings')
            ->get('registration_ftll_expire') && !$account->getLastLoginTime() && $current - $timestamp > $timeout) {
          $route_name = $this->userRegistrationpasswordSetMessage('linkerror', TRUE);
        }
        // Else try to activate the account.
        // Password = user's password - timestamp = current request - login = username.
        // user_pass_rehash($password, $timestamp, $login, $uid)
        elseif ($account->id() && $timestamp >= $account->getCreatedTime() && !$account->getLastLoginTime() && $hash == user_pass_rehash($account,
            $timestamp)
        ) {
          // Format the date, so the logs are a bit more readable.
          $date = $this->dateFormatter->format($timestamp);
          $this->getLogger('user')
            ->notice('User %name used one-time login link at time %timestamp.',
              ['%name' => $account->getAccountName(), '%timestamp' => $date]);
          // Activate the user and update the access and login time to $current.
          $account
            ->activate()
            ->setLastAccessTime($current)
            ->setLastLoginTime($current)
            ->save();

          // user_login_finalize() also updates the login timestamp of the
          // user, which invalidates further use of the one-time login link.
          user_login_finalize($account);

          // Display default welcome message.
          \Drupal::messenger()->addMessage(t('You have just used your one-time login link. Your account is now active and you are authenticated.'));

          // Redirect to user.
          $route_name = 'entity.user.edit_form';
          $route_options = ['user' => $account->id()];
          $url_options = ['query' => ['first_time_logging' => 1]];
        }
        // Something else is wrong, redirect to the password
        // reset form to request a new activation e-mail.
        else {
          $route_name = $this->userRegistrationpasswordSetMessage('linkerror', TRUE);
        }
      }
      else {
        // Deny access, no more clues.
        // Everything will be in the watchdog's
        // URL for the administrator to check.
        $route_name = $this->userRegistrationpasswordSetMessage('linkerror', TRUE);
      }
    }

    return $this->redirect($route_name, $route_options, $url_options);
  }

  /**
   * Set message for user registration.
   *
   * Blatantly stolen from deprecated user_registrationpassword_set_message()
   * in user_registrationpassword module.
   *
   * @param string $type
   *   The type of message.
   *
   * @param string $redirect
   *   Whether to redirect.
   *
   * @return string
   *   The redirect route
   */
  private function userRegistrationpasswordSetMessage(string $type = 'welcome', string $redirect = ''): string {
    $route_name = '';

    // Select what message to display.
    switch ($type) {
      case 'linkerror':
        \Drupal::messenger()->addStatus(t('You have tried to use a one-time login link that has either been used or is no longer valid. Please request a new one using the form below.'));

        // Redirect to user/pass.
        if (!empty($redirect)) {
          $route_name = 'user.pass';
        }
        break;

      case 'welcome':
        \Drupal::messenger()->addStatus(t('Further instructions have been sent to your email address.'));
        // Redirect to front.
        if (!empty($redirect)) {
          $route_name = '<front>';
        }
        break;

    }

    return $route_name;
  }
}
