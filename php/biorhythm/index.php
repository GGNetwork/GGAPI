<?php
/* * * * * * * * * * * *

Licenced for use under the LGPL. See http://www.gnu.org/licenses/lgpl-3.0.txt.

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This licence is there: http://www.gnu.org/licenses/lgpl-3.0.txt.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS /FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

* * * * * * * * * * * * */

/**
* @author       GG Network S.A.
* @copyright    Copyright © 2010, GG Network S.A.
* @license      Licenced for use under the LGPL. See http://www.gnu.org/licenses/lgpl-3.0.txt
*/

require_once '../GGAPI.php';
require_once 'biorhythm.php';
require_once 'lang.php';

$user     = $profile['result']['users'][0];
$birthday = strtotime($user['birth']);

if(!$birthday)
    die('Ustaw datę urodzin w swoim profilu');

$biorhythm =  array(
    get_biorhythm_data(BIORHYTM_EMOTIONAL,   $birthday, $lang_biorhythm_descriptions),
    get_biorhythm_data(BIORHYTM_INTELECTUAL, $birthday, $lang_biorhythm_descriptions),
    get_biorhythm_data(BIORHYTM_PHISICAL,    $birthday, $lang_biorhythm_descriptions),
    get_biorhythm_data(BIORHYTM_INTUITION,   $birthday, $lang_biorhythm_descriptions),
);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="http://www.gg.pl/js/ggapi.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
<script type="text/javascript" src="biorhythm.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
         biorhythm_init(<?= json_encode($biorhythm); ?>,'Mój biorytm na dziś: ', 'http://www.gg.pl/#apps/moj_biorytm/', 'http://dev.gg.pl/images');
    });
</script>
<style type="text/css" media="all">
    @import url(biorhythm.css);
</style>
<title>Mój biorytm</title>
</head>
<body>

<div style='height: 72px;'>
     <div style="float: right; width: 250px;text-align: right;">

        <div>
            <div style="float: right; width: 65px;margin-left: 5px;text-align:left;">
               <b><br/>
                   sekund<br/>
                   minut<br/>
                   godzin<br/>
                   dni<br/>
                   tygodni
               </b>
            </div>
            <div>
                <h2>Żyjesz już:</h2>
                <?php
                    $diff = time() - $birthday;
                    echo number_format($diff)."<br>".
                         number_format($diff/60)."<br>".
                         number_format($diff/(60*60))."<br>".
                         number_format($diff/(60*60*24))."<br>".
                         number_format($diff/(60*60*24*7))."<br>";
                ?>
            </div>
        </div>
    </div>
    <div style="float:left;width: 460px;">
        <?php
        echo "<h1>{$user['label']}, twój biorytm na dzisiaj!</h1>";
        echo "<h2>Twoje predyspozycje na ".$day[date('N') - 1]." ".date('j').' '.$month[date('m') - 1].' '.date('Y')." </h2><br>";
        ?>
    </div>

</div>
<div style="height: 400px;margin-top: 20px;clear: both;">
    <div style="width: 680px; height: 200px;position: relative;border-bottom: 1px solid gray;">
        <div class="bio_result"><span></span></div>
        <div class="bio_result"><span></span></div>
        <div class="bio_result"><span></span></div>
        <div class="bio_result"><span></span></div>
    </div>
</div>


<br style="clear:both" />

<div class="friends">
    <h2>Kliknij na znajomego, aby wysłać mu biorytm:</h2>

    <div id="results"></div>

<?php
foreach($friends['result']['friends'] as $friend){
    echo "<img src='".$gg->getAvatarUrl($friend['id'])."' id='{$friend['id']}' title='{$friend['label']}'>";
}
?>
</div>


</body>
</html>
