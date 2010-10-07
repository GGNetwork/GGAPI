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

try{

    // inicjalizacja GGAPI z parametrami aplikacji client_id i client_secret
    $gg = new GGAPI('client_id', 'client_secret');
    // opcjonalna inicjalizacja sesji na podstawie gg_session_id
    // jeśli token autoryzacyjny ma być zapisany tylko na czas sesji
    $gg->initSession();
    // jeśli nie ma tokena, należy poprosić użytkownika o dostęp do zasobów
    if(!$gg->hasToken()){
       $gg->authorize(array('users','pubdir'));
    }
    // pobranie listy kontaktów
    $friends = $gg->getFriends();
    // pobranie danych o użytkowniku
    $profile = $gg->getProfile();

}catch(GGAPIException $e){
    
    die($e);
}
?>
<html>
    <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <script type="text/javascript" src="http://www.gg.pl/js/ggapi.js"></script>
    <title>Znajomi</title>
    </head>
    <body>
        <?php
        $user = $profile['result']['users'][0];
        echo "
          <a href='http://www.gg.pl/#profile/{$user['id']}' target='_top'>
            <img style='float: right;border: 0;' src='http://avatars.gadu-gadu.pl/{$user['id']}' />
          </a>
          <h1>{$user['label']} - {$user['id']}</h1>
          <h2>Miejscowość: {$user['city']}</h1>
          <h2>Urodziny: ".substr($user['birth'], 0, 10)."</h1>
          <h2>Znajomi</h2>
          <ul>
        ";

        foreach($friends['result']['friends'] as $friend){
          echo "<li>{$friend['id']} - <a href='http://www.gg.pl/#profile/{$friend['id']}' target='_top'>{$friend['label']}</a></li>";
        }

        echo "</ul>";
        ?>
    </body>
</html>