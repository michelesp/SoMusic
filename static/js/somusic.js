
SoMusic = {}

SoMusic.init = function () {
	document.getElementById('feed_scoreDiv').addEventListener("click", SoMusic.modScore);
	document.getElementById("removeScore").addEventListener("click", SoMusic.removeScore, false);
	document.getElementById('score_title_text').addEventListener("change", function () {
		$('input[name=scoreTitle]').val($('#score_title_text').val());
	});
	SoMusic.floatBox = [];
	var oldClose = OW_FloatBox.prototype.close;
	OW_FloatBox.prototype.close = function() {
		/*if(typeof SoMusic.assignmentId!=="undefined")
			delete SoMusic.assignmentId;
		if(typeof SoMusic.assignment!=="undefined")
			delete SoMusic.assignment;*/
		if(SoMusic.floatBox.length>0) {
			var fb = SoMusic.floatBox.pop();
			console.log(fb);
			if(fb.name=="Editor") {
				SoMusic.editor.close();
				delete SoMusic.editor;
			}
		}
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
	var fb = SoMusic.floatBox.pop();
	fb.floatBox.close();
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
	console.log("save");
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
	console.log(SoMusic.assignment);
	$.ajax({
		type: 'post',
		url: url,
		data: SoMusic.assignment,
		dataType: 'JSON',
		success: function(data){
			console.log(data);
			if(data){
				var fb = SoMusic.floatBox.pop();
				fb.floatBox.close();
				SoMusic.floatBox.push({"name":"preview", "floatBox":OW.ajaxFloatBox('SOMUSIC_CMP_Preview', {"multiUser":multiUserMod, "groupId":groupId}, {top:'calc(5vh)', width:'calc(80vw)', height:'calc(85vh)', iconClass: 'ow_ic_add', title: ''})});
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
	SoMusic.closeAllFloatBox();
	SoMusic.floatBox.push({"name":"AssignmentDetails", "floatBox":OW.ajaxFloatBox("SOMUSIC_CMP_AssignmentDetails", {"assignmentId": assignmentId}, {top:"calc(5vh)", width:"calc(60vw)", height:"calc(60vh)", iconClass: "ow_ic_add", title: ""})});
}

SoMusic.completeAssignment = function(assignmentId, compositionId) {
	//SoMusic.closeAllFloatBox();
	var toSend = {"compositionId":compositionId, "assignmentId":assignmentId};
	console.log(toSend);
	SoMusic.assignmentId = assignmentId;
	SoMusic.floatBox.push({"name":"Editor", "floatBox":OW.ajaxFloatBox("SOMUSIC_CMP_Editor", toSend, {top:"calc(5vh)", width:"calc(80vw)", height:"calc(80vh)", iconClass: "ow_ic_add", title: ""})});
}

SoMusic.closeAllFloatBox = function() {
	while(SoMusic.floatBox.length>0) {
		var fb = SoMusic.floatBox.pop();
		fb.floatBox.close();
	}
}

SoMusic.executionDetails = function(assignmentId, executionId) {
	SoMusic.closeAllFloatBox();
	var toSend = {
			"assignmentId": assignmentId,
			"executionId": executionId
	};
	console.log(toSend);
	SoMusic.floatBox.push({"name":"AssignmentExecutionDetails", "floatBox":OW.ajaxFloatBox("SOMUSIC_CMP_AssignmentExecutionDetails", toSend, {top:"calc(5vh)", width:"calc(60vw)", height:"calc(60vh)", iconClass: "ow_ic_add", title: ""})});
}

SoMusic.viewAssignmentExecution = function(executionId, compositionId) {
	var toSend = {"compositionId":compositionId};
	SoMusic.executionId = executionId;
	SoMusic.floatBox.push({"name":"Editor", "floatBox":OW.ajaxFloatBox("SOMUSIC_CMP_Editor", toSend, {top:"calc(5vh)", width:"calc(80vw)", height:"calc(80vh)", iconClass: "ow_ic_add", title: ""})});
}

SoMusic.removeAssignment = function(url, id) {
	$.ajax({
		type: 'post',
		url: url,
		data: {"id": id},
		dataType: 'JSON',
		success: function(data){
			console.log(data);
			if(data)
				setTimeout(function(){ location.reload(); }, 50);
		},
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		}
	});
}

SoMusic.closeAssignment = function(url, id) {
	$.ajax({
		type: 'post',
		url: url,
		data: {"id": id},
		dataType: 'JSON',
		success: function(data){
			console.log(data);
			if(data)
				setTimeout(function(){ location.reload(); }, 50);
		},
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		}
	});
}

SoMusic.saveComment = function(url, id, comment) {
	$.ajax({
		type: 'post',
		url: url,
		data: {"id": id, "comment": comment},
		dataType: 'JSON',
		success: function(data){
			console.log(data);
			if(data)
				$("#commentModal").modal("hide");
		},
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		}
	});
}

SoMusic.removeCompositionInstrument = function(url, i) {
	var row = document.getElementsByClassName("instrumentsSettings")[i];
	$.ajax({
		type: 'post',
		url: url,
		data: {"index": i},
		dataType: 'JSON',
		success: function(data){
			console.log(data);
			if(data) {
				row.parentNode.removeChild(row);
				SoMusic.editor.update();
			}
		},
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		}
	});
}

SoMusic.importMusicXML = function(url, fileInput) {
	var file = fileInput.files[0];
	var reader = new FileReader();
	reader.onload = function(e) {
		document.getElementById("xmlFile").value = reader.result;
		 $.ajax({
		        url: url,
		        type: 'POST',
		        data: new FormData($('form[name="preview_form"]')[0]),
		        cache: false,
		        contentType: false,
		        processData: false,
		        //contentType: "multipart/form-data",
		        dataType: 'JSON',
		        success: function(data){
					console.log(data);
					SoMusic.preview.instrumentsTable.loadTable();
					setTimeout(() => {
						SoMusic.preview.commitPreview();
					}, 1000);
				},
		    });
	}
	reader.readAsText(file);	
}

SoMusic.exportMusicXML = function(url) {
	var a = document.createElement('a');
	a.setAttribute('download', 'music.xml');
	a.href = url;
	a.style.display = 'none';
	document.body.appendChild(a);
	a.click();
}
