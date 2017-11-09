<link href="themes/ecmoban_ehaier/qq/images/qq.css" rel="stylesheet" type="text/css" />
<script language='javascript' src='themes/ecmoban_ehaier/qq/ServiceQQ.js' type='text/javascript' charset='utf-8'></script>
<script type="text/javascript">
var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
</script>
<div id="topNav"  >
<div class="block">
<div class="f_l">
 <a  onClick="window.external.AddFavorite(location.href,document.title);">收藏本站</a>   
</div>
<div class="f_r log">
<ul>
    <li style="float:left;  "  class="clearfix">
       <?php echo $this->smarty_insert_scripts(array('files'=>'utils.js')); ?>
        <font id="ECS_MEMBERZONE">
       <?php 
$k = array (
  'name' => 'member_info',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?> </font>
       </li>
       <li style="float:left"   class="clearfix">  
     <?php if ($this->_var['navigator_list']['top']): ?>
        <?php $_from = $this->_var['navigator_list']['top']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');$this->_foreach['nav_top_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['nav_top_list']['total'] > 0):
    foreach ($_from AS $this->_var['nav']):
        $this->_foreach['nav_top_list']['iteration']++;
?>  
       <a style=" padding:0 5px;" href="<?php echo $this->_var['nav']['url']; ?>" <?php if ($this->_var['nav']['opennew'] == 1): ?> target="_blank" <?php endif; ?>><?php echo $this->_var['nav']['name']; ?></a>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> <?php endif; ?>
        </li>|
    </ul>
    <ul class="ul1" onmouseover="this.className='ul1 ul1_on'" onmouseout="this.className='ul1'">
        <a class="a1" href="user.php">我的账户</a>
        <div class="ul1_float">
        <a   target="_blank" href="user.php">我的账户</a>
        <a class="a1" href="user.php?act=order_list">我的订单</a>
        <a class="a1" href="user.php?act=message_list">我的留言</a>
        <a class="a1" href="user.php?act=collection_list">我的收藏</a>
        <a class="a1" href="user.php?act=affiliate">我的推荐</a>
        </div>
    </ul>
</div>

</div>
</div>


<div style=" clear:both"></div>

<div class="block header_new" >
 <div class="logo"><a href="index.php" name="top"><img style="float:left;" src="themes/ecmoban_ehaier/images/logo.gif" /></a></div>
  
<div class="hot_kw">
   <script type="text/javascript">
    
    <!--
    function checkSearchForm()
    {
        if(document.getElementById('keyword').value)
        {
            return true;
        }
        else
        {
           alert("<?php echo $this->_var['lang']['no_keywords']; ?>");
            return false;
        }
    }
    -->
    
    </script>
    <?php if ($this->_var['searchkeywords']): ?>
   <strong>热门搜索</strong>： 
   <?php $_from = $this->_var['searchkeywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'val');$this->_foreach['no'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['no']['total'] > 0):
    foreach ($_from AS $this->_var['val']):
        $this->_foreach['no']['iteration']++;
?>
   <a  href="search.php?keywords=<?php echo urlencode($this->_var['val']); ?>"><?php echo $this->_var['val']; ?></a>
   <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
   <?php endif; ?>
  </div>
  <div class="search">
   <form id="searchForm" name="searchForm" method="get" action="search.php" onSubmit="return checkSearchForm()" >
   <div class="search_input">
   <input name="keywords" type="text" id="keyword" value="搜索产品" onclick="javascript:this.value=''" class="B_input"/>   
   </div>
   <div class="submit">
<input name="imageField" type="submit" value=" " class="go" style="cursor:pointer;"  />
   </div>
    </form>
  </div>
 <div class="icons"><img src="themes/ecmoban_ehaier/images/50a211df07fd0f6c07000063.png" width="215" height="50" alt=""></div>    
</div>

 



<div class="g-menu">
<div class="menu-shadow-hack"></div>
  <div class="g-menu-wrap">
    <div class="all-catalog" >
      <a href="catalog.php" class="all-btn">所有商品分类<i class="arrow"></i></a>        
    </div>

  <div class="channel">
    <ul>
      <li  <?php if ($this->_var['navigator_list']['config']['index'] == 1): ?>  class="m-home" <?php endif; ?>> <a href="index.php" ><?php echo $this->_var['lang']['home']; ?></a></li>
        <?php $_from = $this->_var['navigator_list']['middle']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');$this->_foreach['no'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['no']['total'] > 0):
    foreach ($_from AS $this->_var['nav']):
        $this->_foreach['no']['iteration']++;
?>
      <li <?php if ($this->_var['nav']['active'] == 1): ?>  class="m-home" <?php endif; ?>><a href="<?php echo $this->_var['nav']['url']; ?>" <?php if ($this->_var['nav']['opennew'] == 1): ?>target="_blank" <?php endif; ?>    ><?php echo $this->_var['nav']['name']; ?></a>
      <span class="tag_hot">hot</span>

     </li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>


</ul>
</div>




<div class="min-cart"  id="ECS_CARTINFO">
<div class="min-cart-btn">
<div class="cart-count">
<span class="cart-count-wrap">
<a  href="flow.php"><?php 
$k = array (
  'name' => 'cart_info',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?></a>    
</span>  
</div> 
<div class="qujiesuan"><a  href="flow.php"> 去结算</a>   </div>    
</div>   
</div>




</div>
</div>





