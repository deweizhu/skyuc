function _movepoint(e) {
	var mypoint = e.target || e.srcElement;
	var userspoint = document.getElementById('userspoint');
	var point_pic = document.getElementById('point_pic');
	if (mypoint.isranked)
		return;
	if(mypoint.timer!=null){
		clearTimeout(mypoint.timer);
	}
	mypoint.classNameOld = mypoint.classNameOld || mypoint.className;
	point_pic.classNameOld = point_pic.classNameOld || point_pic.className;
	if (e.pageX) {
		mypoint.r = parseInt((e.pageX - elementLeft(mypoint)) / 5.5) + 1;
	} else {
		mypoint.r = parseInt(e.offsetX / 5.5) + 1;
	}
	mypoint.className = 'point' + mypoint.r;
	point_pic.className = 'point_num' + mypoint.r;

	mypoint.onmouseout = function(e) {
		if (mypoint.isranked)
			return;
		mypoint.timer = setTimeout( function() {
			point_pic.className = point_pic.classNameOld;
			mypoint.className = mypoint.classNameOld;
		}, 300);
	};
	
	mypoint.onclick = function(e){
        if (mypoint.isranked) 
            return false;
        var rate = mypoint.r;
        var id = mypoint.id;
        mypoint.isranked = true;
      

		Ajax.call( 'ajax.php?do=addrate', 'mid=' + id+'&rate='+rate, function(data){
	        	if (!Utils.isNumber(data))
				{    alert(msg_is_addrate);
					 return false;
				}
        		point_pic.className = 'point_num' + data;
        		userspoint.className='point'+data;
				alert('恭喜，评分成功！');
            } , 'GET', 'TEXT', true, true );
    };
}
function elementLeft(o) {
	for ( var x = 0; o; o = o.offsetParent)
		x += o.offsetLeft;
	return x;
}