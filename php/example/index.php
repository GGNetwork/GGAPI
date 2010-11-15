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
    $friends = $gg->getFriends();
    // pobranie danych o użytkowniku
    $profile = $gg->getProfile();

}catch(GGAPIException $e){

    die($e->getMessage());
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

        try{

            if(isset($_POST['send_data']) && $_POST['send_data'] == "notify"){
                $gg->sendNotification($_POST['text'], $_POST['link']);
                echo "<b>Wysłano powiadomienie</b>";
            }
            if(isset($_POST['send_data']) && $_POST['send_data'] == "event"){
                $gg->sendEvent($_POST['text'], $_POST['link'], $_POST['image']);

                echo "<b>Wysłano na pulpit.</b>";
            }
        }catch(GGAPIException $e){

            die($e->getMessage());
        }


        $user = $profile['result']['users'][0];
        echo "
          <a href='http://www.gg.pl/#profile/{$user['id']}' target='_top'>
            <img style='float: right;border: 0;' src='http://avatars.gadu-gadu.pl/{$user['id']}' />
          </a>
          <h1>{$user['label']} - {$user['id']}</h1>
          <h2>Miejscowość: {$user['city']}</h1>
          <h2>Urodziny: ".substr($user['birth'], 0, 10)."</h1>
        ";
?>
        <form action="./" method="post">
            Wyślij
            <select name="send_data">
                <option value="event">na pulpit</option>
                <option value="notify">powiadomienie</option>
            </select>
            <br/>
            <textarea name="text">Test</textarea>
            <br/>
            Link: <input type="text" name="link" value="http://dev.gg.pl" />
            <br/>
            Obraz: <input type="text" name="image" value="http://dev.gg.pl/images/af-logo.png" />
            <br/>
            <input type="submit" value="Wyślij" />
        </form>
<?php
        echo "<h2>Znajomi</h2>
          <ul>";
        foreach($friends['result']['friends'] as $friend){
          echo "<li>{$friend['id']} - <a href='http://www.gg.pl/#profile/{$friend['id']}' target='_top'>{$friend['label']}</a> ";
          echo "(<a href='javascript:GGAPI.openChat({$friend['id']}, \"Hej {$friend['label']}, zobacz to - http://dev.gg.pl\");'>gg</a>)";
          echo "</li>";
        }

        echo "</ul>";
        ?>
    </body>
</html>
