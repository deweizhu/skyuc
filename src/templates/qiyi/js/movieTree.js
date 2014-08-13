var label1 = CreateFunLabelChange('detail_list1_label', 1);
var label2 = CreateFunLabelChange('detail_list2_label', 1);
var lable3 = CreateFunLabelChange('detail_play_label', 0, 'on');

function CreateFunLabelChange(IDHeader, changeMode, headerShowClass, headerHiddenClass)
{
    headerShowClass = headerShowClass || '';
    headerHiddenClass = headerHiddenClass || '';
    var headerShowId = IDHeader + '1';
    var divShowId = IDHeader + '1_div';
    return function(currHeaderShowId){
        if (currHeaderShowId == headerShowId) 
            return;
        var currDivShowId = currHeaderShowId + '_div';
        if (changeMode == 0) {
            document.getElementById(currHeaderShowId).className = headerShowClass;
            document.getElementById(headerShowId).className = headerHiddenClass;
        }
        else 
            if (changeMode == 1) {
                document.getElementById(headerShowId).src = document.getElementById(headerShowId).src.replace('s.jpg', '.jpg');
                if (document.getElementById(currHeaderShowId).src.indexOf('s.jpg') == -1) {
                    document.getElementById(currHeaderShowId).src = document.getElementById(currHeaderShowId).src.replace('.jpg', 's.jpg');
                }
            }
        document.getElementById(divShowId).style.display = 'none';
        document.getElementById(currDivShowId).style.display = '';
        headerShowId = currHeaderShowId;
        divShowId = currDivShowId;
    }
    
}