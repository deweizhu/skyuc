var showId = "num_pic1"
function showMore(currShowId)
{
	document.getElementById(showId).style.display = "none";
	document.getElementById(currShowId).style.display = "";
	showId = currShowId;
}

var showId2 = "TvCon2LeftConCCMore1"
function showMore2(currShowId2)
{
	document.getElementById(showId2).style.display = "none";
	document.getElementById(currShowId2).style.display = "";
	showId2 = currShowId2;
}