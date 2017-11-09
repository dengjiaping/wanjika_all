// jquery plugins
//plugins_1 **jquery.badword.1.0.js
//plugins_2 **jquery.validate.1.4.0.js
//plugins_3 **jquery.validate.methods.1.4.0.js
/*----jquery.badword.1.0.js----*/
//	Version: 1.0
//	Author: Spring.Cheung(spring.cheung@163.com)
//	LastChangedDate: 2008-08-22
//	Requires: jquery.1.2.6.js

$.badWord = function(content, options)
	{
		options = $.extend(
			{
				words: "法轮|洪志|大纪元|新唐人|强奸|肉棍|淫靡|淫水|六四事件|迷药|窃听器|六合彩|买卖枪支|退党|三唑仑|麻醉药|麻醉乙醚|帝国之梦|毛一鲜|黎阳评|色情|出售枪支|迷药|摇头丸|天葬|鬼村|军长发威|PK黑社会|恶搞晚会|枪决女犯|投毒杀人|强硬发言|出售假币|监听王|昏药|侦探设备|麻醉钢枪|反华|官商勾结|升达|手机复制|戴海静|自杀指南|自杀手册|张小平|安定片|蒙汗|古方迷香|失意药|迷歼药|透视眼镜|远程偷拍|自制手枪|子女任职名单|激情小电影|黄色小电影|小电影|天鹅之旅|盘古乐队|高校暴乱|群体事件|大学骚乱|高校骚乱|催情药|拍肩神药|春药|窃听器材|身份证生成|枪决现场|出售手枪|麻醉枪|办理证件|办理文凭|疆独|藏独|高干子弟|高干子女|枪支弹药|血腥图片|反政府|禁书|无界|特码|反共|成人|换妻|三级片|本拉登|地下先烈|领导财产公示|GPS预警器|毛主席复活|防拍器|电子狗|曝光王|隐形喷剂|催情水|变声电话|变声器|代开发票|死亡笔记|死亡日志|宋平顺|四种当|黄色图片|性爱电影|黄色电影|激情视频|激情图片|激情电影|十八禁|18禁|淫荡|毛片|脱衣舞|江姐问|董存瑞问|吴琼花问|拉登熟知中国|激情自拍|淫书|美女|监听器|淫奸|淫乱|同居社区|淫虫|右派|赌博专用|透视器|核武器|透视照片|拦截器|冰毒|K粉|麻古|万能钥匙|赌博粉|地磅仪|无网界|性网|军火|广安事件|炒股国歌|素女|裸体|乱伦|嫖妓|嫖鸡|小穴|淫魔|做鸡|口交|粗口歌|a片|性虐待|女优|包娃衣|耽美|金瓶梅|18N|丝袜写真|维多利亚包|花花公子|大禁|美腿写真|H漫画|隐私1图片|H电影|H动漫|龙虎豹|啄木鸟公司|巴拉斯|玉蒲团|无码|PLAYBOY|ANDREWBLAKE|少儿不宜|藏春阁|痴汉是犯罪|裸女对对碰|偷窥有罪|虎胆雄心|买春堂|护士24点|秘密潜入|欲望格斗|七宗罪|尾行|冠军足球经理|命令与征服|性感扑克|六月联盟|The3FeelOnline|采花堂|爱姐妹|露拉3D|吸血莱恩|hcartoon|梦幻麻将馆|足球经理2005|性福人生|禁忌试玩|沉默杀手|惊悚空间|美少女麻雀|杨思敏|人工少女|电车之狼|Quake|监禁|臭作|欲望之血|性感沙滩|遗作|色狼网|ILLUSION|红河谷论坛|AV麻将|恐惧杀手|波动少女|MC军团|恐怖牢笼|反雷达测速|英语枪手|假钞|电话拦截|探测狗|手机跟踪|监听宝|针孔摄像|短信群发器|邮件群发器|信息群发器|反中游行|中国断交|大规模游行|中国政治力量|政府禁令|泽东|恩来|锦涛|小平|少奇|法轮|二奶|政府|上海交大|拆迁|水扁|301医院|达赖|明慧|美国之音|活体|蒙汗药|迷魂|迷奸|主席|总理|石靖|台湾|妈的|你妈|他妈|妈b|妈比|fuck|shit|我日|我操",
				filters: "[\\s\*×]*",
				flags: "ig",
				keywords: null
			},
			options || {});
		if (content != "")
		{
			if (options.keywords == null)
			{
				var replace = new RegExp("[^\|]+?", options.flags);
				options.keywords = options.words.replace(
					replace,
					function($1)
					{
						return $1 + options.filters;
					});
			}
			var match = new RegExp(options.keywords, options.flags);
			
			return content.match(match);
		}
		else
		{
			return null;
		}
	};
