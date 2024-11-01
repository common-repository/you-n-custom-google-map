<?php

/**
 * Class for front side
 * 
 * @author giacomo@you-n.com
 * 28/08/13
 */
class YM_front_side {

    public function __construct($settings, $configuration) {
        $this->YM_Settings = $settings;
        $this->YM_userConfiguration = $configuration;

        if ($this->YM_Settings['active'] === true) {
            add_action('wp_enqueue_scripts', array(&$this, 'YM_SetScriptFront'));
            add_shortcode('YM-map', array(&$this, 'YM_render_map'));
        }
    }

    /**
     * Set script for YM map
     */
    function YM_SetScriptFront() {
        global $post;
        $isYM_page = get_post_meta($post->ID, 'YMMap', true);
        if ($isYM_page == 'on') {
            //inserire js!!
            $zoom = $this->YM_Settings['zoom'];
            $visualizzaMarker = FALSE;
            if ($this->YM_userConfiguration[0]['lat'] != 0) {
                $visualizzaMarker = true;
            }
            ?>
            <script type="text/javascript">
                var zoomConfig =<?php echo $zoom ?>;
                var responsive;
                var tipoMappa = '<?php echo $this->YM_Settings['tipoMappa'] ?>';
            <?php if ($this->YM_Settings['responsive'] == TRUE) { ?>
                    responsive='responsive';
            <?php } else { ?>
                    responsive='custom'
            <?php } ?>
                var visualizzaMarker=false;
            <?php if ($visualizzaMarker) { ?>
                         visualizzaMarker=true;
                <?php $arrayMarker = $this->getMarkerObj(); ?>
                    arrayMarker = <?= json_encode($arrayMarker) ?> ;
            <?php } ?>
                
            </script>
            <?php
            wp_register_style('YMBESTYLE', YM_PLUGIN_URI . '/css/YM_front_style.css', array(), '1.0', 'screen');
            wp_enqueue_style('YMBESTYLE');

            wp_register_script('GoogleAPI', 'http://maps.google.com/maps/api/js?libraries=places&sensor=true', array(), '1.0', TRUE);
            wp_register_script('gestMappaFront', YM_PLUGIN_URI . '/js/gestMappaFront.js', array(), '1.0', TRUE);

            wp_enqueue_script('GoogleAPI');
            wp_enqueue_script('gestMappaFront');
        }
    }

    /**
     * extract my map and print
     */
    function YM_render_map($atts) {
        extract(shortcode_atts(array(
                    'stato' => 'off'
                        ), $atts));
        return $this->YM_getMyMap($atts['stato']);
    }

    /**
     * Return my map
     * 
     * @param string $stato
     */
    function YM_getMyMap($stato) {
        global $post;
        //inserire configurazioni varie
        $isYM_page = get_post_meta($post->ID, 'YMMap', true);
        $str = (string) '';
//        var_dump($this);
        $width = $this->YM_Settings['width'];
        $height = $this->YM_Settings['height'];
        $isResponsive = FALSE;
        if ($this->YM_Settings['responsive']) {
            $isResponsive = TRUE;
        }
        if ($stato === 'on' && $isYM_page === 'on' && $this->YM_Settings['active'] === TRUE) {
            ob_start();
            if ($isResponsive) {
                $str.='<div id="wrapper-YM-responsive" style="border: '.$this->YM_Settings['color'].' '.$this->YM_Settings['spessore'].'px solid">'; //open wrapper responsive
                $str.='<div id="YM-mappa" style="width:100%;height:100%;">';
            } else {
                $str.='<div id="YM-mappa" style="width:' . $width . 'px;height:' . $height . 'px; border: '.$this->YM_Settings['color'].' '.$this->YM_Settings['spessore'].'px solid">';
            }

            $str.='</div>'; //chiude ID mappa
            if ($isResponsive) {
                $str.='</div>'; //close wrapper responsive
            }
            ob_end_clean();
            return $str;
        } else {

            return $str = '';
        }
    }

    /**
     * return array marker for use in js
     * @param void
     * @return array 
     * TODO: DA COMPLETARE
     */
    function getMarkerObj() {
        $markers = array();
        foreach ($this->YM_userConfiguration as $elem => $k) {
            foreach ($k as $i => $elemento) {
                $markers[$elem]['id_marker'] = $elem;
                if (in_array($i, array('marker')))
                    $markers[$elem]['immagine'] = $elemento;
                if (in_array($i, array('lat')))
                    $markers[$elem]['latitudine'] = $elemento;
                if (in_array($i, array('lgt')))
                    $markers[$elem]['longitudine'] = $elemento;
                if (in_array($i, array('toolTip')))
                    $markers[$elem]['toolTip'] = $elemento;
            }
        }
        return $markers;
    }

}
?>
