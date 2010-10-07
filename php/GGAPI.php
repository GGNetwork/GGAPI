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
class GGAPI
{
    /**
    * @desc Wersja
    */
    const VERSION = '0.9';

    protected $auth = array(
        'oauth'     => 'https://auth.api.gg.pl/token',
        'authorize' => 'https://www.gg.pl/authorize',
    );

    protected $scopes = array(
        'pubdir' => 'https://pubdir.api.gg.pl',
        'users'  => 'https://users.api.gg.pl',
    );

    /**
    * @desc Typ formatu odpowiedzi z serwera
    */
    protected $responseType = '';
    /**
     * Czy analizować odpowiedź serwera
     */
    protected $parseResponse = true;
    /**
    * @desc Ostatnia odpowiedź serwera
    */
    protected $response;
    /**
    * @desc Tablica ostatnich błędów
    */
    private $lastError;
    /**
     * @desc Ostatnie nagłówki
     */
    private $lastHeaders;
    /**
     * @desc Czas odpowiedzi
     */
    protected $requestTimeout = 3;
    /**
     * @desc Identyfikator aplikacji
     */
    private $client_id = null;
    /**
     * @desc Hasło aplikacji
     */
    private $client_secret = null;
     /**
     * @desc Token użytkownika
     */
    private $access_token = null;
     /**
     * @desc Dane do odnowienia tokenu użytkownika
     */
    private $refresh_token = null;
    /**
     * @desc Inicjalizacja
     * 
     * @param string $client_id
     * @param string $client_secret
     */
    public function  __construct($client_id, $client_secret) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    /**
     * @dest Inicjalizacja sesji użytkownika na podstawie otrzymanego gg_session_id
     *
     * @return void
     */
    public function initSession(){
        
        if(isset($_GET['gg_session_id'])){
            session_id($_GET['gg_session_id']);
        }
        session_start();

        if(isset($_GET['code']) && !isset($_SESSION['token_data'])){
           $token_data = $this->getAccessToken($_GET['code']);
           $_SESSION['token_data'] = $token_data;
        }

        if(isset($_SESSION['token_data'])){
           $this->setToken($_SESSION['token_data']['access_token'], $_SESSION['token_data']['refresh_token']);
        }
    }
    /**
     * @desc Pobierz znajomych
     *
     * @return array
     */
    public function getFriends($user = null, $params = null){

        return $this->doRequest('GET', $this->scopes['users'].'/friends/'.$this->getUser($user), $params === null ? array('limit' => 1000) : $params, array($this->getAuthHeader()));
    }
    /**
     * @desc Pobierz dane profilu
     *
     * @return array
     */
    public function getProfile($user = null){

        return $this->doRequest('GET', $this->scopes['pubdir'].'/users/'.$this->getUser($user), null, array($this->getAuthHeader()));
    }


    // -------------------------------------------------------------------
    // Metody wewnętrzne
    // -------------------------------------------------------------------

