<?php

namespace Drupal\lit_xml\Utils\Ws;

use Drupal\lit_xml\Utils\{
  Ws\Transformers\AnalysisTransformer,
  Ws\Transformers\ArticleTransformer,
  Ws\Transformers\AuthorPortraitTransformer,
  Ws\Transformers\InterviewTransformer,
  Ws\Transformers\ReviewTransformer,
  XmlExporter
};

/**
 * Class WsXmlExporter
 * @package Drupal\lit_xml\Utils\Ws
 */
class WsXmlExporter extends XmlExporter {

  /**
   * @inheritdoc
   */
  protected function getTransformers(): array {
    return [
      'analysis' => new AnalysisTransformer,
      'article' => new ArticleTransformer,
      'author_portrait' => new AuthorPortraitTransformer,
      'interview' => new InterviewTransformer,
      'review' => new ReviewTransformer,
    ];
  }

}
