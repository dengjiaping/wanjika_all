<?php

class InfoAction extends CommAction {

    public function index(){
        if($this->ispost()){
            if($_POST['is_draft'] != 1){
                if(empty($_POST['coverpic_url'])){
                    $this->ajaxReturn(0,'封面不能为空！',0);
                }
                if(empty($_POST['name'])){
                    $this->ajaxReturn(0,'菜谱名不能为空！',0);
                }
                if(empty($_POST['introduction'])){
                    $this->ajaxReturn(0,'简介不能为空！',0);
                }
                if(empty($_POST['materials'])){
                    $this->ajaxReturn(0,'用料不能为空！',0);
                }
                if(empty($_POST['cook_step'])){
                    $this->ajaxReturn(0,'步骤不能为空！',0);
                }
            }
//            $_SESSION['openid'] = GetOpenid();
            $_POST['userid'] = $_SESSION['openid'];
            $_POST['author'] = $_SESSION['nickname'];
            $_POST['isshow'] = 1;
            $_POST['share_url'] = 'testurl';
            $_POST['is_display'] = 1;
            if(empty($_POST['userid'])){
                $this->ajaxReturn(0,'userid不能为空！',0);
            }
            if(empty($_POST['share_url'])){
                $this->ajaxReturn(0,'share_url不能为空！',0);
            }
            if(empty($_POST['is_display'])){
                $this->ajaxReturn(0,'is_display不能为空！',0);
            }
            foreach($_POST['materials'] as $key=>$val){
                if($val["name"] == '' && $val["num"] == ''){
                    unset($_POST['materials'][$key]);
                }
            }
            foreach($_POST['cook_step'] as $key=>$val){
                if($val["step"] == '' && $val["src"] == ''){
                    unset($_POST['cook_step'][$key]);
                }
            }
            if(empty($_POST['materials'])){
                $_POST['materials'] = '';
            }
            else{
                $_POST['materials'] = json_encode($_POST['materials']);
            }
            if(empty($_POST['cook_step'])){
                $_POST['cook_step'] = '';
            }
            else{
                $_POST['cook_step'] = json_encode($_POST['cook_step']);
            }
//            if($_POST['content']){
//                $_POST['content']=stripslashes($_POST['content']);
//            }
//            if(empty($_POST['coverpic_url'])){
//                $url=get_pinyin($_POST['name']);
//                $_POST['coverpic_url']=$url[0];
//            }

            //mysql text换行符转译
            $strarray=array("\r\n","\n","\r");
            $_POST['introduction']=str_replace($strarray,'<br/>',$_POST['introduction']);
            $_POST['tips']=str_replace($strarray,'<br/>',$_POST['tips']);

            $con = M('cookbook')->add($_POST);
            if($con>0){
                //分享成功后的页面
                $id = mysql_insert_id();
                $url = "index.php?s=/".$id.".html";
                $this->ajaxReturn(1,$url,1);
            }else{
                $this->ajaxReturn(0,'添加失败！',0);
            }


        }else{
//            $this->pid=M('cookbook')->where("id='0'")->select();
//            $this->display();
        }

    }
    public function share(){
        $this->id=$_REQUEST['id'];
        $cookbook = M('cookbook')->where("id=$_REQUEST[id]")->select();
        $this->src=$_REQUEST['src'];
        $this->name=$_REQUEST['name'];
        $this->author=$cookbook[0]['author'];
        //mysql text换行符转译
        $cookbook[0]['introduction']=str_replace('<br/>','',$cookbook[0]['introduction']);
        $this->introduction=$cookbook[0]['introduction'];
        $this->display(':share');
    }
    public function edit(){
        if($this->ispost()){
            if($_POST['is_draft'] != 1){
                if(empty($_POST['coverpic_url'])){
                    $this->ajaxReturn(0,'封面不能为空！',0);
                }
                if(empty($_POST['name'])){
                    $this->ajaxReturn(0,'菜谱名不能为空！',0);
                }
                if(empty($_POST['introduction'])){
                    $this->ajaxReturn(0,'简介不能为空！',0);
                }
                if(empty($_POST['materials'])){
                    $this->ajaxReturn(0,'用料不能为空！',0);
                }
                if(empty($_POST['cook_step'])){
                    $this->ajaxReturn(0,'步骤不能为空！',0);
                }
            }
//            $_SESSION['openid'] = GetOpenid();
            $_POST['userid'] = $_SESSION['openid'];
            $_POST['author'] = $_SESSION['nickname'];
            $_POST['isshow'] = 1;
            $_POST['share_url'] = 'testurl';
            $_POST['is_display'] = 1;
            if(empty($_POST['userid'])){
                $this->ajaxReturn(0,'userid不能为空！',0);
            }
            if(empty($_POST['share_url'])){
                $this->ajaxReturn(0,'share_url不能为空！',0);
            }
            if(empty($_POST['is_display'])){
                $this->ajaxReturn(0,'is_display不能为空！',0);
            }

            foreach($_POST['materials'] as $key=>$val){
                if($val["name"] == '' && $val["num"] == ''){
                    unset($_POST['materials'][$key]);
                }
            }
            foreach($_POST['cook_step'] as $key=>$val){
                if($val["step"] == '' && $val["src"] == ''){
                    unset($_POST['cook_step'][$key]);
                }
            }
            if(empty($_POST['materials'])){
                $_POST['materials'] = '';
            }
            else{
                $_POST['materials'] = json_encode($_POST['materials']);
            }
            if(empty($_POST['cook_step'])){
                $_POST['cook_step'] = '';
            }
            else{
                $_POST['cook_step'] = json_encode($_POST['cook_step']);
            }

            //mysql text换行符转译
            $strarray=array("\r\n","\n","\r");
            $_POST['introduction']=str_replace($strarray,'<br/>',$_POST['introduction']);
            $_POST['tips']=str_replace($strarray,'<br/>',$_POST['tips']);

            $con = M('cookbook')->data($_POST)->save($_POST);
            if($con>0){
                //分享成功后的页面
                $id = $_POST['id'];
                $url = "index.php?s=/".$id.".html";
                $this->ajaxReturn(1,$url,1);
            }else{
                $this->ajaxReturn(0,'修改失败！',0);
            }
        }else{
        }
    }
    public function del(){
        if(M('cookbook')->delete($_POST['id'])){
            $this->ajaxReturn(1,'删除成功！',1);
        }else{
            $this->ajaxReturn(0,'删除失败！',0);
        }
    }
}