
function AdminGrid()
{
	this.m_index = 0;
    this.m_sIdField = 'id';
	this.m_aActions = {
        "create": false,
        "edit": false,
        "block": false,
        "unblock": false,
        "delete": false
    };

    this.setIdField = function(sIdField){
        this.m_sIdField = sIdField;
    };

	this.setAction = function(sType, sAction){
        this.m_aActions[sType] = sAction;
    };
	
	this.init = function( index )
	{
    	this.m_index = index;
		
		var _self = this;
		

        $("#ui-admin-grid-"+this.m_index+"-action").val(this.m_sAction);
        
        $("#ui-admin-grid-"+this.m_index+" .ui-row").hover(function(){
            $(this).addClass("hover");
        }, function(){
            $(this).removeClass("hover");
        });

        $("#ui-admin-grid-"+this.m_index+"-check-all").click(function(){
            if ($(this).prop("checked")) {
                $("#ui-admin-grid-"+_self.m_index+" .id").prop("checked", true);
                $("#ui-admin-grid-"+_self.m_index+" .ui-row").addClass("checked");
            }
            else {
                $("#ui-admin-grid-"+_self.m_index+" .id").prop("checked", false);
                $("#ui-admin-grid-"+_self.m_index+" .ui-row").removeClass("checked");
            }
            
            _self.update();
        });
        
        $("#ui-admin-grid-"+this.m_index+" .id").change(function(){
			
            $("#ui-admin-grid-"+_self.m_index+"-check-all").prop("checked", $("#ui-admin-grid-"+_self.m_index+" .id").length == $("#ui-admin-grid-"+_self.m_index+" .id:checked").length);
            
            if ($(this).prop("checked")) 
                $("#ui-admin-grid-"+_self.m_index+"-row-" + $(this).val()).addClass("checked");
            else 
                $("#ui-admin-grid-"+_self.m_index+"-row-" + $(this).val()).removeClass("checked");
            
            _self.update();
        });
        
        
        $("#ui-admin-grid-"+this.m_index+" .id:checked").each(function(){
            $("#ui-admin-grid-"+_self.m_index+"-row-" + $(this).val()).addClass("checked");
        });
        
        _self.update();
    };
	
	this.update = function(){
    
        $checkedID = $("#ui-admin-grid-"+this.m_index+" .id:checked");
        
        var aID = [];
        $checkedID.each(function(){
            aID.push($(this).val());
        });
        $("#ui-admin-grid-"+this.m_index+"-id").val(aID.join(","));
        
        var $edit = $("#ui-admin-grid-"+this.m_index+"-toolbar-edit"), $unblock = $("#ui-admin-grid-"+this.m_index+"-toolbar-unblock"), $block = $("#ui-admin-grid-"+this.m_index+"-toolbar-block"), $delete = $("#ui-admin-grid-"+this.m_index+"-toolbar-delete");
        
        var iLen = $checkedID.length;
        if (iLen == 0) {
            $edit.removeClass("able").addClass("disable");
            $unblock.removeClass("able").addClass("disable");
            $block.removeClass("able").addClass("disable");
            $delete.removeClass("able").addClass("disable");
        }
        else {
            $delete.removeClass("disable").addClass("able");
            if (iLen == 1) 
                $edit.removeClass("disable").addClass("able");
            else 
                $edit.removeClass("able").addClass("disable");
            
            if ($("#ui-admin-grid-"+this.m_index+" .checked .block").length > 0) 
                $unblock.removeClass("disable").addClass("able");
            else 
                $unblock.removeClass("able").addClass("disable");
            
            if ($("#ui-admin-grid-"+this.m_index+" .checked .unblock").length > 0) 
                $block.removeClass("disable").addClass("able");
            else 
                $block.removeClass("able").addClass("disable");
        }
    };
    
    this.create = function(){
        $("#ui-admin-grid-"+this.m_index+"-id").val("0");
        $("#ui-admin-grid-"+this.m_index+"-form").attr("action", this.m_aActions['create']).submit();
    };
    
    this.block = function(id){
		if (id != 0) $("#ui-admin-grid-"+this.m_index+"-id").val(id);
		$("#ui-admin-grid-"+this.m_index+"-form").attr("action", this.m_aActions['block']).submit();
    };
    
    this.unblock = function(id){
        if (id != 0) $("#ui-admin-grid-"+this.m_index+"-id").val(id);
		$("#ui-admin-grid-"+this.m_index+"-form").attr("action", this.m_aActions['unblock']).submit();
    };
    
    this.edit = function(id){
        if (id != 0) $("#ui-admin-grid-"+this.m_index+"-id").val(id);
		$("#ui-admin-grid-"+this.m_index+"-form").attr("action", this.m_aActions['edit']).submit();
    };
    
    this.remove = function(id){
        if (!confirm("该操作不可恢复，确认删除吗?"))
            return false;
		if (id != 0) $("#ui-admin-grid-"+this.m_index+"-id").val(id);
		$("#ui-admin-grid-"+this.m_index+"-form").attr("action", this.m_aActions['delete']).submit();
    };
    
	this.filter = function(){
        $("#ui-admin-grid-"+this.m_index+"-form").attr("action", this.m_aActions['list']).submit();
    };
	
    this.orderBy = function(sField, sDir){
        $("#ui-admin-grid-"+this.m_index+"-id").remove();
        $("#ui-admin-grid-"+this.m_index+"-order-by").val(sField);
        $("#ui-admin-grid-"+this.m_index+"-order-by-dir").val(sDir);
        $("#ui-admin-grid-"+this.m_index+"-form").attr("action", this.m_aActions['list']).submit();
    };
    
    this.gotoPage = function(n){
        $("#ui-admin-grid-"+this.m_index+"-id").remove();
        $("#ui-admin-grid-"+this.m_index+"-page").val(n);
        $("#ui-admin-grid-"+this.m_index+"-action").val(this.m_sAction);
        $("#ui-admin-grid-"+this.m_index+"-form").attr("action", this.m_aActions['list']).submit();
    }	
	
}