
SoMusic = {}

SoMusic.init = function () {
	document.getElementById('feed_scoreDiv').addEventListener("click", SoMusic.modScore);
	document.getElementById("removeScore").addEventListener("click", SoMusic.removeScore, false);
	document.getElementById('score_title_text').addEventListener("change", function () {
		$('input[name=scoreTitle]').val($('#score_title_text').val());
	});
	var oldClose = OW_FloatBox.prototype.close;
	OW_FloatBox.prototype.close = function() {
		/*if(typeof SoMusic.assignmentId!=="undefined")
			delete SoMusic.assignmentId;
		if(typeof SoMusic.assignment!=="undefined")
			delete SoMusic.assignment;*/
		oldClose.call(this);
	}
}

SoMusic.commentSendMessage = function(message, context) {
	var self = context;
	var dataToSend = {
			entityType: self.entityType,
			entityId: self.entityId,
			displayType: self.displayType,
			pluginKey: self.pluginKey,
			ownerId: self.ownerId,
			cid: self.uid,
			attchUid: self.attchUid,
			commentCountOnPage: self.commentCountOnPage,
			commentText: message,
			initialCount: self.initialCount,
			vmJSONData: vmData,
			decription: vmDescription,
			title: vmTitle
	};

	if( self.attachmentInfo ){
		dataToSend.attachmentInfo = JSON.stringify(self.attachmentInfo);
	}
	else if( self.oembedInfo ){
		dataToSend.oembedInfo = JSON.stringify(self.oembedInfo);
	}
	$.ajax({
		type: 'post',
		url: SoMusic.ajax_add_comment,
		data: dataToSend,
		dataType: 'JSON',
		success: function(data){
			self.repaintCommentsList(data);
			OW.trigger('base.photo_attachment_uid_update', {uid:self.attchUid, newUid:data.newAttachUid});
			self.eventParams.commentCount = data.commentCount;
			OW.trigger('base.comment_added', self.eventParams);
			self.attchUid = data.newAttachUid;

			self.$formWrapper.removeClass('ow_preloader');
			self.$commentsInputCont.show();
		},
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		},
		complete: function(){
		}
	});
	self.$textarea.val('').keyup().trigger('input.autosize');
};

SoMusic.loadScore = function (data, id, title) {
	console.log(data);
	var scoreDiv = document.getElementById(id);
	scoreDiv.parentElement.style.display = "none";
	var titleField = document.createElement("p");
	titleField.style.textAlign = "center";
	titleField.style.fontSize= "large";
	titleField.style.fontWeight = "bold";
	titleField.style.paddingTop = "20px";
	titleField.style.marginBottom = "20px";
	var nodeText = document.createTextNode(title);
	titleField.appendChild(nodeText);
	scoreDiv.parentElement.insertBefore(titleField, scoreDiv);
	var scoreCanvas =  document.createElement('canvas');
	scoreCanvas.height = 600;
	scoreCanvas.id = id + "_sc";
	var vmCanvas = document.createElement('canvas');
	vmCanvas.height = 130;
	vmCanvas.id = id + "_vmc";
	scoreDiv.appendChild(scoreCanvas);
	scoreDiv.appendChild(vmCanvas);
	var renderer = new Renderer(scoreCanvas, data.instrumentsUsed);
	renderer.updateComposition(data);
	scoreDiv.parentElement.style.display = "block";
}

SoMusic.removeScore = function () {
	$('input[name=vmHidden]').val('');
	document.getElementById("vm_placeholder").style.display = "none";
	SoMusic.floatBox.close();
	$('.floatbox_canvas').each(function(i, obj) {
		obj.style.display = 'block';
	});
	if(document.getElementById('floatbox_overlay') != null)
		document.getElementById('floatbox_overlay').style.display = 'block';
}

SoMusic.modScore = function (vmData) {
	document.getElementById("vm_placeholder").style.display = "none";
	$('.floatbox_canvas').each(function(i, obj) {
		obj.style.display = 'block';
	});
	document.getElementById('floatbox_overlay').style.display = 'block';
	$('input[name=vmHidden]').val('');
}

