function loadContent(url, containerId) {
  jQuery.ajax({
    url: url,
    success: function (result) {
      if (result != "") {
        console.log(result);
        var container = $(containerId);
        if (container.find("tr").length > 0) {
          container.find("tr:last").after(result);
        } else {
          container.append(result);
        }
      }
    },
  });
}
