<?php

namespace Drupal\lit_migrate\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * @MigrateSource(
 *   id = "lit_comment",
 *   source_provider = "comment"
 * )
 */
class LitComment extends DrupalSqlBase {

  /**
   * Content types D6 type => D8 type.
   *
   * @var array
   */
  public static $contentTypes = [
    'analyse' => 'analysis',
    'artikel' => 'article',
    'forfatter' => 'author_portrait',
    'blog' => 'blog',
    'bog' => 'book',
    'bogliste' => 'book_list',
    'interview' => 'interview',
    'anbefaling' => 'review',
    'noget_der_ligner' => 'similar',
    'tema' => 'topic',
  ];

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('comments', 'c')
      ->fields('c', ['cid', 'pid', 'nid', 'uid', 'subject',
        'comment', 'hostname', 'timestamp', 'status', 'thread', 'name',
        'mail', 'homepage', 'format']);
    $query->innerJoin('node', 'n', 'c.nid = n.nid');
    $query->fields('n', ['type']);
    $query->condition('n.type', array_keys(self::$contentTypes), 'IN');
    $query->orderBy('c.timestamp');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $type = $row->getSourceProperty('type');
    $field_name = self::getFieldName($type);

    $row->setSourceProperty('field_name', $field_name);
    $row->setSourceProperty('comment_type', 'comment');

    // In D6, status=0 means published, while in D8 means the opposite.
    // See https://www.drupal.org/node/237636.
    $row->setSourceProperty('status', !$row->getSourceProperty('status'));
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'cid' => $this->t('Comment ID.'),
      'pid' => $this->t('Parent comment ID. If set to 0, this comment is not a reply to an existing comment.'),
      'nid' => $this->t('The {node}.nid to which this comment is a reply.'),
      'uid' => $this->t('The {users}.uid who authored the comment. If set to 0, this comment was created by an anonymous user.'),
      'subject' => $this->t('The comment title.'),
      'comment' => $this->t('The comment body.'),
      'hostname' => $this->t("The author's host name."),
      'timestamp' => $this->t('The time that the comment was created, or last edited by its author, as a Unix timestamp.'),
      'status' => $this->t('The published status of a comment. (0 = Published, 1 = Not Published)'),
      'format' => $this->t('The {filter_formats}.format of the comment body.'),
      'thread' => $this->t("The vancode representation of the comment's place in a thread."),
      'name' => $this->t("The comment author's name. Uses {users}.name if the user is logged in, otherwise uses the value typed into the comment form."),
      'mail' => $this->t("The comment author's email address from the comment form, if user is anonymous, and the 'Anonymous users may/must leave their contact information' setting is turned on."),
      'homepage' => $this->t("The comment author's home page address from the comment form, if user is anonymous, and the 'Anonymous users may/must leave their contact information' setting is turned on."),
      'type' => $this->t("The {node}.type to which this comment is a reply."),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['cid']['type'] = 'integer';
    return $ids;
  }

  /**
   * Get field name for D6 content type.
   *
   * @param string $d6type
   * @return string
   */
  public static function getFieldName($d6type) {
    $d8type = self::$contentTypes[$d6type];

    return 'field_' . $d8type . '_comments';
  }

}
