<?php /**
 * TVG Express Service Factory
 * @link       smartmedia.is
 * @since      1.0.0
 * @package    Tvgexpress
 * @subpackage Tvgexpress/admin
 */
class Tvgexpress_Service_Factory {

        /**
        * Creates a new instance of the service.
        *
        * @param string $username Username.
        * @param string $api_key ApiKey to connect to service.
        * @param bool   $test Test service or not.
        * @param string $xgateway Xgateway to connect to service.
        * @return Tvgexpress_Service | Tvgexpress_Service_v2
        */
        public static function get( $username, $api_key, $test = false,$xgateway = null) {
            if(!empty($xgateway)){
                require_once dirname(__FILE__) . '/class-tvgexpress-service-v2.php';
                return new Tvgexpress_Service_v2($username, $api_key, $test,$xgateway);
            }else{
                require_once dirname(__FILE__) . '/class-tvgexpress-service.php';
                return new Tvgexpress_Service($username, $api_key, $test);
            }
        }

        public static function loadDefault(){
            return self::get( get_option( 'tvg_api_username' ), self::decrypt( get_option( 'tvg_api_key' ) ), get_option( 'tvg_api_demo' ),self::decrypt( get_option( 'tvg_xgateway_api_key' ) ) );
        }

    public static function decrypt( $raw_value ) {
        if ( ! extension_loaded( 'openssl' ) ) {
            return $raw_value;
        }

        $key  = self::get_default_key();
        $salt = self::get_default_salt();

        $raw_value = base64_decode( $raw_value, true );

        $method = 'aes-256-ctr';
        $ivlen  = openssl_cipher_iv_length( $method );
        $iv     = substr( $raw_value, 0, $ivlen );

        $raw_value = substr( $raw_value, $ivlen );

        $value = openssl_decrypt( $raw_value, $method, $key, 0, $iv );
        if ( ! $value || substr( $value, - strlen( $salt ) ) !== $salt ) {
            return false;
        }

        return substr( $value, 0, - strlen( $salt ) );
    }

    protected static function get_default_key(): string {
        return 'das-ist-kein-geheimer-schluessel';
    }

    protected static function get_default_salt(): string {
        return 'das-ist-kein-geheimes-salz';
    }
}