function fetchAndDisplay(url, containerId) {
  jQuery.ajax({
    url: url,
    success: function (result) {
      if (result != "" && $(containerId).length > 0) {
        $(containerId).val(result);
      }
    },
  });
}
