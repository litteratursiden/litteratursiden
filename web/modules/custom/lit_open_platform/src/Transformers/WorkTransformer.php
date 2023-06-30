<?php

namespace Drupal\lit_open_platform\Transformers;

use Drupal\Core\File\FileSystemInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Class Transformer for work.
 */
class WorkTransformer extends BaseTransformer {

  /**
   * The cover images directory.
   */
  private const FIELD_BOOK_COVER_IMAGE_DIR = 'public://field_book_cover_image';

  /**
   * The literature types vocabulary machine name.
   */
  private const VOCABULARY_LITERATURE_TYPES = 'literature_type';

  /**
   * The general tags vocabulary machine name.
   */
  private const VOCABULARY_GENERAL_TYPES = 'general_tags';

  /**
   * {@inheritdoc}
   */
  public static function transform(array $item): array {
    $pid = $item['pid'][0] ?? NULL;
    $faustnummer = ltrim(stristr($pid, ":"), ':');

    // Transform scalar types.
    $result = [
      'pid' => $pid,
      'title' => $item['dcTitleFull'][0] ?? NULL,
      'field_book_original_title' => $item['source'][0] ?? NULL,
      'field_book_body' => $item['abstract'][0] ?? NULL,
      'field_book_author' => $item['creatorAut'][0] ?? NULL,
      'field_book_translater' => $item['contributorTrl'][0] ?? NULL,
      'field_book_publisher' => $item['publisher'][0] ?? NULL,
      'field_book_language' => $item['language'][0] ?? NULL,
      'field_book_isbn' => $item['identifierISBN'][0] ?? NULL,
      'field_book_number_of_pages' => $item['extent'][0] ?? NULL,
      'field_book_published_year' => $item['date'][0] ?? NULL,
      'field_book_pid' => $pid,
      'field_book_old_library_key' => $faustnummer,
      'langcode' => 'da',
      'status' => 1,
      'type' => 'book',
    ];

    // Transform subject number.
    if (isset($item['subjectDK5'][0], $item['subjectDK5Text'][0])) {
      $result['field_book_subject_number'] = $item['subjectDK5'][0] . ' - ' . $item['subjectDK5Text'][0];
    }

    // Transform image.
    if (isset($item['coverUrlFull'][0])) {
      $result['field_book_cover_image'] = self::transformImage($item['coverUrlFull'][0], self::FIELD_BOOK_COVER_IMAGE_DIR);
    }

    // Transform author reference.
    if (isset($item['creatorAut'])) {
      $result['field_book_reference_author'] = self::transformNode($item['creatorAut'], 'author_portrait');
    }

    // Transform literature type.
    $literature_type = 'faglitteratur';
    if (isset($item['subjectDK5'][0]) && $item['subjectDK5'][0] == 'sk') {
      $literature_type = 'skønlitteratur';
    }

    $result['field_book_literature_type'] = self::transformTerm([$literature_type], self::VOCABULARY_LITERATURE_TYPES);

    // Transform tags.
    $subjectDBCS = $item['subjectDBCS'] ?? [];
    $subjectDBCN = $item['subjectDBCN'] ?? [];
    $subjectDBCF = $item['subjectDBCF'] ?? [];

    $result['field_book_general_tags'] = self::transformTerm($subjectDBCS + $subjectDBCN + $subjectDBCF, self::VOCABULARY_GENERAL_TYPES);

    return $result;
  }

  /**
   * Transform a collection and group it.
   *
   * @param string $field
   *   The field name to group by.
   * @param array $items
   *   A list of items to transform.
   *
   * @return array
   *   The transformed items grouped by field name.
   */
  public static function transformCollectionAndGroupBy(string $field, array $items): array {
    $result = [];

    foreach ($items as $item) {
      $transformed = self::transform($item);
      $result[$transformed[$field]] = $transformed;
    }

    return $result;
  }

  /**
   * Transform url to the image field.
   *
   * @param string $url
   *   The url to transform.
   * @param string $dir
   *   The directory.
   * @param string|null $alt
   *   The alt text for the field.
   * @param string|null $title
   *   The image title.
   *
   * @return array|null
   *   The image to save.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private static function transformImage(string $url, string $dir, string $alt = NULL, string $title = NULL): ?array {
    $url = strpos($url, '://') === FALSE ? 'http:' . $url : $url;

    // Get image content from the url.
    $data = file_get_contents($url);

    if ($data && \Drupal::service('file_system')->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY)) {
      // Generate unique filename based on the url.
      $filename = md5($url) . '.jpeg';

      // Save image.
      $file = \Drupal::service('file.repository')->writeData($data, "$dir/$filename");

      return [
        'target_id' => $file->id(),
        'alt' => $alt,
        'title' => $title,
      ];
    }

    return NULL;
  }

  /**
   * Transform string to the taxonomy term reference field.
   *
   * @param array $names
   *   A list of term names.
   * @param string $vocabulary
   *   A vocabulary id.
   *
   * @return array
   *   A list of term ids.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private static function transformTerm(array $names, string $vocabulary): array {
    $result = [];

    foreach ($names as $name) {
      // Check if the taxonomy term exists.
      $tid = \Drupal::entityQuery('taxonomy_term')
        ->accessCheck(FALSE)
        ->condition('vid', $vocabulary)
        ->condition('name', $name)
        ->execute();

      if (!$tid) {
        // Create the taxonomy term.
        $term = Term::create([
          'name' => $name,
          'vid' => $vocabulary,
        ]);

        if ($term->save()) {
          $tid = [$term->id()];
        }
      }

      $result[] = ['target_id' => reset($tid)];
    }

    return $result;
  }

  /**
   * Transform string to the node reference field.
   *
   * @param array $titles
   *   A list of node titles.
   * @param string $type
   *   The node type.
   *
   * @return array
   *   A list of nodes.
   */
  private static function transformNode(array $titles, string $type): array {
    $result = [];

    foreach ($titles as $title) {
      // Check if the node exists.
      $nid = \Drupal::entityQuery('node')
        ->accessCheck(FALSE)
        ->condition('type', $type)
        ->condition('title', $title)
        ->execute();

      if ($nid) {
        $result[] = ['target_id' => reset($nid)];
      }
    }

    return $result;
  }

}
