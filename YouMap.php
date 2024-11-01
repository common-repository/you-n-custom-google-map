<?php

/**
 * @package YouMap
 * @version 1.1
 * @author giacomo@you-n.com
 */
/*
  Plugin Name: Map You-n v 1.1
  Plugin URI:
  Description: Create and configure your custom Google Map
  Author: giacomo@you-n.com
  Version: 1.1
  Author URI: http://www.you-n.com
 */

defined('YM_PLUGIN_URL') or define('YM_PLUGIN_URL', dirname(__FILE__));

defined('YM_PLUGIN_URI') or define('YM_PLUGIN_URI', plugins_url('', __FILE__));

defined('YM_SETTINGS') or define('YM_SETTINGS', 'ym_settings');
defined('YM_USER_MAP') or define('YM_USER_MAP', 'ym_user_map');

class YouMap {

    var $YM_Settings = array();
    var $YM_userConfiguration = array();

    /**
     * Class constructor 
     */
    public function __construct() {

        $currentDefaultSettings = (array) get_option(YM_SETTINGS);
        $defaultSettings = $this->YM_getBaseSettings();
        $validSettings = $this->YM_validateSettingsInDatabase($currentDefaultSettings);
        //var_dump($validSettings);
        if (!$validSettings) {
            $this->YM_Settings = $defaultSettings;
            update_option(YM_SETTINGS, $defaultSettings);
        } else {
            $this->YM_Settings = wp_parse_args($currentDefaultSettings, $currentDefaultSettings);
        }
        $userSettings = (array) get_option(YM_USER_MAP);
        $dafaultUserSettings = $this->YM_getBaseUserSettings();
        $validUserSettings = $this->YM_validateUserSettingsInDatabase($userSettings);
        if (!$validUserSettings) {
            $this->YM_userConfiguration = $dafaultUserSettings;
            update_option(YM_USER_MAP, $dafaultUserSettings);
        } else {
            $this->YM_userConfiguration =  $userSettings;
        }

        load_plugin_textdomain('YM-language', false, '/YouMap');

        if (is_admin()) {
            //BECKEND RENDER
            include 'core/YM_admin_side.php';
            new YM_admin_side($this->YM_Settings, $this->YM_userConfiguration);
        } else {
            //FRONTEND RENDER
            include 'core/YM_front_side.php';
            new YM_front_side($this->YM_Settings,  $this->YM_userConfiguration);
        }
    }

    /**
     * return default settings for Map
     * 
     * @return Array
     */
    function YM_getBaseSettings() {
        return array(
            'version' => '0.2',
            'active' => true,
            'width' => '625',
            'height' => '480',
            'responsive' => FALSE,
            'zoom' => '6',
            'tipoMappa'=>' ROADMAP',
            'color'=>'',
            'spessore'=>1
        );
    }

    /**
     * Return default settings for custom Map
     * @return Array
     */
    function YM_getBaseUserSettings() {
        return array(
            array(
                'version' => '0.2',
                'isUsed'=>false,
                'lat' => '0',
                'lgt' => '0',
                'marker' => '',
                'toolTip' => ''
            )
        );
    }

    /**
     * check if $settings is stored in DB
     * @param array $settings
     * @return Bool
     */
    function YM_validateSettingsInDatabase($settings) {
        if ($settings) {
            if (!array_key_exists('version', $settings)) {
                return false;
            }
        }
        return true;
    }
    
    function YM_validateUserSettingsInDatabase($settings){
        if($settings[0]['lat']!='0'){
            return true;
        }else{
            return FALSE;
        }
    }

}

new YouMap();
?>
