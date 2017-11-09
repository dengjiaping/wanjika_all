

<div style="float:left">
<?php if ($this->_var['user_info']): ?>

<span><?php echo $this->_var['lang']['hello']; ?>，</span><span class="f4_b"><?php echo $this->_var['user_info']['username']; ?></span>    
[ <a href="user.php"><?php echo $this->_var['lang']['user_center']; ?></a> /  
 <a href="user.php?act=logout"><?php echo $this->_var['lang']['user_logout']; ?></a>  ]

<?php else: ?>
<span>嗨，欢迎来到本商城!</span>
  <a href="user.php">请登录</a> | 
 <a href="user.php?act=register">免费注册</a> |  
<?php endif; ?>

 
</div>