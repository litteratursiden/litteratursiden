<?php

namespace Drupal\lit_og_image\Helper;

use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\Image;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Helper for lit_og_image.
 */
class Helper {

  /**
   * Implements hook_form_alter().
   *
   * Alter forms related to nodes.
   *
   * @param array $form
   *   The form to alter.
   */
  public function alterForm(array &$form) {
    $form['actions']['submit']['#submit'][] = [$this, 'nodeSubmit'];
  }

  public function nodeSubmit(array $form, FormStateInterface $form_state) {
    // The forms to add facebook image for.
    $formIds = [
      'node_article_form' => 'field_article_cover_image',
      'node_article_edit_form' => 'field_article_cover_image',
      'node_page_form' => 'field_spotbox_image',
      'node_page_edit_form' => 'field_spotbox_image',
      'node_blog_form' => 'field_spotbox_image',
      'node_blog_edit_form' => 'field_spotbox_image',
      'node_book_form' => 'field_book_spotbox_image',
      'node_book_edit_form' => 'field_book_spotbox_image',
      'node_book_list_form' => 'field_book_list_spotbox_image',
      'node_book_list_edit_form' => 'field_book_list_spotbox_image',
      'node_author_portrait_form' => 'field_spotbox_image',
      'node_author_portrait_edit_form' => 'field_spotbox_image',
      'node_interview_form' => 'field_spotbox_image',
      'node_interview_edit_form' => 'field_spotbox_image',
      'node_topic_form' => 'field_topic_spotbox_image',
      'node_topic_edit_form' => 'field_topic_spotbox_image',
    ];

    $value = $form_state->getValue($formIds[$form['#form_id']])[0]['fids'][0];

    // Create image from image style if there is an image filled in.
    if(!empty($value)) {
      $this->createImage($value);
    }
  }

  /**
   * Create styled image from a file id.
   *
   * @param $value
   *   The file id.
   */
  private function createImage($value) {
    $entity = File::load($value);
    /** @var File $entity */
    if ($entity instanceof FileInterface) {
      $image = \Drupal::service('image.factory')->get($entity->getFileUri());
      /** @var Image $image */
      if ($image->isValid()) {
        $style = ImageStyle::load('facebook_display');
        $image_uri = $entity->getFileUri();
        /** @var ImageStyle $style */
        $destination = $style->buildUri($image_uri);
        $style->createDerivative($image_uri, $destination);
      }
    }
  }

  /**
   * Implements hook_page_attachments().
   *
   * Add open graph image meta tag.
   *
   * @param $page
   */
  public function pageAttachment(&$page) {
    $possibleImageFields = [
      'article' => 'field_article_cover_image',
      'page' => 'field_spotbox_image',
      'blog' => 'field_spotbox_image',
      'book' => 'field_book_spotbox_image',
      'book_list' => 'field_book_list_spotbox_image',
      'author_portrait' => 'field_spotbox_image',
      'interview' => 'field_spotbox_image',
      'topic' => 'field_topic_spotbox_image',
    ];
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $nodeType = $node->getType();
      $fileReference = $node->get($possibleImageFields[$nodeType]);
      assert($fileReference instanceof EntityReferenceFieldItemList);
      $files = $fileReference->referencedEntities();
      if (!empty($files)) {
        $image_uri = ImageStyle::load('facebook_display')->buildUrl($files[0]->getFileUri());
        $ogImage = [
          '#tag' => 'meta',
          '#attributes' => [
            'property' => 'og:image',
            'content' => $image_uri,
          ],
        ];
        $page['#attached']['html_head'][] = [$ogImage, 'lit_open_graph_image'];
      }
    }
  }
}
