-- Generating backups of JSON-Wikidata --

<?php
// usage: php dumpWikidata.php  [geo][err]

// CONFIGS
  $urlWd_tpl = 'https://www.wikidata.org/w/api.php?action=wbgetentities&format=json&ids=';
  $urlOsm_tpl = 'http://polygons.openstreetmap.fr/get_geojson.py?id=';
  $UF=''; $localCsv = false;  $stopAt=0;

$saveFolder = realpath( dirname(__FILE__)."/../data" );
$url = $localCsv
     ? "$saveFolder/br-state-codes.csv"
     : 'https://github.com/datasets-br/state-codes/raw/master/data/br-state-codes.csv'
;
 // cols 0=subdivision, 1=name_prefix, 2=name, 3=id, 4=idIBGE, 5=wdId, 6=lexLabel
 $uf_idx=0; $wdId_idx = 5;  $lexLabel_idx = 6;


$modo = ($argc>=2)?    ( ($argv[1]=='geo')? 'GEO': 'FIX-ERR'  ): '';
$ext = ($modo=='GEO')? 'geojson': 'json';
print "\n USANDO $modo $url";


// LOAD DATA:
$R = []; // [fname]= wdId
if (($handle = fopen($url, "r")) !== FALSE) {
   for($i=0; ($row=fgetcsv($handle)) && (!$stopAt || $i<$stopAt); $i++)
      if ( $i && isset($row[1]) )
         $R[ $row[$uf_idx] ] = $row[$wdId_idx];
} else
   exit("\nERRO ao abrir planilha das cidades em \n\t$url\n");


if ($modo=='FIX-ERR') foreach($R as $fname=>$wdId) {
  if ( filesize("$saveFolder/dump_wikidata/$fname.$ext")>50 ) unset($R[$fname]);
}

// WGET AND SAVE JSON:
$i=1;
$n=count($R);
$ERR=[];

switch($modo) {

case '':
case 'FIX-ERR':
	foreach($R as $fname=>$wdId) {
	  print "\n\t($i of $n) $fname: $wdId ";
	  $json = file_get_contents("$urlWd_tpl$wdId");
	  if ($json) {
	     $out = json_stdWikidata($json);
	     if ($out) {
	         $savedBytes = file_put_contents(  "$saveFolder/dump_wikidata/$fname.$ext",  $out  );
	         print "saved ($savedBytes bytes) with fresh $wdId";
	     } else
	         ERRset($fname,"invalid Wikidata structure");
	  } else
	    ERRset($fname,"empty json");
	  $i++;
	}
	break;

case 'GEO':
	foreach($R as $fname=>$wdId) {
	  print "\n\t($i of $n) $fname: $wdId ";
	  $osmId= getOsmId($fname,$wdId); // usa wdId?
	  $json='';
	  if ($osmId) $json = file_get_contents("$urlOsm_tpl$osmId");
	  else ERRset($fname,"no osmId or P402");
	  if ($json) {
	     $out = json_stdOsm($json);
	     if ($out) {
	         $savedBytes = file_put_contents(  "$saveFolder/dump_osm/$fname.$ext",  $out  );
	         print "saved ($savedBytes bytes) with fresh OSM/$osmId";
	     } else
	         ERRset($fname,"invalid OSM structure");
	  } else
	    ERRset($fname,"empty json");
	  $i++;
	}
	break;

default:
	die("\n Modo $modo DESCONHECIDO.\n");

} // end switch


if (count($ERR)) { print "\n ----------- ERRORS ---------\n"; foreach($ERR as $msg) print "\n * $msg"; }


///// LIB

function ERRset($fname,$msg) {
   global $ERR;
   $msg = "ERROR, $msg for $fname.";
   print $msg;
   $ERR[] = $msg;
}

function json_stdOsm($jstr) {
  if (!trim($jstr)) return '';
  $j = json_decode($jstr,JSON_BIGINT_AS_STRING|JSON_OBJECT_AS_ARRAY);
  if ( !isset($j['type']) ) return '';
  return json_encode($j,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}

function json_stdWikidata($jstr) {
  if (!trim($jstr)) return '';
  $j = json_decode($jstr,JSON_BIGINT_AS_STRING|JSON_OBJECT_AS_ARRAY);
  if ( !isset($j['entities']) ) return '';
  $ks=array_keys($j['entities']);
  $j = $j['entities'][$ks[0]];
  if ( !isset($j['claims']) ) return '';
  foreach(['lastrevid','modified','labels','descriptions','title','aliases','sitelinks'] as $r) unset($j[$r]);
  $a = []; 
  foreach($j['claims'] as $k=>$r) {
      $a[$k] = [];
      foreach($j['claims'][$k] as $r2)
          $a[$k][] = $r2['mainsnak']['datavalue'];
  }
  $j['claims'] = $a;
  return json_encode($j,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); 
}

function getOsmId($fname) {
  global $saveFolder;
  $f = "$saveFolder/dump_wikidata/$fname.json";
  $j = json_decode( file_get_contents($f), JSON_BIGINT_AS_STRING|JSON_OBJECT_AS_ARRAY);
  if (isset($j['claims']['P402'][0]['value']) )
	return $j['claims']['P402'][0]['value'];
  else
	return 0;
}

?>

... Check git status and do git add.


