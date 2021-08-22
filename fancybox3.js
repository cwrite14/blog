$(document).ready(function()
{ 
  //var video = document.getElementById('video');
  //video.addEventListener('click',function(){
  //  video.play();
  //},false);

  // това работи
  //jQuery("#Dialog1Text").text('bullshit');
  //$("#Dialog1" ).dialog({title: "Инфо"});

  //alert("document.ready");

  //=====================================================================
  // combobox category - get value
  //=====================================================================
  var page =
  { 
    cat00: 'index',
    cat01: 'herbs',
    cat02: 'cats',
    cat04: 'cat_world',
    cat05: 'arms',
    cat06: 'health',
    cat07: 'misc',
    cat09: 'archeology',
    cat11: 'climate',
    cat12: 'egypt',
    cat13: 'places',
    cat14: 'tapestries',
    cat15: 'beauty',
    cat16: 'weather',
    cat17: 'drugs',
    cat18: 'favorites',
    cat21: 'in_memoriam_bel',
    cat22: 'in_memoriam_mr',
    cat23: 'in_memoriam_mtm',
    cat24: 'in_memoriam_tm',
  };

  var arr = location.href.split('/');
  var page_cur = arr[arr.length-1];

  for(var key in page)
  {  
    if ( page_cur.indexOf(page[key]) > -1 )
    { 
      $("#catg_holder").val(key);
      //alert(page[key]);
      break;
    }
  }

  //=====================================================================
  // combobox category - changed - set page
  //=====================================================================
  $('select[name=catg_combo]').change(function()
  {
    var val_id = $(this).val();

    var page_new = page[val_id] + ".html";

    var rivesta_domain = 'http://rivesta.tk/'; 

    window.location = page_new;

  });

  //=====================================================================
  // височина на вертикална снимка
  //=====================================================================
  //var winWidth = $('.gallery5').width();
  //var maxH = 0.7 * winWidth;
  //var jpg_width = $('.gallery5 img').width();
  //var jpg_height = $('.gallery5 img').height();
  //alert(jpg_width + 'x' + jpg_height);
  //if ( jpg_width < jpg_height )
  //   { var jpg_h_new = 0.7 * winWidth;
  //     $('.gallery3 img').css({'height':jpg_h_new +'px'});
  //     var jpg_w_new = jpg_width / jpg_height * jpg_h_new;
  //     $('.gallery3 img').css({'width':jpg_w_new +'px'});
  //   }

  //=====================================================================
  // 
  //=====================================================================
  //var use_fancybox_rq = localStorage.getItem('use_fancybox_rq');
  //if ( use_fancybox_rq === null )
  //   { use_fancybox_rq = 1;
  //     localStorage.setItem('use_fancybox_rq', 1);
  //   }
  //$('#checkbox_fancy').checked = use_fancybox_rq ? true : false;

  //=====================================================================
  // force document.ready on buttons back/forward
  //=====================================================================
  jQuery(window).bind("unload", function(){var x=1;});

  //=====================================================================
  // update time
  //=====================================================================
  function update_time()
  {
    var dt = new Date($.now());
    var min = dt.getMinutes();
    var min1 = min < 10 ? '0' : '';
    var time = dt.getHours() + ":" + min1 + min;
    $('#rtime').text('↻' + time);
  }

  //=====================================================================
  // reload every 5 minutes
  //=====================================================================
  setTimeout(function()
  {
    if ( location.href.indexOf('index.html') > -1 )
    { 
      window.location.reload();
    }
  }, 1000 * 60 * 5);

  //update_time();

  resize_handler();

  $('[data-fancybox]').fancybox(
  {
    //arrows : false,

    onInit: function (instance, current, e)
       {
       },

    afterLoad : function() 
       {
       }
  });
    
});
function resize_handler()
   {
     // височина на вертикална снимка -
     //    според ширината
     var gal_width = $('.gallery4').width();
     var k1 = 0.672; // 70% width
     var jpg_height = k1 * gal_width;
     $('.gallery4 img').css({'height':jpg_height +'px'});
     var img_width = $('.gallery4 img').width();
     var marg = img_width / 2;
     $('.gallery4 img').css({'margin-left':marg +'px'});
     $('.gallery4 img').css({'margin-right':marg +'px'});
   }
$(window).on('resize', resize_handler);
