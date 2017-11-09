<div class="box">
 
 
 
 
 
 <div class="tit1 tit3">
       <span>
 <?php echo htmlspecialchars($this->_var['goods_cat']['name']); ?> 
   
    </span>
        <a class="more" href="<?php echo $this->_var['goods_cat']['url']; ?>">更多</a> 
      </div>
 
 
 
   <div class="blank"></div>
 
 
 
 
    <div class="clearfix goodsBox" style="border:none;">
      <?php $_from = $this->_var['cat_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
      <div class="goodsItem ">
           <a href="<?php echo $this->_var['goods']['url']; ?>"><img src="<?php echo $this->_var['goods']['thumb']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>" class="goodsimg" /></a><br />
           <p><a href="<?php echo $this->_var['goods']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>"><?php echo htmlspecialchars($this->_var['goods']['short_name']); ?></a></p>
          本店价：<?php if ($this->_var['goods']['promote_price'] != ""): ?><font class="f1"><?php echo $this->_var['goods']['promote_price']; ?></font><?php else: ?><font class="f1"><?php echo $this->_var['goods']['shop_price']; ?></font>
          <?php endif; ?>      <font class="market"><?php echo $this->_var['goods']['market_price']; ?></font> 
        </div>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
 
 
</div>
<div class="blank"></div>
