<?php

//header('Access-Control-Allow-Origin: *');

$log_file = 'log.txt';
$handle = fopen($log_file, "a");

$search_rq = 0;
if ( array_key_exists('search_key', $_POST) )
{ 
  $search_key = $_POST["search_key"];

  if ( array_key_exists('category', $_POST) )
  { 
    $category = $_POST["category"];
    if ( strcmp($category, 'search_rq') != 0 )
    { 
      fail();
    }
  }

  $no_result_flag = 0;
  if ( strlen($search_key) == 0 )
  { 
    $no_result_flag = 1;
  }

  if ( $no_result_flag )
  { 
    log_msg("search_key=||\n");
    success_js(array('search' => 'none'));
  }
  $search_rq = 1;
}
else
{ 
  // favorite.txt
  if ( array_key_exists('record', $_GET) )
  { 
    $record = $_GET["record"];
  }
  else
  { 
    fail();
  }

  $db_ar = array();
  $file_fav = '../data/favorite.txt';
  file_exists($file_fav) || die;

  $fhandle = fopen($file_fav, 'r');
  if ( filesize($file_fav) > 0 )
  { 
    $fcontents = fread($fhandle, filesize($file_fav));
    fclose($fhandle);

    $db_ar = explode("\n", $fcontents);
    if ( $db_ar[sizeof($db_ar)-1] == '' )
    { 
      unset($db_ar[sizeof($db_ar)-1]);
    }
  }

  echo "$record<br>";
  if ( fav_check($record, $db_ar) )
  { 
    echo "exists<br>";
    $action = 'remove';
  }
  else
  { 
    $action = 'add';
  }
  echo "acton=$action<br>";

  if ( $action == 'add' )
  {
    echo "not exists<br>";
    array_unshift($db_ar, $record);
    $db_ar = array_values($db_ar);
    fav_write($db_ar);
  }
  if ( $action == 'remove' )
  { 
    $db_ar = fav_remove($record, $db_ar);
  }
  $search_key = '';
}

if ( isset($search_key) && strlen($search_key) > mb_strlen($search_key) )
{ 
  // correction of latin аА to cyrillic
  //log_msg("utf8\n");
  $search_key = str_replace('a', 'а', $search_key);
  $search_key = str_replace('A', 'А', $search_key);
}

log_msg("==================================================================\n");
log_msg("ip=" . $_SERVER['REMOTE_ADDR'] . "\n");
if ( $search_rq )
{ 
  log_msg("search_key=|$search_key|\n");
}
//echo "here 2";
//log_msg("category=$category\n");


//============== processing ========================
$empty_link = 'javascript:';

$page_records = 20;
$index_prev;
$index_next;
$page_prev;
$page_next;

$page_dir = '../../../';

$hash2 = array
( 
  '00' => 'Всичко',
  '09' => 'Археология',
  '01' => 'Билки',
  '14' => 'Гоблени',
  '12' => 'Древен Египет',
  '13' => 'Забележителности',
  '06' => 'Здраве',
  '11' => 'Климат',
  '15' => 'Красота',
  '02' => 'Котета',
  '04' => 'Котешки свят',
  '17' => 'Лекарства',
  '18' => 'Любими',
  '05' => 'Оръжия',
  '07' => 'Разни',
  '21' => 'Белушанко',
  '22' => 'Мравчо',
  '23' => 'Малкият тъжен мишо',
  '24' => 'Тъжният мишо',
);

// fancybox
//$file_f = '../data/fancybox3.html';
//$fhandle_f = fopen($file_f, 'r');
//$fcontents_f = fread($fhandle_f, filesize($file_f));
//fclose($fhandle_f);
 
$out_file;
$db_txt = array();

if ( $search_rq )
{ 
  $db_txt = get_db_records();
}
else
{ 
  for($i = 1; $i <= sizeof($db_ar); $i++)
  { 
    echo "$i: " . $db_ar[$i-1] . "<br>";

    array_push($db_txt, $db_ar[$i-1]);
  }
}
//log_msg("database has " . sizeof($db_txt) . " lines\n");


