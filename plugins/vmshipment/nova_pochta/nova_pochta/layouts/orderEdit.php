<?php
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 11.11.18
	 * Time: 17:27
	 */
	
	
	$InternetDocument_data = $displayData[ 'InternetDocument_data' ];
	$Recipient_data        = $displayData[ 'Recipient_data' ];
	$Recipient_city        = $displayData[ 'Recipient_city' ];
	$Order_params          = $displayData[ 'Order_params' ];
	
	
	$Recipient_BackwardDelivery = $displayData[ 'Recipient_BackwardDelivery' ];
	$Recipient_ServiceType      = $displayData[ 'Recipient_ServiceType' ];
	$method_id                  = $displayData[ 'method_id' ];
	$virtuemart_order_id        = $displayData[ 'virtuemart_order_id' ];
	$id       = $displayData[ 'id' ];
	
	$app = \JFactory::getApplication();


//	onsubmit="return false"
	
	JHtml::_( 'behavior.formvalidator' );
	
	
	// JHtml::_('script', 'jui/jquery.min.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));
?>
    <form id="NovaPoshta_edit" class="form-validate" onsubmit="NP_MethodOrderEdit.On.intDocNumber_save(event);">
        <div id="tablePlg">

            <div id="formBlk" class="formBlk">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="InternetDocument blk-line">
							<?= $InternetDocument_data ?>
                        </div>
                    </div>
                </div>

                <div class="row-fluid">
                    <div class="span12">
                        <div id="Recipient_City" class="blk-line">
                            <div class="span6">
                                <div id="Recipient_data" class="blk">
									<?= $Recipient_data ?>
                                </div>
                            </div>
                            <div class="span6">
                                <div id="Recipient_city" class="blk">
									<?= $Recipient_city ?>
                                </div>
                            </div>
                        </div><!-- #Recipient_City -->
                    </div>
                </div>

                <div class="row-fluid">
                    <div class="span12">

                        <div class="span6">
                            <div id="Order_params" class="blk-line">
								<?= $Order_params ?>
                            </div><!-- #Order_params -->
                        </div>

                        <div class="span6">
                            <div id="RecipientServiceType_data" class="blk">
								<?= $Recipient_ServiceType ?>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row-fluid">
                    <div class="span12">
                        <div id="BackwardDelivery_Adress" class="blk-line">

                            <div class="span6">
                                <div id="RecipientBackwardDelivery_data" class="blk">
									<?= $Recipient_BackwardDelivery ?>
                                </div>
                            </div>


                        </div><!--#BackwardDelivery_Adress -->

                    </div>
                </div>
            </div>
        </div>


        <div id="formNp_footer" class="formBlk">
            <div class="bootons">

                <div class="btn-wrapper" id="toolbar-list">
                    <button type="button" onclick="NP_MethodOrderEdit.On.save(event);"
                            class="btn btn-small button-list">
                        <span class="icon-checkmark" aria-hidden="true"></span>
                        Сохранить
                    </button>
                </div>


                <div class="btn-wrapper" id="toolbar-list">
                    <button type="submit" onclick=""
                            class="btn btn-small button-list">
                        <span class="icon-checkmark" aria-hidden="true"></span>
                        Создать ЕН
                    </button>
                </div>

                <div class="btn-wrapper" id="toolbar-list">
                    <button type="button" onclick="NP_MethodOrderEdit.printDocument()"
                            class="btn btn-small button-list">
                        <span class="icon-checkmark" aria-hidden="true"></span>
                        Печать ЕН
                    </button>
                </div>

                <input type="hidden" name="nova_pochta[id]" value="<?= $id ?>">
                <input type="hidden" name="model" value="">
                <input type="hidden" name="task" value="saveAdminForm">
                <input type="hidden" name="method_id" value="<?= $method_id ?>">
                <input type="hidden" name="virtuemart_order_id" value="<?= $virtuemart_order_id ?>">
				<?php echo JHtml::_( 'form.token' ); ?>


            </div>
        </div>
    </form>

<?php

 