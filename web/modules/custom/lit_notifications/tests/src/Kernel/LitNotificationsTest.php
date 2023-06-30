<?php

namespace Drupal\Tests\lit_notifications\Kernel\Plugin\Action;

use Drupal\comment\Entity\Comment;
use Drupal\flag\Entity\Flag;
use Drupal\node\Entity\Node;
use Drupal\Tests\flag\Kernel\FlagKernelTestBase;

/**
 * Tests email notifications to user.
 *
 * @group lit_notifications
 */
class LitNotificationsTest extends FlagKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'comment',
    'lit_notifications',
    'lit_notifications_test',
  ];

  /**
   * Notifications flag entity.
   *
   * @var \Drupal\flag\FlagInterface
   */
  protected $flag = NULL;

  /**
   * Flag service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService = NULL;

  /**
   * Root user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $root = NULL;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('comment');
    $this->installConfig(['comment']);
    $this->installSchema('comment', ['comment_entity_statistics']);

    $this->flagService = \Drupal::service('flag');
    $this->flag = Flag::load('notifications');

    $bundles = $this->flag->get('bundles');
    $bundles[] = 'lit_notifications_test';
    $this->flag->set('bundles', $bundles);

    $this->root = $this->createUser();
  }

  /**
   * Test user subscription to comments.
   */
  public function testSubscription() {
    $permissions = [
      'post comments',
      'access comments',
      'flag notifications',
      'unflag notifications',
    ];
    $user = $this->createUser($permissions);

    $node = Node::create(['title' => 'TEST', 'type' => 'lit_notifications_test']);
    $node->save();

    $comment_values = [
      'entity_type' => 'node',
      'entity_id' => $node->id(),
      'field_name' => 'lit_notifications_comments',
      'uid' => 0,
      'comment_type' => 'comment',
      'subject' => 'Test Comment',
      'comment_body' => 'Test Comment body',
      'status' => Comment::PUBLISHED,
    ];

    // No subscribers.
    Comment::create($comment_values)->save();
    $this->assertNoMails('Check whether the mails queue is empty, when nobody subscribed to the comments.');

    // One subscriber.
    $this->flagService->flag($this->flag, $node, $user);
    Comment::create($comment_values)->save();
    $this->assertMailsAmount(1, 'Check amount of sent emails after creating a comment with subscription.');
    $this->assertLastMail($user->getEmail(), 'comment_insert', 'Check whether the right user gets an email notification.');

    // Creating unpublished comment.
    $this->clearMails();
    $comment = Comment::create(['status' => Comment::NOT_PUBLISHED] + $comment_values);
    $this->assertNoMails('Make sure that user doesn\'t get an email when created unpublished comment.');

    // Publishing comment.
    $comment->setPublished();
    $comment->save();
    $this->assertMailsAmount(1, 'Make sure that user get an email when the comment is published.');

    // Creating own comment.
    $this->clearMails();
    $comment = Comment::create(['uid' => $user->id()] + $comment_values);
    $comment->save();
    $this->assertNoMails('Make sure that user doesn\'t get email for own comment.');

    // Updating user's comment.
    $comment->save();
    $this->assertLastMail($user->getEmail(), 'comment_update', 'Make sure that user get an email when his comment is updated.');

    // Checking comment reply.
    Comment::create(['pid' => $comment->id()] + $comment_values)->save();
    $this->assertLastMail($user->getEmail(), 'comment_reply', 'Make sure that user get an email when created a reply for his comment.');

    // Checking deleting user's comment.
    $comment->delete();
    $this->assertLastMail($user->getEmail(), 'comment_delete', 'Make sure that user get an email when his comment is deleted.');

    // Check multiple subscriptions.
    $this->clearMails();
    $this->flagService->flag($this->flag, $node, $this->root);
    Comment::create($comment_values)->save();
    $this->assertMailsAmount(2, 'Check whether two users got emails.');
    $this->flagService->unflag($this->flag, $node, $this->root);

    // Unsubscribing.
    $this->clearMails();
    $this->flagService->unflag($this->flag, $node, $user);
    Comment::create($comment_values)->save();
    $this->assertNoMails('Make sure that user doesn\'t get an email when he unsubscribed.');

  }

  /**
   * Asserts the amount of mails in the queue.
   *
   * @param int $amount
   *   Expected amount of mails in queue.
   * @param string $message
   *   Assert message.
   */
  protected function assertMailsAmount($amount, $message = '') {
    $mails = lit_notifications_test_get_mails();
    $this->assertTrue(count($mails) == $amount, $message);
  }

  /**
   * Asserts that the mail queue is empty.
   *
   * @param string $message
   *   Assert message.
   */
  protected function assertNoMails($message = '') {
    $this->assertMailsAmount(0, $message);
  }

  /**
   * Asserts the correctness of the last mail in the queue.
   *
   * @param string $email
   *   User email address who should receive the mail.
   * @param int $key
   *   The email key.
   * @param string $message
   *   Assert message.
   */
  protected function assertLastMail($email, $key, $message = '') {
    $mail_info = $this->getLastMailInfo();
    $this->assertTrue($email == $mail_info['to'] && $key == $mail_info['key'], $message);
  }

  /**
   * Get the latest mail info.
   *
   * @return array
   *   Array with the structure
   *   identical to $message argument in hook_mail_alter().
   */
  protected function getLastMailInfo() {
    $mails = lit_notifications_test_get_mails();
    return end($mails);
  }

  /**
   * Clears mails queue.
   */
  protected function clearMails() {
    _lit_notifications_test_save_mail_message(NULL, TRUE);
  }

}
