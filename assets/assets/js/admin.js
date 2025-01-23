jQuery(document).ready(function ($) {
  // Auto-refresh logs
  if ($("#import-logs").length) {
    setInterval(function () {
      $.ajax({
        url: csvImporterAdmin.ajax_url,
        data: {
          action: "refresh_import_logs",
          nonce: csvImporterAdmin.nonce,
        },
        success: function (response) {
          $("#import-logs").html(response);
        },
      });
    }, 5000);
  }

  // Clear logs
  $("#clear-logs").on("click", function () {
    if (confirm("Are you sure you want to clear the logs?")) {
      $.ajax({
        url: csvImporterAdmin.ajax_url,
        method: "POST",
        data: {
          action: "clear_import_logs",
          nonce: csvImporterAdmin.nonce,
        },
        success: function () {
          $("#import-logs").html("");
        },
      });
    }
  });
});
