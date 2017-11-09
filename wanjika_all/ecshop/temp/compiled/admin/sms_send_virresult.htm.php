<?php if ($this->_var['full_page']): ?>
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>
<div class="form-div">
    <form action="mysms.php?act=virtuallist" method="post">
        <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
        手机号<input name="tel" type="text" id="tel" size="15" value=<?php echo $this->_var['tel']; ?>>
        用户名<input name="user_name" type="text" id="user_name" size="15" value=<?php echo $this->_var['user_name']; ?>>
        <select name="status" id="status">
            <option value="-1">请选择</option>
            <option value="0" <?php if ($this->_var['status'] == 0 && $this->_var['status'] != null): ?>selected="selected"<?php endif; ?>>失败</option>
            <option value="1" <?php if ($this->_var['status'] == 1): ?>selected="selected"<?php endif; ?>>成功</option>
        </select>
        <input type="submit" value="<?php echo $this->_var['lang']['button_search']; ?>" class="button" />
    </form>
</div>

<div class="list-div" id="listDiv">
<?php endif; ?>
    <table cellpadding="3" cellspacing="1">
            <tr>
                <th>用户ID</th>
                <th>用户名</th>
                <th>手机号</th>
                <th>短信内容</th>
                <th>卡类型</th>
                <th>发送结果</th>
            </tr>
                <?php $_from = $this->_var['sendresult_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('okey', 'sendresult');if (count($_from)):
    foreach ($_from AS $this->_var['okey'] => $this->_var['sendresult']):
?>
            <tr>
                <td align="center" valign="top" nowrap="nowrap"><?php echo $this->_var['sendresult']['user_id']; ?></td>
                <td align="center" valign="top" nowrap="nowrap"><?php echo $this->_var['sendresult']['user_name']; ?></td>
                <td align="center" valign="top" nowrap="nowrap"><?php echo $this->_var['sendresult']['tel']; ?></td>
                <td align="center" valign="top" nowrap="nowrap"><?php echo $this->_var['sendresult']['content']; ?></td>
                <td align="center" valign="top" nowrap="nowrap"><?php echo $this->_var['sendresult']['card_type']; ?></td>
                <td align="center" valign="top" nowrap="nowrap"><?php if ($this->_var['sendresult']['status']): ?>成功<?php else: ?>失败<?php endif; ?></td>
            </tr>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </table>
    <table id="page-table" cellspacing="0">
            <tr>
                <td align="right" nowrap="true">
                    <?php echo $this->fetch('page.htm'); ?>
                </td>
            </tr>
        </table>
<?php if ($this->_var['full_page']): ?>
</div>

<script language="JavaScript">
    listTable.recordCount = <?php echo $this->_var['record_count']; ?>;
    listTable.pageCount = <?php echo $this->_var['page_count']; ?>;

    <?php $_from = $this->_var['filter']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
    listTable.filter.<?php echo $this->_var['key']; ?> = '<?php echo $this->_var['item']; ?>';
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</script>
<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>