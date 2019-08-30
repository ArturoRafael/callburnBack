function restore(){$("#record1, #live1").removeClass("disabled");$(".one").addClass("disabled");Fr.voice.stop();}
$(document).ready(function(){

  $(document).on("click", "#record1:not(.disabled)", function(){
    elem = $(this);
    Fr.voice.record($("#live1").is(":checked"), function(){
      elem.addClass("disabled");
      $("#live1").addClass("disabled");
      $(".one").removeClass("disabled");
      $('#play1').show();
    });
  });
  
  $(document).on("click", "#play1:not(.disabled)", function(){
    Fr.voice.export(function(url){
      $("#audio1").attr("src", url);
      $("#audio1")[0].play();
      $('#totalModal2').show();
      var totalTime1 = document.getElementById('audio').duration;
      totalTime1 = Math.ceil(totalTime1);
      $('#totalTimeModal2').html(totalTime1);
    }, "URL");
    restore();
  });
  
  $(document).on("click", "#base641:not(.disabled)", function(){
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
          $('#record_donot').val(data);
        }
      })
    }, "base64");
    restore();
  });
})