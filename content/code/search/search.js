//
// 
// асинхронна ajax заявка
//
$('form#form_search').submit(function()
   {
    //alert("search.js");
    //alert(document.location.host);

    //$('#form_gif').show();

    //document.getElementById('form_gif').style.display = 'block';;

    var script_url = $(this).attr('action');

    //alert(document.location.host);
    //var domain = 'im.rivesta.tk';
    //var cors_url = '';
    //if ( document.location.host == domain )
    //   { cors_url = 'https://rivesta.herokuapp.com/';
    //   }
    //script_url =  cors_url + script_url;
    
    //alert(script_url);

    var data_all = $(this).serialize();
    //var data_all = $(this).serializeArray();
    //alert(data_all);

    var type_rq = 'POST';

    var response = $.ajax(
       {
         url      : script_url,
         type     : type_rq,
         cache    : false,
         data     :  data_all,

         //contentType: "application/x-www-form-urlencoded;charset=UTF-8", //default
         //contentType: "text/plain; charset=UTF-8",

         //dataType : "text/plain; charset=UTF-8",
         //dataType : 'script',  //това пречи на json отговора
         
         //crossDomain: true,
         //dataType: 'jsonp',
         
         async    : true,
         success  : function(data)
            {
              //alert("ajax success");

              //alert(data);
              console.log(data);

              var data_j = JSON.parse(data);
              //alert(data_j.success);

              //location.href = 'search.html';
              //window.location.replace('search.html');

              if ( data_j.success == 'true' )
                 { if ( data_j.result.search == 'ok' )
                      { window.location = 'search.html';
                      }
                   if ( data_j.result.search == 'none' )
                      { alert("Няма намерени.");
                      }
                 }
              //setTimeout(function(){location.reload();}, 5000);
              //alert("ok");
            },
         error    : function(xhr, status, error)
            { 
              alert(document.location.host + ": ajax error");
              //alert(xhr.responseText);

              console.log("readyState: " + xhr.readyState);
              console.log("responseText: "+ xhr.responseText);
              console.log("status: " + xhr.status);
              console.log("text status: " + xhr.textStatus);
              console.log("error: " + error);
            },
         complete : function(data)
            {
              // object Object
              //alert("!" + data + "!");

              // това дава резултат
              //var data_str = JSON.stringify(data);
              //alert("!" + data_str + "!");

              //var data_j = JSON.parse(data);
              //alert(data_j.responseText.success);

              //setTimeout(function()
              //   { window.location.reload();
              //   }, 100);    
            },
        }).responseText;
    //alert(response);

    //$('#form_gif').hide();

    //document.getElementById('form_gif').style.display = 'none';;

    //if ( /success/.test(response) )
    //   { $('#form_search')[0].reset();
    //     //document.getElementById('form_mail').reset();
    //   }

    return false;
   });
