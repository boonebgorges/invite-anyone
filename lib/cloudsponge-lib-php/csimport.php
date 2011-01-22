<?php

// CloudSponge.com PHP Library v0.9 Beta
// http://www.cloudsponge.com
// Copyright (c) 2010 Cloud Copy, Inc.
// Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
//
// Written by Graeme Rouse
// graeme@cloudsponge.com

require_once 'csconstants.php';

/* Import Class */
class CSImport implements iCSConstants {
  /* Constants */
  const URL_BASE = "https://api.cloudsponge.com/";
  const BEGIN_PATH = "begin_import/";
  const CONSENT_PATH = "user_consent/";
  const IMPORT_PATH = "import/";
  const APPLET_PATH = "desktop_applet/";
  const EVENTS_PATH = "events/";
  const CONTACTS_PATH = "contacts/";
  
  // guesses the most appropriate invocation for begin_import_xxx()
  // returns an array of possible objects
  //  Array(
  //    ['import_id'] => 'success' | 'failure',
  //    ['consent_url'] => NULL | $consent_url,
  //    ['applet_tag']  => NULL | $applet_tag
  //  )
  static function begin_import($source_name, $username = NULL, $password = NULL, $tracking_string = '', $redirect_url = NULL) {
    // $this->source_name = $source_name;
    // $this->redirect_url = $redirect_url;
    // $this->import_tracking_id = $tracking_string;
    $id = NULL;
    $consent_url = NULL;
    $applet_tag = NULL;
    
    // look at the given service and decide how which begin function to invoke.
    if (!empty($username)) {
      $resp = CSImport::begin_import_username_password($source_name, $username, $password, $tracking_string);
    } else {
      if (strcasecmp($source_name, "OUTLOOK") == 0 || strcasecmp($source_name, "ADDRESSBOOK") == 0) {
        $resp = CSImport::begin_import_applet($source_name, $tracking_string);
        $applet_tag = CSImport::create_applet_tag($resp['id'], $resp['url']);
      } else {
        $resp = CSImport::begin_import_consent($source_name, $tracking_string, $redirect_url);
        $consent_url = $resp['url'];
      }
    }
    $id = $resp['id'];
    
    return array('import_id' => $id, 'consent_url' => $consent_url, 'applet_tag' => $applet_tag);
  }
  
  // invokes the begin import action for the user consent process.
  // returns the URL of the consent page that the user must use to sign in and grant consent
  // throws an exception if an invalid service is invoked.
  static function begin_import_consent($source_name, $tracking_string = NULL, $redirect_url = NULL) {
    // we need to pass in all params to the call
    $params = array('service' => $source_name, 'tracking_string' => $tracking_string, 'redirect_url' => $redirect_url);
    
    // get and decode the response into an associated array
    // Throws an exception if there was a problem at the server
    $resp = CSImport::post_and_decode_response(CSImport::full_url(CSImport::CONSENT_PATH), $params);
    
    // return the response array in case anyone wants to use the data
    return $resp;
  }
  
  // invokes the begin import action for the desktop applet import process.
  // returns the URL of the applet that should be displayed to the user within the appropriate applet tag
  // throws an exception if an invalid service is invoked.
  static function begin_import_applet($source_name, $tracking_string = NULL) {
    // we need to pass in all params to the call
    $params = array('service' => $source_name, 'tracking_string' => $tracking_string);

    // get and decode the response into an associated array
    // Throws an exception if there was a problem at the server
    $resp = CSImport::post_and_decode_response(CSImport::full_url(CSImport::APPLET_PATH), $params);
    
    // return the response array in case anyone wants to use the data
    return $resp;
  }
  
  // invokes the begin import action for the desktop applet import process.
  // returns the URL of the applet that should be displayed to the user within the appropriate applet tag
  // throws an exception if an invalid service is invoked.
  static function begin_import_username_password($source_name, $username, $password, $tracking_string = NULL) {
    // we need to pass in all params to the call
    $params = array('service' => $source_name, 'tracking_string' => $tracking_string, 'username' => $username, 'password' => $password);

    // get and decode the response into an associated array
    // Throws an exception if there was a problem at the server
    $resp = CSImport::post_and_decode_response(CSImport::full_url(CSImport::IMPORT_PATH), $params);
    
    // return the response array in case anyone wants to use the data
    return $resp;
  }
  
