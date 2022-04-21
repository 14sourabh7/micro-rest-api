var inputFields = [
  { label: "name", type: "text" },
  { label: "category name", type: "text" },
  { label: "price", type: "number" },
  { label: "stock", type: "number" },
];
var additional = [];
var variation = [];
var variationFields = [];
$(document).ready(function () {
  $("#addBtn").click(function () {
    var label = $("#addLabel").val();
    var type = $("#addType").val();
    if (label && type) {
      if (additional.indexOf(label) === -1) {
        additional.push(label);
        var html = `
            <div>
             <label for="${label}" class="form-label">${label}</label>
            <input type="${type}" class="form-control" name='additional[${label}]' required>
            <button class='deleteAdd' data-idx=${additional.indexOf(
              label
            )}>delete</button>
        </div>
        `;
        $("#addForm").append(html);
      }
    }
  });

  $("#addForm").on("click", ".deleteAdd", function () {
    $(this).parent().remove();
    additional.splice($(this).data("idx"), 1);
  });

  $("#addVar").click(function () {
    var label = $("#variationLabel").val();
    var value = $("#variationValue").val();
    var price = $("#variationPrice").val();
    var fields = "";
    if (label && value && price) {
      if (variation.indexOf(label) === -1) {
        variation.push(label);
        for (var i = 0; i < variationFields.length; i++) {
          fields += `  <label>${variationFields[i].name}
          <input 
          type="text" 
          class="form-control" 
          name='variation[${label}][${variationFields[i].name}]' value='${variationFields[i].value}' required></label>`;
        }
        var html =
          `
           <div class="mb-3">
            <label for="${label}" class="form-label">${label}</label>
            ` +
          fields +
          `
            
            <label>price
<input type="number" class="form-control" name='variation[${label}][price]' value='${price}' required></label>
            <button class='varDel' data-idx=${additional.indexOf(
              value
            )}>delete</button>
        </div>
        `;
        $("#addForm").append(html);
        variationFields = [];
        $("#variationHtml").html("");
      }
    }
  });
  $("#addField").click(function () {
    var name = $("#variationValueName").val();
    var value = $("#variationValue").val();
    var html = "";
    if (name && value) {
      var check = variationFields.filter((x) => x.name == name);
      if (check.length < 1) {
        variationFields.push({ name: name, value: value });
        html += `<p class='bg-light'>${name} - ${value} <span class='text-danger' id='variationFieldDel' data-name="${name}">delete</span> </p> `;
      }
    }
    $("#variationHtml").append(html);
  });

  $("#variationHtml").on("click", "#variationFieldDel", function () {
    var name = $(this).data("name");
    variationFields = variationFields.filter((x) => x.name !== name);
    $(this).parent().remove();
  });

  $("#addForm").on("click", ".varDel", function (e) {
    $(this).parent().remove();
    variation.splice($(this).data("idx"), 1);
  });

  $(".viewProduct").click(function () {
    var id = $(this).data("id");
    $.ajax({
      url: "/index/viewProduct",
      data: { id: id },
      method: "POST",
      dataType: "json",
    }).done(function (data) {
      data = data[0];
      $("#pid").html("Product - " + data._id.oid);
      var html = `
      <input type="hidden" name="id" value=${data._id.oid} >
      <table class='table'>
      <tr>
        <th>
          Name
        </th>
        <td>
        <input type="text" name="name" value="${data.name}">
        </td>
      </tr>
      <tr>
        <th>
        Category
        </th>
        <td>
       <input type="text" name="category" value="${data.category}">
        </td>
      </tr>
      <tr>
        <th>
        Price
        </th>
        <td>
         <input type="number" name="price" value="${data.price}">
        </td>
      </tr>
      <tr>
        <th>
        Stock
        </th>
        <td>
         <input type="number" name="stock" value="${data.stock}">
        </td>
      </tr>
      </table>
      
      `;

      if (data.additional) {
        console.log(data.additional);
        html += ` <h3>Additonal</h3><table class='table'>`;
        Object.entries(data.additional).map(function (item) {
          html += `
          <tr>
          <th> ${item[0]}</th><td>  <input type="text" name="additional[${item[0]}]" value="${item[1]}"></td>
          </tr>
          `;
        });
        html += "</table>";
      }

      if (data.variation) {
        html += ` <h3>Variations</h3>`;
        Object.entries(data.variation).map(function (item) {
          html += ` <h5>${item[0]}</h5><table class='table'>`;
          Object.entries(item[1]).map(function (it) {
            html += `
            <tr> <th> ${it[0]}</th><td><input 
            type="text" 
              name='variation[${item[0]}][${it[0]}]' value='${it[1]}'> </td> 
            `;
          });
          html += `</table>`;
        });
      }
      html += `<input type="submit" name='btn' class='btn btn-success btn-lg float-right mx-2' value="update"> 
       <input type="submit" name='btn' value="delete"  class='btn btn-danger btn-lg float-right'>`;
      $(".productData").html(html);
    });
  });
});
