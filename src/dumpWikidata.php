-- Generating backups of JSON-Wikidata --

<?php
// usage: php dumpWikidata.php  flagOpcionalQuandoFixErr

// CONFIGS
  $url_tpl = 'https://www.wikidata.org/w/api.php?action=wbgetentities&format=json&ids=';
 // cols 0=subdivision, 1=name_prefix, 2=name, 3=id, 4=idIBGE, 5=wdId, 6=lexLabel
 $uf_idx=0; $wdId_idx = 5;  $lexLabel_idx = 6;
 $UF=''; $localCsv = false;  $stopAt=0;

$saveFolder = realpath( dirname(__FILE__)."/../data/wikidata" );
$url = $localCsv
     ? "$saveFolder/../br-state-codes.csv"
     : 'https://github.com/datasets-br/state-codes/raw/master/data/br-state-codes.csv'
;
$fixErr = ($argc>=2)? 'MODO FIX-ERR': '';
print "\n USANDO $fixErr $url";


// LOAD DATA:
$R = []; // [fname]= wdId
if (($handle = fopen($url, "r")) !== FALSE) {
   for($i=0; ($row=fgetcsv($handle)) && (!$stopAt || $i<$stopAt); $i++)
      if ( $i && isset($row[1]) )
         $R[ lex2filename($row[$lexLabel_idx]) ] = $row[$wdId_idx];
} else
   exit("\nERRO ao abrir planilha das cidades em \n\t$url\n");


if ($fixErr) foreach($R as $fname=>$wdId) {
  $fs = splitFilename($fname,true);
  if ($fs[2]>50) unset($R[$fname]);
}

// WGET AND SAVE JSON:
$i=1;
$n=count($R);
$ERR=[];
foreach($R as $fname=>$wdId) {
  print "\n\t($i of $n) $fname: $wdId ";
  $json = file_get_contents("$url_tpl$wdId");
  if ($json) {
     $out = json_stdWikidata($json);
     if ($out) {
         $savedBytes = file_put_contents(  "$saveFolder/$fname.json",  $out  );
         print "saved ($savedBytes bytes) with fresh $wdId";
     } else
         ERRset($fname,"invalid Wikidata structure");
  } else
    ERRset($fname,"empty json");
  $i++;
}

if (count($ERR)) { print "\n ----------- ERRORS ---------\n"; foreach($ERR as $msg) print "\n * $msg"; }


///// LIB

function ERRset($fname,$msg) {
   global $ERR;
   $msg = "ERROR, $msg for $fname.";
   print $msg;
   $ERR[] = $msg;
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

function lex2filename($s) {
	$s=ucwords( str_replace('.',' ',$s) );
	return preg_replace('/ D | /','',$s); // elimina preposicao contraida (bug norma lexml)
}

function splitFilename($f,$checkSize=false) {
    global $saveFolder;
    $uf = substr($f,0,2);
    $fname2 = substr($f,3);
    $saveFolder2 = "$saveFolder/$uf";
    $fp = "$saveFolder2/$fname2.json";
    $size = $checkSize? (file_exists($fp)? filesize($fp): 0): null;
    return [$fp,$saveFolder2,$size];
}

?>

... Check git status and do git add.


