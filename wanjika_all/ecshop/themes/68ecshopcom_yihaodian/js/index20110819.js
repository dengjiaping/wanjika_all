/*==============================
 * 美乐乐新版首页JS
 * 2011-08-19
 * laidezhong@meilele.com
 * (c)Meilele.com
================================*/
(function(win,doc){var easyAnim=function(elem){elem=typeof elem==='string'?doc.getElementById(elem):elem;return new AnimExtend(elem);},pow=Math.pow,sin=Math.sin,PI=Math.PI,BACK_CONST=1.70158,animData=[];var Easing={easeBoth:function(t){return(t*=2)<1?.5*t*t:.5*(1-(--t)*(t-2));}};var animBase={getStyle:function(elem,p){return'getComputedStyle'in win?function(){var val=getComputedStyle(elem,null)[p];if((p==='left'||p==='right'||p==='top'||p==='bottom')&&val==='auto'){return'0px';}
return val;}
():function(){var newP=p.replace(/\-(\w)/g,function($,$1){return $1.toUpperCase();});var val=elem.currentStyle[newP];if((newP==="width"||newP==="height")&&val==='auto'){var rect=elem.getBoundingClientRect();return(newP==='width'?rect.right-rect.left:rect.bottom-rect.top)+'px';}
if(newP==='opacity'){var filter=elem.currentStyle.filter;if(/opacity/.test(filter)){val=filter.match(/\d+/)[0]/100;return(val===1||val===0)?val.toFixed(0):val.toFixed(1);}else if(val===undefined){return'1';}}
if((p==='left'||p==='right'||p==='top'||p==='bottom')&&val==='auto'){return'0px';}
return val;}
();},parseStyle:function(prop){var val=parseFloat(prop),unit=prop.replace(/^[\-\d\.]+/,'');return isNaN(val)?{val:this.parseColor(unit),unit:'',fn:function(sv,tv,tu,e){var r=(sv.r+(tv.r-sv.r)*e).toFixed(0),g=(sv.g+(tv.g-sv.g)*e).toFixed(0),b=(sv.b+(tv.b-sv.b)*e).toFixed(0);return'rgb('+r+','+g+','+b+')';}}:{val:val,unit:unit,fn:function(sv,tv,tu,e){return(sv+(tv-sv)*e).toFixed(3)+tu;}}},newObj:function(arr,val){val=val!==undefined?val:1;var obj={};for(var i=0,len=arr.length;i<len;i+=1){obj[arr[i]]=val;}
return obj;},speed:{defaults:300},fxAttrs:function(type,index){var attrs=[];return{attrs:attrs[index],type:type}},setOptions:function(elem,duration,easing,callback){var self=this,options={};options.duration=(function(d){if(typeof d==='number'){return d;}else if(typeof d==='string'&&self.speed[d]){return self.speed[d];}else if(!d){return self.speed.defaults;}})(duration);options.easing=(function(e){return Easing.easeBoth;})(easing);options.callback=function(){if(typeof callback==='function'){callback();}
self.dequeue(elem);};return options;},setProps:function(elem,props,type){if(type){var attrs=props().attrs,type=props().type,val,obj,p;if(type==='hide'){val=attrs[0]==='opacity'?'0':'0px';}
obj=this.newObj(attrs,val);if(type==='show'){for(p in obj){obj[p]=this.getStyle(elem,p);}}
return obj;}else if(props&&typeof props==='object'){return props;}},data:function(elem){var animQueue=elem.animQueue;if(!animQueue){animQueue=elem.animQueue=[];}
return animQueue;},queue:function(elem,data){var animQueue=this.data(elem);if(data){animQueue.push(data);}
if(animQueue[0]!=='runing'){this.dequeue(elem);}},dequeue:function(elem){var self=this,animQueue=self.data(elem),fn=animQueue.shift();if(fn==='runing'){fn=animQueue.shift();}
if(fn){animQueue.unshift('runing');if(typeof fn==='number'){win.setTimeout(function(){self.dequeue(elem);},fn);}else if(typeof fn==='function'){fn.call(elem,function(){self.dequeue(elem);});}}
if(!animQueue.length){elem.animQueue=undefined;}}};var AnimCore=function(elem,options,props,type){this.elem=elem;this.options=options;this.props=props;this.type=type;},$=animBase;AnimCore.prototype={start:function(source,target){this.startTime=+new Date();this.source=source;this.target=target;animData.push(this);var self=this;if(self.elem.timer)
return;self.elem.timer=win.setInterval(function(){for(var i=0,curStep;curStep=animData[i++];){curStep.run();}
if(!animData.length){self.stop();}},13);},run:function(end){var elem=this.elem,type=this.type,props=this.props,startTime=this.startTime,elapsedTime=+new Date(),duration=this.options.duration,endTime=startTime+duration,t=elapsedTime>endTime?1:(elapsedTime-startTime)/duration,e=this.options.easing(t),len=0,i=0,p;for(p in props){len+=1;}
elem.style.overflow='hidden';if(type==='show'){elem.style.display='block';}
for(p in props){i+=1;var sv=this.source[p].val,tv=this.target[p].val,tu=this.target[p].unit;if(end||elapsedTime>=endTime){elem.style.overflow='';if(type==='hide'){elem.style.display='none';}
if(type){if(p==='opacity'){$.setOpacity(elem,1);}else{elem.style[p]=(type==='hide'?sv:tv)+tu;}}else{elem.style[p]=/color/i.test(p)?'rgb('+tv.r+','+tv.g+','+tv.b+')':tv+tu;}
if(i===len){this.complete();this.options.callback.call(elem);}}else{if(sv===tv)
continue;if(p==='opacity'){$.setOpacity(elem,(sv+(tv-sv)*e).toFixed(3));}else{elem.style[p]=this.target[p].fn(sv,tv,tu,e);}}}},stop:function(){win.clearInterval(this.elem.timer);this.elem.timer=undefined;},complete:function(){for(var i=animData.length-1;i>=0;i--){if(this===animData[i]){animData.splice(i,1);}}}};var AnimExtend=function(elem){this.elem=elem;};AnimExtend.prototype={custom:function(props,duration,easing,callback){var elem=this.elem,options=$.setOptions(elem,duration,easing,callback),type=typeof props==='function'?props().type:null;props=$.setProps(elem,props,type);$.queue(elem,function(){var source={},target={},p;for(p in props){if(type==='show'){if(p==='opacity'){$.setOpacity(elem,'0');}else{elem.style[p]='0px';}}
source[p]=$.parseStyle($.getStyle(elem,p));target[p]=$.parseStyle(props[p]);}
var core=new AnimCore(elem,options,props,type);core.start(source,target);});return this;},stop:function(clear,end){var elem=this.elem,i=animData.length;if(clear){elem.animQueue=undefined;}
while(i--){if(animData[i].elem===elem){if(end){animData[i].run(true);if(elem.timer)
return;elem.timer=win.setInterval(function(){for(var j=0,curStep;curStep=animData[j++];){curStep.run();}
if(!i){win.clearInterval(elem.timer);elem.timer=undefined;}},13);}
animData.splice(i,1);}}
if(!end){$.dequeue(elem);}
return this;}};win.easyAnim=easyAnim;})(window,document);var undefined;var $=function(selector,context){context=context||document
if(selector.indexOf("#")==0){var r=context.getElementById(selector.substr(1));if(r&&r.nodeName)
r.length=1;}else if(selector.indexOf(".")==0){var r=[],all=context.getElementsByTagName("*");for(var i=0;i<all.length;i++){if(all[i].className.indexOf(selector.substr(1))>-1){r.push(all[i]);}}}else if(selector.indexOf("|")>-1){var s=selector.split("|");s[0]=s[0]||"*";var r=[];var tmp=context.getElementsByTagName(s[0]);for(var k=0;k<tmp.length;k++){if(tmp[k].getAttribute(s[1])!=undefined){r.push(tmp[k]);}}
tmp=null}else{var r=context.getElementsByTagName(selector);}
return r;}
var addClass=function(obj,clas){obj.className+=" "+clas;return obj;}
var removeClass=function(obj,clas){if(typeof obj.className==="string")
obj.className=obj.className.replace(clas,"");return obj;}
var inArray=function(value,array){for(var k=0;k<array.length;k++){if(value==array[k])
return k;}
return-1}
var offsetTop=function(obj){var offsetParent=obj;var top=0;while(offsetParent!=null&&offsetParent!=document.body){top+=offsetParent.offsetTop;offsetParent=offsetParent.offsetParent;}
return top;}
var backLoad={i:0,add:function(str){this.i++;},del:function(){var that=this;that.i--;if(that.i<=0){that.i=0;setTimeout(function(){that.loadImg()},0);}},loadImg:function(){var that=this;for(var k=0;k<lazyImgs.length;k++){if(that.i>0)
return;if(lazyImgs[k]&&!lazyImgs[k]._loading){lazyImgs[k]._loading=1;lazyImgs[k].src=lazyImgs[k]._dataSrc;return;}}}};var SCROLL=function(stage,left,right,step,showerWidth,name){var that=this;this.name=name;this.stage=stage;this.left=left;this.right=right;this.step=step;this.showerWidth=showerWidth;this.allWidth=parseInt(this.stage.offsetWidth);this.marginLeft=0;this.left.onclick=function(){that.scrol(that.left,1)};this.right.onclick=function(){that.scrol(that.right,-1)};}
SCROLL.prototype.scrol=function(obj,type){var that=this;if(obj.className.indexOf("disabled")>-1)
return false;var to=that.marginLeft+type*that.step;if(to>0)
to=0;if(to<0-(that.allWidth-that.showerWidth))
to=0-(that.allWidth-that.showerWidth);if(to<0){removeClass(that.left,"disabled");}else{addClass(that.left,"disabled");};if(to>0-(that.allWidth-that.showerWidth)){removeClass(that.right,"disabled");}else{addClass(that.right,"disabled");}
if(to==that.marginLeft)
return false;easyAnim(that.stage).stop(true,false).custom({marginLeft:to+"px"},300);that.marginLeft=to;}
var SLIDE=function(stage,nav,stageId,navId,height,count,name){var that=this;this.name=name;this.now=1;this.lock=0;this.stage=stage;this.nav=nav;this.stageId=stageId;this.navId=navId;this.height=height;this.count=count;this.hoverDom=$(".JS_focus");for(var k=0;k<this.hoverDom.length;k++){
	
	
	this.hoverDom[k].onmouseclick=function(){that.lock=1}
this.hoverDom[k].onmouseclick=function(){that.lock=0}}


this.li=$("li",this.nav);

for(var k=0;k<this.li.length;k++){
	
	this.li[k]._Id=k;this.li[k].onmouseclick=function(){that.scrol(this._Id+1);that.lock=1;}};
	
	}
