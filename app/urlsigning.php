<?php
namespace App;
/**
 * Url Signing Helper Class
 *
 * @author Paul Ferrett <paul.ferrett@servicecentral.com.au>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class urlsigning {

    /**
     * Sign a URL
     *
     * @param string $url
     * @param string $private_key
     * @param string $param_name
     * @return string Signed URL
     */
    public  function getSignedUrl($url, $private_key, $param_name = 'signature') {

        $join = parse_url($url, PHP_URL_QUERY) ? '&' : '?';

        return $url . $join . $param_name ;
    }


   /* public  function getSignedUrl2($url, $private_key, $param_name = 'signature') {

        $join = parse_url($url, PHP_URL_QUERY) ? '&' : '?';

        return $url . $join . $param_name . '=' . self::getUrlSignature($url, $private_key);
    }*/

    /**
     * Get the signature for the given URL
     *
     * @param string $url
     * @param string $private_key
     * @return string URL signature string
     */
    public  function getUrlSignature($url, $private_key) {

        return md5($url . ':' . $private_key);
    }

    /**
     * Check that the given URL is correctly signed
     *
     * @param string $url
     * @param string $private_key
     * @param string $param_name
     * @return bool True if URL contains valid signature, false otherwise
     */
    public  function verifySignedUrl($url, $private_key, $param_name = 'signature') {
        $param_name = preg_quote($param_name);
        if(!preg_match($regex = "/(:?&|\?)?{$param_name}=([0-9a-f]{32})/", $url, $matches)) {
            return false;
        }

        // Get the signature param
        $passed_sig = $matches[1];

        // Strip signature from the given URL
        $url = preg_replace($regex, '', $url);

        // Check that the given signature matches the correct one
        return self::getUrlSignature($url, $private_key) === $passed_sig;
    }
}
