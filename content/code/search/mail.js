//
// изпраща формата за поща на Perl скрипт
// посредством синхронна ajax заявка
//
$('form#form_mail').submit(function(){
   //alert("running");
   var response = $.ajax({
            type     : "POST",
            cache    : false,
            url      : $(this).attr('action'),
            data     : $(this).serializeArray(),
            dataType : 'script',
            async: false,
    }).responseText;
   //alert(response);
   if ( /success/.test(response) )
      {  $('#form_mail')[0].reset();
      }

   return false;
});
