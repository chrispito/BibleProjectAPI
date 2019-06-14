<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Bible;
use App\Models\BibleType;
use Illuminate\Support\Facades\Log;
use Orchestra\Parser\Xml\Facade as XmlParser;
use \XMLReader;
use \DOMDocument;

class BibleController extends Controller
{

    public $successStatus = 200;

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
        }
        
      };
    
      return response()->json(['info' => $info, 'books' => $books, 'other' => $other], $this->successStatus);
    }
}
