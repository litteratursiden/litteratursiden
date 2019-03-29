<?php

namespace Drupal\lit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for books.
 */
class BooksController extends ControllerBase {

    /**
     * Returns content.
     */
    public function withoutImagesList(Request $request) {
        $offset = $request->get('offset', 0);

        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->fields('n', ['nid', 'title']);
        $query->leftJoin('node__field_book_cover_image', 'f', 'f.entity_id = n.nid');
        $query->condition('n.type', 'book');
        $query->isNull('f.entity_id');
        $query->orderBy('title');
        $query->range($offset, 100);

        $books = $query->execute()->fetchAll();

        return [
            '#theme' => 'lit_books_without_image',
            '#books' => $books,
            '#offset' => !$books && $offset-100 >= 0 ? $offset-100 : $offset + 100,
            '#cache' => [
                'tags' => ['lit:books_without_image'],
            ],
        ];
    }
}