// Find results.
$ar4 = array();

if ( $search_rq )
{ 
  for($i = 1; $i <= sizeof($db_txt); $i++)
  { 
    $line = $db_txt[$i-1];

    if ( strcmp(substr($line,0,1), '#') == 0 )
    { 
      continue;
    }

    $db = explode("|", $line);
    //log_msg("i=$i parts=" . sizeof($db) . "\n");
    sizeof($db) == 7 || fail_msg("line=$i parts=" . sizeof($db));

    $text = $db[7-1];

    $pos = mb_stripos($text, $search_key);
    if ( $pos !== false )
    { 
      array_push($ar4, $i);
      //log_msg("$i: $text\n");
    }
  }
}
else
{ 
  for($i = 1; $i <= sizeof($db_txt); $i++)
  { 
    array_push($ar4, $i);
  }
}
$nresults = sizeof($ar4);
log_msg("nresults=$nresults\n");

if ( $nresults == 0 )
{ 
  success_js(array('search' => 'none'));
}

$num_records = $nresults;

if ( $search_rq )
{ 
  $page = 'search';
}
else
{ 
  $page = 'favorites';
}

//$page_start = $page . '.html';
$page_start = $page . '1.html';

//$index_end = floor($num_records / $page_records) + 1;
$index_end = floor($num_records / $page_records);
if ( ($num_records % $page_records) )
{ 
  $index_end++;
}
//$index_end2 = get_skype_index($index_end);
//$page_end = $page . $index_end . '.html';
$page_end = $page . '.html';

if ( $index_end == 1 )
{ 
  $page_start = $empty_link;
  $page_end = $empty_link;
}
 
$k = 0;

