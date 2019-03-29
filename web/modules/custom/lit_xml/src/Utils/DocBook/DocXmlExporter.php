<?php

namespace Drupal\lit_xml\Utils\DocBook;

use Drupal\lit_xml\Utils\{
  DocBook\Transformers\AnalysisTransformer,
  DocBook\Transformers\ArticleTransformer,
  DocBook\Transformers\AuthorPortraitTransformer,
  DocBook\Transformers\BookListTransformer,
  DocBook\Transformers\InterviewTransformer,
  DocBook\Transformers\ReviewTransformer,
  XmlExporter
};

/**
 * Class DocXmlExporter
 * @package Drupal\lit_xml\Utils\DocBook
 */
class DocXmlExporter extends XmlExporter {

  /**
   * @inheritdoc
   */
  public function addRecord(array $record) {
    $article = $this->dom->createElement('article');
    $this->dom->documentElement->appendChild($article);

    $article->appendChild($this->buildTitle($record['title']));

    $article->appendChild($this->buildInfo($record));


    switch ($record['type']) {
      case 'analysis':
        $bibliography = $this->dom->createElement('bibliography');
        $article->appendChild($bibliography);

        $bibliodiv = $this->dom->createElement('bibliodiv');
        $bibliography->appendChild($bibliodiv);

        $bibliodiv->appendChild($this->buildTitle('Bog'));
        $bibliodiv->appendChild($this->buildBibliotekBook($record['isbn'], $record['book_title'], $record['author']));

        if (!empty($record['links'])) {
          $bibliodiv = $this->dom->createElement('bibliodiv');
          $bibliodiv->appendChild($this->buildTitle('Links'));
          foreach ($record['links'] as $key => $link) {
            $bibliodiv->appendChild($this->buildBibliotekLink($link['title'], $link['url']));
          }
        }

        $article->appendChild($this->buildSection('Analyse', $record['analysis']));

        break;

      case 'author_portrait':
        if (!empty($record['books']) || !empty($record['links'])) {
          $bibliography = $this->dom->createElement('bibliography');
          $article->appendChild($bibliography);

          if (!empty($record['books'])) {
            $bibliodiv = $this->dom->createElement('bibliodiv');
            $bibliography->appendChild($bibliodiv);

            $bibliodiv->appendChild($this->buildTitle('Bibliografi'));

            foreach ($record['books'] as $isbn => $title) {
              $bibliodiv->appendChild($this->buildBibliotekBook($isbn, $title, $record['author']));
            }
          }

          if (!empty($record['links'])) {
            $bibliodiv = $this->dom->createElement('bibliodiv');
            $bibliography->appendChild($bibliodiv);

            $bibliodiv->appendChild($this->buildTitle('Links'));

            foreach ($record['links'] as $key => $link) {
              $bibliodiv->appendChild($this->buildBibliotekLink($link['title'], $link['url']));
            }
          }
        }

        $article->appendChild($this->buildSection('Om '. $record['author'], $record['om']));
        if (strlen($record['read_author'])) {
          $article->appendChild($this->buildSection('Læs forfatter', $record['read_author']));
        }

        if (strlen($record['inspiration'])) {
          $article->appendChild($this->buildSection('Inspiration', $record['inspiration']));
        }

        break;

      case 'article':
        if (!empty($record['links'])) {
          $bibliography = $this->dom->createElement('bibliography');
          $article->appendChild($bibliography);

          $bibliodiv = $this->dom->createElement('bibliodiv');
          $bibliography->appendChild($bibliodiv);

          $bibliodiv->appendChild($this->buildTitle('Links'));

          foreach ($record['links'] as $key => $link) {
            $bibliodiv->appendChild($this->buildBibliotekLink($link['title'], $link['url']));
          }
        }

        $article->appendChild($this->buildSection('Artikel', $record['artikel']));

        break;

      case 'book_list':
        if (!empty($record['books'])) {
          $bibliography = $this->dom->createElement('bibliography');
          $article->appendChild($bibliography);

          $bibliodiv = $this->dom->createElement('bibliodiv');
          $bibliography->appendChild($bibliodiv);

          $bibliodiv->appendChild($this->buildTitle('Bøger'));

          foreach ($record['books'] as $book) {
            $bibliodiv->appendChild($this->buildBibliotekBook($book['isbn'], $book['title'], $book['author']));
          }
        }

        $article->appendChild($this->buildSection('Beskrivelse', $record['list']));

        break;

      case 'interview':
        if (!empty($record['links'])) {
          $bibliography = $this->dom->createElement('bibliography');
          $article->appendChild($bibliography);

          $bibliodiv = $this->dom->createElement('bibliodiv');
          $bibliography->appendChild($bibliodiv);

          $bibliodiv->appendChild($this->buildTitle('Links'));

          foreach ($record['links'] as $key => $link) {
            $bibliodiv->appendChild($this->buildBibliotekLink($link['title'], $link['url']));
          }
        }

        $article->appendChild($this->buildSection('Interview med '. $record['author'], $record['interview']));

        break;

      case 'review':
        $bibliography = $this->dom->createElement('bibliography');
        $article->appendChild($bibliography);

        $bibliodiv = $this->dom->createElement('bibliodiv');
        $bibliography->appendChild($bibliodiv);

        $bibliodiv->appendChild($this->buildTitle('Bog'));
        $bibliodiv->appendChild($this->buildBibliotekBook($record['isbn'], $record['book_title'], $record['author']));

        if (!empty($record['links'])) {
          $bibliodiv = $this->dom->createElement('bibliodiv');
          $bibliography->appendChild($bibliodiv);

          $bibliodiv->appendChild($this->buildTitle('Links'));

          foreach ($record['links'] as $key => $link) {
            $bibliodiv->appendChild($this->buildBibliotekLink($link['title'], $link['url']));
          }
        }

        $article->appendChild($this->buildSection('Anbefaling', $record['recommendation']));

        break;

      default:
        break;
    }

    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function buildDom(): \DOMDocument {
    $dom = new \DOMDocument("1.0", "ISO-8859-1");

    $records = $dom->createElement('book');
    $dom->appendChild($records);

    // Add xmlns.
    $xmlns = $dom->createAttribute('xmlns');
    $dom->documentElement->appendChild($xmlns);
    $xmlns->appendChild($dom->createTextNode('http://docbook.org/ns/docbook'));

    // Add version.
    $version = $dom->createAttribute('version');
    $dom->documentElement->appendChild($version);
    $version->appendChild($dom->createTextNode('5.0'));

    // Add lang.
    $lang = $dom->createAttribute('xml:lang');
    $dom->documentElement->appendChild($lang);
    $lang->appendChild($dom->createTextNode('da'));

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
      'book_list' => new BookListTransformer,
      'interview' => new InterviewTransformer,
      'review' => new ReviewTransformer,
    ];
  }

