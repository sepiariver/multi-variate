<?php
/**
 * mvStickyRandom
 * @author @sepiariver
 * @copyright YJ Tso
 * @package MultiVariate
 * @license GPL, no warranties, use at own risk. See included license file.
 * 
 * @description Returns randomized output, and optionally persists via cookie. Used for MV testing.
 * 
 * Example usage:
 * Randomly cycle between 4 options and persist
 * [[!stickyRandom?
 *      &cookieName=`dimensionName`
 *      &option0=`@INLINE <script>window.onload = function() { ga('set', 'dimensionName', 'value0'); };</script>`
 *      &option1=`@INLINE <script>window.onload = function() { ga('set', 'dimensionName', 'value1'); };</script>`
 *      &option2=`@INLINE <script>window.onload = function() { ga('set', 'dimensionName', 'value2'); };</script>`
 *      &option3=`@INLINE <script>window.onload = function() { ga('set', 'dimensionName', 'value3'); };</script>`
 *      &power=`3`
 *  ]]
 * Randomly cycle between 2 options
 * [[!stickyRandom?
 *      &option0=`zero`
 *      &option1=`one`
 * ]]
 * 
 * Note: the &power property cannot be an even number, in order to evenly distribute randomization. 
 * If an even number is provided it will be decremented. If less than (&power + 1) optons are provided,
 * the default output will be rendered for randomization instances where &option{$result} is not set.
 */
 
// PATHS
$mvPath = $modx->getOption('multivariate.core_path', null, $modx->getOption('core_path') . 'components/multivariate/');
$mvModelPath = $mvPath . 'model/';

// Get Class
if (file_exists($mvModelPath . 'multivariate.class.php')) $mv = $modx->getService('multivariate', 'MultiVariate', $mvModelPath, $scriptProperties);
if (!($mv instanceof MultiVariate)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[mvStickyRandom] could not load the required class!');
    return;
}

// OPTIONS
$power = $modx->getOption('power', $scriptProperties, 1);
if ($power % 2 === 0) $power--;
$cookieName = $modx->getOption('cookieName', $scriptProperties, '');
$default = $modx->getOption('default', $scriptProperties, '');
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, '');

// Cookie Handling
$cookieVal = (!empty($cookieName) && isset($_COOKIE[$cookieName])) ? (int) $_COOKIE[$cookieName] : null;
$result = ($cookieVal === null) ? mt_rand()&$power : $cookieVal;
if (!empty($cookieName) && ($cookieVal === null)) {
    $expires = $modx->getOption('expires', $scriptProperties, '+14days');
    $expires = (is_numeric($expires)) ? intval($expires) + time() : strtotime($expires);
    $path = $modx->getOption('path', $scriptProperties, '/');
    $domain = $modx->getOption('domain', $scriptProperties, $modx->getOption('http_host', null, MODX_HTTP_HOST));
    $secure = $modx->getOption('secure', $scriptProperties, false);
    $httponly = $modx->getOption('httponly', $scriptProperties, false);
        
    // Don't set cookie if $secure but request is not over https
    if ($secure && empty($_SERVER['HTTPS'])) return;
        
    // Set the cookie
    setcookie($cookieName, $result, $expires, $path, $domain, $secure, $httponly);
}

// Set output options
$options = array();
foreach ($scriptProperties as $propkey => $propval) {
    if (strpos($propkey, 'option') !== 0) continue;
    $options[substr($propkey, strlen('option'))] = $mv->getChunk($propval, $scriptProperties);
}

// OUTPUT
$output = (isset($options[$result])) ? $options[$result] : $default;
if (empty($toPlaceholder)) return $output;
$modx->setPlaceholder($toPlaceholder, $output);