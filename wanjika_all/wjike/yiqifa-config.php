<?php
	/**
	* �����ļ�
	* ==========================================================================
	* ˵����
	* 		���ļ��б��������˵�Ƚ���Ҫ����������ݱ������ݲ�����Ҫ�Լ����ú�����
	* 
	* ==========================================================================
	*@author lsj
	*@version 0.2
	*/


	//Ĭ�ϻ���(cid)
	define("default_campaign_id", "18432");
	
	//Ĭ�ϵ�Ŀ���ַ(url)��һ��Ĭ�ϵ�����ҳ
	define("default_target", "www.wjike.com");
	
	//cookie������(���鱣��������������.yiqifa.com)
	define("union_cookie_domain",".wjike.com");
	
	//Ĭ�ϻ����
	define("default_channel", "cps");
	
	//���뷽ʽ(�����һ��sdk������GBK�ı��룬������ǵĻ�����UTF-8�����޸��������)
	define("default_charset", "UTF-8");
	
	//cookie����
	define("union_cookie_name","yiqifa");
	
    //cookie��Ч��,��λΪ����(30��)
    define("union_cookie_maxage",2592000);
    
    //�����������cookie����(������ͬ��Ĺ�˾)��ͷ����β���Ƕ���
    define("clean_cookie_names",",yiqifa,linkt,");	
			
	//����ʱ��,��λΪ����
	define("connect_timeout",3000);
	
	//��Ӧʱ��,��λΪ����
	define("read_timeout", 3000);
	
	//�Ƿ����IP����,true����ip��false������ip
	define("limit_ip",false);

	//������ʵ�ip��ַ
	define("ip_list","127.0.0.3,127.0.0.2");

	//�Ƿ����ǩ����֤,trueǩ����֤��false������ǩ����֤
	define("is_sign",false);

	//ÿһ���ӿڶ��������ôһ��ֵ����Ҫ����صļ������㹵ͨҪ�á���interIdΪ�����ã���ʽ�лID����Ҫ�������á�
	define("interId","565d68ff7c5cd0b77aa20842");
?>