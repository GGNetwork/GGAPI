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

// adres aplikacji w serwisie gg.pl
$app_url = 'http://www.gg.pl/#apps/moj_biorytm/';
// pełna ścieżka do aplikacji
$app_real_url   = 'http://dev.gg.pl/gg';
// pelna ścieżka do obrazków aplikacji
$app_media_url  = 'http://dev.gg.pl/images';

define('BIORHYTM_EMOTIONAL',     28);
define('BIORHYTM_INTELECTUAL',   33);
define('BIORHYTM_PHISICAL',      23);
define('BIORHYTM_INTUITION',     38);

function count_biorhythm($birth, $type){

    $time_diff = (time() - $birth) / (24 * 60 * 60);
    return round(sin(2 * pi() * $time_diff / $type) * 100);
}


function get_biorhythm_data($type, $birth, $descriptions){

    $value = count_biorhythm($birth, $type);
    $c     = count($descriptions[$type]) - 1;
    $v     = min(array(round((100 + $value) * $c / 200), $c-1)) + 1;

    return array(
        'name'        => $descriptions[$type][0],
        'value'       => $value,
        'image'       => $descriptions[$type][$v]['img'],
        'description' => $descriptions[$type][$v]['desc'],
    );

}

try{

    // inicjalizacja GGAPI z parametrami aplikacji client_id i client_secret
    // pobranymi z konfiguracji aplikacji na http://dev.gg.pl
    $gg = new GGAPI('client_id', 'client_secret');
    // opcjonalna inicjalizacja sesji na podstawie gg_session_id
    // jeśli token autoryzacyjny ma być zapisany tylko na czas sesji
    $gg->initSession();
    // jeśli nie ma tokena, należy poprosić użytkownika o dostęp do zasobów
    if(!$gg->hasToken()){
       $gg->authorize(array('users','pubdir','life'));
    }
    // pobranie listy kontaktów
    $friends = !isset($_SESSION['friends']) ? $gg->getFriends() : $_SESSION['friends'];
    // pobranie danych o użytkowniku
    $profile = !isset($_SESSION['profile']) ? $gg->getProfile() : $_SESSION['profile'];

    $_SESSION['friends'] = $friends;
    $_SESSION['profile'] = $profile;

}catch(GGAPIException $e){

    die($e->getMessage());
}
