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

    var arr = new Array();
    for(var i=0;i<list.length;i++)
    {
        if(list[i].platform == 1)
        {
            arr.push(list[i]);
        }
    }
    for(var i=0;i<arr.length;i++)
    {
        newimage+='<div class="swiper-slide blue-slide adscroller"><a href="'+unescape(arr[i].imgLink)+'" target="_blank" rel="nofollow" style="display: block;"><img src="'+arr[i].imgUrl+'"  /></a></div>'
    }
    document.getElementById('playBox').innerHTML = '<div class="swiper-container"><div class="swiper-wrapper ad">'+newimage+'</div></div>';

    var mySwiper = new Swiper('.swiper-container',{
        loop: true,
        autoplay: 3000
    });
}

if(typeof(flashdata) == 'undefined')
{
    flashdata = 'data/flashdata/test/data.js';
}
$importjs(flashdata, show_flash);