<?php
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 09.11.18
	 * Time: 14:05
	 */
	
	$form = $displayData['form'];
	$form_Address = $displayData['form_Address'];
	$openForm = $displayData['openForm'];
	$css_class_blk = $displayData['css_class_blk'];
	$method_id = $displayData['method_id'];
	
	?>

<div   class="<?=$css_class_blk ?> method_id<?=$method_id?> additional_settings <?=($openForm?'openForm_forse':'')?>">
	<div class="fields_form">
	
	<?= $form ?>
	</div>
    <div class="fields_form_Address">
        <?=$form_Address ?>
    </div>
    <input type="hidden" name="method_id" value="<?=$method_id?>">
</div>
