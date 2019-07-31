<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Bible;
use App\Models\BibleType;
use Illuminate\Support\Facades\Log;
use Orchestra\Parser\Xml\Facade as XmlParser;
use TeamTNT\TNTSearch\TNTSearch;
use \XMLReader;
use \DOMDocument;

class BibleController extends Controller
{

    public $successStatus = 200;
    public $errorStatus = 500;

    public function search(Request $request){
      $query_string = $request->get('q');
      $options = [
        'simple'        => false,
        'wholeWord'     => true,
        'caseSensitive' => false,
        'stripLinks'    => false,
        'tagOptions' => [
            'class' => 'search-term',
            'title' => 'You searched for this.',
            'data-toggle' => 'tooltip',
        ]
    ];

      try {
        $bile_verses = Bible::search($query_string)
        ->orderBy('verse_id', 'asc')
        ->get();

        $tnt = new TNTSearch;

        $result = $bile_verses->map(function($bile_verse) use ($query_string, $tnt, $options){
            $bile_verse->verse = $tnt->highlight($bile_verse->verse, $query_string, 'em', $options);

            return $bile_verse;
        });

        return response()->json(['total' => count($result), 'result' => $result], $this->successStatus);
      } catch (Exception $e) {
        return response()->json(['error' => 'Something Wrong'], $this->errorStatus);
      }
    }

    /**
     * generate bible api
     *
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request){
      $path = storage_path("app/data/xml/bible_small.xml");
      $xmlReader = new XMLReader;
      $xmlReader->open($path);
      $document = new DOMDocument;
      
      $info = array();
      $books = [];
      $other = array();

      while ($xmlReader->read()) {
        if ($xmlReader->nodeType == XMLREADER::ELEMENT &&  $xmlReader->localName == 'BIBLEBOOK')
        {
          if (!empty($book)) {
            if (!empty($chapter)) {
              array_push($book['chapter'], $chapter);
              $chapter = array();
            }
            array_push($books, $book);
          }
          $book = array();
          $book['b_nbr'] = $xmlReader->getAttribute('bnumber');
          $book['b_name'] = $xmlReader->getAttribute('bsname');
          $book['chapter'] = [];
        }

        if ($xmlReader->nodeType == XMLREADER::ELEMENT &&  $xmlReader->localName == 'CHAPTER')
        {
          if (!empty($chapter)) {
            array_push($book['chapter'], $chapter);
          }
          $chapter = array();
          $chapter['c_nbr'] = $xmlReader->getAttribute('cnumber');
          $chapter['verses'] = [];
        }

        if ($xmlReader->nodeType == XMLREADER::ELEMENT &&  $xmlReader->localName == 'VERS')
        {
          $verse = array();
          $verse['v_nbr'] = $xmlReader->getAttribute('vnumber');
          $xmlReader->read();
          $verse['verse'] = $xmlReader->value;
          array_push($chapter['verses'], $verse);
          $bible_to_save = new Bible();
          $bible_to_save->verse_id = $this->getBibleId($book['b_nbr'], $chapter['c_nbr'], $verse['v_nbr']);
          $bible_to_save->book_nr = intval($book['b_nbr']);
          $bible_to_save->chapter_nr = intval($chapter['c_nbr']);
          $bible_to_save->verse_nr = intval($verse['v_nbr']);
          $bible_to_save->verse = $verse['verse'];
          $bible_to_save->verse_for_search = $this->getVerseForSearch($verse['verse']);
          array_push($info, $bible_to_save);
          $bible_to_save->save();
          $bible_to_save->type()->save($this->getBibleType($book['b_nbr']));
        }

        if ($xmlReader->nodeType == XMLREADER::END_ELEMENT && $xmlReader->localName == 'XMLBIBLE')
        {
          if (!empty($book)) {
            if (!empty($chapter)) {
              array_push($book['chapter'], $chapter);
              $chapter = array();
            }
            array_push($books, $book);
          }
        }
      };
    
      return response()->json(['message' => "OK", 'size' => sizeof($info)], $this->successStatus);
    }

    private function getBibleId($b_nbr, $c_nbr, $v_nbr)
    {
      $result = "";
      $result .= sprintf("%02d", intval($b_nbr));
      $result .= sprintf("%03d", intval($c_nbr));
      $result .= sprintf("%03d", intval($v_nbr));

      return $result;
    }

    private function getBibleType($b_nbr) {
      $bibleType = new BibleType();
      if (in_array($b_nbr, [1,2,3,4,5])) {
        $bibleType->type = 'law';
        $bibleType->sub_type = 'law';
        $bibleType->name = 'torah';
        $bibleType->sub_name = 'pentateuch';

        return $bibleType;
      }
      if (in_array($b_nbr, [6,7,9,10,11,12])) {
        $bibleType->type = 'prophets';
        $bibleType->sub_type = 'former';
        $bibleType->name = "nevi'im";
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [23,24,26])) {
        $bibleType->type = 'prophets';
        $bibleType->sub_type = 'major';
        $bibleType->name = "nevi'im";
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [28,29,30,31,32,33,34,35,36,37,38,39])) {
        $bibleType->type = 'prophets';
        $bibleType->sub_type = 'minor';
        $bibleType->name = "nevi'im";
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [18,19,20])) {
        $bibleType->type = 'writings';
        $bibleType->sub_type = 'wisdom';
        $bibleType->name = "ketuvim";
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [8,17,21,22,25])) {
        $bibleType->type = 'writings';
        $bibleType->sub_type = 'festival scrolls';
        $bibleType->name = "ketuvim";
        $bibleType->sub_name = 'megillot';

        return $bibleType;
      }
      if (in_array($b_nbr, [13,14,15,16,27])) {
        $bibleType->type = 'writings';
        $bibleType->sub_type = 'revalation';
        $bibleType->name = "ketuvim";
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [40,41,42,43])) {
        $bibleType->type = 'gospels';
        $bibleType->sub_type = 'gospels';
        $bibleType->name = null;
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [44])) {
        $bibleType->type = 'acts';
        $bibleType->sub_type = 'acts';
        $bibleType->name = null;
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [45,46,47,48,49,50,51,52,53])) {
        $bibleType->type = 'epistles';
        $bibleType->sub_type = 'paul to the churches';
        $bibleType->name = null;
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [58])) {
        $bibleType->type = 'epistles';
        $bibleType->sub_type = 'paul to the hebrews';
        $bibleType->name = null;
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [54,55,56,57])) {
        $bibleType->type = 'epistles';
        $bibleType->sub_type = 'paul to the brothers';
        $bibleType->name = null;
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [59,60,61,62,63,64,65])) {
        $bibleType->type = 'epistles';
        $bibleType->sub_type = 'general';
        $bibleType->name = null;
        $bibleType->sub_name = null;

        return $bibleType;
      }
      if (in_array($b_nbr, [66])) {
        $bibleType->type = 'revelation';
        $bibleType->sub_type = 'revelation';
        $bibleType->name = null;
        $bibleType->sub_name = null;

        return $bibleType;
      }
      $bibleType->type = null;
      $bibleType->sub_type = null;
      $bibleType->name = null;
      $bibleType->sub_name = null;

      return $bibleType;
    }

    private function getVerseForSearch($verse_for_search)
    {
      $result  = $verse_for_search;
      $result  = str_replace(str_split('\\/:*?"<>,.;+#|'), "", $result);
      $result  = strtolower($result);
      return $result;
    }
}
