
  <div id="category_tree">
  <div class="tit"><img src="themes/ecmoban_ehaier/images/category_tit.gif"> </div>
  <div class="clearfix" style=" border:1px solid #ccc; border-top:none">
    <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat');if (count($_from)):
    foreach ($_from AS $this->_var['cat']):
?>
     <dl>
     <dt><a href="<?php echo $this->_var['cat']['url']; ?>"><?php echo htmlspecialchars($this->_var['cat']['name']); ?></a></dt>
	 
     <dd>
	 <?php $_from = $this->_var['cat']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');if (count($_from)):
    foreach ($_from AS $this->_var['child']):
?>
     <a href="<?php echo $this->_var['child']['url']; ?>"><?php echo htmlspecialchars($this->_var['child']['name']); ?></a>
      
     <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
       </dd>
       </dl>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
  </div>
</div>
<div class="blank"></div>