  /**
   * @param array $record
   * @return \DOMElement
   */
  private function buildInfo(array $record) {
    $info = $this->dom->createElement('info');

    $this->buildInfoHeader($info, $record);

    $abstract = $this->dom->createElement('abstract');
    $info->appendChild($abstract);
    $abstract->appendChild($this->buildPara($record['abstract']));

    $author = $this->dom->createElement('author');
    $info->appendChild($author);
    $personname = $this->dom->createElement('personname');
    $personname->appendChild($this->dom->createTextNode($record['name']));
    $author->appendChild($personname);


    $uri = $this->dom->createElement('uri');
    $type = $this->dom->createAttribute('type');
    $type->appendChild($this->dom->createTextNode('webpage'));
    $uri->appendChild($type);
    $uri->appendChild($this->dom->createTextNode($record['url']));
    $author->appendChild($uri);

    $pubdate = $this->dom->createElement('pubdate');
    $info->appendChild($pubdate);
    $pubdate->appendChild($this->dom->createTextNode(date('Y/m/d H:i:s', $record['date'])));

    $publisher = $this->dom->createElement('publisher');
    $info->appendChild($publisher);
    $publishername = $this->dom->createElement('publishername');
    $publishername->appendChild($this->dom->createTextNode('Litteratursiden.dk'));
    $publisher->appendChild($publishername);

    return $info;
  }

  /**
   * @param $title
   * @param $para
   * @return \DOMElement
   */
  private function buildSection($title, $para) {
    $section = $this->dom->createElement('section');
    $section->appendChild($this->buildTitle($title));
    $section->appendChild($this->buildPara($para));

    return $section;
  }

  /**
   * @param $isbn
   * @param $title
   * @param string $author
   * @return \DOMElement
   */
  private function buildBibliotekBook($isbn, $title, $author = 'Unknown') {
    $biblioentry = $this->dom->createElement('biblioentry');
    $biblioentry->appendChild($this->buildTitle($title));
    $biblioentry->appendChild($this->buildAuthor($author));

    $bibliosource = $this->dom->createElement('bibliosource');
    $class = $this->dom->createAttribute('class');
    $class->appendChild($this->dom->createTextNode('uri'));
    $bibliosource->appendChild($class);

    $bibliotek_url = 'http://bibliotek.dk/linkme.php';
    $bibliotek_url .= $isbn == '' ? 'ccl=ti=' . urlencode($title) : 'ccl=is=' . $isbn;
    $bibliosource->appendChild($this->dom->createTextNode($bibliotek_url));

    $biblioentry->appendChild($bibliosource);

    return $biblioentry;
  }

  /**
   * @param $title
   * @param $url
   * @return \DOMElement
   */
  private function buildBibliotekLink($title, $url) {
    $title = $title ?: $url;

    $biblioentry = $this->dom->createElement('biblioentry');
    $biblioentry->appendChild($this->buildTitle($title));

    $bibliosource = $this->dom->createElement('bibliosource');
    $bibliosource->appendChild($this->dom->createTextNode($url));
    $class = $this->dom->createAttribute('class');
    $class->appendChild($this->dom->createTextNode('uri'));
    $bibliosource->appendChild($class);
    $biblioentry->appendChild($bibliosource);

    return $biblioentry;
  }

  /**
   * @param \DOMElement $info
   * @param array $record
   */
  private function buildInfoHeader(\DOMElement $info, array $record) {
    $bibliosource = $this->dom->createElement('bibliosource');
    $bibliosource->appendChild($this->dom->createTextNode($record['nid']));
    $class = $this->dom->createAttribute('class');
    $class->appendChild($this->dom->createTextNode('doi'));
    $bibliosource->appendChild($class);
    $info->appendChild($bibliosource);
  }

  /**
   * @param $str
   * @return \DOMElement
   */
  private function buildTitle($str) {
    $title = $this->dom->createElement('title');
    $title->appendChild($this->dom->createTextNode($str));
    return $title;
  }

  /**
   * @param $name
   * @return \DOMElement
   */
  private function buildAuthor($name) {
    $author = $this->dom->createElement('author');
    $personname = $this->dom->createElement('personname');
    $personname->appendChild($this->dom->createTextNode($name));
    $author->appendChild($personname);

    return $author;
  }

  /**
   * @param $str
   * @return \DOMElement
   */
  private function buildPara($str) {
    $para = $this->dom->createElement('para');
    $para->appendChild($this->dom->createTextNode($str));
    return $para;
  }
}
