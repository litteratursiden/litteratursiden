<?php

namespace Drupal\lit_taxonomy\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Html;
use Drupal\lit_taxonomy\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Plugin\EntityReferenceSelection\TermSelection as BaseTermSelection;

/**
 * Provides specific access control for the taxonomy_term entity type.
 *
 * @EntityReferenceSelection(
 *   id = "lit:taxonomy_term",
 *   label = @Translation("Taxonomy Term selection"),
 *   entity_types = {"taxonomy_term"},
 *   group = "default",
 *   weight = 1
 * )
 */
class TermSelection extends BaseTermSelection {

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $account = \Drupal::currentUser();

    if ($match || $limit) {
      $this->configuration['handler_settings']['sort'] = ['field' => 'name', 'direction' => 'asc'];
      return $this->getReferenceableEntitiesByMatch($account, $match, $match_operator, $limit);
    }

    $options = [];

    $bundles = $this->entityTypeManager->getBundleInfo('taxonomy_term');
    $handler_settings = $this->configuration['handler_settings'];
    $bundle_names = !empty($handler_settings['target_bundles']) ? $handler_settings['target_bundles'] : array_keys($bundles);

    foreach ($bundle_names as $bundle) {
      if ($vocabulary = Vocabulary::load($bundle)) {
        if ($terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vocabulary->id(), 0, NULL, TRUE)) {
          foreach ($terms as $term) {
            if (Term::access($term, 'view', $account)->isAllowed()) {
              $options[$vocabulary->id()][$term->id()] = str_repeat('-', $term->depth) . Html::escape($this->entityRepository->getTranslationFromContext($term)->label());
            }
          }
        }
      }
    }

    return $options;
  }

  /**
   * @param $account
   * @param null $match
   * @param string $match_operator
   * @param int $limit
   * @return array
   */
  private function getReferenceableEntitiesByMatch($account, $match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $query = $this->buildEntityQuery($match, $match_operator);
    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    $options = [];
    $entities = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($result);
    foreach ($entities as $entity_id => $entity) {
      if (Term::access($entity, 'view', $account)->isAllowed()) {
        $bundle = $entity->bundle();
        $options[$bundle][$entity_id] = Html::escape($this->entityRepository->getTranslationFromContext($entity)
          ->label());
      }
    }

    return $options;
  }

}
