/*
Flash Name:  新版轮播
 Description: 新版轮播
*/
document.write('<div id="playBox"></div>');
$importjs = (function()
{
    var uid = 0;
    var curr = 0;
    var remove = function(id)
    {
        var head = document.getElementsByTagName('head')[0];
        head.removeChild( document.getElementById('jsInclude_'+id) );
    };

    return function(file,callback)
    {
        var callback;
        var id = ++uid;
        var head = document.getElementsByTagName('head')[0];
        var js = document.createElement('script');
        js.setAttribute('type','text/javascript');
        js.setAttribute('src',file);
        js.setAttribute('id','jsInclude_'+id);
        if( document.all )
        {
            js.onreadystatechange = function()
            {
                if(/(complete|loaded)/.test(this.readyState))
                {
                    try
                    {
                        callback(id);remove(id);
                    }
                    catch(e)
                    {
                        setTimeout(function(){remove(id);include_js(file,callback)},2000);
                    }
                }
            };
        }
        else
        {
            js.onload = function(){callback(id); remove(id); };
        }
        head.appendChild(js);
        return uid;
    };
}
)();

function show_flash()
{
    var newimage="";
    var newli="";
    var arr = new Array();
    for(var i=0;i<list.length;i++)
    {
        if(list[i].platform != 1)
        {
            arr.push(list[i]);
        }
    }
    for(var i=0;i<arr.length;i++)
    {
        newimage+='<li><a href="'+unescape(arr[i].imgLink)+'" target="_blank" rel="nofollow"><img src="'+arr[i].imgUrl+'"</a></li>'
    }

    for(var i=0;i<arr.length;i++)
    {
        if(i==0)
        {
            newli='<li class="thistitle"></li>';
        }
        else
        {
            newli+='<li></li>';
        }
    }
    document.getElementById('playBox').innerHTML = '<div class="pre" style="display: none;"></div><div class="next" style="display: none;"></div>' +
        '<div class="smalltitle"><ul>'+newli+'</ul></div><ul class="oUlplay">'+newimage+'</ul>';
}

if(typeof(flashdata) == 'undefined')
{
    flashdata = 'data/flashdata/test/data.js';
}
$importjs(flashdata, show_flash);