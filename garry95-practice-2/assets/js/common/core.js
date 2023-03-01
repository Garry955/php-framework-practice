function preView(input) {
  if(input.files && input.files[0]) {
    var render = new FileReader();
    render.onload = function(e) {
      $('#img-view').attr('src', e.target.result);
    }
    render.readAsDataURL(input.files[0]);
  }
}

$('#pic-in').change(function() {
  preView(this);
})