    /**
     * @desc Pobierz nagłówek autoryzacyjny
     *
     * @return string
     */
    public function getAuthHeader(){
        return 'Authorization: OAuth '.$this->access_token;
    }
    /**
     * @desc Poproś użytkownika o zgodę na dostęp do zasobów
     *
     * @return void
     */
    public function authorize($scopes, $response_type = 'code'){
        
        $diff = array_diff($scopes, array_keys($this->scopes));

        if(count($diff)){
            throw new GGAPIException('Incorrect scope: '.join(' ', $diff));
        }

        $params = array(
            'response_type' => $response_type,
            'client_id'     => $this->client_id,
            'scope'         => join(' ', $scopes),
            'redirect_uri'  => $this->getURI()
        );
        
        $url = $this->auth['authorize'].'?'.http_build_query($params);

        header('Location: '.$url);
        exit();
    }
    /**
     * @desc Ustaw token do komunikacji
     *
     * @return GGAPI
     */
    public function setToken($access_token, $refresh_token){

        $this->access_token  = $access_token;
        $this->refresh_token = $refresh_token;

        return $this;
    }
    /**
     * @desc Czy mamy token
     *
     * @return bool
     */
    public function hasToken(){

        return $this->access_token !== null;
    }
    /**
     * @desc Pobierz token
     *
     * @return string
     */
    public function getAccessToken($code){

        return $this->doRequest('POST', $this->auth['oauth'], array(
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri'  => $this->getURI(),
        ));
    }
    /**
     * @desc Pobierz nowy token na podstawie starego
     *
     * @return string
     */
    public function refreshToken(){

        return $this->doRequest('POST', $this->auth['oauth'], array(
            'refresh_token' => $this->refresh_token,
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri'  => $this->getURI(),
        ));
    }
    /**
     * @desc Pobierz identyfikator użytkownika
     *
     * @return string
     */
    protected function getUser($user = null){

        return $user === null ? 'me' : 'user,'.$user;
    }
    /**
     * @desc Pobierz adres serwisu
     *
     * @return string
     */
    protected function getURI(){
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://': 'http://').$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
    * @desc Pobranie adresu url zapytania do api
    *
    * @param string     $method     nazwa metody http: 'GET','POST','PUT','DELETE'
    * @param string     $uri        nazwa zasobu jako uri
    * @param mixed      $params     dodatkowe parametry zapytania
    * @param bool       $ssl        czy zapytanie jest po https
    *
    * @return string
    */
    protected function getRequestURL($method, $uri, $params = null, $ssl = false, $responseType = ''){
        return $uri.($responseType ? '.'.$responseType : '').(is_array($params) && count($params) > 0 && $method == 'GET' ? '?'.http_build_query($params) : '');
    }
    /**
    * @desc Zapytanie http do api realizowane przez użytkownika podpisane przez OAuth
    *
    * @param string     $method     nazwa metody http: 'GET','POST','PUT','DELETE'
    * @param string     $uri        nazwa zasobu jako uri
    * @param mixed      $params     dodatkowe parametry zapytania
    * @param bool       $ssl            czy zapytanie jest po https
    * @param string     $responseType   w jakim formacie ma być odpowiedź z serwera
    *
    * @return mixed     tablica elementów zwróconych przez API
    */
    private function doRequest($method, $uri, $params = null, $headers = null, $ssl = false, $responseType = null){

        try{
            $resp = $this->ggApiRequest($method, $uri, $params, $headers, $ssl, $responseType);
        }catch(GGAPIUnauthorizedException $e){
            if($e->getMessage() != 'expired_token' || !$this->refresh_token){
                throw $e;
            }
            $token_data = $this->refreshToken();
            $_SESSION['token_data'] = $token_data;
            $this->setToken($_SESSION['token_data']['access_token'], $_SESSION['token_data']['refresh_token']);
            $resp = $this->ggApiRequest($method, $uri, $params, array($this->getAuthHeader()), $ssl, $responseType);
        }
        if($resp !== false)
           return $resp;

    	throw new GGAPIException($this->getRequestURL($method, $uri, $params, $ssl, $responseType).' '.$this->getLastError(), 408);
    }
    /**
    * @desc Zapytanie http do api
    *
    * @param string     $method         nazwa metody http: 'GET','POST','PUT','DELETE'
    * @param string     $uri            nazwa zasobu jako uri
    * @param mixed      $params         dodatkowe parametry zapytania
    * @param bool       $ssl            czy zapytanie jest po https
    * @param string     $responseType   w jakim formacie ma być odpowiedź z serwera
    *
    * @return mixed     tablica elementów zwróconych przez API
    */
    private function ggApiRequest($method, $uri, $params = null, $headers = null, $ssl = false, $responseType = 'json'){

    	$responseType = $responseType === null ? $this->responseType : $responseType;

    	if(!in_array($method, array('GET','POST','PUT','DELETE')))
            throw new GGAPIException('Nieprawidłowa metoda');

        $ch = curl_init();
        if(($method == 'POST' || $method == 'PUT')){
            $simpleParams = http_build_query((array)$params, null, '&');
            curl_setopt($ch,CURLOPT_POSTFIELDS, !preg_match('/=%40/', $simpleParams) ? $simpleParams : $params);
        }
        if($method != 'POST') {
            curl_setopt($ch,CURLOPT_CUSTOMREQUEST, $method);
        }

        $requestUrl = $this->getRequestURL($method, $uri, $params, $ssl, $responseType);
        curl_setopt($ch,CURLOPT_URL, $requestUrl);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array_merge((array) $headers, array(
                                                                            'Expect: ',
                                                                            'User-Agent: GGAPIPHP v'.self::VERSION.' '.php_uname('n'),
                                                                            'Accept-Charset: ISO-8859-2,utf-8;q=0.7,*;q=0.7'
                                                                         )));
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_HEADER, true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch,CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch,CURLOPT_TIMEOUT, $this->requestTimeout);
        curl_setopt($ch,CURLOPT_ENCODING, 'gzip');

        $this->lastHeaders  = array();
        $this->response     = curl_exec($ch);
        $this->lastError    = curl_error($ch);
        $this->info         = curl_getinfo($ch);
        curl_close($ch);

        if($this->response === false)
           return false;

		$this->lastHeaders = $this->setLastHeaders(substr($this->response, 0, $this->info['header_size'] - 4));
		$this->response    = substr($this->response, $this->info['header_size']);

        if($this->parseResponse === false)
            return true;

