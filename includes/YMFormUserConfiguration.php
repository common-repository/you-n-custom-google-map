<?php
/**
 * @author giacomo@you-n.com
 * 22/07/13
 */
?>
<div class="YM-fase-2 goto-seleziona-foto">
    <h2><?php _e('Configure the Map', 'YM-language') ?></h2>
    <form method="post" action="admin-post.php" name="YMUpdateUserSettings" >
        <?php settings_fields(YM_USER_MAP); ?>
        <?php if ($this->YM_userConfiguration[0]['lat'] != 0) { ?>
            <div id="YM-wrapper-same-marker">
                <div class="clear"></div>
                <label class="form"><?php
        _e('Currently you have entered ', 'YM-language');
        echo sizeof($this->YM_userConfiguration)
        ?> Marker</label>
                <div class="clear"></div>
                <label class="form"><?php _e('Do you want to use the same image?', 'YM-language') ?></label>
                <div class="clear"></div>
                <select id="YM-same-marker" name="<?php echo YM_USER_MAP ?>[stessoMarker]">
                    <option id="YM-opt-marker-y" value="yes"><?php _e('Yes', 'YM-language') ?></option>
                    <option id="YM-opt-marker-n" value="no"><?php _e('No', 'YM-language') ?></option>
                </select>
                <div class="YM-helper-dialog"><p><?php _e('If the upload fails try setting the permissions to 775 to the folder markers inside the folder of the plugin', 'YM-language') ?></p></div>
                <div class="clear"></div>
                <div id="YM-riuso-marker">
    <?= $this->YM_getExistMarker(60, 60) ?>
                </div>
            </div>
<?php } ?>

        <div class="clear"></div>
        <div id="YM-wrapper-file-upload">
            <label class="form"><?php _e('Upload marker', 'YM-language') ?> <span class="formPiccolo">(.png)</span></label>
            <br />
            <div id="file-upload" style="float:left;margin-right: 10px;margin-bottom: 10px; background-color: #ccc">
                <div class="qq-uploader">
                    <div class="qq-upload-drop-area" style="display: none; "> <span></span> </div>
                    <div class="qq-upload-button" style="position: relative; overflow: hidden; direction: ltr;">
                        upload sostituire con BG
                        <input type="file" class="form" name="file" style="position: absolute; right: 0px; top: 0px; font-family: Arial; font-size: 12px; margin: 0px; padding: 0px; cursor: pointer; opacity:0;" />
                    </div>
                    <ul class="qq-upload-list">
                    </ul>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <input type="hidden" id="fileup" name="fileup" value="" />
        <div id="risposta"></div>
        <div class="clear"></div>
        <div id="result2"></div>
        <div class="clear"></div>
        <input type="hidden" name="<?php echo YM_USER_MAP ?>[path]" id="srcFoto" value="" />
        <input type="hidden" name="<?php echo YM_USER_MAP ?>[nome]" id="NomeFoto" value="" />
        <div class="clear"></div>
        <label for=""><?php _e('Insert here the address', 'YM-language') ?></label>
        <div class="clear"></div>
        <input type="text" id="indirizzo" name="" />
        <div class="YM-helper-dialog"><p><?php _e('Location not accurate? drag the marker on the Map and save change to set new position!', 'YM-language') ?></p></div>
        <div class="clear"></div>
        <label for="citta"><?php _e('Insert the city', 'YM-language') ?></label>
        <div class="clear"></div>
        <input id="citta" name="" type="text" />
        <div class="clear"></div>
        <label for="nazione"><?php _e('Insert the nation', 'YM-language') ?></label>
        <div class="clear"></div>
        <input id="nazione" type="text" />
        <div class="clear"></div>
        <label for="tooltip"><?php _e('If you want to add a tooltip enter it below', 'YM-language') ?></label>
        <div class="clear"></div>
        <textarea id="tooltip" name="<?php echo YM_USER_MAP ?>[tooltip]"></textarea>
        <div class="YM-helper-dialog"><p><?php _e('Insert here the text you want displayed when you click on a tooltip. You can insert HTML tags', 'YM-language') ?></p></div>
        <div class="clear"></div>
        <input id="getCoords" class="YM-button" type="submit" value="<?php _e('Map preview', 'YM-language') ?>" />
        <div class="clear"></div>
        <input type="hidden" name="<?php echo YM_USER_MAP ?>[lat]" id="lat" />
        <input type="hidden" name="<?php echo YM_USER_MAP ?>[lgt]" id="lgt" />
        <input type="hidden" name="action" value="YMUpdateUserSettings"/>
        <div class="clear"></div>

        <div id="anteprima-mappa" style="width: <?= $this->YM_Settings['width'] ?>px;height: <?= $this->YM_Settings['height'] ?>px">
            <div id="map" style="height:<?= $this->YM_Settings['height'] ?>px" ></div>
            <div id="info"></div>
            <div id="markerStatus"></div>

        </div>
        <div class="clear"></div>
        <br />
        <br />
        <input class="YM-button" type="submit" value="<?php _e('Save Changes', 'YM-language') ?>" id="YM-salvaMappaUtente" name="YMUpdateUserSettings"  />
    </form> 

</div>
<div class="YM-fase-2 goto-ordine-foto">
    <h2><?php _e('Your Markers', 'YM-language') ?></h2>
    <?php
    //var_dump($this->YM_userConfiguration);
    //echo $this->YM_userConfiguration[0];
    if (isset($this->YM_userConfiguration[0])) {
        if ($this->YM_userConfiguration[0]['lat'] != 0) {
            ?>
            <div class="img-gallery-content left" id="YM-paginatore-thumb" style="width: <?= $this->YM_Settings['width'] ?>px;">
        <?= $this->YM_getThumbnail(60, 60) ?>
            </div>
            <div class="clear"></div>
        <?php } else {
            ?>
            <div class="no-elements"><?php _e('No Marker found!', 'YM-language') ?></div>
            <?php
        }
    } else {
        ?>
        <div class="no-elements"><?php _e('No Marker Found', 'YM-language') ?></div>
<?php } ?>
</div>