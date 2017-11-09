<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
<meta name="Generator" content="ECSHOP v2.7.3" />
        <base target="_blank" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
        <meta name="Description" content="<?php echo $this->_var['description']; ?>" />
        
        <title>
            <?php echo $this->_var['page_title']; ?>
        </title>
        
        
        
        <link rel="shortcut icon" href="favicon.ico" />
        <link rel="icon" href="animated_favicon.gif" type="image/gif" />
        <link href="<?php echo $this->_var['ecs_css_path']; ?>" rel="stylesheet" type="text/css" />
        <link rel="alternate" type="application/rss+xml" title="RSS|<?php echo $this->_var['page_title']; ?>"
        href="<?php echo $this->_var['feed_url']; ?>" />
    </head>
    
    <body>
         <?php echo $this->smarty_insert_scripts(array('files'=>'common.js,index.js')); ?>
        <?php echo $this->fetch('library/page_header.lbi'); ?>
        <div class="block clearfix">
            

            <div class="AreaL">
                <?php echo $this->fetch('library/category_tree2.lbi'); ?>
                
<?php echo $this->fetch('library/new_articles.lbi'); ?>

                

                
            </div>
            <div class="AreaR">
                <?php echo $this->fetch('library/index_ad.lbi'); ?>
                
<?php $this->assign('cat_goods',$this->_var['cat_goods_44']); ?><?php $this->assign('goods_cat',$this->_var['goods_cat_44']); ?><?php echo $this->fetch('library/cat_goods.lbi'); ?>
<?php $this->assign('cat_goods',$this->_var['cat_goods_43']); ?><?php $this->assign('goods_cat',$this->_var['goods_cat_43']); ?><?php echo $this->fetch('library/cat_goods.lbi'); ?>
<?php $this->assign('cat_goods',$this->_var['cat_goods_311']); ?><?php $this->assign('goods_cat',$this->_var['goods_cat_311']); ?><?php echo $this->fetch('library/cat_goods.lbi'); ?>
<?php $this->assign('cat_goods',$this->_var['cat_goods_230']); ?><?php $this->assign('goods_cat',$this->_var['goods_cat_230']); ?><?php echo $this->fetch('library/cat_goods.lbi'); ?>
<?php $this->assign('cat_goods',$this->_var['cat_goods_310']); ?><?php $this->assign('goods_cat',$this->_var['goods_cat_310']); ?><?php echo $this->fetch('library/cat_goods.lbi'); ?>

		</div>
                
            </div>
            <div class="blank">
            </div>
        </div>
        
        <?php echo $this->fetch('library/help.lbi'); ?>
        <?php echo $this->fetch('library/page_footer.lbi'); ?>
    </body>

</html>
