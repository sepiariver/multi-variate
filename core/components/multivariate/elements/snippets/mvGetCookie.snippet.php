<?php
/**
 * mvGetCookie
 * @author @sepiariver
 * @copyright YJ Tso
 * @package MultiVariate
 * @license GPL, no warranties, use at own risk. See included license file.
 * 
 * A custom cookie checker for use in multi-variate testing. To check the 
 * named cookie on every request, this Snippet must be called uncacheable:
 * [[!mvGetCookie]]
 * 
 * PROPERTIES:
 * - &name        (string)  Required. Name of cookie to get/set.
 * - &setValue    (string)  Optional. String to output if cookie is set.
 *                          If empty, the cookie value will be used. Default:
 * - &setTpl      (string)  Optional. Name of Chunk to template the output of
 *                          &setValue, using placeholder [[+value]]. If empty, 
 *                          only &setValue will be output. Default: 
 * - &notSetValue (string)  Optional. String to output if cookie is not set.
 *                          If empty, empty string is output. Default: 
 * - &notSetTpl   (string)  Optional. Name of Chunk to template the output of
 *                          &notSetValue, using placeholder [[+value]]. If
 *                          empty, only &notSetValue will be output. Default:
 * - &toPlaceholder(string) Optional. Name of placeholder to which to send the
 *                          output, instead of returning it. Default:
 * 
 * EXAMPLE USAGE:
 * 
 * [[!mvGetCookie? &name=`variation`]]
 * If $_COOKIE['variation'] is set, the value will be returned. Otherwise 
 * nothing will be output. Cookie values are run through htmlspecialchars().
 * 
 * [[!mvGetCookie? 
 *    &name=`v` 
 *    &setValue=`hasCookie` 
 *    &notSetValue=`cookieless`
 *    &toPlaceholder=`property-set`
 * ]]
 * If $_COOKIE['v'] is set, "hasCookie" will be output, otherwise "cookieless" 
 * will be output. Output goes to [[+property-set]]. This is the biggest 
 * departure from other cookie handlers. Use with other Snippets to control 
 * behaviour:
 * [[!Redirectoid@[[+property-set]]?]]
 *  
 * [[!mvGetCookie? 
 *    &name=`v` 
 *    &notSetValue=`no cookie here` 
 *    &setTpl=`cookieTpl`
 *    &notSetTpl=`cookieTpl`
 *    &toPlaceholder=`cookie-result`
 * ]]
 * If $_COOKIE['v'] is set, the value will be sent to [[+value]] inside the 
 * Chunk "cookieTpl". If not set, the string "no cookie here" will be sent
 * to the placeholder in the same Chunk. The output will be sent to 
 * [[+cookie-result]] and nothing will be output.
 * 
 */
 
// PATHS
$mvPath = $modx->getOption('multivariate.core_path', null, $modx->getOption('core_path') . 'components/multivariate/');
$mvModelPath = $mvPath . 'model/';

// Get Class
if (file_exists($mvModelPath . 'multivariate.class.php')) $mv = $modx->getService('multivariate', 'MultiVariate', $mvModelPath, $scriptProperties);
if (!($mv instanceof MultiVariate)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[mvGetCookie] could not load the required class!');
    return;
}
 
// REQUIRED
$name = $modx->getOption('name', $scriptProperties, '');
if (empty($name)) return;
// OPTIONS
$setValue = $modx->getOption('setValue', $scriptProperties, '');
$setTpl = $modx->getOption('setTpl', $scriptProperties, '');
$notSetValue = $modx->getOption('notSetValue', $scriptProperties, '');
$notSetTpl = $modx->getOption('notSetTpl', $scriptProperties, '');
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, '');
 
// Default output
$value = $notSetValue;
$tpl = $notSetTpl;

// Check cookie
if (isset($_COOKIE[$name])) {
    $value = (!empty($setValue)) ? $setValue : htmlspecialchars($_COOKIE[$name]);
    $tpl = $setTpl;
}

// Output
$output = (empty($tpl)) ? $value : $mv->getChunk($tpl, array('value' => $value));
if (empty($toPlaceholder)) return $output;
$modx->setPlaceholder($toPlaceholder, $output);