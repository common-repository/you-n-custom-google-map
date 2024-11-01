<?php
/**
 * @author giacomo@you-n.com
 * 
 * 22/07/13
 */
?>
<div id="icon-options-general" class="icon32"></div>
<h2><?php _e('You Map') ?></h2> 
<br />
<br />
<div id="YM-descrizione-plugin">
    <?php _e('<h2>The configuration page is composed by three parts:</h2>', 'YM-language') ?>
    <?php _e('<p>The first part is for basic configuration of the map: width, height, zoom level and the possibility to choose between active or inactive state of the plugin.</p>', 'YM-language') ?>
    <?php _e('<p>The second part consists of an upload form of the images which will be used as markers; you can upload markers one at a time. You can also edit the position of an existing marker by dragging and dropping it.</p>', 'YM-language') ?>
    <?php _e('<p>The third part includes a list of all map markers and allows to change the tooltips position and text of a selected marker.</p>', 'YM-language') ?>
    <br />
    <br />
    <?php _e('<h2>There are two ways to show a map in a page:</h2>', 'YM-language') ?>
    <?php _e('<p>By using the shortcode [YM-Map stato="on"] into the Wordpress text editor</p>', 'YM-language') ?>
    <?php _e('<p>Or, if you are a theme developer, you can include the PHP code: echo do_shortcode(\'[YM-Map stato="on"]\');</p>', 'YM-language') ?>
    <br />
    <br />
    <div class="menu-interno-ys">
        <ul class="YS-indice-config">
            <li><a href="#" id="vai-configurazione"><?php _e('General configuration', 'YM-language'); ?></a></li>
            <li><a href="#" id="vai-seleziona-foto"><?php _e('Map configuration', 'YM-language'); ?></a></li>
            <li><a href="#" id="vai-ordine-foto"><?php _e('Management of marker', 'YM-language'); ?></a></li>
        </ul>
    </div>
    <a class="you-logo" href="http://www.you-n.com" title="Visita il sito dell'autore" target="_blank"><img src="<?= YM_PLUGIN_URI ?>/images/you-n-logo.png" alt="You-n, agenzia di comunicazione integrata" title="Visita il sito web dell'autore" /></a>
    <div class="clear"></div>
    <?php
    $manuale =(string) '';
    if(WPLANG=='it_IT'){
        $manuale= 'Manuale Mappa You-n 1.0 - Plugin Wordpress';
    }else{
        $manuale= 'Map You-n 1.0 manual - Wordpress plugin';
    }
    ?>
    
    <a class="you-docs" href="<?php echo YM_PLUGIN_URI.'/'.$manuale ?>.pdf" target="_blank"><?php echo _e('Read the manual', 'YM-language') ?></a> 
    <p><?php _e('Please report all bugs to', 'YM-language') ?>: <a href="mailto:info@you-n.com">info@you-n.com</a>, <?php _e('thanks', 'YM-language') ?></p>
