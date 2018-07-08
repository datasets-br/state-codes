-- Generating backups of JSON-Wikidata --

<?php
// usage: php dumpWikidata.php  [geo][err]

// CONFIGS
  $urlWd_tpl = 'https://www.wikidata.org/w/api.php?action=wbgetentities&format=json&ids=';
  $urlOsm_tpl = 'http://polygons.openstreetmap.fr/get_geojson.py?id=';
  $UF=''; $localCsv = true;  $stopAt=0;

$saveFolder = realpath( dirname(__FILE__)."/../data" );
$url = $localCsv
     ? $saveFolder
     : 'https://github.com/datasets-br/state-codes/raw/master/data'
;
 // cols 0=subdivision, 1=region, 2=name_prefix, 3=name, 4=id, 5=idIBGE, 6=wdId, 7=lexLabel
 $uf_idx=0; $wdId_idx = 6;  //$lexLabel_idx = 7;


$modo = ($argc>=2)?    ( ($argv[1]=='geo')? 'GEO': 'FIX-ERR-WD'  ): '';
$jext = ($modo=='GEO')? 'geojson': 'json';
print "\n USANDO $modo $url";


// LOAD DATA:
$R = []; // [fname]= wdId
loadFile('br-state-codes.csv', '',    $uf_idx,$wdId_idx,9); // ignore extinct idx=9
loadFile('br-region-codes.csv','reg_',0,1,5); // ignore extinct idx=5

// WGET AND SAVE JSON:
$i=1;
$n=count($R);
$ERR=[];

//print "\nOK! $n itens at R="; var_dump($R); die("\n\n");

switch($modo) {

case '':
case 'FIX-ERR-WD':
	foreach($R as $fname=>$wdId) {
	  print "\n\t($i of $n) $fname: $wdId ";
	  $json = file_get_contents("$urlWd_tpl$wdId");
	  if ($json) {
	     $out = json_stdWikidata($json);
	     if ($out) {
	         $savedBytes = file_put_contents(  "$saveFolder/dump_wikidata/$fname.$jext",  $out  );
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
	         $savedBytes = file_put_contents(  "$saveFolder/dump_osm/$fname.$jext",  $out  );
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

/**
 * Load a CSV with wdIds.
 * @return affect global $R. Dies on error.
 */
function loadFile($file='br-state-codes.csv',$prefix='',$keyIdx=0,$valIdx=5,$ignore=0) {
  global $R;
  global $url;
  global $stopAt;
  global $modo;
  global $saveFolder;
  global $jext;
  //print "\n--debug loadFile(file=$file,prefix=$prefix, keyIdx=$keyIdx,valIdx=$valIdx,ig=$ignore)";
  if (($handle = fopen("$url/$file", "r")) !== FALSE) {
     for($i=0; ($row=fgetcsv($handle)) && (!$stopAt || $i<$stopAt); $i++) {
        if ( $i && isset($row[0]) && ($ignore=='' || $row[$ignore]=='') )
           $R[ $prefix.$row[$keyIdx] ] = $row[$valIdx];
        //print "\n\t-- debug $i = $row[$keyIdx] ... ($row[0]) && ($ignore=='' || {$row[$ignore]})";
      }
  } else
     exit("\nERRO ao abrir planilha $file em \n\t$url\n");
  if ($modo=='FIX-ERR-WD') foreach($R as $fname=>$wdId) {
    if ( filesize("$saveFolder/dump_wikidata/$fname.$jext")>50 ) unset($R[$fname]);
  }
  return true; // false for error
}

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
