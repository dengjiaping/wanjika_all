function postrev(url){
	if($("input[name='name']").val()==false){
							$("input[name='name']").focus();
							return false
				}
				if($("input[name='email']").val()==false){
							$("input[name='email']").focus();
							return false
				}
				if($("input[name='tel']").val()==false){
							$("input[name='tel']").focus();
							return false
				}
				if($("#con").val()==false){
							$("#con").focus();
							return false
				}
				
				$.ajax({
					type:'post',
					url:url,
					data:{
						name:$("input[name='name']").val(),
						email:$("input[name='email']").val(),
						tel:$("input[name='tel']").val(),
						content:$("#con").val(),
						infoid:$("input[name='infoid']").val(),
					},
					success:function(msg){
						if(msg.status=='1'){
							alert(msg.info);
							window.location.reload();
						}else{
						alert(msg.info);
						}
					},
					
				});	
	
}