// Write HTML code. 
for($i1 = 1; $i1 <= sizeof($ar4); $i1++)
{
  $i = $ar4[$i1-1];

  $line = $db_txt[$i-1];
  if ( strcmp(substr($line,0,1), '#') == 0 )
  { 
    continue;
  }

  $db = explode('|', $line);
  sizeof($db) == 7 || fail_msg("line=$i parts=" . sizeof($db) . "line=$line");

  $date = $db[1-1];
  $date = get_date($date);

  $cat = $db[2-1];
  $hash2[$cat] || fail_msg("Enter category on line $i");

  $cat_id = 'cat' . $cat;

  $jpg_ar = explode(',', $db[3-1]);

  $jpg_format_ar = explode(',', $db[4-1]);
  //if ( strcmp($jpg_format_ar[0], $db[4-1]) == 0 )
  if ( $db[4-1] == '' )
  { 
    // If no photos, assume all photos are horizontal.
    $jpg_format_ar = array();
    for($j = 1; $j <= sizeof($jpg_ar); $j++)
    { 
      array_push($jpg_format_ar, 1);
    }
  }
  if ( sizeof($jpg_ar) != sizeof($jpg_format_ar) ) 
  { 
    $size_1 = sizeof($jpg_ar);
    $size_2 = sizeof($jpg_format_ar);
    fail_msg("db_line=$i size_1=$size_1 size_2=$size_2");
  }

  $favorite = $db[5-1];

  $url = $db[6-1];

  $text = $db[7-1];

  $k++;
  
  if ( ($k % $page_records) == 1 )
  { 
    // Open next file.
  
    $index_cur = floor($k / $page_records) + 1;
    //$index_cur2 = get_skype_index($index_cur);
    //if ( $k == 1 )
    //{ 
    //  $index_cur = '';
    //}
    $out_file = $page_dir . $page . $index_cur . '.html';
    if ( $index_cur == $index_end )
    {
      $out_file = $page_dir . $page . '.html';
    }
  
    //open(OUT, ">:encoding(UTF-8)", $out_file) || die "$out_file $!";
    $fh1 = fopen($out_file, 'w');
    $fh1 || fail();
  
    // Write arrows in the beginning of page.
    $index_prev = floor($k / $page_records);
    $index_next = floor($k / $page_records) + 2;
  
    //$index_prev2 = get_skype_index($index_prev);
    $page_prev = $page . $index_prev . '.html';
    if ( $k == 1 )
    { 
      $page_prev = $empty_link;
      // Lates stays like this.
      $page_start = $empty_link;
    }
    if ( $k == $page_records + 1 )
    { 
      //$page_prev = $page . '.html';
      $page_prev = $page . '1.html';
      // On 2nd page correct.
      // Stays like this to the end.
      $page_start = $page . '1.html';
    }            
  
    //$index_next2 = get_skype_index($index_next);
    $page_next = $page . $index_next . '.html';
    if ( $index_cur == $index_end -1 )
    {
      $page_next = $page . '.html';
    }

    if ( $k + $page_records > $num_records )
    { 
      $page_next = $empty_link;
      $page_end = $empty_link;
    }
    
    $html_start = <<<HTML_START
<!DOCTYPE html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   <meta name="robots" content="noindex, follow" />
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Котешки свят</title>
   <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
   <link rel="stylesheet" type="text/css" media="screen" href="content/themes/style.css" />

   <!-- jQuery library -->
   <script src="content/code/jquery/3.3.1/jquery-3.3.1.min.js"></script>

   <!-- jQuery - graphical -->
   <script src="content/code/jquery-ui/1.12.1/jquery-ui.min.js"></script>
   <link rel="stylesheet" href="content/code/jquery-ui/1.12.1/themes/smoothness/jquery-ui.css" type="text/css" media="screen" />

   <!-- FancyBox -->
   <link rel="stylesheet" href="content/code/fancybox/3.2.10/jquery.fancybox.min.css" type="text/css" media="screen" />
   <script type="text/javascript" src="content/code/fancybox/3.2.10/jquery.fancybox.min.js"></script>

   <script src="fancybox3.js"></script>

</head>

<body>

<div id="main">
  <div id="s_title_main">
     Котешки свят

     <div id="dash">
        <img src="content/themes/images/menu4.png" onClick="toggle_menu()">
     </div>
  </div>

  <div id=cat_holder_main>
     <!--
     <div id ="main_line_text">
      
        <div id ="catg_holder_text">
           Рубрика
        </div>
      
        <div id ="color_holder_text">
           Тема
        </div>
      
     </div>
     --> 

     <div id ="main_line">

        <select id="catg_holder" name="catg_combo" size="1">
          <option value="cat00">Всичко</option>
          <option disabled>‒‒‒‒‒‒‒‒‒‒‒‒‒</option>
          <option value="cat09">Археология</option>
          <option value="cat01">Билки</option>
          <option value="cat16">Времето</option>
          <option value="cat14">Гоблени</option>
          <option value="cat12">Древен Египет</option>
          <option value="cat13">Забележителности</option>
          <option value="cat06">Здраве</option>
          <option value="cat11">Климат</option>
          <option value="cat02">Котета</option>
          <option value="cat04">Котешки свят</option>
          <option value="cat15">Красота</option>
          <option value="cat17">Лекарства</option>
          <option value="cat18">Любими</option>
          <option value="cat05">Оръжия</option>
          <option value="cat07">Разни</option>
          <option disabled>‒‒‒‒‒‒‒‒‒‒‒‒‒</option>
          <option value="cat03">Ривеста Стар</option>
          <option disabled>‒‒‒‒‒‒‒‒‒‒‒‒‒</option>
          <!-- <optgroup label="Възпоминание"> -->
             <option value="cat21">Белушанко</option>
             <option value="cat22">Мравчо</option>
             <option value="cat23">Малкият тъжен мишо</option>
             <option value="cat24">Тъжният мишо</option>
          <!-- </optgroup> -->
        </select>

        <select id="color_holder" name="color_combo" size="1">
           <optgroup label="Обикновена">
              <option value="S1">Обикновена жълта</option>
              <option value="S2">Обикновена черна</option>
              <option value="S3">Обикновена червена</option>
              <option value="S4">Обикновена синя</option>
              <option value="S5">Обикновена бледа</option>
          </optgroup>
          <optgroup label="Панорамна">
             <option value="P1">Панорамна червена</option>
             <option value="P2">Панорамна синя</option>
             <option value="P3">Панорамна черна</option>
          </optgroup>
          <optgroup label="Воден знак">
             <option value="W1">Воден знак кафява</option>
             <option value="W2">Воден знак бежова</option>
             <option value="W3">Воден знак зелена</option>
             <option value="W4">Воден знак розова</option>
          </optgroup>
        </select>

     </div>
HTML_START;
    fwrite($fh1, $html_start);

    $html_start_2 = <<<HTML_START_2
     <form id="form_search" action="content/code/php/search.php" method="POST" target="hiddenFrame">
        <input id="search_id" type="text" name="search_key" placeholder="${search_key}" >
        <input type="hidden" name="category" value="search_rq">
        <div id="search_result"> $num_records резултата</div>
        <input type="button" value="Обратно" onClick="form_search_clear()">
     </form>

     <script type="text/javascript" src="content/code/search/search.js"></script>
     <script type="text/javascript">
        function form_search_clear()
           { 
             location.href = 'index.html';
             document.getElementById('form_search').reset();
           }
     </script>
  </div>
HTML_START_2;

    $html_start_3 = <<<HTML_START_3
     <form id="form_search" action="content/code/php/search.php" method="POST" target="hiddenFrame">
        <input id="search_id" type="text" name="search_key" placeholder="">
        <input type="hidden" name="category" value="search_rq">
        <input type="submit" value="Търсене">

        <div id="rtime">
           20:49
        </div>

     </form>
 </div>
HTML_START_3;
    if ( $search_rq )
    { 
      fwrite($fh1, $html_start_2);
    }
    else
    { 
      fwrite($fh1, $html_start_3);
    }
    
    $html_nav = <<<HTML_NAV
<table class="arrows">
<tr>
<td>
<div class="gallery5">
   <a href="${page_start}">
      <img src="content/themes/images/left_end2tr-64.png" alt="photo">
   </a>
</div>
</td>
<td>
<div class="gallery5">
   <a href="${page_prev}">
      <img src="content/themes/images/left_arrow2tr-64.png" alt="photo">
   </a>
</div>
</td>
<td>
<div class="gallery5">
   <a href="${page_next}">
      <img src="content/themes/images/right_arrow2tr-64.png" alt="photo">
   </a>
</div>
</td>
<td>
<div class="gallery5">
   <a href="${page_end}">
      <img src="content/themes/images/right_end2tr-64.png" alt="photo">
   </a>
</div>
</td>
HTML_NAV;
          fwrite($fh1, $html_nav);
          $html_7 = <<<HTML7
<td>
<div class="gallery5">
   <a href="javascript:window.scrollTo(0, document.body.scrollHeight)">
      <img src="content/themes/images/down_arrow2tr-64.png" alt="photo">
   </a>
</div>
</td>
</tr>
</table>
HTML7;
    fwrite($fh1, $html_7);
  }

  // Write record number and date.
  //<div class="s_title"  name="${cat_id}_A">запис $k / $date</div>
  $html_1 = <<<HTML1
<div class="h_line" name="${cat_id}_L>"></div>
<div class="s_title"  name="${cat_id}_A">$date</div>
<div class="s_title2" name="${cat_id}_B">Категория &quot;$hash2[$cat]&quot;</div>

HTML1;
  fwrite($fh1, $html_1);

  // Write text.
  $text_new = $text;
  if ( $jpg_ar[0] == '' )
  { 
    $text_new = '<a href="' . $url . '">' . $text . '</a>';
  }
  $html_3 = <<<HTML3
<div class="post">
<div class="s_text" name="${cat_id}_T">
  $text_new
</div>
HTML3;
  fwrite($fh1, $html_3);

  // Write photos.
  for($j = 1; $j <= sizeof($jpg_ar); $j++)
  { 
    if ( $jpg_ar[0] == '' )
    { 
      break;
    }
    
    $jpg = $jpg_ar[$j-1];
  
    //if ( preg_match('/href/', $text) )
    if ( $url != '' )
    {
      // Photos with url - link photo to url.
      $j == 1 || fail();
      //preg_match('/href=\"(.*?)\"/', $text, $matches);
      //$url = $matches[1];
    
      if ( $jpg_format_ar[$j-1] == 1 )
      { 
        // Horizontal photo.
        $gallery = 'gallery3';
      } 
      else
      { 
        // Vertical photo.
        $gallery = 'gallery4';
      }
           
      $html_11 = <<<HTML11
<div class="$gallery" name="${cat_id}_$j">
   <a href="$url">
      <img src="content/images/$jpg" alt="photo">
   </a>
</div>
HTML11;
      fwrite($fh1, $html_11);
    }
    else
    { 
      // Photos with text.
    
      $jpg_big = $jpg;
      $jpg_thm = $jpg;
    
      $beg = substr($jpg,0,3);
      if ( strcmp($beg, 'DSC') == 0 || strcmp($beg, 'CAM') == 0 )
      { 
        $jpg_big = str_replace('.', 'big.', $jpg_big);
        $jpg_thm = str_replace('.', 'thm.', $jpg_thm);
      }
    
      if ( $jpg_format_ar[$j-1] == 1 )
      { 
        // Horizontal photo.
        $gallery = 'gallery3';
      }
      else
      { 
        // Vertical photo.
        $gallery = 'gallery4';
        //echo "yes $jpg_big";
        //die;
      }
      $html_2 = <<<HTML2
<div class="$gallery" name="$cat_id">
   <a class="zoom" href="content/images/$jpg_big" data-fancybox="images" data-caption="${text}">
      <img src="content/images/$jpg_thm" alt="photo">
   </a>
</div>
HTML2;
      fwrite($fh1, $html_2);
    }
  }

//<div class="favorite">
//   <a href="content/code/php/search.php?record=$line">
//      <img src="content/themes/images/star_red.png" onclick="do_fav()">
//   </a>
//</div>

  // запис на край на поста
  $html_12 = <<<HTML_12
</div>
HTML_12;
  fwrite($fh1, $html_12);

  if ( ($k % $page_records) == 0 || $k == $num_records )
  {
    // Write arrows in the end of page.
    $html_10 = <<<HTML10
<div class="h_line" name="${cat_id}_L>"></div>
HTML10;
    fwrite($fh1, $html_10);

    fwrite($fh1, $html_nav);
    $html_8 = <<<HTML8
<td>
<div class="gallery5">
   <a href="javascript:window.scrollTo(0, 0)">
      <img src="content/themes/images/up_arrow2tr-64.png" alt="photo">
   </a>
</div>
</td>
</tr>
</table>
<div id="Dialog1"><div id="Dialog1Text"></div></div>
</div>
 
</body>
</html>
HTML8;
    fwrite($fh1, $html_8);
    fclose($fh1);
  }
}