</div>
<div class="clear"></div>
<br />
<br />
<div class="YM-fase-1 goto-configurazione">
    <h2><?php _e('General configuration', 'YM-language') ?></h2>
    <form method="post" id="YM-form-config" action="admin-post.php" name="YMUpdateSettings">
        <?php settings_fields(YM_SETTINGS); ?>  
        <?php
        $checked = 'Not active';
        $classe = 'colore-classe-non-attivo';
        if ($this->YM_Settings['active'] === true) {
            $checked = 'Active';
            $classe = 'colore-classe-attivo';
        }
        ?>
        <span><?php _e('Plugin state', 'YM-language') ?> </span><span class="<?= $classe ?>"><?= $checked ?></span>
        <div class="clear"></div>
        <select id="attivo" name="<?php echo YM_SETTINGS ?>[active]">
            <option value="null"><?php _e('Select', 'YM-language') ?></option>
            <option id="YM-opt-attivo" value="true"><?php _e('Activate', 'YM-language') ?></option>
            <option id="YM-opt-disattivo" value="false"><?php _e('Deactivate', 'YM-language') ?></option>
        </select>
        <div class="YM-helper-dialog"><p><?php _e('Here you can choose whether to enable or disable the plugin from ALL pages in which it was included. <br /> If you want to disable a specific page of your theme clears the shortcode, passes the parameter "off" to the shortcode or uncheck the options', 'YM-language'); ?></p></div>
        <div class="clear"></div>
        <label for="responsive"><?php _e('Size of the map', 'YM-language') ?></label>
        <div class="clear"></div>
        <select id="YM-opt-responsive-select" name="<?php echo YM_SETTINGS ?>[responsive]">
            <option id="YM-opt-responsive" value="responsive"><?php _e('Responsive', 'YM-language') ?></option>
            <option id="YM-opt-custom" value="custom"><?php _e('Custom size', 'YM-language') ?></option>
        </select>
        <div class="YM-helper-dialog"><p><?php _e('Choose the size of your map: "Responsive" manipulate the map based on the size of the window (recommended choice, especially for viewing on phones and tablets) <br /> If you set "Custom Size" you will need to specify the width and height of the map', 'YM-language') ?></p></div>
        <div class="clear"></div>
        <div id="wrapper-is-responsive">
            <div>
                <label for="width"><?php _e('Width', 'YM-language') ?>:</label>
                <div class="clear"></div>
                <input id="width" type="text" name="<?php echo YM_SETTINGS ?>[width]" value="<?= $this->YM_Settings['width'] ?>" />
                <div class="YM-helper-dialog"><p><?php _e('Here you can set the width of the map.', 'YM-language') ?></p></div>
            </div>
            <div>
                <div class="clear"></div>
                <label for="height"><?php _e('Height', 'YM-language') ?>:</label>
                <div class="clear"></div>
                <input id="height" type="text" name="<?php echo YM_SETTINGS ?>[height]" value="<?= $this->YM_Settings['height'] ?>" />
                <div class="YM-helper-dialog"><p><?php _e('Here you can set the height of the map.', 'YM-language') ?></p></div>
            </div>
        </div>
        <div class="clear"></div>
        <label for="zoom">Zoom</label>
        <div class="clear"></div>
        <select id="YM-zoom" name="<?php echo YM_SETTINGS ?>[zoom]">
            <option id="YM-opt-zoom_1">1</option>
            <option id="YM-opt-zoom_2">2</option>
            <option id="YM-opt-zoom_3">3</option>
            <option id="YM-opt-zoom_4">4</option>
            <option id="YM-opt-zoom_5">5</option>
            <option id="YM-opt-zoom_6">6</option>
            <option id="YM-opt-zoom_7">7</option>
            <option id="YM-opt-zoom_8">8</option>
            <option id="YM-opt-zoom_9">9</option>
            <option id="YM-opt-zoom_10">10</option>
            <option id="YM-opt-zoom_11">11</option>
            <option id="YM-opt-zoom_12">12</option>
            <option id="YM-opt-zoom_13">13</option>
            <option id="YM-opt-zoom_14">14</option>
            <option id="YM-opt-zoom_15">15</option>
            <option id="YM-opt-zoom_16">16</option>
            <option id="YM-opt-zoom_17">17</option>
            <option id="YM-opt-zoom_18">18</option>
            <option id="YM-opt-zoom_19">19</option>
            <option id="YM-opt-zoom_20">20</option>
            <option id="YM-opt-zoom_21">21</option>
        </select>
        <div class="clear"></div>
        <label for="tipoMappa"><?php _e('Map types', 'YM-language') ?></label>
        <div class="clear"></div>
        <select id="YM-tipoMappa" name="<?php echo YM_SETTINGS ?>[tipoMappa]">
            <option id="YM-opt-tipoMappa_1">ROADMAP</option>
            <option id="YM-opt-tipoMappa_2">SATELLITE</option>
            <option id="YM-opt-tipoMappa_3">HYBRID</option>
            <option id="YM-opt-tipoMappa_4">TERRAIN</option>
        </select>
        <div class="clear"></div>
        <label for="coloreMappa"><?php _e('Border color of the map','YM-language'); ?></label>
        <div class="clear"></div>
         <input id="YM-color-picker" name="<?php echo YM_SETTINGS ?>[color]" value="<?= $this->YM_Settings['color'] ?>" />
        <div class="clear"></div>
        <label for="spessoreBordo"><?php _e('Select the width of the border','YM-language'); ?></label>
        <div class="clear"></div>
        <div id="YM-spessore">
            <div id="YM-spessore-posizione"></div>
        </div>
        <div class="clear"></div>
        <div id="YM-wrapper-anteprima-spessore">
            <div id="YM-anteprima-spessore"><p id="YM-pixel-scelto"></p></div>
        </div>
        <div class="clear"></div>
        <div id="YM-messaggio-anteprima-spessore"></div>
        <div class="clear"></div>
        <br />
        <br />
        <br />
        <input type="hidden" name="action" value="YMUpdateSettings"/>

        <input type="submit" id="YM-submitConfig" name="YMUpdateSettings" class="YM-button" value="<?php _e('Save Changes'); ?>"/>
    </form>




</div>