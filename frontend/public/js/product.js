$(document).ready(function () {
  $(".viewProduct").click(function () {
    var id = $(this).data("id");
    $.ajax({
      url: "/index/viewProduct",
      data: { id: id },
      method: "POST",
      dataType: "json",
    }).done(function (data) {
      $("#pid").html("Product - " + data._id.$oid);
      var html = `
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
      <input type="hidden" name="id" value=${data._id.$oid} >
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

      $(".productData").html(html);
    });
  });
});