log_msg("done\n");

if ( $search_rq )
{ 
  success_js(array('search' => 'ok'));
}
else
{ 
  if ( isset($_SERVER['HTTP_REFERER']) )
     { header("Location: " . $_SERVER['HTTP_REFERER']);
     }
}
fail();


//====================================================================================

function log_msg($message)
{ 
  global $handle;

  $timestamp = strftime('%Y-%m-%d %H:%M:%S', time());
  fwrite($handle, $timestamp . ' ' . $message);
}
function success_js($result)
{
  $ar = array
  ( 
    'success' => 'true',
    'result' => $result, 
  );
  $result = json_encode($ar);
  print "$result";
  exit(0);
}
function fail_js($result)
{
  $ar = array
  ( 
    'success' => 'false',
    'result' => $result,
  );
  $result = json_encode($ar);
  print "$result";
  exit(-1);
}
function get_db_records()
{
  $data_dir = '../data/';

  global $search_rq;

  if ( $search_rq )
  { 
    $file_ar = array
    ( 
      'blog_db.txt',
      'blog_db2.txt',
    );
  }
  else
  { 
    //$file_ar = array
    //   ( 'favorite.txt',
    //   );
    fail();
  }

  $db_ar = array();
  for($i = 1; $i <= sizeof($file_ar); $i++)
  { 
    $file = $data_dir . $file_ar[$i-1];
    file_exists($file) || fail();
    if ( filesize($file) )
    { 
      $fhandle = fopen($file, 'r');
      $fcontents = fread($fhandle, filesize($file));
      fclose($fhandle);
    
      $db_ar_tmp = explode("\n", $fcontents);
      for($j = 1; $j <= sizeof($db_ar_tmp); $j++)
      { 
        if ( strlen($db_ar_tmp[$j-1]) > 0 )
        { 
          array_push($db_ar, $db_ar_tmp[$j-1]);
        }
      }
    }
  }
  return $db_ar;
}
function fail_msg($message)
{
  $ar = debug_backtrace();
  $line = $ar[0]['line'];
  $timestamp = strftime('%Y-%m-%d %H:%M:%S', time());
  error_log("$timestamp Error in line=$line: $message\n", 3, 'log.txt');
  fail_js(array('search' => 'error'));
}
function fail()
{ 
  $ar = debug_backtrace();
  $line = $ar[0]['line'];
  $timestamp = strftime('%Y-%m-%d %H:%M:%S', time());
  error_log("$timestamp Error in line=$line.\n", 3, 'log.txt');
  fail_js(array('search' => 'error'));
}
//function get_skype_index($index)
//   {
//     //$index_new = $index < 10 ? "0$index" : $index;
//     $index_new = sprintf("%03d", $index);
//     return $index_new;
//   }
function get_date($date_p)
{
  $date_ar = explode(' ', $date_p);
  sizeof($date_ar) == 2 || fail();
  $date_hash = array
  ( 
    '01' => 'януари',
    '02' => 'февруари',
    '03' => 'март',
    '04' => 'април',
    '05' => 'май',
    '06' => 'юни',
    '07' => 'юли',
    '08' => 'август',
    '09' => 'септември',
    '10' => 'октомври',
    '11' => 'ноември',
    '12' => 'декември',
  );
  $date_comp = explode('-', $date_ar[1-1]);
  sizeof($date_comp) == 3 || fail();
  $month = $date_comp[2-1];
  $day = $date_comp[3-1];
  $year = $date_comp[1-1];
  $date_new = $day . ' ' . $date_hash{$month} . ' ' . $year;
  $time = $date_ar[2-1];
  $date_new .= ' г. ' . $time . ' ч.';;

  return $date_new;
}
function fav_check($record, $file_ar)
{
  for($i = 1; $i <= sizeof($file_ar); $i++)
  { 
    $line = $file_ar[$i-1];
  
    if ( strcmp($line, $record) == 0 )
    { 
      return 1;
    }
  }
  return 0;
}
 
