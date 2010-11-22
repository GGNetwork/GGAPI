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
?>
<div class="box_normal">
<?php
try{
    $friend = $gg->getProfile($GET['id']);
    $birthday = strtotime($friend['result']['users'][0]['birth']);
    if(!$birthday)
        die('Znajomy nie ustawił daty urodzin w katalogu publicznym.');
    $biorhythm = count_biorhythm($birthday, BIORHYTM_EMOTIONAL);
    $gg->sendNotification('Twój biorytm na dziś - Emocje: '.$biorhythm.'%', 'http://www.gg.pl/#apps/moj_biorytm', $_GET['id']);
    echo "Wysłano powiadomienie do ".$friend['result']['users'][0]['label'];
}catch(Exception $e){
    die('Wysłanie powiadomienia nie powiodło się.');
}
?>
</div>