/*----jquery.validate.1.4.0.js----*/
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(7($){$.F($.2q,{1i:7(b){k(!6.C){b&&b.1U&&2r.1j&&1j.4p("37 2s, 4q\'t 1i, 4r 37");8}l c=$.12(6[0],\'r\');k(c){8 c}c=1V $.r(b,6[0]);$.12(6[0],\'r\',c);k(c.p.38){6.4s("1W, 4t").1k(".4u").39(7(){c.2t=u});6.2u(7(a){k(c.p.1U)a.4v();7 1X(){k(c.p.3a){c.p.3a.W(c,c.13);8 G}8 u}k(c.2t){c.2t=G;8 1X()}k(c.L()){k(c.1d){c.1x=u;8 G}8 1X()}1e{c.1Y();8 G}})}8 c},J:7(){k($(6[0]).2v(\'L\')){8 6.1i().L()}1e{l a=G;l b=$(6[0].L).1i();6.M(7(){a|=b.H(6)});8 a}},4w:7(a){l b={},$H=6;$.M(a.1y(/\\s/),7(){b[6]=$H.1z(6);$H.4x(6)});8 b},16:7(c,d){l e=6[0];k(c){l f=$.12(e.L,\'r\').p.16;l g=$.r.2w(e);1Z(c){17"20":$.F(g,$.r.1A(d));f[e.q]=g;2x;17"4y":k(!d){O f[e.q];8 g}l h={};$.M(d.1y(/\\s/),7(a,b){h[b]=g[b];O g[b]});8 h}}l i=$.r.3b($.F({},$.r.3c(e),$.r.3d(e),$.r.3e(e),$.r.2w(e)),e);k(i.X){l j=i.X;O i.X;i=$.F({X:j},i)}8 i},Y:7(t){8 6.4z(6.20(t).4A())}});$.F($.4B[":"],{4C:7(a){8!$.21(a.N)},4D:7(a){8!!$.21(a.N)},4E:7(a){8!a.3f}});$.18=7(b,c){k(P.C==1)8 7(){l a=$.3g(P);a.4F(b);8 $.18.1B(6,a)};k(P.C>2&&c.22!=3h){c=$.3g(P).4G(1)}k(c.22!=3h){c=[c]}$.M(c,7(i,n){b=b.3i(1V 3j("\\\\{"+i+"\\\\}","g"),n)});8 b};$.r=7(a,b){6.p=$.F({},$.r.2y,a);6.13=b;6.3k()};$.F($.r,{2y:{S:{},23:{},16:{},19:"3l",2z:"4H",1Y:u,3m:$([]),2A:$([]),38:u,3n:[],4I:7(a){6.3o=a;k(6.p.4J&&!6.4K){6.p.1C&&6.p.1C.W(6,a,6.p.19);6.1D(a).2B()}},4L:7(a){k(!6.1l(a)&&(a.q Z 6.1f||!6.E(a))){6.H(a)}},4M:7(a){k(a.q Z 6.1f||a==6.3p){6.H(a)}},4N:7(a){k(a.q Z 6.1f)6.H(a)},2C:7(a,b){$(a).24(b)},1C:7(a,b){$(a).2D(b)}},4O:7(a){$.F($.r.2y,a)},S:{X:"4P 3q 2v X.",1E:"I 2E 6 3q.",1F:"I K a J 1F 4Q.",1G:"I K a J 4R.",1m:"I K a J 1m.",26:"I K a J 1m (4S).",27:"3r 3s 3t 2F gü4T 4U 2F.",1H:"I K a J 1H.",28:"3r 3s 3t 4V 4W 2F.",1I:"I K 4X 1I",29:"I K a J 4Y 4Z.",3u:"I K 3v 50 N 51.",3w:"I K a N 52 a J 53.",14:$.18("I K 3x 54 2G {0} 2H."),1n:$.18("I K 55 56 {0} 2H."),2a:$.18("I K a N 3y {0} 3z {1} 2H 57."),2b:$.18("I K a N 3y {0} 3z {1}."),1o:$.18("I K a N 58 2G 3A 3B 3C {0}."),1p:$.18("I K a N 59 2G 3A 3B 3C {0}.")},3D:G,5a:{3k:7(){6.2c=$(6.p.2A);6.3E=6.2c.C&&6.2c||$(6.13);6.2d=$(6.p.3m).20(6.p.2A);6.1f={};6.5b={};6.1d=0;6.1a={};6.1q={};6.1J();l e=(6.23={});$.M(6.p.23,7(c,d){$.M(d.1y(/\\s/),7(a,b){e[b]=c})});l f=6.p.16;$.M(f,7(a,b){f[a]=$.r.1A(b)});7 1r(a){l b=$.12(6[0].L,"r");b.p["3F"+a.1s]&&b.p["3F"+a.1s].W(b,6[0])}$(6.13).1r("3G 3H 5c",":2I, :5d, :5e, 2e, 5f",1r).1r("39",":3I, :3J",1r)},L:7(){6.3K();$.F(6.1f,6.1t);6.1q=$.F({},6.1t);k(!6.J())$(6.13).3L("1q-L.1i",[6]);6.1g();8 6.J()},3K:7(){6.2J();Q(l i=0,11=(6.2f=6.11());11[i];i++){6.2g(11[i])}8 6.J()},H:7(a){a=6.2K(a);6.3p=a;6.2L(a);6.2f=$(a);l b=6.2g(a);k(b){O 6.1q[a.q]}1e{6.1q[a.q]=u}k(!6.3M()){6.15.Y(6.2d)}6.1g();8 b},1g:7(b){k(b){$.F(6.1t,b);6.R=[];Q(l c Z b){6.R.Y({1b:b[c],H:6.2h(c)[0]})}6.1h=$.3N(6.1h,7(a){8!(a.q Z b)})}6.p.1g?6.p.1g.W(6,6.1t,6.R):6.3O()},2M:7(){k($.2q.2M)$(6.13).2M();6.1f={};6.2J();6.2N();6.11().2D(6.p.19)},3M:7(){8 6.2i(6.1q)},2i:7(a){l b=0;Q(l i Z a)b++;8 b},2N:7(){6.2O(6.15).2B()},J:7(){8 6.3P()==0},3P:7(){8 6.R.C},1Y:7(){k(6.p.1Y){3Q{$(6.3R()||6.R.C&&6.R[0].H||[]).1k(":5g").3S()}3T(e){}}},3R:7(){l a=6.3o;8 a&&$.3N(6.R,7(n){8 n.H.q==a.q}).C==1&&a},11:7(){l a=6,2P={};8 $([]).20(6.13.11).1k(":1W").1K(":2u, :1J, :5h, [5i]").1K(6.p.3n).1k(7(){!6.q&&a.p.1U&&2r.1j&&1j.3l("%o 5j 3x q 5k",6);k(6.q Z 2P||!a.2i($(6).16()))8 G;2P[6.q]=u;8 u})},2K:7(a){8 $(a)[0]},2Q:7(){8 $(6.p.2z+"."+6.p.19,6.3E)},1J:7(){6.1h=[];6.R=[];6.1t={};6.1L=$([]);6.15=$([]);6.1x=G;6.2f=$([])},2J:7(){6.1J();6.15=6.2Q().Y(6.2d)},2L:7(a){6.1J();6.15=6.1D(a)},2g:7(a){a=6.2K(a);k(6.1l(a)){a=6.2h(a.q)[0]}l b=$(a).16();l c=G;Q(T Z b){l d={T:T,2R:b[T]};3Q{l f=$.r.1M[T].W(6,$.21(a.N),a,d.2R);k(f=="1N-1O"){c=u;5l}c=G;k(f=="1a"){6.15=6.15.1K(6.1D(a));8}k(!f){6.3U(a,d);8 G}}3T(e){6.p.1U&&2r.1j&&1j.5m("5n 5o 5p 5q H "+a.3V+", 2g 3v \'"+d.T+"\' T");5r e;}}k(c)8;k(6.2i(b))6.1h.Y(a);8 u},3W:7(a,b){k(!$.1u)8;l c=6.p.2S?$(a).1u()[6.p.2S]:$(a).1u();8 c.S&&c.S[b]},3X:7(a,b){l m=6.p.S[a];8 m&&(m.22==3Y?m:m[b])},3Z:7(){Q(l i=0;i<P.C;i++){k(P[i]!==2j)8 P[i]}8 2j},2k:7(a,b){8 6.3Z(6.3X(a.q,b),6.3W(a,b),a.5s||2j,$.r.S[b],"<40>5t: 5u 1b 5v Q "+a.q+"</40>")},3U:7(a,b){l c=6.2k(a,b.T);k(1c c=="7")c=c.W(6,b.2R,a);6.R.Y({1b:c,H:a});6.1t[a.q]=c;6.1f[a.q]=c},2O:7(a){k(6.p.2l)a.Y(a.5w(6.p.2l));8 a},3O:7(){Q(l i=0;6.R[i];i++){l a=6.R[i];6.p.2C&&6.p.2C.W(6,a.H,6.p.19);6.2T(a.H,a.1b)}k(6.R.C){6.1L.Y(6.2d)}k(6.p.1v){Q(l i=0;6.1h[i];i++){6.2T(6.1h[i])}}k(6.p.1C){Q(l i=0,11=6.41();11[i];i++){6.p.1C.W(6,11[i],6.p.19)}}6.15=6.15.1K(6.1L);6.2N();6.2O(6.1L).42()},41:7(){8 6.2f.1K(6.43())},43:7(){8 $(6.R).44(7(){8 6.H})},2T:7(a,b){l c=6.1D(a);k(c.C){c.2D().24(6.p.19);c.1z("45")&&c.46(b)}1e{c=$("<"+6.p.2z+"/>").1z({"Q":6.2U(a),45:u}).24(6.p.19).46(b||"");k(6.p.2l){c=c.2B().42().5x("<"+6.p.2l+">").5y()}k(!6.2c.5z(c).C)6.p.47?6.p.47(c,$(a)):c.5A(a)}k(!b&&6.p.1v){c.2I("");1c 6.p.1v=="1P"?c.24(6.p.1v):6.p.1v(c)}6.1L.Y(c)},1D:7(a){8 6.2Q().1k("[@Q=\'"+6.2U(a)+"\']")},2U:7(a){8 6.23[a.q]||(6.1l(a)?a.q:a.3V||a.q)},1l:7(a){8/3I|3J/i.U(a.1s)},2h:7(c){l d=6.13;8 $(5B.5C(c)).44(7(a,b){8 b.L==d&&b.q==c&&b||48})},1Q:7(a,b){1Z(b.49.4a()){17\'2e\':8 $("4b:2s",b).C;17\'1W\':k(6.1l(b))8 6.2h(b.q).1k(\':3f\').C}8 a.C},4c:7(a,b){8 6.2V[1c a]?6.2V[1c a](a,b):u},2V:{"5D":7(a,b){8 a},"1P":7(a,b){8!!$(a,b.L).C},"7":7(a,b){8 a(b)}},E:7(a){8!$.r.1M.X.W(6,$.21(a.N),a)&&"1N-1O"},4d:7(a){k(!6.1a[a.q]){6.1d++;6.1a[a.q]=u}},4e:7(a,b){6.1d--;k(6.1d<0)6.1d=0;O 6.1a[a.q];k(b&&6.1d==0&&6.1x&&6.L()){$(6.13).2u()}},2m:7(a){8 $.12(a,"2m")||$.12(a,"2m",5E={2W:48,J:u,1b:6.2k(a,"1E")})}},1R:{X:{X:u},1F:{1F:u},1G:{1G:u},1m:{1m:u},26:{26:u},27:{27:u},1H:{1H:u},28:{28:u},1I:{1I:u},29:{29:u}},4f:7(a,b){a.22==3Y?6.1R[a]=b:$.F(6.1R,a)},3d:7(a){l b={};l c=$(a).1z(\'5F\');c&&$.M(c.1y(\' \'),7(){k(6 Z $.r.1R){$.F(b,$.r.1R[6])}});8 b},3e:7(a){l b={};l c=$(a);Q(T Z $.r.1M){l d=c.1z(T);k(d){b[T]=d}}k(b.14&&/-1|5G|5H/.U(b.14)){O b.14}8 b},3c:7(a){k(!$.1u)8{};l b=$.12(a.L,\'r\').p.2S;8 b?$(a).1u()[b]:$(a).1u()},2w:7(a){l b={};l c=$.12(a.L,\'r\');k(c.p.16){b=$.r.1A(c.p.16[a.q])||{}}8 b},3b:7(d,e){$.M(d,7(a,b){k(b===G){O d[a];8}k(b.2X||b.2n){l c=u;1Z(1c b.2n){17"1P":c=!!$(b.2n,e.L).C;2x;17"7":c=b.2n.W(e,e);2x}k(c){d[a]=b.2X!==2j?b.2X:u}1e{O d[a]}}});$.M(d,7(a,b){d[a]=$.5I(b)?b(e):b});$.M([\'1n\',\'14\',\'1p\',\'1o\'],7(){k(d[6]){d[6]=2Y(d[6])}});$.M([\'2a\',\'2b\'],7(){k(d[6]){d[6]=[2Y(d[6][0]),2Y(d[6][1])]}});k($.r.3D){k(d.1p&&d.1o){d.2b=[d.1p,d.1o];O d.1p;O d.1o}k(d.1n&&d.14){d.2a=[d.1n,d.14];O d.1n;O d.14}}k(d.S){O d.S}8 d},1A:7(a){k(1c a=="1P"){l b={};$.M(a.1y(/\\s/),7(){b[6]=u});a=b}8 a},5J:7(a,b,c){$.r.1M[a]=b;$.r.S[a]=c;k(b.C<3){$.r.4f(a,$.r.1A(a))}},1M:{X:7(a,b,c){k(!6.4c(c,b))8"1N-1O";1Z(b.49.4a()){17\'2e\':l d=$("4b:2s",b);8 d.C>0&&(b.1s=="2e-5K"||($.2Z.30&&!(d[0].5L[\'N\'].5M)?d[0].2I:d[0].N).C>0);17\'1W\':k(6.1l(b))8 6.1Q(a,b)>0;5N:8 a.C>0}},1E:7(d,e,f){k(6.E(e))8"1N-1O";l g=6.2m(e);k(!6.p.S[e.q])6.p.S[e.q]={};6.p.S[e.q].1E=1c g.1b=="7"?g.1b(d):g.1b;k(g.2W!==d){g.2W=d;l h=6;6.4d(e);l i={};i[e.q]=d;$.31({1G:f,4g:"32",4h:"1i"+e.q,5O:"5P",12:i,1v:7(a){k(!a){l b={};b[e.q]=a||h.2k(e,"1E");h.1g(b)}1e{l c=h.1x;h.2L(e);h.1x=c;h.1h.Y(e);h.1g()}g.J=a;h.4e(e,a)}});8"1a"}1e k(6.1a[e.q]){8"1a"}8 g.J},1n:7(a,b,c){8 6.E(b)||6.1Q(a,b)>=c},14:7(a,b,c){8 6.E(b)||6.1Q(a,b)<=c},2a:7(a,b,c){l d=6.1Q(a,b);8 6.E(b)||(d>=c[0]&&d<=c[1])},1p:7(a,b,c){8 6.E(b)||a>=c},1o:7(a,b,c){8 6.E(b)||a<=c},2b:7(a,b,c){8 6.E(b)||(a>=c[0]&&a<=c[1])},1F:7(a,b){8 6.E(b)||/^((([a-z]|\\d|[!#\\$%&\'\\*\\+\\-\\/=\\?\\^V`{\\|}~]|[\\v-\\w\\x-\\y\\A-\\B])+(\\.([a-z]|\\d|[!#\\$%&\'\\*\\+\\-\\/=\\?\\^V`{\\|}~]|[\\v-\\w\\x-\\y\\A-\\B])+)*)|((\\4i)((((\\2o|\\1S)*(\\33\\4j))?(\\2o|\\1S)+)?(([\\4k-\\5Q\\4l\\4m\\5R-\\5S\\4n]|\\5T|[\\5U-\\5V]|[\\5W-\\5X]|[\\v-\\w\\x-\\y\\A-\\B])|(\\\\([\\4k-\\1S\\4l\\4m\\33-\\4n]|[\\v-\\w\\x-\\y\\A-\\B]))))*(((\\2o|\\1S)*(\\33\\4j))?(\\2o|\\1S)+)?(\\4i)))@((([a-z]|\\d|[\\v-\\w\\x-\\y\\A-\\B])|(([a-z]|\\d|[\\v-\\w\\x-\\y\\A-\\B])([a-z]|\\d|-|\\.|V|~|[\\v-\\w\\x-\\y\\A-\\B])*([a-z]|\\d|[\\v-\\w\\x-\\y\\A-\\B])))\\.)+(([a-z]|[\\v-\\w\\x-\\y\\A-\\B])|(([a-z]|[\\v-\\w\\x-\\y\\A-\\B])([a-z]|\\d|-|\\.|V|~|[\\v-\\w\\x-\\y\\A-\\B])*([a-z]|[\\v-\\w\\x-\\y\\A-\\B])))\\.?$/i.U(b.N)},1G:7(a,b){8 6.E(b)||/^(5Y?|5Z):\\/\\/(((([a-z]|\\d|-|\\.|V|~|[\\v-\\w\\x-\\y\\A-\\B])|(%[\\1T-f]{2})|[!\\$&\'\\(\\)\\*\\+,;=]|:)*@)?(((\\d|[1-9]\\d|1\\d\\d|2[0-4]\\d|25[0-5])\\.(\\d|[1-9]\\d|1\\d\\d|2[0-4]\\d|25[0-5])\\.(\\d|[1-9]\\d|1\\d\\d|2[0-4]\\d|25[0-5])\\.(\\d|[1-9]\\d|1\\d\\d|2[0-4]\\d|25[0-5]))|((([a-z]|\\d|[\\v-\\w\\x-\\y\\A-\\B])|(([a-z]|\\d|[\\v-\\w\\x-\\y\\A-\\B])([a-z]|\\d|-|\\.|V|~|[\\v-\\w\\x-\\y\\A-\\B])*([a-z]|\\d|[\\v-\\w\\x-\\y\\A-\\B])))\\.)+(([a-z]|[\\v-\\w\\x-\\y\\A-\\B])|(([a-z]|[\\v-\\w\\x-\\y\\A-\\B])([a-z]|\\d|-|\\.|V|~|[\\v-\\w\\x-\\y\\A-\\B])*([a-z]|[\\v-\\w\\x-\\y\\A-\\B])))\\.?)(:\\d*)?)(\\/((([a-z]|\\d|-|\\.|V|~|[\\v-\\w\\x-\\y\\A-\\B])|(%[\\1T-f]{2})|[!\\$&\'\\(\\)\\*\\+,;=]|:|@)+(\\/(([a-z]|\\d|-|\\.|V|~|[\\v-\\w\\x-\\y\\A-\\B])|(%[\\1T-f]{2})|[!\\$&\'\\(\\)\\*\\+,;=]|:|@)*)*)?)?(\\?((([a-z]|\\d|-|\\.|V|~|[\\v-\\w\\x-\\y\\A-\\B])|(%[\\1T-f]{2})|[!\\$&\'\\(\\)\\*\\+,;=]|:|@)|[\\60-\\61]|\\/|\\?)*)?(\\#((([a-z]|\\d|-|\\.|V|~|[\\v-\\w\\x-\\y\\A-\\B])|(%[\\1T-f]{2})|[!\\$&\'\\(\\)\\*\\+,;=]|:|@)|\\/|\\?)*)?$/i.U(b.N)},1m:7(a,b){8 6.E(b)||!/62|63/.U(1V 64(a))},26:7(a,b){8 6.E(b)||/^\\d{4}[\\/-]\\d{1,2}[\\/-]\\d{1,2}$/.U(a)},27:7(a,b){8 6.E(b)||/^\\d\\d?\\.\\d\\d?\\.\\d\\d\\d?\\d?$/.U(a)},1H:7(a,b){8 6.E(b)||/^-?(?:\\d+|\\d{1,3}(?:,\\d{3})+)(?:\\.\\d+)?$/.U(a)},28:7(a,b){8 6.E(b)||/^-?(?:\\d+|\\d{1,3}(?:\\.\\d{3})+)(?:,\\d+)?$/.U(a)},1I:7(a,b){8 6.E(b)||/^\\d+$/.U(a)},29:7(a,b){k(6.E(b))8"1N-1O";k(/[^0-9-]+/.U(a))8 G;l c=0,e=0,2p=G;a=a.3i(/\\D/g,"");Q(n=a.C-1;n>=0;n--){l d=a.65(n);l e=66(d,10);k(2p){k((e*=2)>9)e-=9}c+=e;2p=!2p}8(c%10)==0},3w:7(a,b,c){c=1c c=="1P"?c:"67|68?g|69";8 6.E(b)||a.6a(1V 3j(".("+c+")$","i"))},3u:7(a,b,c){8 a==$(c).6b()}}})})(34);(7($){l c=$.31;l d={};$.31=7(a){a=$.F(a,$.F({},$.6c,a));l b=a.4h;k(a.4g=="32"){k(d[b]){d[b].32()}8(d[b]=c.1B(6,P))}8 c.1B(6,P)}})(34);(7($){$.M({3S:\'3G\',6d:\'3H\'},7(a,b){$.1w.35[b]={6e:7(){k($.2Z.30)8 G;6.6f(a,$.1w.35[b].36,u)},6g:7(){k($.2Z.30)8 G;6.6h(a,$.1w.35[b].36,u)},36:7(e){P[0]=$.1w.2E(e);P[0].1s=b;8 $.1w.1X.1B(6,P)}}});$.F($.2q,{1r:7(c,d,e){8 6.6i(c,7(a){l b=$(a.4o);k(b.2v(d)){8 e.1B(b,P)}})},6j:7(a,b){8 6.3L(a,[$.1w.2E({1s:a,4o:b})])}})})(34);',62,392,'||||||this|function|return||||||||||||if|var||||settings|name|validator|||true|u00A0|uD7FF|uF900|uFDCF||uFDF0|uFFEF|length||optional|extend|false|element|Please|valid|enter|form|each|value|delete|arguments|for|errorList|messages|method|test|_|call|required|push|in||elements|data|currentForm|maxlength|toHide|rules|case|format|errorClass|pending|message|typeof|pendingRequest|else|submitted|showErrors|successList|validate|console|filter|checkable|date|minlength|max|min|invalid|delegate|type|errorMap|metadata|success|event|formSubmitted|split|attr|normalizeRule|apply|unhighlight|errorsFor|remote|email|url|number|digits|reset|not|toShow|methods|dependency|mismatch|string|getLength|classRuleSettings|x09|da|debug|new|input|handle|focusInvalid|switch|add|trim|constructor|groups|addClass||dateISO|dateDE|numberDE|creditcard|rangelength|range|labelContainer|containers|select|currentElements|check|findByName|objectLength|undefined|defaultMessage|wrapper|previousValue|depends|x20|bEven|fn|window|selected|cancelSubmit|submit|is|staticRules|break|defaults|errorElement|errorLabelContainer|hide|highlight|removeClass|fix|ein|than|characters|text|prepareForm|clean|prepareElement|resetForm|hideErrors|addWrapper|rulesCache|errors|parameters|meta|showLabel|idOrName|dependTypes|old|param|Number|browser|msie|ajax|abort|x0d|jQuery|special|handler|nothing|onsubmit|click|submitHandler|normalizeRules|metadataRules|classRules|attributeRules|checked|makeArray|Array|replace|RegExp|init|error|errorContainer|ignore|lastActive|lastElement|field|Bitte|geben|Sie|equalTo|the|accept|no|between|and|or|equal|to|autoCreateRanges|errorContext|on|focusin|focusout|radio|checkbox|checkForm|triggerHandler|numberOfInvalids|grep|defaultShowErrors|size|try|findLastActive|focus|catch|formatAndAdd|id|customMetaMessage|customMessage|String|findDefined|strong|validElements|show|invalidElements|map|generated|html|errorPlacement|null|nodeName|toLowerCase|option|depend|startRequest|stopRequest|addClassRules|mode|port|x22|x0a|x01|x0b|x0c|x7f|target|warn|can|returning|find|button|cancel|preventDefault|removeAttrs|removeAttr|remove|setArray|get|expr|blank|filled|unchecked|unshift|slice|label|onfocusin|focusCleanup|blockFocusCleanup|onfocusout|onkeyup|onclick|setDefaults|This|address|URL|ISO|ltiges|Datum|eine|Nummer|only|credit|card|same|again|with|extension|more|at|least|long|less|greater|prototype|valueCache|keyup|password|file|textarea|visible|image|disabled|has|assigned|continue|log|exception|occured|when|checking|throw|title|Warning|No|defined|parents|wrap|parent|append|insertAfter|document|getElementsByName|boolean|previous|class|2147483647|524288|isFunction|addMethod|multiple|attributes|specified|default|dataType|json|x08|x0e|x1f|x21|x23|x5b|x5d|x7e|https|ftp|uE000|uF8FF|Invalid|NaN|Date|charAt|parseInt|png|jpe|gif|match|val|ajaxSettings|blur|setup|addEventListener|teardown|removeEventListener|bind|triggerEvent'.split('|'),0,{}))
/*----jquery.validate.methods.1.4.0.js----*/
jQuery.validator.addMethod("maxWords", function(value, element, params) { 
    return this.optional(element) || value.match(/\b\w+\b/g).length < params; 
}, "Please enter {0} words or less."); 
 
jQuery.validator.addMethod("minWords", function(value, element, params) { 
    return this.optional(element) || value.match(/\b\w+\b/g).length >= params; 
}, "Please enter at least {0} words."); 
 
jQuery.validator.addMethod("rangeWords", function(value, element, params) { 
    return this.optional(element) || value.match(/\b\w+\b/g).length >= params[0] && value.match(/bw+b/g).length < params[1]; 
}, "Please enter between {0} and {1} words.");


jQuery.validator.addMethod("letterswithbasicpunc", function(value, element) {
	return this.optional(element) || /^[a-z-.,()'\"\s]+$/i.test(value);
}, "Letters or punctuation only please");  

jQuery.validator.addMethod("alphanumeric", function(value, element) {
	return this.optional(element) || /^\w+$/i.test(value);
}, "Letters, numbers, spaces or underscores only please");  

jQuery.validator.addMethod("lettersonly", function(value, element) {
	return this.optional(element) || /^[a-z]+$/i.test(value);
}, "Letters only please"); 

jQuery.validator.addMethod("nowhitespace", function(value, element) {
	return this.optional(element) || /^\S+$/i.test(value);
}, "No white space please"); 

jQuery.validator.addMethod("ziprange", function(value, element) {
	return this.optional(element) || /^90[2-5]\d\{2}-\d{4}$/.test(value);
}, "Your ZIP-code must be in the range 902xx-xxxx to 905-xx-xxxx");

/**
* Return true, if the value is a valid vehicle identification number (VIN).
*
* Works with all kind of text inputs.
*
* @example <input type="text" size="20" name="VehicleID" class="{required:true,vinUS:true}" />
* @desc Declares a required input element whose value must be a valid vehicle identification number.
*
* @name jQuery.validator.methods.vinUS
* @type Boolean
* @cat Plugins/Validate/Methods
*/ 
jQuery.validator.addMethod(
	"vinUS",
	function(v){
		if (v.length != 17)
			return false;
		var i, n, d, f, cd, cdv;
		var LL    = ["A","B","C","D","E","F","G","H","J","K","L","M","N","P","R","S","T","U","V","W","X","Y","Z"];
		var VL    = [1,2,3,4,5,6,7,8,1,2,3,4,5,7,9,2,3,4,5,6,7,8,9];
		var FL    = [8,7,6,5,4,3,2,10,0,9,8,7,6,5,4,3,2];
		var rs    = 0;
		for(i = 0; i < 17; i++){
		    f = FL[i];
		    d = v.slice(i,i+1);
		    if(i == 8){
		        cdv = d;
		    }
		    if(!isNaN(d)){
		        d *= f;
		    }
		    else{
		        for(n = 0; n < LL.length; n++){
		            if(d.toUpperCase() === LL[n]){
		                d = VL[n];
		                d *= f;
		                if(isNaN(cdv) && n == 8){
		                    cdv = LL[n];
		                }
		                break;
		            }
		        }
		    }
		    rs += d;
		}
		cd = rs % 11;
		if(cd == 10){cd = "X";}
		if(cd == cdv){return true;}
		return false; 
	},
	"The specified vehicle identification number (VIN) is invalid."
);

/**
  * Return true, if the value is a valid date, also making this formal check dd/mm/yyyy.
  *
  * @example jQuery.validator.methods.date("01/01/1900")
  * @result true
  *
  * @example jQuery.validator.methods.date("01/13/1990")
  * @result false
  *
  * @example jQuery.validator.methods.date("01.01.1900")
  * @result false
  *
  * @example <input name="pippo" class="{dateITA:true}" />
  * @desc Declares an optional input element whose value must be a valid date.
  *
  * @name jQuery.validator.methods.dateITA
  * @type Boolean
  * @cat Plugins/Validate/Methods
  */
jQuery.validator.addMethod(
	"dateITA",
	function(value, element) {
		var check = false;
		var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/
		if( re.test(value)){
			var adata = value.split('/');
			var gg = parseInt(adata[0],10);
			var mm = parseInt(adata[1],10);
			var aaaa = parseInt(adata[2],10);
			var xdata = new Date(aaaa,mm-1,gg);
			if ( ( xdata.getFullYear() == aaaa ) && ( xdata.getMonth () == mm - 1 ) && ( xdata.getDate() == gg ) )
				check = true;
			else
				check = false;
		} else
			check = false;
		return this.optional(element) || check;
	}, 
	"Please enter a correct date"
);

/**
 * matches US phone number format 
 * 
 * where the area code may not start with 1 and the prefix may not start with 1 
 * allows '-' or ' ' as a separator and allows parens around area code 
 * some people may want to put a '1' in front of their number 
 * 
 * 1(212)-999-2345
 * or
 * 212 999 2344
 * or
 * 212-999-0983
 * 
 * but not
 * 111-123-5434
 * and not
 * 212 123 4567
 */
jQuery.validator.addMethod("phone", function(phone_number, element) {
    phone_number = phone_number.replace(/\s+/g, ""); 
	return this.optional(element) || phone_number.length > 9 &&
		phone_number.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
}, "Please specify a valid phone number");

// TODO check if value starts with <, otherwise don't try stripping anything
jQuery.validator.addMethod("strippedminlength", function(value, element, param) {
	return jQuery(value).text().length >= param;
}, jQuery.format("Please enter at least {0} characters"));

// same as email, but TLD is optional
jQuery.validator.addMethod("email2", function(value, element, param) {
	return this.optional(element) || /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i.test(value); 
}, jQuery.validator.messages.email);

// same as url, but TLD is optional
jQuery.validator.addMethod("url2", function(value, element, param) {
	return this.optional(element) || /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value); 
}, jQuery.validator.messages.url);

//	bad words
jQuery.validator.addMethod("badWord", function(value, element) { 
    return this.optional(element) || $.badWord(value) == null;
}, "Your words must not contain sensitive word."); 

//	special words
jQuery.validator.addMethod("specialWord", function(value, element) { 
    return this.optional(element) || /^[^<>]+$/.test(value);
}, "Your words must not contain special word."); 