function fav_write($file_ar)
{
  global $file_fav;

  $fh = fopen($file_fav, 'w');
  
  for($i = 1; $i <= sizeof($file_ar); $i++)
  { 
    $line = $file_ar[$i-1];
    if ( $line != '' )
    { 
      fwrite($fh, "$line\n");
    }
  }
  fclose($fh);
}

function fav_remove($record, $file_ar)
{
  $found = 0;
  $file_ar2 = array();

  for($i = 1; $i <= sizeof($file_ar); $i++)
  { 
    $line = $file_ar[$i-1];
    //echo "$i: " . $file_ar[$i-1] . "<br>";
  
    if ( strcmp($line, $record) == 0 )
    { 
      $found = $i;
      unset($file_ar[$i-1]);
      $file_ar2 = array_values($file_ar);
    }
  }

  if ( $found == 0 )
  { 
    fail();
  }

  //for($i = 1; $i <= sizeof($file_ar2); $i++)
  //   { echo "$i: " . $file_ar2[$i-1] . "<br>";
  //   }
  
  global $file_fav;

  $fh = fopen($file_fav, 'w');

  for($i = 1; $i <= sizeof($file_ar2); $i++)
  { 
    $line = $file_ar2[$i-1];
    if ( $line != '' )
    { 
      fwrite($fh, "$line\n");
    }
  }
  fclose($fh);

  return $file_ar2;
}

?>
