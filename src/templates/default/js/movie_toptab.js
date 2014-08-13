
var currId = 'con1_right_update1';
function changTop1(newId)
{
	if( currId!=null)
    {
	  document.getElementById(currId).style.display="none";
	}	
	document.getElementById(newId).style.display="block";
	currId = newId;
}

var cur1Id = 'con4_right_update_mo1';
function changTop2(newId)
{
	if( currId!=null)
    {
	  document.getElementById(cur1Id).style.display="none";
	}	
	document.getElementById(newId).style.display="block";
	cur1Id = newId;
}
function changTitle(id){
     for(var i=1 ; i<=3; i++){         
       if(i == id){                 
          document.getElementById("header"+i).className = "con1_center_click_top1";
          document.getElementById("head"+i).className = "strong";
          document.getElementById("header"+i+"_main").style.display = "block";
       }else{
       	  document.getElementById("header"+i).className = "con1_center_click_top2";
          document.getElementById("head"+i).className = "";
          document.getElementById("header"+i+"_main").style.display = "none";
       	}          
     }
		} 