SLIDE.prototype.scrol=function(to){var that=this;to=to||(that.now)%that.count+1;if(to==that.now)
return;if(to>1&&_scrollTop<630)
that.loadImg(to);var navObj=$("#"+that.navId+to);for(var k=0;k<that.li.length;k++){removeClass(that.li[k],"current");}
addClass(navObj,"current");easyAnim(that.stage).stop(true,false).custom({marginTop:((1-to)*that.height)+"px"},300);that.now=to;}
SLIDE.prototype.loadImg=function(to){var that=this;var imgs=$("img",$("#"+that.stageId+to));for(var k=0;k<imgs.length;k++){if(imgs[k]&&!imgs[k]._loading){if(to>1)
backLoad.add("SLIDE"+k);imgs[k]._loading=1;imgs[k].src=imgs[k]._dataSrc;}}}
var lazyImg=function(){for(var k=0;k<lazyImgs.length;k++){lazyImgs[k]._id=k;lazyImgs[k]._offsetTop=offsetTop(lazyImgs[k]);lazyImgs[k]._dataSrc=lazyImgs[k].getAttribute("data-src");lazyImgs[k].onload=function(){var id=this._id;if(lazyImgs[id]&&lazyImgs[id]._loading){backLoad.del();lazyImgs[id]=null;}}}
var scrollTimer=-1;window.onscroll=function(){if(scrollTimer!=-1)
clearTimeout(scrollTimer);scrollTimer=window.setTimeout(function(){var scrollTop=_scrollTop=window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop||0;for(var k=0;k<lazyImgs.length;k++){if(!lazyImgs[k])
continue;if(lazyImgs[k]._loading)
continue;if(scrollTop+document.documentElement.clientHeight>lazyImgs[k]._offsetTop&&scrollTop<lazyImgs[k]._offsetTop+(lazyImgs[k].height||lazyImgs[k].cilentHeight)){backLoad.add("SCROLL:"+k);lazyImgs[k]._loading=1;lazyImgs[k].src=lazyImgs[k]._dataSrc;}}},500);}}
var _scrollTop;var lazyImgs=$("img|data-src");lazyImg();var news_goods_scroll=new SCROLL($("#JS_news_goods_stage"),$("#JS_news_goods_left"),$("#JS_news_goods_right"),228,228*4,"news_goods_scroll");var hot_goods_scroll=new SCROLL($("#JS_hot_goods_stage"),$("#JS_hot_goods_left"),$("#JS_hot_goods_right"),228,228*4,"hot_goods_scroll");var show_scroll=new SCROLL($("#JS_show_stage"),$("#JS_show_left"),$("#JS_show_right"),230,230*4,"show_scroll");var index_focus=new SLIDE($("#JS_focus_stage"),$("#JS_focus_nav"),"JS_focus_stage_","JS_focus_nav_",447,5,"index_focus");setInterval(function(){if(!index_focus.lock)
index_focus.scrol();},10000);backLoad.loadImg();var index_notice_tab=function(id){if(id==index_notice_tab.Id)
return;var ids=[1,2,3]
for(var k=0;k<ids.length;k++){if(ids[k]==id){document.getElementById("JS_notice_"+ids[k]).className="body";document.getElementById("JS_noticet_"+ids[k]).className="current";}else{document.getElementById("JS_notice_"+ids[k]).className="body none";document.getElementById("JS_noticet_"+ids[k]).className="";}}
index_notice_tab.Id=id;}