VISUALMELODY = {}
if(document.getElementsByName("save").length > 0) {
	document.getElementsByName("save")[0].addEventListener("click", function (e) {
		document.getElementById("vm_placeholder").style.display = "none";
	});
}

VISUALMELODY.init = function () {
	var ks = document.getElementById("ks");
	VISUALMELODY.preview = new Preview(ks, document.getElementById("prev"));
	VISUALMELODY.editor = new Renderer("score", "scoreDiv", "vmCanvas");
	document.getElementById("next").addEventListener("click", function () {
		document.getElementById("firstDiv").style.display = "none";
		document.getElementById("secondDiv").style.display = "block";
		var instrumentsUsed = VISUALMELODY.preview.getInstrumentsUsed();
		VISUALMELODY.editor.init(instrumentsUsed.instruments, instrumentsUsed.totNScores, ks);
		VISUALMELODY.idPost = -1;
	}, false);
	document.getElementById("add").addEventListener("click", function () {
		VISUALMELODY.addListener(VISUALMELODY.editor);			//TODO: ?
	});
	document.getElementById('feed_scoreDiv').addEventListener("click", VISUALMELODY.modScore);
	document.getElementById("removeScore").addEventListener("click", VISUALMELODY.removeScore, false);
	document.getElementById("ks").addEventListener("change", function(e){VISUALMELODY.preview.update();}, false);
	document.getElementById('score_title_text').addEventListener("change", function () {
		$('input[name=scoreTitle]').val($('#score_title_text').val());
	});
	var elements = document.getElementsByName("timeLab");
	for (var i = 0; i < elements.length; i++)
		elements[i].addEventListener("click", function(e){VISUALMELODY.preview.update(e);}, false);
}

VISUALMELODY.commentSendMessage = function(message, context) {
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
		//url: self.addUrl,
		url: VISUALMELODY.ajax_add_comment,
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

VISUALMELODY.loadScore = function (data, id, title) {
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
	var renderer = new Renderer(scoreCanvas.id, id, vmCanvas.id);
	renderer.restoreData(data);
	scoreDiv.parentElement.style.display = "block";
}

VISUALMELODY.removeScore = function () {
	$('input[name=vmHidden]').val('');
	document.getElementById("vm_placeholder").style.display = "none";
	previewFloatBox.close();
	delete previewFloatBox;
	$('.floatbox_canvas').each(function(i, obj) {
		obj.style.display = 'block';
	});
	if(document.getElementById('floatbox_overlay') != null)
		document.getElementById('floatbox_overlay').style.display = 'block';
}

VISUALMELODY.modScore = function (vmData) {
	document.getElementById("vm_placeholder").style.display = "none";
	$('.floatbox_canvas').each(function(i, obj) {
		obj.style.display = 'block';
	});
	document.getElementById('floatbox_overlay').style.display = 'block';
	$('input[name=vmHidden]').val('');
}

VISUALMELODY.modPostScore = function (idPost, vmData) {
	var instrumentsUsed = vmData.instrumentsUsed;
	var totNScores = 0;
	for(var i=0; i<instrumentsUsed.length; i++)
		totNScores += instrumentsUsed[i].scoresClef.length;
	VISUALMELODY.editor.init(instrumentsUsed, totNScores, document.getElementById("ks"));
	VISUALMELODY.editor.restoreData(vmData);
	VISUALMELODY.idPost = idPost;
}

VISUALMELODY.addListener = function (ren) {
	document.getElementById('score').classList.remove("shake");
	document.getElementById('score').classList.remove("animated");
	$('.floatbox_canvas').each(function(i, obj) {
		obj.style.display = 'none';
	});
	document.getElementById('floatbox_overlay').style.display = 'none';
	if(VISUALMELODY.idPost!=-1) {
		var dataToSend = {
				idPost: VISUALMELODY.idPost,
				scores: JSON.stringify(VISUALMELODY.editor.saveData())
		};
		console.log(dataToSend);
		console.log(VISUALMELODY.ajax_update_score);
		$.ajax({
			type: 'post',
			url: VISUALMELODY.ajax_update_score,
			data: dataToSend,
			dataType: 'JSON',
			success: function(data){
				if(data.status)
					setTimeout(function(){ location.reload(); }, 50);
			},
			error: function( XMLHttpRequest, textStatus, errorThrown ){
				OW.error(textStatus);
			},
			complete: function(){
			}
		});
		return;
	}
	var vmData = ren.saveData();
	$('input[name=vmHidden]').val(JSON.stringify(vmData));
	var ren2 = new Renderer("feed_score", "feed_scoreDiv", "feed_vmCanvas");
	document.getElementById("vm_placeholder").style.display = "block";
	ren2.restoreData(vmData);
	document.getElementById('score_title_text').value = "";
	$('input[name=scoreTitle]').val('');
	document.getElementsByTagName("BODY")[0].classList.remove("floatbox_nooverflow");
}

