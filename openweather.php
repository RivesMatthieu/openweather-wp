<?php 

/*
* @wordpres-plugin
* Plugin Name: Openweather - API
* Description: Récupérer les informations de la météo distrivue par Openweather
* Author: Matthieu Rives
* Version: 1.0.0
*/

define( 'OPENWEATHER_PATH', plugin_dir_path( __FILE__ ) );

require_once( OPENWEATHER_PATH . 'admin/class-openweather-admin.php');

add_action('wp_enqueue_scripts', 'openweather_style');

function openweather_style() {
    wp_enqueue_style( 'openweather-css', plugins_url('css/openweather.css', __FILE__ ));
}

class Openweather
{
    private $url;

    private $city;

    private $api_key;

    private $lang;

    private $data;

    public function __construct(  )
    {
       $this->get_key();

       $this->hook_wp();

       $this->setup_data();

       $this->get_data();

    }

    public function hook_wp()
    {
       add_shortcode('openweather', array( $this, 'display_weather') );
       add_action('wp_head', array( $this, 'cdn_fontawesome' ) );
    } 

   public function cdn_fontawesome()
   {
        $cdn = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" />';
        echo $cdn;
   }

    public function  get_key()
    {
        $this->api_key = get_option( 'openweather' );
        $this->api_key = $this->api_key['openweather_identifiant'];
        return $this->api_key;
    }

    public function display_weather( $param, $content )
    {
        $this->city = $content;
        $append  = '<div class="flex-parent-openweather">';
        $append .= '<span class="datetime-openweather">'. $this->data['date'] . ', ' . $this->data['hours'] .'</span>';
        $append .= '<span class="zone-openweather">' . $this->city .', ' . strtoupper($this->lang) .  '</span>';
        $append .= '<div class="flex-inline-openweather">';
        $append .= '<img src="http://openweathermap.org/img/wn/'. $this->data['icon'] .'.png"</span>';
        $append .= '<span class="temp-openweather">'. $this->data['temp'] .'°C</span>';
        $append .= '</div>';
        $append .= '<span class="description-openweather">Ressentie : '. $this->data['feels_like'] . '°C, ' . ucfirst($this->data['description']) .'</span>';
        $append .= '<div class="flex-inline-openweather flex-wrap-openweather">';
        $append .= '<span class="wind-openweather">'. $this->data['wind'] .'m/s</span>';
        $append .= '<span class="pressure-openweather">' . $this->data['pressure'] . 'hPa</span>';
        $append .= '<span class="humidity-openweather">Humidité :' . $this->data['humidity'] . '%</span>';
        $append .= '<span class="visibility-openweather">Visibilité :' . $this->data['visibility'] . 'Km</span>';
        $append .= '</div>';
        $append .= '</div>';
        $append .= '<style>.wind-openweather:before {transform: rotate(' . ( 314 - $this->data['deg'] ) . 'deg) !important}</style>';
        return $append;
    } 

    public function setup_data()
    {
        $this->lang = 'fr';
        $this->url = "http://api.openweathermap.org/data/2.5/weather?q={$this->city},{$this->lang}&lang={$this->lang}&appid={$this->api_key}";
        return $this->url;
    }

    public function get_data()
    {
        $curl = curl_init( $this->url );
        curl_setopt_array( $curl, [
            CURLOPT_CAINFO          => CURLOPT_CAINFO, __DIR__ . DIRECTORY_SEPARATOR . 'cert.cer',
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => 1
        ]);
        $data = curl_exec( $curl );
        if( $data === false || curl_getinfo( $curl, CURLINFO_HTTP_CODE !== 200 )) {
            return null;
        } else {
            $data = json_decode( $data, true );
            setlocale (LC_TIME, 'fr_FR.utf8','fra'); 
            $this->data = [
                'feels_like'    => ( ( intval($data['main']['feels_like']) - 273.15 ) * 100 ) / 100,
                'temp'          => ( ( intval($data['main']['temp']) - 273.15 ) * 100 ) / 100,
                'description'   => $data['weather'][0]['description'],
                'icon'          => $data['weather'][0]['icon'],
                'hours'         => date("H:i", strtotime('+2 hours')),
                'date'          => strftime("%A %d %B"),
                'humidity'      => $data['main']['humidity'], 
                'pressure'      => $data['main']['pressure'],
                'wind'          => $data['wind']['speed'],
                'deg'           => $data['wind']['deg'],
                'visibility'    => number_format( ( intval($data['visibility']) * 1 ) )  
            ];
            return $this->data;
        } 
        curl_close($curl);
    } 

}

if( is_admin() )
    $openweather_admin = new Openweather_admin();

$openweather = new Openweather();