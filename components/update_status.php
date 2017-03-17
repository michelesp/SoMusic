<?php
class SOMUSIC_CMP_UpdateStatus extends NEWSFEED_CMP_UpdateStatus {
	public function __construct($feedAutoId, $feedType, $feedId, $actionVisibility = null) {
		parent::__construct ( $feedAutoId, $feedType, $feedId, $actionVisibility = null );
	}
	public function createForm($feedAutoId, $feedType, $feedId, $actionVisibility) {
		$form = parent::createForm ( $feedAutoId, $feedType, $feedId, $actionVisibility );
		
		$vmButton = new Button ( 'vm_open_dialog' );
		$vmButton->setValue ( "SCORE" );
		$form->addElement ( $vmButton );
		$scoreTitle = new HiddenField ( ("scoreTitle") );
		$vmHidden = new HiddenField ( "vmHidden" );
		$form->addElement ( $vmHidden );
		$form->addElement ( $scoreTitle );
		$script = "            
            $('#{$vmButton->getId()}').click(function(e){
                if(typeof previewFloatBox != 'undefined' || document.getElementsByName('floatbox_canvas').length > 0) {
                    $('.floatbox_canvas').each(function(i, obj) {
                        obj.style.display = 'block';
                    });
                    if(document.getElementById('floatbox_overlay') != null)
                        document.getElementById('floatbox_overlay').style.display = 'block';
                    previewFloatBox.close();
                    //delete previewFloatBox;
                }
                previewFloatBox = OW.ajaxFloatBox('SOMUSIC_CMP_Preview', {component:'map-controllet'} , {top:'calc(5vh)', width:'calc(80vw)', height:'calc(85vh)', iconClass: 'ow_ic_add', title: ''});
                document.getElementById('vm_placeholder').style.display = 'none';
            });
        ";
		OW::getDocument ()->addOnloadScript ( $script );
		
		// $form->setAction ( OW::getRequest ()->buildUrlQueryString ( OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_Ajax', 'statusUpdate' ) ) );
		$form->setAction ( "" );
		$form->setAjax ( true );
		$form->bindJsFunction(Form::BIND_SUBMIT, ' function(e) {
                var form = document.getElementById(\''.$form->getId().'\');
				var dataToSend = {
					form_name: "'.$form->getId().'",
					csrf_token: document.getElementsByName("csrf_token")[0].value,
					status: document.getElementsByName("status")[0].value,
					attachment: document.getElementsByName("attachment")[0].value,
					feedType: document.getElementsByName("feedType")[0].value,
					feedId: document.getElementsByName("feedId")[0].value,
					visibility: document.getElementsByName("visibility")[0].value,
					vmHidden: document.getElementsByName("vmHidden")[0].value,
					scoreTitle: document.getElementsByName("scoreTitle")[0].value
				};
				console.log(dataToSend);
				$.ajax({
					type: \'post\',
					url: VISUALMELODY.ajax_update_status,
					data: dataToSend,
					dataType: \'JSON\',
					success: function(data){
						location.reload();
					},
					error: function( XMLHttpRequest, textStatus, errorThrown ){
						console.log(textStatus);
						//OW.error(textStatus);
					},
					complete: function(){
					}
				});
				
				return false;
        } ');
		return $form;
	}
}