  // returns an associative array with any new event or NULL if no new events are available
  // Array(
  //    ['state'] => INITIALIZING | GATHERING | COMPLETE | ERROR,
  //    ['data'] => (integer count of current number of contacts imported or error code),
  //    ['message'] => (optional data in the case of an error)
  // )
  static function get_events($import_id) {
    // call to CloudSponge.com for the latest event status and return it
    $events_array = NULL;
    
    // create the appropriate url to fetch the contacts
    $full_url = CSImport::generate_poll_url(CSImport::EVENTS_PATH, $import_id);
    
    // get the response from the server and decode it
    $resp = CSImport::get_and_decode_response($full_url);
    
    // interpret the result
    if (array_key_exists('events', $resp))
      $events_array = $resp['events'];
    
    // return the response object
    return $events_array;
  }
  
  // call to CloudSponge.com for the contacts,
  // returns an error (not ready or fatal error)
  // or an array of CSContacts
  static function get_contacts($import_id) {
    $contacts = NULL;
    $contacts_owner = NULL;
    
    // create the appropriate url to fetch the contacts
    $full_url = CSImport::generate_poll_url(CSImport::CONTACTS_PATH, $import_id);
    
    // get the response from the server and decode it
    try {
      $resp = CSImport::get_and_decode_response($full_url);
    } catch (CSException $e) {
      if ($e->getCode() == 404)
        return NULL;
      else
        throw $e;
    }
    
    // interpret the result
    if (array_key_exists('contacts', $resp))
      $contacts = CSContact::from_array($resp['contacts']);

    if (array_key_exists('contacts_owner', $resp))
      $contacts_owner = new CSContact($resp['contacts_owner']);

    // return the response object
    return Array('contacts' => $contacts, 'contacts_owner' => $contacts_owner);
  }
  
  static function forward_auth($get_params, $post_params = null) {
    $post_params['appctx'] = stripslashes($post_params['appctx']);
    $url = CSImport::URL_BASE . 'auth?' . http_build_query($get_params);
    if (isset($post_params)) {
      $response = CSImport::post_url($url, $post_params);
    } else {
      $response = CSImport::get_url($url);
    }
    return $response['body'];
  }
  