SoMusic.save = function(composition) {
	if(typeof SoMusic.executionId!=="undefined"){
		$.ajax({
			type: 'post',
			url: SoMusic.editExecutionURL,
			data: { "executionId": SoMusic.executionId, "composition": composition },
			dataType: 'JSON',
			success: function(data){
				console.log(data);
				delete SoMusic.assignmentId;
				if(data)
					setTimeout(function(){ location.reload(); }, 50);
			},
			error: function( XMLHttpRequest, textStatus, errorThrown ){
				OW.error(textStatus);
			},
			complete: function(){ }
		});
		return;
	}
	if(typeof SoMusic.assignmentId!=="undefined"){
		$.ajax({
			type: 'post',
			url: SoMusic.commitExecutionURL,
			data: { "assignmentId": SoMusic.assignmentId, "composition": composition },
			dataType: 'JSON',
			success: function(data){
				console.log(data);
				delete SoMusic.assignmentId;
				if(data)
					setTimeout(function(){ location.reload(); }, 50);
			},
			error: function( XMLHttpRequest, textStatus, errorThrown ){
				OW.error(textStatus);
			},
			complete: function(){ }
		});
		return;
	}
	if(typeof SoMusic.assignment!=="undefined") {
		$.ajax({
			type: 'post',
			url: SoMusic.saveAssignmentURL,
			data: { "composition": composition },
			dataType: 'JSON',
			success: function(data){
				console.log(data);
				if(data)
					setTimeout(function(){ location.reload(); }, 50);
			},
			error: function( XMLHttpRequest, textStatus, errorThrown ){
				OW.error(textStatus);
			},
			complete: function(){ }
		});
		return;
	}
	console.log("not assignment");
	if(SoMusic.idPost!=-1) {
		$.ajax({
			type: 'post',
			url: SoMusic.ajax_update_score,
			data: { idPost: SoMusic.idPost },
			dataType: 'JSON',
			success: function(data){
				console.log(data);
				if(data.status)
					setTimeout(function(){ location.reload(); }, 50);
			},
			error: function( XMLHttpRequest, textStatus, errorThrown ){
				OW.error(textStatus);
			},
			complete: function(){ }
		});
		return;
	}
	$('input[name=vmHidden]').val(JSON.stringify(composition));
	var renderer = new Renderer(document.getElementById('feed_score'), composition.instrumentsUsed);
	document.getElementById('vm_placeholder').style.display = 'block';
	renderer.updateComposition(composition);
	document.getElementById('score_title_text').value = '';
	$('input[name=scoreTitle]').val('');
	document.getElementsByTagName('BODY')[0].classList.remove('floatbox_nooverflow');
}

SoMusic.newAssignment = function(url, groupId, name, multiUserMod) {
	SoMusic.assignment = { "groupId": groupId, "name": name, "isMultiUser": multiUserMod };
	$.ajax({
		type: 'post',
		url: url,
		data: SoMusic.assignment,
		dataType: 'JSON',
		success: function(data){
			if(data){
				SoMusic.floatBox.close();
				SoMusic.floatBox = OW.ajaxFloatBox('SOMUSIC_CMP_Preview', {"multiUser":multiUserMod, "groupId":groupId}, {top:'calc(5vh)', width:'calc(80vw)', height:'calc(85vh)', iconClass: 'ow_ic_add', title: ''});
			}
			else console.log("errore");
		},
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		},
		complete: function(){ }
	});
}

SoMusic.assignmentDetails = function(assignmentId) {
	if(typeof SoMusic.floatBox!=="undefined")
		SoMusic.floatBox.close();
	SoMusic.floatBox = OW.ajaxFloatBox("SOMUSIC_CMP_AssignmentDetails", {"assignmentId": assignmentId}, {top:"calc(5vh)", width:"calc(60vw)", height:"calc(60vh)", iconClass: "ow_ic_add", title: ""});
}

SoMusic.completeAssignment = function(assignmentId, timeSignature, keySignature, instrumentsUsed, composition) {
	if(typeof SoMusic.floatBox!=="undefined")
		SoMusic.floatBox.close();
	var toSend = {"timeSignature":timeSignature,"keySignature":keySignature,"instrumentsUsed":instrumentsUsed,"composition":composition};
	SoMusic.assignmentId = assignmentId;
	SoMusic.floatBox = OW.ajaxFloatBox("SOMUSIC_CMP_Editor", toSend, {top:"calc(5vh)", width:"calc(60vw)", height:"calc(60vh)", iconClass: "ow_ic_add", title: ""});
}

SoMusic.viewAssignmentExecution = function(executionId, timeSignature, keySignature, instrumentsUsed, composition) {
	var toSend = {"timeSignature":timeSignature,"keySignature":keySignature,"instrumentsUsed":instrumentsUsed,"composition":composition};
	SoMusic.executionId = executionId;
	SoMusic.floatBox = OW.ajaxFloatBox("SOMUSIC_CMP_Editor", toSend, {top:"calc(5vh)", width:"calc(60vw)", height:"calc(60vh)", iconClass: "ow_ic_add", title: ""});
}

