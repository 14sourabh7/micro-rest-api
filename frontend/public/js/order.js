var products = [];
var pid;
$(document).ready(function () {
  var statusfilter = "all";
  var date = $("#filterDate");

  $.ajax({
    url: "/order/getproducts",
    method: "POST",
    dataType: "json",
  }).done(function (data) {
    products = data;
    console.log(products);
    pid = products[0]._id.$oid;
    $("#pid").val(pid);
    var html = "";
    for (let i = 0; i < products.length; i++) {
      html += `
      <option name='product' id=${i} value='${i}'>${products[i].name}</option>
      `;
    }
    $("#products").html(html);
    if (products[0].variation) {
      displayVariation(products[0].variation);
    }
  });

  $("#products").click(function () {
    var idx = $(this).val();
    pid = products[idx]._id.$oid;
    $("#pid").val(pid);
    $("#stock").attr("max", Number(products[idx].stock));
    if (products[idx].variation) {
      displayVariation(products[idx].variation);
    } else {
      $(".variation").html("");
    }
  });

  $("#date").change(function () {
    if ($(this).val() == "custom") {
      $(".customDate").attr("disabled", false);
    } else {
      $(".customDate").attr("disabled", true);
    }
  });
});

function displayVariation(variation) {
  var html = `<label for="variation" class="form-label">Select Variaant </label><select name='variation' class='btn'>`;
  Object.entries(variation).map(function (item) {
    html += `
        <option  name='variation' value="${item[0]}">${item[0]}</option>
        `;
  });
  html += "<select>";
  $(".variation").html(html);
}
