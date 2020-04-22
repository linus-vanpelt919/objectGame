$(function() {
   $('.js-btn-action01').hover(function(){
       $(this).prev("span").css('display','block');
   },function() {//はなした時の処理
       $(this).prev("span").css('display','none');
   });
    $('.js-btn-action02').hover(function(){
        $(this).prev("span").css('display','block');
    },function() {//はなした時の処理
        $(this).prev("span").css('display','none');
    });
    $('.js-btn-action03').hover(function(){
        $(this).prev("span").css('display','block');
    },function() {//はなした時の処理
        $(this).prev("span").css('display','none');
    });
    $('.js-btn-action04').hover(function(){
        $(this).prev("span").css('display','block');
    },function() {//はなした時の処理
        $(this).prev("span").css('display','none');
    });

     // 攻撃時に点滅させる Ajax処理を先にする
    // $('.js-attack-motion').on('click',function(){
    //    $('.js-img-motion').fadeOut(1000,function(){$('.js-img-motion').fadeIn(1000)});
    // });

});