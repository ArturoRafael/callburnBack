function restore(){$("#record, #live").removeClass("disabled");$(".one").addClass("disabled");Fr.voice.stop();}
$(document).ready(function(){
  $(document).on("click", "#record:not(.disabled)", function(){
    elem = $(this);
    Fr.voice.record($("#live").is(":checked"), function(){
      elem.addClass("disabled");
      $("#live").addClass("disabled");
      $(".one").removeClass("disabled");
      $('#play').show();
      $('#base64').show();
    });
  });
  
  $(document).on("click", "#play:not(.disabled)", function(){
    Fr.voice.export(function(url){
      $("#audio").attr("src", url);
      $("#audio")[0].play();
      $('#timer_records').show();
      var totalTime1 = document.getElementById('audio').duration;
      totalTime1 = Math.ceil(totalTime1);
      $('#totalTime1').html(totalTime1);
    }, "URL");
    restore();
  });
  
  $(document).on("click", "#base64:not(.disabled)", function(){
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
          $('#record_create').val(data);
        }
      })
    }, "base64");
    restore();
  });
})