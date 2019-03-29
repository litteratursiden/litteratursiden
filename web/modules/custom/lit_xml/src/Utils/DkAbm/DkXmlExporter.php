<?php

namespace Drupal\lit_xml\Utils\DkAbm;

use Drupal\lit_xml\Utils\{
  DkAbm\Transformers\AnalysisTransformer,
  DkAbm\Transformers\ArticleTransformer,
  DkAbm\Transformers\AuthorPortraitTransformer,
  DkAbm\Transformers\InterviewTransformer,
  DkAbm\Transformers\ReviewTransformer,
  XmlExporter
};

/**
 * Class DkXmlExporter
 * @package Drupal\lit_xml\Utils\DkAbm
 */
class DkXmlExporter extends XmlExporter {

  /**
   * @inheritdoc
   */
  public function setXmlData(array $types) {
    foreach ($types as $type) {
      if (!$this->exporterIsRunning($type)) {
        $nodes = $this->getNodes($type);

        $transformer = $this->getTransformer($type);
        $records = $transformer ? $transformer::transformCollection($nodes) : [];

        foreach ($records as $record) {
          $this->addRecord($record);
          $this->updateCacheStamp($type);
        }

        // Process deleted nodes.
        $query = \Drupal::database()
          ->select('lit_xml_logs', 'logs')
          ->fields('logs', ['nid', 'created_at'])
          ->condition('event', 'delete')
          ->condition('type', $type)
          ->condition('created_at', $this->getLastRun(), '>=');

        $deleted = $query->execute()->fetchAll();
        foreach ($deleted as $record) {
          $this->addDeletedRecord($record);
          $this->updateCacheStamp($type);
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function addRecord(array $record) {
    $record['activity'] = $record['created'] > $this->getLastRun() ? 'created' : 'modified';

    $article = $this->dom->createElement('record');
    $this->dom->documentElement->appendChild($article);

    $this->buildAc($article, $record);
    $this->buildDc($article, $record);

    switch ($record['type']) {
      case 'analysis':
      case 'article':
      case 'interview':
        if (is_array($record['terms'])) {
          foreach ($record['terms'] as $term) {
            $dc_subject = $this->dom->createElement('dc:subject');
            $dc_subject->appendChild($this->dom->createTextNode($term));
            $article->appendChild($dc_subject);
          }
        }
        break;

      case 'author_portrait':
        $dc_subject = $this->dom->createElement('dc:subject');
        $dc_subject->appendChild($this->dom->createTextNode('Forfatterportrætter'));
        $article->appendChild($dc_subject);
        break;

      default:
        break;
    }

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function addDeletedRecord($object) {
    $article = $this->dom->createElement('record');
    $this->dom->documentElement->appendChild($article);

    $record = [
      'nid' => $object->nid,
      'created' => $object->created_at,
      'activity' => 'delete-out-of-scope',
    ];

    $this->buildAc($article, $record);

    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function buildDom(): \DOMDocument {
    $dom = new \DOMDocument("1.0", "ISO-8859-1");

    $records = $dom->createElement('collection');
    $dom->appendChild($records);

    // xmlns:dkabm
    $xmlns = $dom->createAttribute('xmlns');
    $records->appendChild($xmlns);
    $xmlns_val = $dom->createTextNode('http://biblstandard.dk/abm/namespace/dkabm/');
    $xmlns->appendChild($xmlns_val);

    // xmlns:ISO639-2
    $xmlns_iso = $dom->createAttribute('xmlns:ISO639-2');
    $records->appendChild($xmlns_iso);
    $xmlns_iso_val = $dom->createTextNode('http://lcweb.loc.gov/standards/iso639-2/');
    $xmlns_iso->appendChild($xmlns_iso_val);

    // xmlns:dcmitype
    $xmlns_dcmitype = $dom->createAttribute('xmlns:dcmitype');
    $records->appendChild($xmlns_dcmitype);
    $xmlns_dcmitype_val = $dom->createTextNode('http://purl.org/dc/dcmitype/');
    $xmlns_dcmitype->appendChild($xmlns_dcmitype_val);

    // xmlns:dc
    $xmlns_dc = $dom->createAttribute('xmlns:dc');
    $records->appendChild($xmlns_dc);
    $xmlns_dc_val = $dom->createTextNode('http://purl.org/dc/elements/1.1/');
    $xmlns_dc->appendChild($xmlns_dc_val);

    // xmlns:dcterms
    $xmlns_dcterms = $dom->createAttribute('xmlns:dcterms');
    $records->appendChild($xmlns_dcterms);
    $xmlns_dcterms_val = $dom->createTextNode('http://purl.org/dc/terms/');
    $xmlns_dcterms->appendChild($xmlns_dcterms_val);

    // xmlns:ac
    $xmlns_ac = $dom->createAttribute('xmlns:ac');
    $records->appendChild($xmlns_ac);
    $xmlns_ac_val = $dom->createTextNode('http://biblstandard.dk/ac/namespace/');
    $xmlns_ac->appendChild($xmlns_ac_val);

    // xmlns:dkdcplus
    $xmlns_dk = $dom->createAttribute('xmlns:dkdcplus');
    $records->appendChild($xmlns_dk);
    $xmlns_dk_val = $dom->createTextNode('http://biblstandard.dk/abm/namespace/dkdcplus/');
    $xmlns_dk->appendChild($xmlns_dk_val);

    // xmlns:xsi
    $xmlns_xsi = $dom->createAttribute('xmlns:xsi');
    $records->appendChild($xmlns_xsi);
    $xmlns_xsi_val = $dom->createTextNode('http://www.w3.org/2001/XMLSchema-instance');
    $xmlns_xsi->appendChild($xmlns_xsi_val);

    // xsi:schemaLocation
    $xmlns_sl = $dom->createAttribute('xsi:schemaLocation');
    $records->appendChild($xmlns_sl);
    $xmlns_sl_val = $dom->createTextNode('http://biblstandard.dk/abm/namespace/dkabm/ http://biblstandard.dk/abm/schemas/dkabm_2009-08-20.xsd');
    $xmlns_sl->appendChild($xmlns_sl_val);

    return $dom;
  }

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

  /**
   * @param \DOMElement $element
   * @param array $record
   */
  private function buildAc(\DOMElement $element, array $record) {
    // ac:identifier (nid)
    $ac_ident = $this->dom->createElement('ac:identifier');
    $ac_ident->appendChild($this->dom->createTextNode($record['nid']));
    $element->appendChild($ac_ident);

    // ac:date
    $ac_date = $this->dom->createElement('ac:date');
    $ac_date->appendChild($this->dom->createTextNode(date('Ymd', $record['created'])));
    $element->appendChild($ac_date);

    // ac:type of activity
    if (isset($record['activity'])) {
      $ac_activity = $this->dom->createElement('ac:activity');
      $ac_action = $this->dom->createElement('ac:action');
      $ac_activity->appendChild($ac_action);
      $ac_action_attr = $this->dom->createAttribute('xsi:type');
      $ac_action_attr->appendChild($this->dom->createTextNode('ac:TypeOfActivity'));
      $ac_action->appendChild($ac_action_attr);
      $ac_action->appendChild($this->dom->createTextNode($record['activity']));
      $element->appendChild($ac_activity);
    }
  }

  /**
   * @param \DOMElement $element
   * @param array $record
   */
  public function buildDc(\DOMElement $element, array $record) {
    // dc:identifier (url)
    $dc_ident = $this->dom->createElement('dc:identifier');
    $dc_ident_attr = $this->dom->createAttribute('xsi:type');
    $dc_ident_attr->appendChild($this->dom->createTextNode('dcterms:URI'));
    $dc_ident->appendChild($dc_ident_attr);
    $dc_ident->appendChild($this->dom->createTextNode($record['url']));
    $element->appendChild($dc_ident);

    // Add dc:lang
    $dc_lang = $this->dom->createElement('dc:language');
    $dc_lang_attr = $this->dom->createAttribute('xsi:type');
    $dc_lang_attr->appendChild($this->dom->createTextNode('dcterms:ISO639-2'));
    $dc_lang->appendChild($dc_lang_attr);
    $dc_lang->appendChild($this->dom->createTextNode($record['language']));
    $element->appendChild($dc_lang);

    // dc:creator (name)
    if ($record['name'] == 'Re') {
      $data['name'] = 'Litteratursiden';
    }
    $dc_creator = $this->dom->createElement('dc:creator');
    $dc_creator->appendChild($this->dom->createTextNode($record['name']));
    $element->appendChild($dc_creator);

    // dc:publisher (litteratursiden.dk)
    $dc_pub = $this->dom->createElement('dc:publisher');
    $dc_pub->appendChild($this->dom->createTextNode('Litteratursiden'));
    $element->appendChild($dc_pub);

    // dc.type (xsi-type)
    $dc_type = $this->dom->createElement('dc:type');
    $dc_xsitype_attr = $this->dom->createAttribute('xsi:type');
    $dc_xsitype_attr->appendChild($this->dom->createTextNode('dkdcplus:BibDK-Type'));
    $dc_type->appendChild($dc_xsitype_attr);
    $dc_type->appendChild($this->dom->createTextNode($record['xsi-type']));
    $element->appendChild($dc_type);

    // dc.title (title)
    $dc_title = $this->dom->createElement('dc:title');
    $dc_title->appendChild($this->dom->createTextNode($record['title']));
    $element->appendChild($dc_title);

    // dc.description (teaser)
    $dc_desc = $this->dom->createElement('dcterms:abstract');
    $dc_desc->appendChild($this->dom->createTextNode($record['teaser']));
    $element->appendChild($dc_desc);

    // dc.references (ISBN)
    if ($record['isbn'] != '') {
      // Create element
      $dc_ref = $this->dom->createElement('dcterms:references');
      $dc_ref_attr = $this->dom->createAttribute('xsi:type');
      $dc_ref_attr->appendChild($this->dom->createTextNode('dkdcplus:ISBN'));
      $dc_ref->appendChild($dc_ref_attr);
      $dc_ref->appendChild($this->dom->createTextNode($record['isbn']));
      $element->appendChild($dc_ref);
    }

    // dc.date -> date:created (created)
    $dc_date_created = $this->dom->createElement('dcterms:created');
    $dc_date_created->appendChild($this->dom->createTextNode(date('Y-m-d h:i:s', $record['created'])));
    $element->appendChild($dc_date_created);

    // dc.date -> date:modified (changed)
    $dc_date_modified = $this->dom->createElement('dcterms:modified');
    $dc_date_modified->appendChild($this->dom->createTextNode(date('Y-m-d h:i:s', $record['changed'])));
    $element->appendChild($dc_date_modified);

    // dc:subject (author)
    if ($record['author_book'] != '') {
      $dc_subjec = $this->dom->createElement('dc:subject');
      $dc_subjec->appendChild($this->dom->createTextNode($record['author_book']));
      $element->appendChild($dc_subjec);
    }

    // dc:subject (book title)
    if ($record['title_book'] != '') {
      $dc_subjec = $this->dom->createElement('dc:subject');
      $dc_subjec->appendChild($this->dom->createTextNode($record['title_book']));
      $element->appendChild($dc_subjec);
    }

    // dc.references (book pid)
    if (isset($record['pid'])) {
      $dc_ref = $this->dom->createElement('dcterms:references');
      $dc_ref_attr = $this->dom->createAttribute('xsi:type');
      $dc_ref_attr->appendChild($this->dom->createTextNode('dkdcplus:pid'));
      $dc_ref->appendChild($dc_ref_attr);
      $dc_ref->appendChild($this->dom->createTextNode($record['pid']));
      $element->appendChild($dc_ref);
    }
  }
}
