
function AssignmentManager(groupId, isAdmin, closeURL, removeURL, saveCommentURL, newAssignmentURL, completeAssignmentURL) {
	this.closeURL = closeURL;
	this.removeURL = removeURL;
	this.saveCommentURL = saveCommentURL;
	this.newAssignmentURL = newAssignmentURL;
	this.completeAssignmentURL = completeAssignmentURL;
	this.groupId = groupId;
	this.isAdmin = isAdmin;
	this.assignmentId = -1;
	this.executionId = -1;
}


AssignmentManager.prototype.viewAssignmentExecution = function(executionId, compositionId) {
	var toSend = {"compositionId":compositionId, "noteColor":(this.isAdmin?"red":"black")};
	this.executionId = executionId;
	SoMusic.floatBox.push({
		"name": "Editor",
		"floatBox": OW.ajaxFloatBox("SOMUSIC_CMP_Editor", toSend, {top:"calc(5vh)", width:"calc(80vw)", height:"calc(80vh)", iconClass: "ow_ic_add", title: ""})
	});
}

AssignmentManager.prototype.closeAssignment = function() {
	this.ajaxRequest(this.closeURL, {"id": this.assignmentId}, function(result) {
		if(result.status)
			setTimeout(function(){ location.reload(); }, 50);
		else OW.error(result.message);
	});
}

AssignmentManager.prototype.removeAssignment = function() {
	this.ajaxRequest(this.removeURL, {"id": this.assignmentId}, function(result) {
		if(result.status)
			setTimeout(function(){ location.reload(); }, 50);
		else OW.error(result.message);
	});
}

AssignmentManager.prototype.viewComment = function(executionId, comment) {
	this.executionId = executionId;
	var assignmentDetails = this;
	document.getElementById("commentText").innerHTML = comment;
	$('#commentModal').on('hidden.bs.modal', function () {
		assignmentDetails.executionId = -1;
	})
	$("#commentModal").modal("show");
}

AssignmentManager.prototype.saveComment = function(comment) {
	this.ajaxRequest(this.saveCommentURL, {"id": this.executionId, "comment": comment}, function(result) {
		if(result.status)
			setTimeout(function(){ location.reload(); }, 50);
		else OW.error(result.message);
	});
	this.executionId = -1;
}

AssignmentManager.prototype.completeAssignment = function(assignmentId, executionId=null) {
	var assignmentManager = this;
	this.assignmentId = assignmentId;
	$.ajax({
		type: 'post',
		url: assignmentManager.completeAssignmentURL,
		data: {"assignmentId":assignmentId, "executionId":executionId},
		dataType: 'JSON',
		success: function(data){
			console.log(data);
			if(data.status)
				SoMusic.floatBox.push({
					"name": "Editor",
					"floatBox": OW.ajaxFloatBox("SOMUSIC_CMP_Editor", {}, {top:"calc(5vh)", width:"calc(80vw)", height:"calc(80vh)", iconClass: "ow_ic_add", title: ""})
				});
			else OW.error(data.message);
		}
	});
}

AssignmentManager.prototype.assignmentDetails = function(assignmentId) {
	this.assignmentId = assignmentId;
	SoMusic.floatBox.push({"name":"AssignmentDetails", "floatBox":OW.ajaxFloatBox("SOMUSIC_CMP_AssignmentDetails", {"assignmentId": assignmentId}, {top:"calc(5vh)", width:"calc(60vw)", height:"calc(60vh)", iconClass: "ow_ic_add", title: ""})});
}

AssignmentManager.prototype.executionDetails = function(assignmentId, executionId) {
	var toSend = {
			"assignmentId": assignmentId,
			"executionId": executionId
	};
	this.assignmentId = assignmentId;
	this.executionId = executionId;
	SoMusic.floatBox.push({"name":"AssignmentExecutionDetails", "floatBox":OW.ajaxFloatBox("SOMUSIC_CMP_AssignmentExecutionDetails", toSend, {top:"calc(5vh)", width:"calc(60vw)", height:"calc(60vh)", iconClass: "ow_ic_add", title: ""})});
}

AssignmentManager.prototype.reset = function () {
	this.assignmentId = -1;
	this.executionId = -1;
}

AssignmentManager.prototype.closeViewAssignmentExecution = function () {
	this.executionId = -1;
}

AssignmentManager.prototype.setAssignmentId = function (assignmentId) {
	this.assignmentId = assignmentId;
}

AssignmentManager.prototype.openNewAssignment = function () {
	SoMusic.floatBox.push({
		'name': 'NewAssignment',
		'floatBox': OW.ajaxFloatBox('SOMUSIC_CMP_NewAssignment', {'groupId':this.groupId}, {top:'calc(5vh)', width:'calc(30vw)', height:'calc(30vh)', iconClass: 'ow_ic_add', title: ''})});
}

AssignmentManager.prototype.newAssignment = function(name, multiUserMod) {
	var assignmentManager = this;
	this.assignment = { "groupId": this.groupId, "name": name, "isMultiUser": multiUserMod };
	this.ajaxRequest(this.newAssignmentURL, this.assignment, function(result){
		if(result.status){
			var fb = SoMusic.floatBox.pop();
			fb.floatBox.close();
			SoMusic.floatBox.push({
				"name": "Preview",
				"floatBox": OW.ajaxFloatBox('SOMUSIC_CMP_Preview', {"name":"", "multiUser":multiUserMod, "groupId":assignmentManager.groupId}, {top:'calc(5vh)', width:'calc(80vw)', height:'calc(85vh)', iconClass: 'ow_ic_add', title: ''})
			});
		}
		else OW.error(result.message);
	});
}

AssignmentManager.prototype.ajaxRequest = function(url, data, succFunc) {
	$.ajax({
		type: 'post',
		url: url,
		data: data,
		dataType: 'JSON',
		success: succFunc,
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		}
	});
}

