var again;


$(document).ready(function () {
    $('img').imgAreaSelect({
    	handles: true,
        onSelectEnd: function (img, selection) {
            $('input[name="x1"]').val(selection.x1);
            $('input[name="y1"]').val(selection.y1);
            $('input[name="x2"]').val(selection.x2);
            $('input[name="y2"]').val(selection.y2); 
			
			var img_width = $("#contentDiv").width();
			var img_height = $("#contentDiv").height();
			$('input[name="img_width"]').val(img_width);
			$('input[name="img_height"]').val(img_height);

			document.getElementById("FACE_ON_PAGE").checked=true;
			show();
        }
    });
});
	
	
function show(){
	document.getElementById("another_button").style.display = "block";
	$(".data").attr("required", true)
	
	document.getElementById("another_button").onmouseenter= function(){document.getElementById("another_instructions").style.visibility="visible";};
	document.getElementById("another_button").onmouseleave= function(){document.getElementById("another_instructions").style.visibility="hidden";};

	document.getElementById("submit_button").onmouseenter= function(){document.getElementById("done_instructions").style.visibility="visible";};
	document.getElementById("submit_button").onmouseleave= function(){document.getElementById("done_instructions").style.visibility="hidden";};
}
	
function hide(){
	//document.getElementById("multi").style.display = "none";
	//document.getElementById("cat").style.display = "none";
		//document.getElementById("instructions2").style.display="none";
	document.getElementById("another_button").style.display = "none";
	$(".data").attr("required", false)
}	

function another(){
	var face = document.getElementById("FaceText");
	var no_face = document.getElementById("NoFaceText");

	if (again){
		show();
		document.getElementById("FACE_ON_PAGE").checked=true;
		face.innerHTML = "I'm tagging a face";
		no_face.innerHTML = "There are no more faces on this page";

	} 
	else {
		no_face.innerHTML = "There are no faces on this page";
		face.innerHTML = "There is at least one face on this page";
	 }
}

function check_inputs(){
 x1=document.getElementsByName("x1");	

 if (!x1[0].value){
	alert("Please click and drag the mouse over the image to select a face.");
 }
}

function auto_submit(){
	document.getElementById("auto").submit();
}

function set_warning(){
	document.getElementById("warning").onmouseover= function(){document.getElementById("warning").style.display="none";};
}

function check(id){
	document.getElementById(id).checked=true;
}