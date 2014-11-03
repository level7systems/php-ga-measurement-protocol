<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class GA
{
  public static function event($params = array())
  {
    self::required(array('ec', 'ea'), $params);
    self::allowed(array('ec', 'ea', 'el', 'ev'), $params);
    $params['t'] = 'event';
    
    return self::request($params);
  }
 
  private static function required($required, $params)
  {
    // Tracking ID is always required
    $allowed[] = 'tid';
    foreach ($required as $r) {
      if (!array_key_exists($r, $params)) {
        throw new Exception(sprintf("Missing required parameter \"%s\".", $r));
      }
    }
  }
 
  private static function allowed($allowed, $params)
  {
    // Tracking ID is always allowed
    $allowed[] = 'tid';
    foreach ($params as $key => $p) {
      if (!in_array($key, $allowed)) {
        throw new Exception(sprintf("Invalid parameter \"%s\".", $key));
      }
    }
  }
 
  private function request($params)
  {
    // google analytics API url
    $url = "https://ssl.google-analytics.com/collect";
    // generate cid
    $cid = static::genUuid();
    
    $config = array(
      'v'   => 1,
      'cid' => $cid
    );
    
    $config += $params;
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER    => 1,
      CURLOPT_URL               => $url,
      CURLOPT_SSL_VERIFYHOST    => 0,
      CURLOPT_SSL_VERIFYPEER    => 0,
      CURLOPT_POST              => count($config),
      CURLOPT_POSTFIELDS        => http_build_query($config)
    ));
    $response = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);
    
    if ($http_status != 200) {
        throw new Exception('Google Analytics communication error.');
    }
    
    return $response;
  }
 
  // UUID Version 4
  private static function genUuid()
  {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
      mt_rand( 0, 0xffff ),
      mt_rand( 0, 0x0fff ) | 0x4000,
      mt_rand( 0, 0x3fff ) | 0x8000,
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }
}