  /* Private Utility Functions */
  static function full_url($path) {
    return CSImport::URL_BASE . CSImport::BEGIN_PATH . $path;
  }
  static function authenticated_params($params = array()) {
    // append domain_key, domain_password to params and serialze into a query string
    $params["domain_key"] = CSImport::DOMAIN_KEY;
    $params["domain_password"] = CSImport::DOMAIN_PASSWORD;
    return $params;
  }
  static function authenticated_query($params = array()) {
    return http_build_query(CSImport::authenticated_params($params));
  }
  static function create_applet_tag($id, $url) {
    return <<<EOS
<APPLET archive="$url" code="ContactsApplet" id="Contact_Importer" width="0" height="0">
  <PARAM name="cookieValue" value="document.cookie"/>
  <PARAM name="importActionID" value="$id"/>
  Your browser does not support Java which is required for this utility to operate correctly.
</APPLET>
EOS;
  }
  static function generate_poll_url($path, $import_id) {
    // get the query_string with authentication params
    $query_string = CSImport::authenticated_query();
    // assemble the full url
    $full_url = CSImport::URL_BASE . $path .  $import_id . "?" . $query_string;
    // echo $full_url;
    return $full_url;
  }
  static function post_and_decode_response($url, $params) {
    $params = CSImport::authenticated_params($params);
    
    // post the response
    $response = CSImport::post_url($url, $params);
    // decode the response into an asscoiative array
    $resp = CSImport::decode_response($response, 'json');
    
    if (array_key_exists('error', $resp) ) {
      throw new CSException($resp['error']['message'], $response['code']);
    }
    
    return $resp;
  }
  static function get_and_decode_response($full_url) {
    // get the response
    $response = CSImport::get_url($full_url);
    
    // decode the response into an asscoiative array
    $resp = CSImport::decode_response($response, 'json');
    
    if (array_key_exists('error', $resp) ) {
      throw new CSException($resp['error']['message'], $response['code']);
    }

    // set object properties for future requests
    // $this->import_id = $resp['id'];
    return $resp;
  }
  static function post_url($url, $encoded_params) {
    return CSImport::url_request($url, 'post', $encoded_params);
  }
  static function get_url($url) {
    return CSImport::url_request($url);
  }
  static function url_request($url, $method = 'get', $params = null) {
    // init the curl agent
    $agent = curl_init();
    
    // get the url requested
    if (!curl_setopt($agent, CURLOPT_URL, $url)) {
      return array('code' => 0, 'body' => curl_error($agent));
    }
    
    if ($method == 'post')
    {
      // this is a post request
      if (!curl_setopt($agent, CURLOPT_POST, 1)) {
        return array('code' => 0, 'body' => curl_error($agent));
      }
      
      if (is_a($params, 'array')) {
        $encoded_params = http_build_query($params);
      } else {
        $encoded_params = $params;
      }
      // here is the post data
      if (!curl_setopt($agent, CURLOPT_POSTFIELDS, $encoded_params)) {
        return array('code' => 0, 'body' => curl_error($agent));
      }
    }
    
    //return the transfer as a string 
    if (!curl_setopt($agent, CURLOPT_RETURNTRANSFER, 1)) {
      return array('code' => 0, 'body' => curl_error($agent));
    }
    
    // $output contains the output string 
    if (($output = curl_exec($agent)) === false) {
      return array('code' => 0, 'body' => curl_error($agent));
    }
    
    // get the http response code
    if (($code = curl_getinfo($agent, CURLINFO_HTTP_CODE)) === false) {
      return array('code' => 0, 'body' => curl_error($agent));
    }
    
    // close curl resource to free up system resources 
    curl_close($agent);
    
    return array('code' => $code, 'body' => $output);
  }
  static function decode_response($response, $format = 'json'){
    $object = null;
    try {
      $object = json_decode($response['body'], true);
    } catch (Exception $e) {
      // try to decode another way, manually (?)
    }
    
    if (!isset($object)) {
      print_r($response);
      throw new CSException($response['body'], $response['code']);
    }
    return $object;
  }
}

class CSContact {
  static public function from_array($list) {
    $contacts = array();
    
    foreach ($list as $contact_data) {
      $contact = new CSContact($contact_data);
      $contacts[] = $contact;
    }
    
    return $contacts;
  }
  
  function __construct($contact_data) {
    // get the basic data
    $this->first_name = $contact_data['first_name'];
    $this->last_name = $contact_data['last_name'];
    // get the phone numbers
    if (array_key_exists('phone', $contact_data) && !is_null($contact_data['phone'])) {
      foreach ($contact_data['phone'] as $phone) {
        if (array_key_exists('type',$phone))
          $this->add_phone($phone['number'], $phone['type']);
        else
          $this->add_phone($phone['number']);
      }
    }
    // get the email addresses
    if (array_key_exists('email', $contact_data) && !is_null($contact_data['email'])) {
      foreach ($contact_data['email'] as $email) {
        if (array_key_exists('type',$email))
          $this->add_email($email['address'], $email['type']);
        else
          $this->add_email($email['address']);
      }
    }
  }
  
  /* Properties */
  var $first_name;
  var $last_name;
  var $emails = array();
  var $phones = array();
  
  function name() {
    return $this->first_name . " " . $this->last_name;
  }
  function email() {
    return $this->emails[0]['value'];
  }
  function phone() {
    return $this->phones[0]['value'];
  }
  function add_email($value, $type = NULL) {
    $this->emails[] = array('value' => $value, 'type' => $type);
  }
  function add_phone($value, $type) {
    $this->phones[] = array('value' => $value, 'type' => $type);
  }  
}

class CSException extends Exception {}

?>
