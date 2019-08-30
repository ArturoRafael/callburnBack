function restore(){$("#record2, #live2").removeClass("disabled");$(".one").addClass("disabled");Fr.voice.stop();}
$(document).ready(function(){

  $(document).on("click", "#record2:not(.disabled)", function(){
    elem = $(this);
    Fr.voice.record($("#live2").is(":checked"), function(){
      elem.addClass("disabled");
      $("#live2").addClass("disabled");
      $(".one").removeClass("disabled");
      $('#play2').show();
    });
  });
  
  $(document).on("click", "#play2:not(.disabled)", function(){
    Fr.voice.export(function(url){
      $("#audio2").attr("src", url);
      $("#audio2")[0].play();
      $('#totalModal1').show();
      var totalTime1 = document.getElementById('audio2').duration;
      totalTime1 = Math.ceil(totalTime1);
      $('#totalTimeModal1').html(totalTime1);
    }, "URL");
    restore();
  });
  
  $(document).on("click", "#base642:not(.disabled)", function(){
    var tok = $('.tok').val(),
        lang = $('#language').val();
    Fr.voice.export(function(url){
      var base64 = url.split(',');
      var base = base64[base64.length - 1];
      $.ajax({
        url:'/'+lang+'/base',
        method:'POST',
        data:{_token:tok,baseUrl:base},
        success:function(data){
          $('#record_callback').val(data);
        }
      })
    }, "base64");
    restore();
  });

});
