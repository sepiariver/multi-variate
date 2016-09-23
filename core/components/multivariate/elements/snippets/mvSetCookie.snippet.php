<?php
/**
 * mvSetCookie
 * @author @sepiariver
 * @copyright YJ Tso
 * @package MultiVariate
 * @license GPL, no warranties, use at own risk. See included license file.
 * 
 * Sets a cookie using PHP's setcookie()
 * 
 * PROPERTIES:
 * - &name      (string)  Required. Name of cookie to set. Default:
 * - &value     (string)  Optional. String to set as cookie value. Default:
 * - &expires   (mixed)   Optional.
 * - &path      (string)
 * - &domain    (string)
 * - &secure    (bool)
 * - &httponly  (bool)
 * - &debug     (bool)
 */
 
// REQUIRED
$name = $modx->getOption('name', $scriptProperties, '');
if (empty($name)) return;

// OPTIONS
$value = $modx->getOption('value', $scriptProperties, '');
$expires = $modx->getOption('expires', $scriptProperties, '');
$path = $modx->getOption('path', $scriptProperties, '/');
$domain = $modx->getOption('domain', $scriptProperties, $modx->getOption('http_host', null, MODX_HTTP_HOST));
$secure = $modx->getOption('secure', $scriptProperties, false);
$httponly = $modx->getOption('httponly', $scriptProperties, false);
$debug = $modx->getOption('debug', $scriptProperties, false);
$logFails = $modx->getOption('logFails', $scriptProperties, false);

// Format $expires
$expires = (is_numeric($expires)) ? intval($expires) + time() : strtotime($expires);

// Debug info
$debuginfo = array(
    'name' => $name,
    'value' => $value,
    'expires' => $expires,
    'path' => $path,
    'domain' => $domain,
    'secure' => $secure,
    'httponly' => $httponly,
);

// Escape with debug info
if ($debug) return '<pre>' . print_r($debuginfo, true) . '</pre>';

// Don't set cookie if $secure but request is not over https
if ($secure && empty($_SERVER['HTTPS'])) return;

// Set the cookie
$success = setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);

// Log failures
if ($logFails && !$success) {
  $modx->log(modX::LOG_LEVEL_ERROR, 'mvSetCookie failed at URI: ' . $_SERVER['REQUEST_URI'] . ' Debug info: ' . print_r($debuginfo, true));
}

// This Snippet returns nothing
return;