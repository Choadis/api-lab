$(document).ready(function() {
  $("#buildsForm").submit(function(event) {
    var form = $(this);
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "http://localhost:3000/Slim/builds",
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:3000/slimClient/builds");
      }
    });
  });

  $("#buildsEditForm").submit(function(event) {
    // var id = window.location.href.split('/');
    var id = $("#id").attr("value");
    var form = $(this);
    console.log(form.serialize());
    event.preventDefault();
    $.ajax({
      type: "PUT",
      url: "http://localhost:3000/Slim/builds/" + id,
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:3000/slimClient/builds");
      }
    });
  });

  $( ".deletebtn" ).click(function() {
    var id = $(this).attr("data-id");
    if (confirm("Are you sure you wanna do that?")) {
      $.ajax({
        type: "DELETE",
        url: "http://localhost:3000/Slim/builds/" + id,
        success: function(data) {
          alert("You did it boi");
        }
      });
    }
  });
});
