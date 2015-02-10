$.validator.setDefaults({
    highlight: function (element, errorClass, validClass) {
		//console.log(element.type);
		//alert("holiwi");
        if (element.type === "radio") {
            this.findByName(element.name).addClass(errorClass).removeClass(validClass);
        } else {
			
            $(element).closest('.form-group').removeClass('has-success has-feedback').addClass('has-error has-feedback');
            $(element).closest('.form-group').find('i.fa').remove();
            //if(element.type!="select-one")
			//	$(element).closest('.form-group').append('<i class=" fa fa-remove "></i>');
        }
    },
	
    unhighlight: function (element, errorClass, validClass) {
		//console.log(element.type);
        if (element.type === "radio") {
            this.findByName(element.name).removeClass(errorClass).addClass(validClass);
        } else {
            $(element).closest('.form-group').removeClass('has-error has-feedback').addClass('has-success has-feedback');
            $(element).closest('.form-group').find('i.fa').remove();
			//if(element.type!="select-one")
			//	$(element).closest('.form-group').append('<i class="glyphicon fa glyphicon-ok form-control-feedback"></i>');
        }
    }
});

$.validator.addMethod("usuario-unico", function(value, element) {
  $.ajax({
      type: "GET",
      url: "ajax/comprobarusuario",
      data: "usuario="+value,
	 // async: false, 
     // dataType:"html",
	  success: function(msg)
	  {
		  if(msg == "ocupado")
			 return false;  // already exists
		  return true;      // username is free to use
	   }
  })}
, "Nombre de usuario ya es&aacute; siendo usado");

  
$.validator.addMethod("rango", function (value, element) {
    var startDate =moment($('input[name=fecha_prestamo]').val(), "DD/MM/YYYY"); 
    var finDate =moment($('input[name=fecha_devolucion]').val(), "DD/MM/YYYY"); 
	return finDate.isAfter(startDate);
   
   if(startDate.diff(finDate)<0)
		return false;
	else
		return true;
}, "* la fecha de devoluci&oacute;n tiene que ser despu&eacute;s del pr&eacute;stamo");

$.validator.addMethod("rango", function (value, element) {
    var startDate =moment($('input[name=fecha_prestamo]').val(), "DD/MM/YYYY"); 
    var finDate =moment($('input[name=fecha_devolucion]').val(), "DD/MM/YYYY"); 
	return finDate.isAfter(startDate);
   
   if(startDate.diff(finDate)<0)
		return false;
	else
		return true;
}, "* la fecha de devoluci&oacute;n tiene que ser despu&eacute;s del pr&eacute;stamo");