VISUALMELODY = {}
if(document.getElementsByName("save").length > 0) {
	document.getElementsByName("save")[0].addEventListener("click", function (e) {
		document.getElementById("vm_placeholder").style.display = "none";
	});
}

VISUALMELODY.initEditor = function () {
	var keySig = document.getElementById("ks");
	Object.keys(Vex.Flow.keySignature.keySpecs).forEach(function (key) {
		var option = document.createElement("option");
		option.text = key;
		keySig.add(option);
	});
	var ren = new Renderer("score", "scoreDiv", "vmCanvas");
	document.getElementById("next").addEventListener("click", function () {
		document.getElementById("firstDiv").style.display = "none";
		document.getElementById("secondDiv").style.display = "block";
		ren.init();
	}, false);
	document.getElementById("add").addEventListener("click", function () {
		VISUALMELODY.addListener(ren);
	});
	document.getElementById('feed_scoreDiv').addEventListener("click", VISUALMELODY.modScore);
	document.getElementById("removeScore").addEventListener("click", VISUALMELODY.removeScore, false);
	document.getElementById("ks").addEventListener("change", VISUALMELODY.preview, false);
	document.getElementById('score_title_text').addEventListener("change", function () {
		$('input[name=scoreTitle]').val($('#score_title_text').val());
	});
	var elements = document.getElementsByName("timeLab");
	for (var i = 0; i < elements.length; i++)
		elements[i].addEventListener("click", VISUALMELODY.preview, false);
	var elements = document.getElementsByName("smtLab");
	for (var i = 0; i < elements.length; i++)
		elements[i].addEventListener("click", VISUALMELODY.preview, false);
	var prevDiv = document.getElementById("prev");
	VISUALMELODY["prevRenderer"] = new Vex.Flow.Renderer(prevDiv, Vex.Flow.Renderer.Backends.SVG);
	VISUALMELODY.preview();
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

VISUALMELODY.addListener = function (ren) {
	document.getElementById('score').classList.remove("shake");
	document.getElementById('score').classList.remove("animated");
	$('.floatbox_canvas').each(function(i, obj) {
		obj.style.display = 'none';
	});
	document.getElementById('floatbox_overlay').style.display = 'none';
	var vmData = ren.saveData();
	$('input[name=vmHidden]').val(JSON.stringify(vmData));
	var ren2 = new Renderer("feed_score", "feed_scoreDiv", "feed_vmCanvas");
	document.getElementById("vm_placeholder").style.display = "block";
	ren2.restoreData(vmData);
	document.getElementById('score_title_text').value = "";
	$('input[name=scoreTitle]').val('');
	document.getElementsByTagName("BODY")[0].classList.remove("floatbox_nooverflow");
}

VISUALMELODY.preview = function () {
	var ctx = VISUALMELODY["prevRenderer"].getContext();
	//ctx.setFont("Arial", 10, "")
	var keySign = $("#ks :selected").text();
	var instrumentsUsed = getInstrumentsUsed();
	var instruments = instrumentsUsed.instruments;
	var totNScores= instrumentsUsed.totNScores;
	var staves = [];
	var braces = [];
	var lines = [];
	if(arguments.length>0 && arguments[0].target.attributes[1]!=null && arguments[0].target.attributes[1].nodeValue=="timeLab")
		var timeSign = arguments[0].target.childNodes[1].id;
	else 
		var timeSign = getRadioSelected("time");
	VISUALMELODY["prevRenderer"].resize(400, (totNScores*70>400?totNScores*75:400));
	ctx.clear();
	for(var i=0, j=0; i<instruments.length; i++) {
		var inst = instruments[i];
		var start = j*70+130;
		var end = start;
		for(var k=0; k<inst.scoresClef.length; k++){
			end = j*70;
			staves.push(new Vex.Flow.Stave(100, end, 200));
			staves[j].addClef(inst.scoresClef[k]).addTimeSignature(timeSign).addKeySignature(keySign).setContext(ctx).draw();
			j++;
			if(j>1){
				lines.push(new Vex.Flow.StaveConnector(staves[j-2], staves[j-1]).setType(1))
				lines.push(new Vex.Flow.StaveConnector(staves[j-2], staves[j-1]).setType(6))
			}
		}
		for(var k=0; k<inst.braces.length; k++)
			braces.push(new Vex.Flow.StaveConnector(staves[j-inst.scoresClef.length+inst.braces[k][0]], staves[j-inst.scoresClef.length+inst.braces[k][1]]).setType(3));
		var y = (start+end)/2;
		ctx.fillText(instruments[i].labelName,10,y);
	}
	for(var i=0; i<braces.length; i++)
		braces[i].setContext(ctx).draw();
	for(var i=0; i<lines.length; i++)
		lines[i].setContext(ctx).draw();
}


function getInstrumentsUsed() {
	var tab = document.getElementById("tabInstrumentsBody");
	var instrumentsUsed = [];
	var totNScores = 0;
	for(var i=0; i<tab.children.length; i++) {
		var tr = tab.children[i];
		var labelName=null, name=null;
		for(var j=0; j<tr.children.length; j++) {
			var td = tr.children[j];
			for(var k=0; k<td.children.length; k++){
				if(td.children[k].nodeName=="INPUT")
					labelName=td.children[k].value;
				if(td.children[k].nodeName=="SELECT")
					name=td.children[k].value;
			}
		}
		instrumentsUsed.push({"labelName":labelName, "name":name, "scoresClef":instruments[name]["scoresClef"], "braces":instruments[name]["braces"]});
		totNScores+=instrumentsUsed[instrumentsUsed.length-1].scoresClef.length;
	}
	return {instruments: instrumentsUsed, totNScores: totNScores };
}