        if($this->info['http_code'] !== 200){
            try{
                $parsedResponse = $this->parseResponse($this->response, $this->info['content_type']);
            } catch(GGAPIParseException $e) {
                $parsedResponse = array('result' => array('errorMsg' => is_array($this->lastHeaders) ? array_shift($this->lastHeaders) : $this->info['http_code']));
            }
            switch($this->info['http_code']){
            	case 401:
            	   throw new GGAPIUnauthorizedException($this->getErrorMsg($parsedResponse), $this->info['http_code']);
            	case 403:
            	   throw new GGAPIForbiddenException($this->getErrorMsg($parsedResponse), $this->info['http_code']);
            	case 404:
            	   throw new GGAPINotFoundException($this->getErrorMsg($parsedResponse), $this->info['http_code']);
            	case 400:
                   throw new GGAPIBadRequestException($this->getErrorMsg($parsedResponse), $this->info['http_code']);
            	case 500:
                   throw new GGAPIInternalServerErrorException($this->getErrorMsg($parsedResponse), $this->info['http_code']);
            	default:
            	    throw new GGAPIException($this->getErrorMsg($parsedResponse), $this->info['http_code']);
            }
        }else{
           $parsedResponse = $this->parseResponse($this->response, $this->info['content_type']);
        }

        return $parsedResponse;
    }
    /**
    * @desc Informacja zwracana przez biblioteke curl
    * kody protokołu http http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
    */
    public function getLastInfo(){

        return $this->info;
    }
    /**
    * @desc Informacja zwracana przez biblioteke curl
    * kody protokołu http http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
    */
    public function getLastError(){

        return $this->lastError;
    }
    /**
     * @desc Pobierz kod odpowiedzi
     *
     * @return int
     */
    public function getResponseCode(){

        return $this->info[0];
    }
    /**
    * @desc Translacja odpowiedzi do tablicy php w zależności od jej formatu
    *
    * @param string     $response   odpowiedź serwera
    *
    * @return mixed
    */
    public function parseResponse($response, $type){

    	switch($type){
        	case 'text/xml':
        		$parsedResponse = array('result' => $this->parseXML($response));
        		break;
        	case 'application/phps':
        		$parsedResponse = @unserialize($response);
        		if($parsedResponse === false)
        			throw new GGAPIParseException();
        		break;
        	case 'application/json':
            default:
                $parsedResponse = $this->parseJSON($response);
                if($parsedResponse === false)
        			throw new GGAPIParseException();
                break;
        }

        return  $parsedResponse;
    }
    /**
     * Pobranie odpowiedzi z serwera
     *
     * @return string
     */
    public function getRawResponse(){

    	return $this->response;
    }
    /**
     * Pobranie nagłówków ostatniego zapytania
     *
     * @return array
     */
    public function getResponseHeaders(){

    	return $this->lastHeaders;
    }
    /**
     * Ustawienie typu odpowiedzi
     *
     * @param string $responseType
     * @return string
     */
    public function setResponseType($responseType){

    	return $this->responseType = $responseType;
    }
    /**
     * @desc Czy analizować odpowiedź serwera
     *
     * @return bool
     */
    public function setParseResponse($parseResponse){

    	return $this->parseResponse = (bool) $parseResponse;
    }
	/**
	 * @desc Ustawienie nagłówków
	 *
	 * @param string $header
	 * @return array
	 */
    private function setLastHeaders($header){

       return $this->lastHeaders = explode("\r\n", $header);
	}
    /**
     * @desc Pobranie informacji o błędzie
     *
     * @param array $parsedResponse
     * @return string
     */
    private function getErrorMsg($parsedResponse){
        
        return isset($parsedResponse['error']) ? $parsedResponse['error'] : @$parsedResponse['result']['errorMsg'];
    }
    /**
    * @desc Translacja xml do php
    *
    * @param string $input
    */
    protected function parseXML($input){
        $sxml = @simplexml_load_string($input);
        if($sxml === false)
            throw new GGAPIParseException('Not valid XML response');;
        $arr = array();
        if ($sxml) {
          foreach ($sxml as $k => $v) {
            if ($sxml['list']) {
              $arr[] = self::convert_simplexml_to_array($v);
            } else {
              $arr[$k] = self::convert_simplexml_to_array($v);
            }
          }
        }
        if (sizeof($arr) > 0) {
          return $arr;
        } else {
          return (string)$sxml;
        }
    }
    /**
    * @desc Translacja JSON do PHP
    *
    * @param string $input
    * @return midex
    */
    protected function parseJSON($input){

        return json_decode($input, true);
    }
    /**
     * @desc Translacja obiektu simplexml do tablicy
     *
     * @param Object $sxml
     * @return mixed
     */
    public static function convert_simplexml_to_array($sxml) {
        $arr = array();
        if ($sxml) {
          foreach ($sxml as $k => $v) {
                if($arr[$k]){
                    $arr[$k." ".(count($arr) + 1)] = self::convert_simplexml_to_array($v);
                }else{
                    $arr[$k] = self::convert_simplexml_to_array($v);
                }
            }
        }
        if (sizeof($arr) > 0) {
          return $arr;
        } else {
          return (string)$sxml;
        }
    }
}

class GGAPIException extends Exception{
}
class GGAPIParseException extends GGAPIException{
}
class GGAPIUnauthorizedException extends GGAPIException {
}
class GGAPIForbiddenException extends GGAPIException {
}
class GGAPIBadRequestException extends GGAPIException {
}
class GGAPINotFoundException extends GGAPIException {
}
class GGAPIInternalServerErrorException extends GGAPIException {
}
