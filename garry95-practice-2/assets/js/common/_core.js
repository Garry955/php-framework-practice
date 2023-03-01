var b = $('body');
var arr = [];
var head = $('header');
var menu = $('header .menus li a');
var cb = $('#toggler');
var menus = $('header .menus li:last-of-type');
var input = $('input[name="email"]');
var addEl = '<li><a href="#">YOLO</a></li>';
var lbl = $('label[for="toggler"]');
lbl.addClass("animate");


//lbl.text("#y0l0#");
//
//lbl.dblclick(function() {
//  $(this).prepend("#Swag");
//  $(this).append("Swag#");
//  var l = $(this).text().length;
//  if (l > 70) {
//    $(this).text("##RiP##");
//  } else if ($(this).mouseover(function() {
//    $(this).css({
//      top : "-=20"
//    })
//  }));
//  console.log(l);
//})

// lbl.mouseover(function() {
// $(this).text("###LUL###");
// $(this).css({
// top : "-=60",
// right : "-=40",
// height : "50",
// lineHeight : "50px",
// fontSize : "20px"
// })
// }).mouseout(function() {
// $(this).css({
// top : "+=120",
// right : "+=80"
// });
// var pt = $(this).css('top');
// var pr = $(this).css('right');
// console.log(pt, pr);
// if (pt > "400" && pt < "80") {
// $(this).css({
// top : "100px"
// })
// }
// if (pr > "700px" && pr < "40px") {
// $(this).css({
// right : "40px"
// })
// }
// })

// if (typeof jQuery != 'undefinde') {
// var v = jQuery.fn.jquery;
// cb.change(function() {
// console.log('jQuery version: ' + v);
// })
// }
//
//
// lbl.mouseover(function() {
// $(this).height(50).css({
// cursor : "no-drop",
// backgroundColor : "red",
// top : "-=10"
// }).$(this).text('LUL').mouseout(function() {
// $(this).height(25).css({
// cursor : "pointer",
// backgroundColor : "blue",
// color : "#fff",
// top : "+=10"
// })
// })
// })
//
// if (typeof jQuery != 'undefined') {
// var v = jQuery.fn.jquery;
// console.log(v);
// }

// cb.change( function() {
// ($(this).checked) ? menu.unwrap : menu.wrap("<a
// href=\"www.google.com\"></a>");
// console.log($(this.checked))
// })

// lbl.mouseover( function(){
// $(this).height(50).css({
// cursor : "no-drop",
// backgroundColor : "red"
// }).mouseout( function(){
// $(this).height(25).css({
// cursor: "pointer",
// backgroundColor: "blue",
// color: "#fff"
// });
// });
// });
// cb.change( function(){
// lbl.css({
// color : "#000",
// backgroundColor: "green",
// top : "-=10"
// })
// });

//
// $('label[for="toggler"]').addClass("animate").mouseover(function() {
// $(this).height(50).css({
// cursor : "no-drop",
// backgroundColor : "red"
// }).mouseout( function() {
// $(this).height(25).css({
// cursor : "pointer",
// backgroundColor : "blue"
// })
// });
// });

// $('*').click( function() {
// var c = $( this ).height();
// alert($(this), "height: " + c);
// })

// cb.change( function() {
// $(this).attr('yolo',!this.checked);
// console.log("checked:" + this.checked,"yolo:" + $(this).attr('yolo'));
// })

// cb.change( function() {
// if ( this.checked ) {
// var p = prompt("Remove class:","YOLO/SWAG");
// if ( p == "YOLO" ) {
// head.toggleClass("YOLO")
// } else if ( p == "SWAG" ) {
// menu.toggleClass("SWAG")
// } else if ( p == "YOLO/SWAG" ) {
// head.toggleClass("YOLO");
// menu.toggleClass("SWAG");
// }
// }
// });
